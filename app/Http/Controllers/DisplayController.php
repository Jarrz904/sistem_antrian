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
     * Logic: Mencari aktivitas terakhir berdasarkan loket petugas yang sedang aktif.
     */
    public function getDisplayData() {
        // Ambil semua petugas yang memiliki loket_id (petugas yang bertugas)
        $petugasAktif = User::whereNotNull('loket_id')
            ->with(['loket', 'layanan'])
            ->get()
            ->sortBy('loket.nama_loket');

        $displayData = [];
        $today = Carbon::today('Asia/Jakarta');

        foreach ($petugasAktif as $petugas) {
            /**
             * MENGAMBIL PREFIX:
             * Jika petugas unit, ambil prefix layanannya. Jika pengambilan, default 'A'.
             */
            $prefixLayanan = $petugas->layanan->prefix ?? 'A';

            /**
             * QUERY ANTRIAN TERAKHIR (LATEST ACTIVITY):
             * Mencari antrian yang baru saja berinteraksi dengan loket ini hari ini.
             * Kita memantau status: 
             * - dipanggil (Layanan Unit)
             * - pengambilan_dokumen (Layanan Pengambilan)
             * - selesai / lewat (Agar nomor tetap tampil di monitor meski sudah selesai dipanggil)
             */
            $lastQueue = Queue::with('layanan')
                ->where('loket_id', $petugas->loket_id)
                ->whereDate('created_at', $today)
                ->whereIn('status', ['dipanggil', 'pengambilan_dokumen', 'selesai', 'lewat', 'diproses'])
                ->orderByRaw("FIELD(status, 'dipanggil', 'pengambilan_dokumen', 'diproses', 'selesai', 'lewat') ASC") 
                ->orderBy('updated_at', 'desc')
                ->first();

            /**
             * PENENTUAN NOMOR TAMPIL:
             * Tampilkan nomor terakhir. Jika belum ada aktivitas, tampilkan [Prefix]000.
             */
            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefixLayanan . '000';
            
            /**
             * PENENTUAN LABEL LAYANAN:
             * Jika sedang aktif memanggil, ambil nama layanan dari antrian tersebut.
             */
            $labelLayanan = 'SIAP MELAYANI';
            if ($lastQueue) {
                $labelLayanan = $lastQueue->layanan->nama_layanan ?? 'Pelayanan';
                
                // Jika statusnya diproses (baru saja dilempar oleh unit), beri keterangan
                if ($lastQueue->status == 'diproses') {
                    $labelLayanan = 'Menuju Loket Pengambilan';
                }
            } elseif ($petugas->layanan) {
                $labelLayanan = $petugas->layanan->nama_layanan;
            } else {
                $labelLayanan = "Loket Pengambilan";
            }

            /**
             * TRIGGER SUARA (Voice Call):
             * Voice call hanya dipicu jika statusnya adalah 'dipanggil' atau 'pengambilan_dokumen'.
             */
            $isCalling = $lastQueue && in_array($lastQueue->status, ['dipanggil', 'pengambilan_dokumen']);

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
                'status'        => $isCalling ? 'dipanggil' : 'standby',
                
                /**
                 * UPDATED TOKEN:
                 * Sangat penting untuk fitur Recall. Setiap kali update_at berubah (karena panggil ulang),
                 * token ini berubah, memicu JavaScript monitor untuk membunyikan suara lagi.
                 */
                'updated_token' => $lastQueue ? $lastQueue->updated_at->format('Y-m-d H:i:s.u') : null,
            ];
        }

        return response()->json($displayData);
    }
}