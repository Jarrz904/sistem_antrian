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
     * Logika disesuaikan agar nomor yang baru dipanggil otomatis (setelah tombol 'Lewati' ditekan)
     * langsung muncul dan memicu suara pada monitor.
     */
    public function getDisplayData() {
        // Ambil semua petugas yang bertugas di loket hari ini
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
             * PERBAIKAN LOGIKA & SINKRONISASI:
             * 1. Cari antrian yang statusnya 'dipanggil' hari ini (PRIORITAS UTAMA).
             * 2. Jika petugas baru saja menekan 'Lewati', status nomor lama menjadi 'lewat' 
             * dan nomor baru menjadi 'dipanggil'. Query ini akan otomatis menangkap nomor baru tersebut.
             * 3. Jika tidak ada yang 'dipanggil', ambil yang terakhir 'selesai' atau 'lewat'.
             * 4. FIELD(status, 'dipanggil', 'selesai', 'lewat') memastikan status aktif selalu di posisi pertama.
             */
            $lastQueue = Queue::with('layanan')
                ->where('loket_id', $petugas->loket_id)
                ->whereDate('created_at', Carbon::today())
                ->whereIn('status', ['dipanggil', 'selesai', 'lewat'])
                ->orderByRaw("FIELD(status, 'dipanggil', 'selesai', 'lewat') ASC") 
                ->orderBy('updated_at', 'desc')
                ->first();

            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefix . '000';
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
                 * Saat petugas klik 'Lewati' dan sistem otomatis memanggil nomor baru, 
                 * updated_at nomor baru tersebut akan dikirim ke sini sebagai token baru 
                 * sehingga monitor publik langsung berbunyi.
                 */
                'updated_token' => $lastQueue ? $lastQueue->updated_at->format('Y-m-d H:i:s.u') : null,
            ];
            
            $index++;
        }

        return response()->json($displayData);
    }
}