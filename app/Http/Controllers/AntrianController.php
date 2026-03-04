<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AntrianController extends Controller
{
    /**
     * Tampilan Dashboard Petugas
     */
    public function index() {
        $user = auth()->user();
        // Mengambil tanggal hari ini berdasarkan zona waktu Jakarta
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // 1. Ambil antrian MENUNGGU spesifik layanan petugas hari ini
        $antrian = Queue::where('status', 'menunggu')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today) 
            ->orderBy('created_at', 'asc') 
            ->with('layanan')
            ->get();

        // 2. Ambil antrian DILEWATI hari ini
        $skipped = Queue::where('status', 'lewat')
            ->where('layanan_id', $user->layanan_id)
            ->whereDate('created_at', $today)
            ->orderBy('updated_at', 'desc')
            ->with('layanan')
            ->get();
            
        // 3. Ambil antrian yang SEDANG DIPROSES oleh petugas ini hari ini
        $current = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->with(['layanan', 'loket'])
            ->first();

        return view('petugas.dashboard', compact('antrian', 'current', 'skipped'));
    }

    /**
     * Panggil Antrian Berikutnya
     */
    public function panggil(Request $request) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        if (!$user->layanan_id) {
            return back()->with('error', 'Akun Anda belum dikaitkan dengan jenis layanan apapun.');
        }

        // Validasi: Cek apakah petugas masih memiliki antrian aktif (status dipanggil)
        $isBusy = Queue::where('user_id', $user->id)
            ->where('status', 'dipanggil')
            ->whereDate('created_at', $today)
            ->exists();

        if ($isBusy) {
            return back()->with('error', 'Selesaikan antrian yang sedang aktif terlebih dahulu.');
        }

        // Cari nomor antrian tertua yang masih menunggu hari ini
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
                'updated_at' => $now // Memastikan jam panggil sesuai waktu Jakarta
            ]);

            return back()->with([
                'success'       => 'Memanggil nomor ' . $q->nomor_antrian,
                'panggil_suara' => $q->nomor_antrian,
                'nomor_loket'   => $user->loket->nama_loket ?? 'Loket'
            ]);
        }
        
        return back()->with('error', 'Antrian sudah habis.');
    }

    /**
     * Fungsi Panggil Ulang (Recall)
     */
    public function panggilUlang($id) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        
        $q = Queue::where('id', $id)->firstOrFail();

        // Jika dipanggil dari daftar 'lewat', kembalikan status ke 'dipanggil'
        if ($q->status == 'lewat') {
            $q->update([
                'status'     => 'dipanggil',
                'user_id'    => $user->id,
                'loket_id'   => $user->loket_id,
                'updated_at' => $now
            ]);
        }

        return back()->with([
            'panggil_suara' => $q->nomor_antrian,
            'nomor_loket'   => $user->loket->nama_loket ?? 'Loket',
            'success'       => 'Memanggil ulang nomor ' . $q->nomor_antrian
        ]);
    }

    /**
     * Aksi Selesai atau Lewati
     */
    public function aksi($id, $status) {
        $now = Carbon::now('Asia/Jakarta');
        $user = auth()->user();

        if (in_array($status, ['selesai', 'lewat'])) {
            Queue::where('id', $id)
                ->update([
                    'status'     => $status,
                    'user_id'    => $user->id, 
                    'updated_at' => $now 
                ]);
            
            $pesan = $status == 'selesai' ? 'Antrian berhasil diselesaikan.' : 'Antrian ditandai telah dilewati.';
            return back()->with('success', $pesan);
        }

        return back();
    }
}