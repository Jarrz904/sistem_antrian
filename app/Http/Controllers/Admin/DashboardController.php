<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan, User};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama Dashboard Admin (Ringkasan Statistik)
     */
    public function index()
    {
        $data = $this->getStatsData();
        $layanans = Layanan::all();

        return view('admin.dashboard', array_merge($data, ['layanans' => $layanans]));
    }

    /**
     * API untuk Realtime Update Statistik Dashboard
     */
    public function getRealtimeStats()
    {
        return response()->json($this->getStatsData());
    }

    /**
     * Logika internal untuk mengambil data statistik harian
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

    /*
    |--------------------------------------------------------------------------
    | FITUR KELOLA LAYANAN (CRUD) - DISESUAIKAN DENGAN MIGRATION USER
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar semua layanan
     */
    public function layananIndex()
    {
        // Menggunakan 'prefix' sebagai kolom pengurutan sesuai Migration Anda
        $layanan = Layanan::orderBy('prefix', 'asc')->get();
        return view('admin.layanan', compact('layanan'));
    }

    /**
     * Menyimpan layanan baru ke database
     */
    public function layananStore(Request $request)
    {
        $request->validate([
            'nama_layanan'    => 'required|string|max:255',
            'kode_layanan'    => 'required|alpha|max:1|unique:layanans,prefix', // Validasi ke kolom 'prefix'
            'is_nik_required' => 'required|boolean',
            'deskripsi'       => 'nullable|string'
        ]);

        Layanan::create([
            'nama_layanan'    => $request->nama_layanan,
            'prefix'          => strtoupper($request->kode_layanan), // Simpan input 'kode_layanan' ke kolom 'prefix'
            'is_nik_required' => $request->is_nik_required,
            'deskripsi'       => $request->deskripsi,
            // Jika Anda ingin menggunakan default icon bisa ditambahkan di sini
            'icon'            => 'fas fa-file-alt' 
        ]);

        return back()->with('success', 'Layanan baru berhasil ditambahkan!');
    }

    /**
     * Memperbarui data layanan yang sudah ada
     */
    public function layananUpdate(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        $request->validate([
            'nama_layanan'    => 'required|string|max:255',
            'kode_layanan'    => 'required|alpha|max:1|unique:layanans,prefix,' . $id,
            'is_nik_required' => 'required|boolean',
            'deskripsi'       => 'nullable|string'
        ]);

        $layanan->update([
            'nama_layanan'    => $request->nama_layanan,
            'prefix'          => strtoupper($request->kode_layanan),
            'is_nik_required' => $request->is_nik_required,
            'deskripsi'       => $request->deskripsi,
        ]);

        return back()->with('success', 'Data layanan berhasil diperbarui!');
    }

    /**
     * Menghapus layanan (dengan proteksi pengecekan relasi)
     */
    public function layananDestroy($id)
    {
        $layanan = Layanan::findOrFail($id);
        
        // Proteksi: Jangan hapus jika sudah ada antrian yang menggunakan layanan ini
        if ($layanan->queues()->exists()) {
             return back()->with('error', 'Layanan tidak bisa dihapus karena memiliki riwayat data antrian.');
        }

        $layanan->delete();
        return back()->with('success', 'Layanan berhasil dihapus!');
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR KELOLA DATA ANTRIAN (LOG & RIWAYAT)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan halaman riwayat antrian dengan filter
     */
    public function antrianIndex(Request $request)
    {
        $query = $this->applyFilters($request);
        $dataAntrian = $query->latest()->get();
        $layanans = Layanan::all();
        $users = User::where('role', 'petugas')->get();

        return view('admin.antrian_index', compact('dataAntrian', 'layanans', 'users'));
    }

    /**
     * Mengupdate data masyarakat di dalam antrian
     */
    public function updateAntrian(Request $request, $id)
    {
        $request->validate([
            'nama_pendaftar' => 'required|string|max:255',
            'nik'            => 'required|numeric|digits:16',
            'layanan_id'     => 'required|exists:layanans,id',
        ]);

        $antrian = Queue::findOrFail($id);
        $antrian->update([
            'nama_pendaftar' => $request->nama_pendaftar,
            'nik'            => $request->nik,
            'layanan_id'     => $request->layanan_id,
            'updated_at'     => Carbon::now('Asia/Jakarta')
        ]);

        return back()->with('success', 'Data masyarakat berhasil diperbarui!');
    }

    /**
     * Menghapus record antrian
     */
    public function deleteAntrian($id)
    {
        $antrian = Queue::findOrFail($id);
        $antrian->delete();

        return back()->with('success', 'Data antrian berhasil dihapus!');
    }

    /**
     * Mereset seluruh tampilan statistik hari ini menjadi nol
     */
    public function resetDisplay()
    {
        try {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            Queue::whereDate('created_at', $today)->delete();

            return back()->with('success', 'Sistem berhasil direset! Statistik hari ini kembali ke 0.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset sistem: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR EXPORT & HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * Export data antrian ke format CSV
     */
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
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns, ',');

            foreach ($antrian as $q) {
                $date = Carbon::parse($q->created_at)->timezone('Asia/Jakarta');
                fputcsv($file, [
                    $q->nomor_antrian,
                    $q->nama_pendaftar,
                    $q->nik . "\t", 
                    $q->layanan->nama_layanan ?? '-',
                    $date->translatedFormat('d F Y'),
                    $date->format('H:i'),
                    $q->loket->nama_loket ?? 'Belum Diproses',
                    $q->user->name ?? 'Belum Dipanggil',
                    ucfirst($q->status)
                ], ',');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper Function: Menerapkan filter pencarian data
     */
    private function applyFilters(Request $request)
    {
        $query = Queue::with(['layanan', 'loket', 'user']);

        if ($request->filled('prefix')) {
            $query->where('nomor_antrian', 'like', $request->prefix . '%');
        }

        if ($request->filled('layanan_id')) {
            $query->where('layanan_id', $request->layanan_id);
        }

        if ($request->filled('petugas_id')) {
            $query->where('user_id', $request->petugas_id);
        }

        if ($request->filled('tgl_mulai') && $request->filled('tgl_selesai')) {
            $query->whereBetween('created_at', [
                $request->tgl_mulai . ' 00:00:00',
                $request->tgl_selesai . ' 23:59:59'
            ]);
        }

        return $query;
    }
}