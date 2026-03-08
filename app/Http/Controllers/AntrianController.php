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

        $antrian = Queue::where('status', 'menunggu')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today) 
            ->orderBy('created_at', 'asc') 
            ->with('layanan')
            ->get();

        $skipped = Queue::where('status', 'lewat')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today)
            ->orderBy('updated_at', 'desc')
            ->with('layanan')
            ->get();
            
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
     * Panggil Antrian Berikutnya (Manual)
     */
    public function panggil(Request $request) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        if (!$user->layanan_id) {
            return back()->with('error', 'Akun Anda belum dikaitkan dengan layanan.');
        }

        // 1. Ambil nomor antrian pertama dalam daftar tunggu
        $q = Queue::where('status', 'menunggu')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc') 
            ->first();

        if(!$q) {
            return back()->with('info', 'Antrian sudah habis dikerjakan.');
        }

        // 2. Otomatis selesaikan antrian yang sedang aktif sebelumnya
        Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->update(['status' => 'selesai', 'updated_at' => $now]);

        // 3. Update antrian baru menjadi 'dipanggil'
        $q->update([
            'status'     => 'dipanggil', 
            'loket_id'   => $user->loket_id,
            'user_id'    => $user->id,
            'panggil_at' => $now,
            'updated_at' => $now 
        ]);

        return back()->with([
            'success'       => 'Memanggil nomor ' . $q->nomor_antrian,
            'panggil_suara' => $q->nomor_antrian,
            'nomor_loket'   => $user->loket->nama_loket ?? 'Loket'
        ]);
    }

    /**
     * Panggil Ulang (Recall)
     */
    public function panggilUlang($id) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        
        $q = Queue::where('id', $id)->firstOrFail();

        // Matikan nomor lain yang mungkin masih 'dipanggil' agar tidak double di monitor
        Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->where('id', '!=', $id)
            ->update(['status' => 'lewat', 'updated_at' => $now]);

        $q->update([
            'status'     => 'dipanggil',
            'user_id'    => $user->id,
            'loket_id'   => $user->loket_id,
            'updated_at' => $now 
        ]);

        return back()->with([
            'panggil_suara' => $q->nomor_antrian,
            'nomor_loket'   => $user->loket->nama_loket ?? 'Loket',
            'success'       => 'Memanggil ulang nomor ' . $q->nomor_antrian
        ]);
    }

    /**
     * Aksi Selesai atau Lewati (Otomatis panggil selanjutnya jika LEWAT)
     */
    public function aksi($id, $status) {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $user = auth()->user();

        if (!in_array($status, ['selesai', 'lewat'])) {
            return back();
        }

        // 1. Update status antrian yang dipilih
        Queue::where('id', $id)->update([
            'status'     => $status,
            'user_id'    => $user->id, 
            'updated_at' => $now 
        ]);

        // 2. LOGIKA OTOMATIS: Jika 'lewat', langsung panggil nomor berikutnya
        if ($status == 'lewat') {
            $next = Queue::where('status', 'menunggu')
                ->where('layanan_id', $user->layanan_id)
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($next) {
                $next->update([
                    'status'     => 'dipanggil',
                    'loket_id'   => $user->loket_id,
                    'user_id'    => $user->id,
                    'panggil_at' => $now,
                    'updated_at' => $now
                ]);

                return back()->with([
                    'success'       => 'Antrian dilewati. Memanggil nomor ' . $next->nomor_antrian,
                    'panggil_suara' => $next->nomor_antrian,
                    'nomor_loket'   => $user->loket->nama_loket ?? 'Loket'
                ]);
            }
            
            return back()->with('success', 'Antrian dilewati. Tidak ada antrian selanjutnya.');
        }

        return back()->with('success', 'Antrian berhasil diselesaikan.');
    }
}