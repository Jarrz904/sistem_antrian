<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function index() {
        return view('public.display');
    }

    public function getDisplayData() {
        // 1. Ambil semua petugas yang sedang login/aktif di loket
        $petugasAktif = User::whereNotNull('loket_id')
            ->with(['loket', 'layanan'])
            ->get()
            ->sortBy('loket.nama_loket');

        $displayData = [];
        $today = Carbon::today('Asia/Jakarta');

        foreach ($petugasAktif as $petugas) {
            $prefixLayanan = $petugas->layanan->prefix ?? 'A';
            
            // Base query untuk antrian hari ini
            $query = Queue::with('layanan')
                ->whereDate('created_at', $today);

            if ($petugas->layanan_id) {
                /**
                 * --- LOKET UNIT LAYANAN (Loket 1-7) ---
                 * Logika: Cari nomor terakhir yang ditangani oleh petugas ini.
                 * Status 'selesai diproses' ditambahkan agar nomor tidak hilang saat selesai diproses.
                 */
                $lastQueue = $query->where(function($q) use ($petugas) {
                        $q->where('loket_id', $petugas->loket_id)
                          ->orWhere('user_id', $petugas->id);
                    })
                    // PERBAIKAN: Ganti 'diproses' menjadi 'selesai diproses'
                    ->whereIn('status', ['dipanggil', 'selesai diproses', 'pengambilan_dokumen', 'selesai', 'lewat'])
                    // Urutan prioritas tampilan
                    ->orderByRaw("FIELD(status, 'dipanggil', 'selesai diproses', 'pengambilan_dokumen', 'selesai', 'lewat') ASC")
                    ->orderBy('updated_at', 'desc')
                    ->first();

            } else {
                /**
                 * --- LOKET PENGAMBILAN DOKUMEN ---
                 */
                $lastQueue = (clone $query)
                    ->where('loket_id', $petugas->loket_id)
                    ->whereIn('status', ['pengambilan_dokumen', 'selesai', 'lewat'])
                    ->orderByRaw("FIELD(status, 'pengambilan_dokumen', 'selesai', 'lewat') ASC")
                    ->orderBy('updated_at', 'desc')
                    ->first();
            }

            // --- Penentuan Text Nomor Antrian ---
            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefixLayanan . '000';
            
            // --- Penentuan Label Status Layanan (Visual) ---
            $labelLayanan = $petugas->layanan->nama_layanan ?? 'Loket Pengambilan';
            
            if ($lastQueue) {
                if ($petugas->layanan_id) {
                    // Jika di unit layanan tapi status sudah di tahap pengambilan atau selesai
                    if ($lastQueue->status == 'pengambilan_dokumen') {
                        $labelLayanan = $lastQueue->layanan->nama_layanan . ' (Menuju Pengambilan)';
                    } 
                    // PERBAIKAN: Gunakan status 'selesai diproses' untuk label visual
                    elseif (in_array($lastQueue->status, ['selesai diproses', 'selesai'])) {
                        $labelLayanan = $lastQueue->layanan->nama_layanan . ' (Selesai)';
                    }
                } else {
                    // Jika di loket pengambilan
                    $labelLayanan = 'AMBIL: ' . ($lastQueue->layanan->nama_layanan ?? 'DOKUMEN');
                }
            }

            // --- Trigger Suara Panggilan (Voice) ---
            $statusUntukSuara = 'standby';
            if ($lastQueue) {
                if ($petugas->layanan_id && $lastQueue->status == 'dipanggil') {
                    $statusUntukSuara = 'dipanggil';
                } 
                elseif (!$petugas->layanan_id && $lastQueue->status == 'pengambilan_dokumen') {
                    $statusUntukSuara = 'dipanggil';
                }
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
                'status'         => $statusUntukSuara,
                'updated_token' => $lastQueue ? $lastQueue->updated_at->format('Y-m-d H:i:s.u') : null,
            ];
        }

        return response()->json($displayData);
    }
}