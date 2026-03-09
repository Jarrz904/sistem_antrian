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
     * Mendukung tampilan untuk loket layanan biasa dan loket pengembalian.
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
             * Default ke 'A' jika petugas tidak memiliki layanan terkait.
             */
            $prefixLayanan = $petugas->layanan->prefix ?? 'A';

            /**
             * LOGIKA & SINKRONISASI:
             * Mencari antrian terakhir yang diproses di loket ini.
             * Menambahkan status 'pengembalian' agar terpantau di monitor jika loket tsb
             * sedang melayani pengambilan dokumen.
             */
            $lastQueue = Queue::with('layanan')
                ->where('loket_id', $petugas->loket_id)
                ->whereDate('created_at', Carbon::today())
                ->whereIn('status', ['dipanggil', 'selesai', 'lewat', 'pengembalian'])
                ->orderByRaw("FIELD(status, 'dipanggil', 'pengembalian', 'selesai', 'lewat') ASC") 
                ->orderBy('updated_at', 'desc')
                ->first();

            /**
             * PENENTUAN NOMOR TAMPIL:
             * Jika loket belum mulai memanggil (pagi hari), tampilkan format 000.
             */
            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefixLayanan . '000';
            
            /**
             * PENENTUAN TEXT LAYANAN:
             * Jika sedang melayani, tampilkan nama layanannya. 
             * Khusus untuk status 'pengembalian', kita beri keterangan tambahan.
             */
            $labelLayanan = 'SIAP MELAYANI';
            if ($lastQueue) {
                $labelLayanan = $lastQueue->layanan->nama_layanan;
                if ($lastQueue->status == 'pengembalian') {
                    $labelLayanan = 'Menunggu di ' . $labelLayanan;
                }
            } elseif ($petugas->layanan) {
                $labelLayanan = $petugas->layanan->nama_layanan;
            }

            $displayData[] = [
                'id_antrian'    => $lastQueue->id ?? null,
                'loket' => [
                    'id_loket'   => $petugas->loket_id,
                    'nama_loket' => $petugas->loket->nama_loket
                ],
                'nomor_antrian' => $nomorTampil,
                'layanan' => [
                    'nama_layanan' => $labelLayanan
                ],
                /** * Status 'dipanggil' memicu trigger suara di JavaScript Monitor.
                 * Kita pastikan hanya status 'dipanggil' yang memicu suara bell.
                 */
                'status'        => ($lastQueue && $lastQueue->status == 'dipanggil') ? 'dipanggil' : 'standby',
                
                /**
                 * updated_token digunakan sebagai ID unik perubahan.
                 * Monitor akan membandingkan token ini, jika berbeda dari data sebelumnya,
                 * maka monitor akan menjalankan fungsi panggil/animasi.
                 */
                'updated_token' => $lastQueue ? $lastQueue->updated_at->format('Y-m-d H:i:s.u') : null,
            ];
        }

        return response()->json($displayData);
    }
}