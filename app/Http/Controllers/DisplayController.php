<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Carbon;

class DisplayController extends Controller
{
    /**
     * Menampilkan halaman utama monitor antrian (Blade View)
     */
    public function index() {
        return view('public.display');
    }

    /**
     * Mengambil data JSON untuk monitor
     * Logika ini menangani tampilan nomor terakhir dan panggil ulang agar bersuara
     */
    public function getDisplayData() {
        // 1. Ambil semua petugas yang memiliki loket, urutkan berdasarkan nama loket
        $petugasAktif = User::whereNotNull('loket_id')
            ->with(['loket', 'layanan'])
            ->get()
            ->sortBy('loket.nama_loket');

        $displayData = [];
        $prefixes = range('A', 'Z'); 
        $index = 0;

        foreach ($petugasAktif as $petugas) {
            $prefix = $prefixes[$index] ?? 'Z';

            /**
             * LOGIKA UTAMA:
             * Mencari antrian terakhir yang diproses di loket ini hari ini.
             * Menggunakan status 'dipanggil', 'selesai', atau 'lewat'.
             * Ini memastikan jika antrian habis, nomor terakhir tetap terpampang (tidak reset ke A000).
             */
            $lastQueue = Queue::with('layanan')
                ->where('loket_id', $petugas->loket_id)
                ->whereDate('created_at', Carbon::today())
                ->whereIn('status', ['dipanggil', 'selesai', 'lewat'])
                ->orderBy('updated_at', 'desc') // Mengambil yang paling baru diupdate
                ->first();

            // Penentuan nomor dan layanan teks di monitor
            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefix . '000';
            $layananTampil = $lastQueue ? $lastQueue->layanan->nama_layanan : ($petugas->layanan->nama_layanan ?? 'SIAP MELAYANI');

            /**
             * LOGIKA SUARA & RECALL:
             * 'status' dikirim sebagai 'dipanggil' hanya jika status di DB memang 'dipanggil'.
             * 'updated_token' (timestamp) adalah kunci agar JavaScript tahu ada perubahan (Recall).
             * Di sisi Frontend (JS), buat logika: jika token berubah dan status 'dipanggil', putar suara.
             */
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
                // Status 'dipanggil' akan memicu suara pada monitor
                // Jika sudah 'selesai', status menjadi 'standby' agar tidak bersuara terus-menerus
                'status'        => ($lastQueue && $lastQueue->status == 'dipanggil') ? 'dipanggil' : 'standby',
                
                // Token unik berdasarkan waktu update terakhir. 
                // Saat tombol 'Panggil Ulang' ditekan, timestamp ini akan berubah.
                'updated_token' => $lastQueue ? $lastQueue->updated_at->timestamp : null,
            ];
            
            $index++;
        }

        return response()->json($displayData);
    }
}