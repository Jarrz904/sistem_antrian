<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AntrianController extends Controller
{
    /**
     * Tampilan Dashboard Petugas & API Data Realtime
     */
    public function index(Request $request) {
        $user = auth()->user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // 1. Ambil antrian MENUNGGU hari ini (untuk layanan yang sama dengan petugas)
        $antrian = Queue::where('status', 'menunggu')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today) 
            ->orderBy('created_at', 'asc') 
            ->with('layanan')
            ->get();

        // 2. Ambil antrian DILEWATI hari ini (untuk layanan yang sama)
        $skipped = Queue::where('status', 'lewat')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today)
            ->orderBy('updated_at', 'desc')
            ->with('layanan')
            ->get();
            
        // 3. Ambil antrian yang SEDANG DIPROSES oleh petugas ini secara spesifik
        $current = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->with(['layanan', 'loket'])
            ->first();

        // JIKA REQUEST ADALAH AJAX (untuk update realtime tanpa reload halaman)
        if ($request->ajax()) {
            return response()->json([
                'antrian' => $antrian,
                'skipped' => $skipped,
                'count'   => $antrian->count(),
                // Opsional: kirim ID antrian yang sedang dipanggil petugas lain 
                // agar tabel sinkron jika ada data yang mendadak hilang dari list
            ]);
        }

        return view('petugas.dashboard', compact('antrian', 'current', 'skipped'));
    }

    /**
     * Panggil Antrian Berikutnya (Lanjutkan Pelayanan)
     */
    public function panggil(Request $request) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        if (!$user->layanan_id) {
            return back()->with('error', 'Akun Anda belum dikaitkan dengan layanan.');
        }

        // Selesaikan antrian aktif sebelumnya jika ada (Auto-complete)
        $activeQueue = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->first();

        if ($activeQueue) {
            $activeQueue->update(['status' => 'selesai']);
        }

        // Ambil nomor antrian pertama dalam daftar tunggu
        $q = Queue::where('status', 'menunggu')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc') 
            ->first();

        if($q) {
            $q->update([
                'status'     => 'dipanggil', 
                'loket_id'   => $user->loket_id,
                'user_id'    => $user->id,
                'updated_at' => $now 
            ]);

            return back()->with([
                'success'       => 'Memanggil nomor ' . $q->nomor_antrian,
                'panggil_suara' => $q->nomor_antrian,
                'nomor_loket'   => $user->loket->nama_loket ?? 'Loket'
            ]);
        }
        
        return back()->with('info', 'Antrian sudah habis dikerjakan.');
    }

    /**
     * Fungsi Panggil Ulang (Recall)
     */
    public function panggilUlang($id) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        
        $q = Queue::where('id', $id)->firstOrFail();

        // Validasi: Jangan panggil ulang jika petugas sedang melayani nomor LAIN yang aktif
        $activeOther = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->where('id', '!=', $id)
            ->exists();

        if ($activeOther) {
            return back()->with('error', 'Selesaikan antrian aktif Anda terlebih dahulu.');
        }

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
     * Aksi Selesai atau Lewati
     * Digunakan untuk mengubah status antrian yang sedang aktif
     */
    public function aksi($id, $status) {
        $now = Carbon::now('Asia/Jakarta');
        $user = auth()->user();

        // Pastikan status yang dikirim valid
        if (in_array($status, ['selesai', 'lewat'])) {
            // Update antrian berdasarkan ID
            Queue::where('id', $id)
                ->update([
                    'status'     => $status,
                    'user_id'    => $user->id, 
                    'updated_at' => $now 
                ]);
            
            $pesan = $status == 'selesai' ? 'Antrian berhasil diselesaikan.' : 'Antrian telah dilewati.';
            return back()->with('success', $pesan);
        }

        return back();
    }
}