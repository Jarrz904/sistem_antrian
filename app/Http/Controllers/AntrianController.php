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
                // PERBAIKAN: Loket Pengambilan menunggu antrian yang dikirim unit (status: 'selesai diproses')
                return $q->where('status', 'selesai diproses');
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
                // Riwayat pengambilan di loket ini
                return $q->where('status', 'selesai')->where('loket_id', $user->loket_id);
            }, function($q) use ($user) {
                // PERBAIKAN: Menampilkan status 'selesai diproses' agar muncul di tabel riwayat petugas unit
                return $q->whereIn('status', ['selesai', 'diproses', 'selesai diproses', 'pengambilan_dokumen'])
                         ->where('user_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();
            
        // 4. ANTRIAN AKTIF SAAT INI
        $current = Queue::whereDate('created_at', $today)
            ->where(function($query) use ($user, $isPengambilanPetugas) {
                if ($isPengambilanPetugas) {
                    // Jika petugas pengambilan, cari berdasarkan loket_id aktif
                    $query->where('loket_id', $user->loket_id)
                          ->where('status', 'pengambilan_dokumen');
                } else {
                    // Jika petugas unit, cari berdasarkan user_id (kepemilikan antrian)
                    $query->where('user_id', $user->id)
                          ->where('status', 'dipanggil');
                }
            })
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
        // Jika diproses oleh petugas pengambilan, status akhirnya mutlak 'selesai'
        if ($isPengambilanPetugas) {
            return 'selesai';
        }

        // Ambil nama layanan untuk pengecekan
        $namaLayanan = strtoupper($queue->layanan->nama_layanan ?? '');

        // Daftar kata kunci layanan yang tidak membutuhkan pengambilan dokumen
        $langsungSelesai = [
            'REKAM KTP',
            'REKAM BIOMETRIK',
            'KONSULTASI',
            'PENGADUAN',
            'SINKRONISASI',
            'UPDATE DATA'
        ];

        foreach ($langsungSelesai as $item) {
            if (str_contains($namaLayanan, $item)) {
                return 'selesai';
            }
        }

        // Default: Unit Layanan melempar ke Loket Pengambilan
        return 'selesai diproses';
    }

    /**
     * Panggil Antrian Berikutnya
     */
    public function panggil(Request $request) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $isPengambilanPetugas = is_null($user->layanan_id);

        // 1. OTOMATISASI: Selesaikan antrian lama yang sedang ditangani user/loket ini
        $oldQueue = Queue::whereDate('created_at', $today)
            ->where(function($q) use ($user, $isPengambilanPetugas) {
                if ($isPengambilanPetugas) {
                    $q->where('loket_id', $user->loket_id)->where('status', 'pengambilan_dokumen');
                } else {
                    $q->where('user_id', $user->id)->where('status', 'dipanggil');
                }
            })->first();

        if ($oldQueue) {
            $statusAuto = $this->determineFinalStatus($oldQueue, $isPengambilanPetugas);
            $oldQueue->update([
                'status' => $statusAuto, 
                'updated_at' => $now
            ]);
        }

        // 2. Cari antrian berikutnya
        $qNext = Queue::whereDate('created_at', $today)
            ->when($isPengambilanPetugas, function($query) {
                // PERBAIKAN: Cari antrian yang sudah dikirim oleh unit (status: 'selesai diproses')
                return $query->where('status', 'selesai diproses');
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
        
        $updateData = [
            'status'     => $newStatus, 
            'loket_id'   => $user->loket_id, 
            'panggil_at' => $now,
            'updated_at' => $now 
        ];

        // PENTING: Jika petugas pengambilan, JANGAN timpa user_id agar nomor tetap terkunci pada petugas unit asal.
        if (!$isPengambilanPetugas) {
            $updateData['user_id'] = $user->id;
        }

        $qNext->update($updateData);

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
        }

        $updateData = [
            'status'     => $finalStatus,
            'updated_at' => $now 
        ];

        if ($status == 'lewat') {
            $updateData['user_id'] = $user->id;
        }

        $q->update($updateData);
        
        return back()->with('success', 'Status antrian ' . $q->nomor_antrian . ' diperbarui.');
    }

    /**
     * Panggil Ulang (Recall) dengan Validasi Antrian Aktif
     */
    public function panggilUlang($id) {
        $user = auth()->user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        
        $isPengambilanPetugas = is_null($user->layanan_id);

        $activeQueue = Queue::whereDate('created_at', $today)
            ->where(function($query) use ($user, $isPengambilanPetugas) {
                if ($isPengambilanPetugas) {
                    $query->where('loket_id', $user->loket_id)
                          ->where('status', 'pengambilan_dokumen');
                } else {
                    $query->where('user_id', $user->id)
                          ->where('status', 'dipanggil');
                }
            })->first();

        if ($activeQueue) {
            return back()->with('error', 'Selesaikan pelayanan nomor ' . $activeQueue->nomor_antrian . ' terlebih dahulu sebelum memanggil nomor lain.');
        }

        $q = Queue::findOrFail($id);
        $status = $isPengambilanPetugas ? 'pengambilan_dokumen' : 'dipanggil';

        $updateData = [
            'status'     => $status,
            'loket_id'   => $user->loket_id,
            'updated_at' => $now 
        ];

        if (!$isPengambilanPetugas) {
            $updateData['user_id'] = $user->id;
        }

        $q->update($updateData);

        return back()->with([
            'panggil_suara' => $q->nomor_antrian,
            'nomor_loket'   => $user->loket->nama_loket ?? 'Loket',
            'success'       => 'Memanggil ulang nomor ' . $q->nomor_antrian
        ]);
    }
}