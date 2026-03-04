<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Set waktu sekarang ke Jakarta
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // 1. Data untuk Statistik (Card atas) - Filter hari ini Jakarta
        $totalAntrian = Queue::whereDate('created_at', $today)->count();
        $selesai = Queue::where('status', 'selesai')->whereDate('created_at', $today)->count();
        $lewat = Queue::where('status', 'lewat')->whereDate('created_at', $today)->count();

        // 2. Data untuk Tabel Dashboard (Hari ini saja)
        // Load relasi 'loket' agar bisa menampilkan petugas yang menangani
        $dataAntrian = Queue::with(['layanan', 'loket'])
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        $layanans = Layanan::all();

        return view('admin.dashboard', compact(
            'totalAntrian',
            'selesai',
            'lewat',
            'dataAntrian',
            'layanans'
        ));
    }

    /**
     * Halaman Kelola Semua Antrian (Riwayat)
     */
    public function antrianIndex()
    {
        // Load relasi layanan dan loket untuk riwayat lengkap
        $dataAntrian = Queue::with(['layanan', 'loket'])->latest()->get();
        $layanans = Layanan::all();

        return view('admin.antrian_index', compact('dataAntrian', 'layanans'));
    }

    /**
     * Fitur Update Data Antrian (Edit Masyarakat)
     */
    public function updateAntrian(Request $request, $id)
    {
        $request->validate([
            'nama_pendaftar' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16',
            'layanan_id' => 'required|exists:layanans,id',
        ]);

        $antrian = Queue::findOrFail($id);

        // Pastikan update_at juga mengikuti waktu Jakarta
        $antrian->update([
            'nama_pendaftar' => $request->nama_pendaftar,
            'nik' => $request->nik,
            'layanan_id' => $request->layanan_id,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        return back()->with('success', 'Data masyarakat berhasil diperbarui!');
    }

    /**
     * Fitur Hapus Data Antrian
     */
    public function deleteAntrian($id)
    {
        $antrian = Queue::findOrFail($id);
        $antrian->delete();

        return back()->with('success', 'Data antrian berhasil dihapus!');
    }

    /**
     * Fitur Export CSV dengan Detail Waktu Jakarta & Petugas
     */
    public function exportCSV()
    {
        // Nama file dengan timestamp Jakarta
        $fileName = 'rekap_antrian_' . Carbon::now('Asia/Jakarta')->format('Y-m-d_H-i') . '.csv';

        // Ambil data dengan eager loading 'user' (petugas) untuk performa
        $antrian = Queue::with(['layanan', 'loket', 'user'])->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        // Kolom sesuai permintaan Anda
        $columns = [
            'No Antrian',
            'Nama Pemohon',
            'NIK',
            'Layanan',
            'Waktu Daftar',
            'Loket',
            'Petugas yang Menanggapi',
            'Status'
        ];

        $callback = function () use ($antrian, $columns) {
            $file = fopen('php://output', 'w');

            // Tambahkan BOM agar Excel tidak berantakan (UTF-8)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $columns);

            foreach ($antrian as $q) {
                // Konversi created_at ke Jakarta
                $date = Carbon::parse($q->created_at)->timezone('Asia/Jakarta');

                // Format Waktu: 04 Maret 2026 14:30
                $waktuFull = $date->translatedFormat('d F Y') . ' ' . $date->format('H:i');

                fputcsv($file, [
                    $q->nomor_antrian,
                    $q->nama_pendaftar,
                    "'" . $q->nik, // Menjaga NIK tetap text di Excel
                    $q->layanan->nama_layanan ?? '-',
                    $waktuFull,
                    $q->loket->nama_loket ?? 'Belum Diproses',
                    $q->user->name ?? 'Belum Dipanggil', // Nama petugas dari relasi user
                    ucfirst($q->status)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}