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
        $isPengambilan = is_null($user->layanan_id);

        // DAFTAR TUNGGU
        $antrian = Queue::whereDate('created_at', $today)
            ->when($isPengambilan, function($q) {
                // Loket Pengambilan menunggu antrian yang berstatus 'pengambilan'
                return $q->where('status', 'pengambilan');
            }, function($q) use ($user) {
                // Loket Unit menunggu antrian sesuai layanan masing-masing
                return $q->where('status', 'menunggu')->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'asc')
            ->with('layanan')
            ->get();

        // DAFTAR DILEWATI
        $skipped = Queue::where('status', 'lewat')
            ->whereDate('created_at', $today)
            ->when($isPengambilan, function($q) use ($user) {
                return $q->where('loket_id', $user->loket_id);
            }, function($q) use ($user) {
                return $q->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();
            
        // ANTRIAN AKTIF SAAT INI
        $current = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->with(['layanan', 'loket'])
            ->first();

        if ($request->ajax()) {
            return response()->json([
                'antrian' => $antrian,
                'skipped' => $skipped,
                'count'   => $antrian->count(),
                'current' => $current
            ]);
        }

        return view('petugas.dashboard', compact('antrian', 'current', 'skipped'));
    }

    /**
     * Panggil Antrian Berikutnya
     */
    public function panggil(Request $request) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $isPengambilan = is_null($user->layanan_id);

        // Cari antrian baru berdasarkan tipe petugas
        $qNext = Queue::whereDate('created_at', $today)
            ->when($isPengambilan, function($query) {
                return $query->where('status', 'pengambilan');
            }, function($query) use ($user) {
                return $query->where('status', 'menunggu')->where('layanan_id', $user->layanan_id);
            })
            ->orderBy('updated_at', 'asc') 
            ->first();

        if(!$qNext) {
            return back()->with('info', 'Antrian sudah habis dikerjakan.');
        }

        // OTOMATISASI: Selesaikan antrian lama jika masih ada yang berstatus 'dipanggil'
        $oldQueue = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->first();

        if ($oldQueue) {
            $finalStatus = 'selesai';
            
            // Jika BUKAN petugas pengambilan DAN BUKAN layanan Rekam KTP, lempar ke pengambilan
            if (!$isPengambilan && $oldQueue->layanan?->nama_layanan !== 'Pelayanan Rekam KTP') {
                $finalStatus = 'pengambilan';
            }
            
            $oldQueue->update(['status' => $finalStatus, 'updated_at' => $now]);
        }

        // Update antrian baru ke status dipanggil
        $qNext->update([
            'status'     => 'dipanggil', 
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
     * Tombol Aksi Manual (Selesai, Lewati)
     */
    public function aksi($id, $status) {
        $now = Carbon::now('Asia/Jakarta');
        $user = auth()->user();
        $isPengambilanPetugas = is_null($user->layanan_id);

        $q = Queue::with('layanan')->findOrFail($id);

        // Penanganan Logika Selesai/Pengambilan
        if ($status == 'pengambilan' || $status == 'selesai') {
            // Jika Petugas Loket Pengambilan ATAU Layanan Rekam KTP, paksa status jadi 'selesai'
            if ($isPengambilanPetugas || $q->layanan?->nama_layanan == 'Pelayanan Rekam KTP') {
                $status = 'selesai';
                $msg = 'Antrian telah selesai dan diarsipkan.';
            } else {
                // Selain itu (Layanan umum), lempar ke loket pengambilan
                $status = 'pengambilan';
                $msg = 'Antrian diteruskan ke Pengambilan Dokumen.';
            }
        } else {
            // Untuk status 'lewat'
            $msg = 'Antrian dilewati.';
        }

        $q->update([
            'status'     => $status,
            'user_id'    => $user->id, 
            'updated_at' => $now 
        ]);
        
        return back()->with('success', $msg);
    }

    /**
     * Panggil Ulang (Recall)
     */
    public function panggilUlang($id) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $q = Queue::findOrFail($id);

        $q->update([
            'status'     => 'dipanggil',
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