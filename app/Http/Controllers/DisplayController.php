<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Carbon;

class DisplayController extends Controller
{
    /**
     * Menampilkan halaman display antrian publik.
     */
    public function index() {
        return view('public.display');
    }

    /**
     * Mengambil data antrian untuk monitor display.
     * Prefix disesuaikan berdasarkan layanan yang ditangani loket.
     */
    public function getDisplayData() {
        // Ambil semua petugas yang bertugas di loket hari ini
        $petugasAktif = User::whereNotNull('loket_id')
            ->with(['loket', 'layanan'])
            ->get()
            ->sortBy('loket.nama_loket');

        $displayData = [];

        foreach ($petugasAktif as $petugas) {
            /**
             * MENGAMBIL PREFIX PER LAYANAN:
             * Kita ambil prefix dari tabel layanan yang terhubung dengan petugas.
             * Jika tidak ada, default ke 'A'.
             */
            $prefixLayanan = $petugas->layanan->prefix ?? 'A';

            /**
             * LOGIKA & SINKRONISASI:
             * Mencari antrian yang sedang dipanggil, selesai, atau lewat di loket spesifik.
             */
            $lastQueue = Queue::with('layanan')
                ->where('loket_id', $petugas->loket_id)
                ->whereDate('created_at', Carbon::today())
                ->whereIn('status', ['dipanggil', 'selesai', 'lewat'])
                ->orderByRaw("FIELD(status, 'dipanggil', 'selesai', 'lewat') ASC") 
                ->orderBy('updated_at', 'desc')
                ->first();

            /**
             * PENENTUAN NOMOR TAMPIL:
             * Jika ada data antrian, tampilkan nomornya (misal C001).
             * Jika loket masih kosong (pagi hari), tampilkan PrefixLayanan + 000 (misal C000).
             */
            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefixLayanan . '000';
            $layananTampil = $lastQueue ? $lastQueue->layanan->nama_layanan : ($petugas->layanan->nama_layanan ?? 'SIAP MELAYANI');

            $displayData[] = [
                'id_antrian'    => $lastQueue->id ?? null,
                'loket' => [
                    'id_loket'   => $petugas->loket_id,
                    'nama_loket' => $petugas->loket->nama_loket
                ],
                'nomor_antrian' => $nomorTampil,
                'layanan' => [
                    'nama_layanan' => $layananTampil
                ],
                // Status 'dipanggil' memicu suara di JS monitor
                'status'        => ($lastQueue && $lastQueue->status == 'dipanggil') ? 'dipanggil' : 'standby',
                
                /**
                 * updated_token (microtime) tetap dipertahankan. 
                 * Menggunakan updated_at agar monitor tahu kapan harus memicu suara panggilan baru.
                 */
                'updated_token' => $lastQueue ? $lastQueue->updated_at->format('Y-m-d H:i:s.u') : null,
            ];
        }

        return response()->json($displayData);
    }
}