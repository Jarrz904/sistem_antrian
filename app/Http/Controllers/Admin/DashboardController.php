<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan, User, Setting}; // Pastikan model Setting ada jika digunakan untuk simpan status
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama Dashboard Admin
     */
    public function index()
    {
        $data = $this->getStatsData();
        $layanans = Layanan::all();

        // Ambil status sistem (default 'on' jika belum ada)
        $systemStatus = Cache::get('system_status', 'on');

        return view('admin.dashboard', array_merge($data, [
            'layanans' => $layanans,
            'systemStatus' => $systemStatus
        ]));
    }

    /**
     * API untuk Realtime Update Statistik Dashboard
     */
    public function getRealtimeStats()
    {
        $data = $this->getStatsData();
        $data['systemStatus'] = Cache::get('system_status', 'on');
        return response()->json($data);
    }

    /**
     * Fitur Buka/Tutup Sistem (ON/OFF)
     */
    public function toggleSystem(Request $request)
    {
        $status = $request->status === 'on' ? 'on' : 'off';

        // Simpan status di Cache agar akses cepat oleh Petugas & User
        Cache::forever('system_status', $status);

        $message = $status === 'on' ? 'Sistem Pendaftaran dibuka.' : 'Sistem Pendaftaran ditutup.';
        return back()->with('success', $message);
    }

    /**
     * API Status Sistem untuk dibaca Dashboard Petugas secara Realtime
     */
    public function getSystemStatusApi()
    {
        return response()->json([
            'status' => Cache::get('system_status', 'on')
        ]);
    }

    /**
     * Logika internal statistik harian
     */
    private function getStatsData()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Query dasar untuk efisiensi
        $baseQuery = Queue::whereDate('created_at', $today);

        return [
            'totalAntrian'       => (clone $baseQuery)->count(),
            'menunggu'           => (clone $baseQuery)->where('status', 'menunggu')->count(),
            'dipanggil'          => (clone $baseQuery)->where('status', 'dipanggil')->count(),
            'selesaidiproses'    => (clone $baseQuery)->where('status', 'selesai diproses')->count(),
            'pengambilanDokumen' => (clone $baseQuery)->where('status', 'pengambilan_dokumen')->count(),
            'dilewati'           => (clone $baseQuery)->where('status', 'dilewati')->count(),
            'selesai'            => (clone $baseQuery)->where('status', 'selesai')->count(),
            'dataAntrian'        => Queue::with(['layanan', 'loket', 'petugas'])
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'desc')
                ->take(10) // Ambil 10 terbaru untuk performa dashboard
                ->get()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR KELOLA LAYANAN (CRUD)
    |--------------------------------------------------------------------------
    */

    public function layananIndex()
    {
        $layanan = Layanan::orderBy('prefix', 'asc')->get();
        return view('admin.layanan', compact('layanan'));
    }

    public function layananStore(Request $request)
    {
        $request->validate([
            'nama_layanan'    => 'required|string|max:255',
            'kode_layanan'    => 'required|alpha|max:1|unique:layanans,prefix',
            'is_nik_required' => 'required|boolean',
            'deskripsi'       => 'nullable|string',
            'kuota_harian' => 'required|integer|min:0'
        ]);

        Layanan::create([
            'nama_layanan'    => $request->nama_layanan,
            'prefix'          => strtoupper($request->kode_layanan),
            'is_nik_required' => $request->is_nik_required,
            'deskripsi'       => $request->deskripsi,
            'icon'            => 'fas fa-file-alt',
            'is_active'       => true
        ]);

        return back()->with('success', 'Layanan baru berhasil ditambahkan!');
    }

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
            'kuota_harian'    => $request->kuota_harian,
        ]);

        return back()->with('success', 'Data layanan berhasil diperbarui!');
    }

    public function layananDestroy($id)
    {
        $layanan = Layanan::findOrFail($id);

        if ($layanan->queues()->exists()) {
            return back()->with('error', 'Layanan tidak bisa dihapus karena memiliki riwayat data antrian.');
        }

        $layanan->delete();
        return back()->with('success', 'Layanan berhasil dihapus!');
    }

    /**
     * Mengaktifkan atau menonaktifkan layanan tertentu (Hanya Admin)
     */
    public function layananToggle($id)
    {
        try {
            // Cari layanan berdasarkan ID
            $layanan = Layanan::findOrFail($id);

            // Balikkan status: jika true (1) jadi false (0), dan sebaliknya
            $layanan->is_active = !$layanan->is_active;
            $layanan->save();

            $statusText = $layanan->is_active ? 'diaktifkan (Buka)' : 'dinonaktifkan (Tutup)';

            return back()->with('success', "Layanan {$layanan->nama_layanan} berhasil {$statusText}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status layanan: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR KELOLA DATA ANTRIAN
    |--------------------------------------------------------------------------
    */

    public function antrianIndex(Request $request)
    {
        $query = $this->applyFilters($request);
        $dataAntrian = $query->latest()->get();
        $layanans = Layanan::all();
        $users = User::where('role', 'petugas')->get();

        $prefixes = Queue::selectRaw('SUBSTRING(nomor_antrian, 1, 1) as prefix')
            ->distinct()
            ->orderBy('prefix')
            ->pluck('prefix');

        return view('admin.antrian_index', compact('dataAntrian', 'layanans', 'users', 'prefixes'));
    }

    public function updateAntrian(Request $request, $id)
    {
        $request->validate([
            'nama_pendaftar' => 'required|string|max:255',
            'layanan_id'     => 'required|exists:layanans,id',
            'status'         => 'required|in:menunggu,dipanggil,dilewati,selesai diproses,pengambilan_dokumen,selesai'
        ]);

        $antrian = Queue::findOrFail($id);
        $antrian->update([
            'nama_pendaftar' => $request->nama_pendaftar,
            'layanan_id'     => $request->layanan_id,
            'status'         => $request->status,
            'updated_at'     => Carbon::now('Asia/Jakarta')
        ]);

        return back()->with('success', 'Data masyarakat berhasil diperbarui!');
    }

    public function deleteAntrian($id)
    {
        $antrian = Queue::findOrFail($id);
        $antrian->delete();

        return back()->with('success', 'Data antrian berhasil dihapus!');
    }

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
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns, ',');

            foreach ($antrian as $q) {
                $date = Carbon::parse($q->created_at)->timezone('Asia/Jakarta');
                $isRekam = str_contains(strtolower($q->layanan->nama_layanan ?? ''), 'rekam');

                $statusLabel = match ($q->status) {
                    'selesai'             => 'Selesai',
                    'pengambilan_dokumen' => 'Menunggu Pengambilan',
                    'selesai diproses'    => $isRekam ? 'Selesai (Rekam KTP)' : 'Selesai Pelayanan (Proses Dokumen)',
                    'dipanggil'           => 'Dipanggil',
                    'dilewati'            => 'Dilewati',
                    default               => 'Menunggu',
                };

                fputcsv($file, [
                    $q->nomor_antrian,
                    $q->nama_pendaftar,
                    (!empty($q->nik) ? $q->nik . "\t" : '-'),
                    $q->layanan->nama_layanan ?? '-',
                    $date->translatedFormat('d F Y'),
                    $date->format('H:i'),
                    $q->loket->nama_loket ?? '-',
                    $q->petugas->name ?? 'Belum Dipanggil',
                    $statusLabel
                ], ',');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function applyFilters(Request $request)
    {
        $query = Queue::with(['layanan', 'loket', 'petugas']);

        if ($request->filled('prefix')) {
            $query->where('nomor_antrian', 'like', $request->prefix . '%');
        }

        if ($request->filled('layanan_id')) {
            $query->where('layanan_id', $request->layanan_id);
        }

        if ($request->filled('petugas_id')) {
            $query->where('user_id', $request->petugas_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
