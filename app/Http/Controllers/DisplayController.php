<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function index()
    {
        return view('public.display');
    }

    public function displayDepan()
    {
        return view('public.display', ['lokasi' => 'depan']);
    }

    public function displayBelakang()
    {
        return view('public.display', ['lokasi' => 'belakang']);
    }

    public function getDisplayData(Request $request)
    {
        $lokasi = $request->query('lokasi');

        $queryPetugas = User::whereNotNull('loket_id')
            ->with(['loket', 'layanan']);

        if ($lokasi === 'depan') {
            // Filter untuk monitor depan (opsional, sesuaikan dengan kebutuhan)
            $queryPetugas->whereHas('layanan', function ($q) {
                $q->whereNotIn('nama_layanan', ['Pelayanan Rekam KTP', 'Pelayanan Pencetakan KTP']);
            });
        } elseif ($lokasi === 'belakang') {
            // KHUSUS MONITOR BELAKANG: Hanya Rekam KTP dan Cetak KTP
            $queryPetugas->whereHas('layanan', function ($q) {
                $q->whereIn('nama_layanan', [
                    'Pelayanan Rekam KTP',
                    'Pelayanan Pencetakan KTP'
                ]);
            });
        }

        $petugasAktif = $queryPetugas->get()->sortBy('loket.nama_loket');

        $displayData = [];
        $today = Carbon::today('Asia/Jakarta');

        foreach ($petugasAktif as $petugas) {
            $prefixLayanan = $petugas->layanan->prefix ?? 'A';

            // Base query untuk antrian hari ini
            $query = Queue::with('layanan')
                ->whereDate('created_at', $today);

            if ($petugas->layanan_id) {
                $lastQueue = $query->where(function ($q) use ($petugas) {
                    $q->where('loket_id', $petugas->loket_id)
                        ->orWhere('user_id', $petugas->id);
                })
                    ->whereIn('status', ['dipanggil', 'selesai diproses', 'pengambilan_dokumen', 'selesai', 'lewat', 'dilewati'])
                    ->orderByRaw("FIELD(status, 'dipanggil', 'pengambilan_dokumen', 'selesai diproses', 'selesai', 'dilewati', 'lewat') ASC")
                    ->orderBy('updated_at', 'desc')
                    ->first();
            } else {
                $lastQueue = (clone $query)
                    ->where('loket_id', $petugas->loket_id)
                    ->whereIn('status', ['pengambilan_dokumen', 'selesai', 'lewat', 'dilewati'])
                    ->orderByRaw("FIELD(status, 'pengambilan_dokumen', 'selesai', 'dilewati', 'lewat') ASC")
                    ->orderBy('updated_at', 'desc')
                    ->first();
            }

            $nomorTampil = $lastQueue ? $lastQueue->nomor_antrian : $prefixLayanan . '000';
            $labelLayanan = $petugas->layanan->nama_layanan ?? 'Loket Pengambilan';

            if ($lastQueue) {
                if ($petugas->layanan_id) {
                    if ($lastQueue->status == 'pengambilan_dokumen') {
                        $labelLayanan = $lastQueue->layanan->nama_layanan . ' (Ke Pengambilan)';
                    } elseif (in_array($lastQueue->status, ['selesai diproses', 'selesai'])) {
                        $labelLayanan = $lastQueue->layanan->nama_layanan . ' (Selesai)';
                    } elseif (in_array($lastQueue->status, ['lewat', 'dilewati'])) {
                        $labelLayanan = $lastQueue->layanan->nama_layanan . ' (Dilewati)';
                    }
                } else {
                    if (in_array($lastQueue->status, ['lewat', 'dilewati'])) {
                        $labelLayanan = 'DILEWATI: ' . ($lastQueue->layanan->nama_layanan ?? '');
                    } else {
                        $labelLayanan = 'AMBIL: ' . ($lastQueue->layanan->nama_layanan ?? 'DOKUMEN');
                    }
                }
            }

            $statusUntukSuara = 'standby';
            if ($lastQueue) {
                if ($petugas->layanan_id && $lastQueue->status == 'dipanggil') {
                    $statusUntukSuara = 'dipanggil';
                } elseif (!$petugas->layanan_id && $lastQueue->status == 'pengambilan_dokumen') {
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
