<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan, User};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama Dashboard Admin
     */
    public function index()
    {
        $data = $this->getStatsData();
        $layanans = Layanan::all();

        return view('admin.dashboard', array_merge($data, ['layanans' => $layanans]));
    }

    /**
     * API untuk Realtime Update
     */
    public function getRealtimeStats()
    {
        return response()->json($this->getStatsData());
    }

    /**
     * Logika pengambilan data statistik
     */
    private function getStatsData()
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        return [
            'totalAntrian' => Queue::whereDate('created_at', $today)->count(),
            'selesai'      => Queue::where('status', 'selesai')->whereDate('created_at', $today)->count(),
            'lewat'        => Queue::where('status', 'lewat')->whereDate('created_at', $today)->count(),
            'dataAntrian'  => Queue::with(['layanan', 'loket'])
                                ->whereDate('created_at', $today)
                                ->orderBy('created_at', 'desc')
                                ->get()
        ];
    }

    /**
     * Fitur Reset Display & Statistik
     */
    public function resetDisplay()
    {
        try {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            Queue::whereDate('created_at', $today)->delete();

            return back()->with('success', 'Sistem berhasil direset! Statistik kembali ke 0.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset sistem: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Kelola Semua Antrian (Dengan Filter Otomatis)
     */
    public function antrianIndex(Request $request)
    {
        // Gunakan fungsi filter terpusat agar konsisten
        $query = $this->applyFilters($request);

        $dataAntrian = $query->latest()->get();
        $layanans = Layanan::all();
        $users = User::where('role', 'petugas')->get();

        return view('admin.antrian_index', compact('dataAntrian', 'layanans', 'users'));
    }

   public function exportCSV(Request $request)
{
    $fileName = 'rekap_antrian_' . Carbon::now('Asia/Jakarta')->format('Y-m-d_H-i') . '.csv';
    
    $query = $this->applyFilters($request);
    $antrian = $query->get();

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $columns = ['No Antrian', 'Nama Pemohon', 'NIK', 'Layanan', 'Tanggal', 'Jam', 'Loket', 'Petugas', 'Status'];

    $callback = function () use ($antrian, $columns) {
        $file = fopen('php://output', 'w');
        
        // 1. WAJIB: Tambahkan BOM UTF-8 agar Excel tidak bingung membaca format file
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // 2. Gunakan Standar Koma (,) sebagai pemisah agar Excel Global langsung membagi kolom
        fputcsv($file, $columns, ',');

        foreach ($antrian as $q) {
            $date = Carbon::parse($q->created_at)->timezone('Asia/Jakarta');
            
            fputcsv($file, [
                $q->nomor_antrian,
                $q->nama_pendaftar,
                // Tambahkan Tab (\t) di akhir NIK agar Excel menganggapnya teks murni dan tetap panjang
                $q->nik . "\t", 
                $q->layanan->nama_layanan ?? '-',
                $date->translatedFormat('d F Y'),
                $date->format('H:i'),
                $q->loket->nama_loket ?? 'Belum Diproses',
                $q->user->name ?? 'Belum Dipanggil',
                ucfirst($q->status)
            ], ','); // Pastikan di sini juga menggunakan koma
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

    /**
     * Helper Function untuk menerapkan filter (Private)
     */
    private function applyFilters(Request $request)
    {
        $query = Queue::with(['layanan', 'loket', 'user']);

        // Filter Huruf Depan (A, B, C)
        if ($request->filled('prefix')) {
            $query->where('nomor_antrian', 'like', $request->prefix . '%');
        }

        // Filter Berdasarkan Layanan
        if ($request->filled('layanan_id')) {
            $query->where('layanan_id', $request->layanan_id);
        }

        // Filter Berdasarkan Petugas yang menangani
        if ($request->filled('petugas_id')) {
            $query->where('user_id', $request->petugas_id);
        }

        // Filter Rentang Tanggal (Mulai - Selesai)
        if ($request->filled('tgl_mulai') && $request->filled('tgl_selesai')) {
            $query->whereBetween('created_at', [
                $request->tgl_mulai . ' 00:00:00',
                $request->tgl_selesai . ' 23:59:59'
            ]);
        }

        return $query;
    }

    /**
     * Fitur Update Data Antrian
     */
    public function updateAntrian(Request $request, $id)
    {
        $request->validate([
            'nama_pendaftar' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16',
            'layanan_id' => 'required|exists:layanans,id',
        ]);

        $antrian = Queue::findOrFail($id);
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
}