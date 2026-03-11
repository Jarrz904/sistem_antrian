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
     * API untuk Realtime Update Statistik Dashboard
     */
    public function getRealtimeStats()
    {
        return response()->json($this->getStatsData());
    }

    /**
     * Logika internal statistik harian
     */
    private function getStatsData()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // Query dasar untuk efisiensi
        $baseQuery = Queue::whereDate('created_at', $today);

        // 1. Menunggu
        $menungguCount = (clone $baseQuery)
            ->where('status', 'menunggu')
            ->count();

        // 2. Dipanggil
        $dipanggilCount = (clone $baseQuery)
            ->where('status', 'dipanggil')
            ->count();

        // 3. Selesai di Proses (Diseragamkan menggunakan: 'selesai diproses')
        $diprosesCount = (clone $baseQuery)
            ->where('status', 'selesai diproses')
            ->count();

        // 4. Pengambilan Dokumen
        $pengambilanCount = (clone $baseQuery)
            ->where('status', 'pengambilan_dokumen')
            ->count();

        // 5. Lewat/Dilewati
        $lewatCount = (clone $baseQuery)
            ->where('status', 'lewat')
            ->count();

        // 6. Selesai
        $selesaiCount = (clone $baseQuery)
            ->where('status', 'selesai')
            ->count();

        return [
            'totalAntrian'       => (clone $baseQuery)->count(),
            'menunggu'           => $menungguCount,
            'dipanggil'          => $dipanggilCount,
            'selesaidiproses'    => $diprosesCount, // Key tetap sama agar sinkron dengan JS/Blade
            'pengambilanDokumen' => $pengambilanCount,
            'lewat'              => $lewatCount,
            'selesai'            => $selesaiCount,
            'dataAntrian'        => Queue::with(['layanan', 'loket', 'petugas'])
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'desc')
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
            'deskripsi'       => 'nullable|string'
        ]);

        Layanan::create([
            'nama_layanan'    => $request->nama_layanan,
            'prefix'          => strtoupper($request->kode_layanan),
            'is_nik_required' => $request->is_nik_required,
            'deskripsi'       => $request->deskripsi,
            'icon'            => 'fas fa-file-alt'
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
            // Menjamin status 'selesai diproses' valid untuk diinput manual oleh Admin
            'status'         => 'required|in:menunggu,dipanggil,lewat,selesai diproses,pengambilan_dokumen,selesai'
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

                // Mapping label status agar rapi di file CSV
                $statusLabel = match($q->status) {
                    'selesai'             => 'Selesai',
                    'pengambilan_dokumen' => 'Menunggu Pengambilan',
                    'selesai diproses'    => $isRekam ? 'Selesai (Rekam KTP)' : 'Selesai Pelayanan (Proses Dokumen)',
                    'dipanggil'           => 'Dipanggil',
                    'lewat'               => 'Dilewati',
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