<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AntrianController extends Controller
{
    /**
     * Dashboard Petugas & API Data Realtime
     */
    public function index(Request $request) {
        $user = auth()->user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        
        // Cek apakah user adalah petugas loket pengambilan (tidak terikat layanan_id tertentu)
        $isPengambilanPetugas = is_null($user->layanan_id);

        // 1. DAFTAR TUNGGU
        $antrian = Queue::whereDate('created_at', $today)
            ->when($isPengambilanPetugas, function($q) {
                // Loket Pengambilan menunggu antrian yang berstatus 'diproses'
                return $q->where('status', 'diproses');
            }, function($q) use ($user) {
                // Loket Unit menunggu antrian sesuai layanan masing-masing ('menunggu')
                return $q->where('status', 'menunggu')->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'asc')
            ->with('layanan')
            ->get();

        // 2. DAFTAR DILEWATI
        $skipped = Queue::where('status', 'lewat')
            ->whereDate('created_at', $today)
            ->when($isPengambilanPetugas, function($q) use ($user) {
                return $q->where('loket_id', $user->loket_id);
            }, function($q) use ($user) {
                return $q->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // 3. DAFTAR SELESAI / DIPROSES (Riwayat Pelayanan)
        $selesai = Queue::whereDate('created_at', $today)
            ->when($isPengambilanPetugas, function($q) use ($user) {
                // Loket Pengambilan: Hanya yang statusnya sudah 'selesai' di loketnya
                return $q->where('status', 'selesai')->where('loket_id', $user->loket_id);
            }, function($q) use ($user) {
                // Loket Unit: Menampilkan yang sudah 'selesai' ATAU 'diproses' (lempar ke pengambilan)
                return $q->whereIn('status', ['selesai', 'diproses'])
                         ->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();
            
        // 4. ANTRIAN AKTIF SAAT INI
        $current = Queue::where('user_id', $user->id)
            ->whereIn('status', ['dipanggil', 'pengambilan_dokumen'])
            ->whereDate('created_at', $today)
            ->with(['layanan', 'loket'])
            ->first();

        // Response untuk AJAX Realtime
        if ($request->ajax()) {
            return response()->json([
                'antrian' => $antrian,
                'skipped' => $skipped,
                'selesai' => $selesai,
                'count'   => $antrian->count(),
                'current' => $current
            ]);
        }

        return view('petugas.dashboard', compact('antrian', 'current', 'skipped', 'selesai'));
    }

    /**
     * Fungsi Internal untuk menentukan status akhir
     */
    private function determineFinalStatus($queue, $isPengambilanPetugas) {
        if ($isPengambilanPetugas) {
            return 'selesai';
        }

        $namaLayanan = strtoupper($queue->layanan->nama_layanan ?? '');

        $langsungSelesai = [
            'REKAM KTP',
            'REKAM BIOMETRIK',
            'KONSULTASI',
            'PENGADUAN'
        ];

        foreach ($langsungSelesai as $item) {
            if (str_contains($namaLayanan, $item)) {
                return 'selesai';
            }
        }

        return 'diproses';
    }

    /**
     * Panggil Antrian Berikutnya
     */
    public function panggil(Request $request) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $isPengambilanPetugas = is_null($user->layanan_id);

        // 1. OTOMATISASI: Selesaikan antrian lama yang masih menggantung
        $oldQueue = Queue::where('user_id', $user->id)
            ->whereIn('status', ['dipanggil', 'pengambilan_dokumen'])
            ->whereDate('created_at', $today)
            ->first();

        if ($oldQueue) {
            $statusAuto = $this->determineFinalStatus($oldQueue, $isPengambilanPetugas);
            $oldQueue->update(['status' => $statusAuto, 'updated_at' => $now]);
        }

        // 2. Cari antrian berikutnya
        $qNext = Queue::whereDate('created_at', $today)
            ->when($isPengambilanPetugas, function($query) {
                return $query->where('status', 'diproses');
            }, function($query) use ($user) {
                return $query->where('status', 'menunggu')->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'asc') 
            ->first();

        if(!$qNext) {
            return back()->with('info', 'Antrian sudah habis dikerjakan.');
        }

        // 3. Update antrian baru
        $newStatus = $isPengambilanPetugas ? 'pengambilan_dokumen' : 'dipanggil';

        $qNext->update([
            'status'     => $newStatus, 
            'loket_id'   => $user->loket_id,
            'user_id'    => $user->id,
            'panggil_at' => $now,
            'updated_at' => $now 
        ]);

        return back()->with([
            'success'       => 'Memanggil nomor ' . $qNext->nomor_antrian,
            'panggil_suara' => $qNext->nomor_antrian,
            'nomor_loket'   => $user->loket->nama_loket ?? 'Loket'
        ]);
    }

    /**
     * Tombol Aksi Manual (Selesai Pelayanan / Lewati)
     */
    public function aksi($id, $status) {
        $now = Carbon::now('Asia/Jakarta');
        $user = auth()->user();
        $isPengambilanPetugas = is_null($user->layanan_id);

        $q = Queue::with('layanan')->findOrFail($id);
        
        $finalStatus = $status;

        if ($status == 'selesai') {
            $finalStatus = $this->determineFinalStatus($q, $isPengambilanPetugas);
            
            if ($finalStatus == 'selesai') {
                $msg = 'Antrian ' . $q->nomor_antrian . ' telah selesai (Arsip).';
            } else {
                $msg = 'Pelayanan ' . $q->nomor_antrian . ' selesai, lanjut ke pengambilan dokumen.';
            }
        } elseif ($status == 'lewat') {
            $msg = 'Antrian ' . $q->nomor_antrian . ' berhasil dilewati.';
        } else {
            $msg = 'Status antrian diperbarui.';
        }

        $q->update([
            'status'     => $finalStatus,
            'user_id'    => $user->id, 
            'updated_at' => $now 
        ]);
        
        return back()->with('success', $msg);
    }

    /**
     * Panggil Ulang (Recall) dari tabel Dilewati
     */
    public function panggilUlang($id) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $q = Queue::findOrFail($id);

        $isPengambilanPetugas = is_null($user->layanan_id);
        $status = $isPengambilanPetugas ? 'pengambilan_dokumen' : 'dipanggil';

        $q->update([
            'status'     => $status,
            'user_id'    => $user->id,
            'updated_at' => $now 
        ]);

        return back()->with([
            'panggil_suara' => $q->nomor_antrian,
            'nomor_loket'   => $user->loket->nama_loket ?? 'Loket',
            'success'       => 'Memanggil ulang nomor ' . $q->nomor_antrian
        ]);
    }
}