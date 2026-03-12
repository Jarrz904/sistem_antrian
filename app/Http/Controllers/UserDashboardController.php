<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan, Loket};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class UserDashboardController extends Controller
{
    /**
     * Menampilkan halaman utama untuk publik/masyarakat
     */
    public function index() {
        // Cek status sistem, jika 'off' arahkan kembali ke welcome
        if (Cache::get('system_status', 'on') === 'off') {
            return redirect()->route('welcome');
        }

        $lokets = Loket::all();
        $layanans = Layanan::all();
        
        return view('public.dashboard_user', compact('lokets', 'layanans'));
    }

    /**
     * Menyimpan pendaftaran antrian baru dari masyarakat
     */
    public function store(Request $request) {
        // 1. Validasi Status Sistem Operasional (Mencegah Bypass)
        if (Cache::get('system_status', 'on') === 'off') {
            return redirect()->route('welcome')->with('error', 'Pendaftaran sedang ditutup.');
        }

        // 2. Inisialisasi Waktu Jakarta
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $today = $now->toDateString();

        // 3. Ambil data layanan
        $layanan = Layanan::findOrFail($request->layanan_id);
        
        // 4. Validasi Input
        $request->validate([
            'nama' => 'required|string|max:255',
            'layanan_id' => 'required|exists:layanans,id',
            'nik' => 'nullable|numeric|digits:16',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nik.numeric'   => 'NIK harus berupa angka.',
            'nik.digits'    => 'Jika diisi, NIK harus berjumlah tepat 16 digit.',
        ]);

        // 5. Hitung urutan antrian per layanan KHUSUS HARI INI
        $count = Queue::where('layanan_id', $layanan->id)
                      ->whereDate('created_at', $today)
                      ->count();
        
        $nomorUrut = $count + 1;

        /** * FORMAT NOMOR ANTRIAN:
         * Mengambil prefix dari database layanan (contoh: A, B, C)
         */
        $nomorAntrian = $layanan->prefix . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);

        // 6. Simpan ke database
        $queue = Queue::create([
            'nomor_antrian'  => $nomorAntrian,
            'nama_pendaftar' => $request->nama,
            'nik'            => $request->nik ?? null, 
            'layanan_id'     => $request->layanan_id,
            'loket_id'       => null, 
            'status'         => 'menunggu',
            'created_at'     => $now, 
            'updated_at'     => $now,
        ]);

        /**
         * 7. REDIRECT KE HALAMAN WELCOME DENGAN DATA SUKSES UNTUK STRUK
         */
        return redirect()->route('welcome')->with('success_data', [
            'id'      => $queue->id,
            'nomor'   => $nomorAntrian,
            'nama'    => $request->nama,
            'nik'     => $queue->nik ?? '-', 
            'layanan' => $layanan->nama_layanan,
            'waktu'   => $now->format('H:i'),
            'tanggal' => $now->translatedFormat('d F Y')
        ]);
    }

    /**
     * API untuk mengecek status sistem secara realtime (Digunakan oleh JS di Welcome Page)
     * Menghubungkan ke route: api.system-status.public
     */
    public function getStatus()
    {
        return response()->json([
            'status' => Cache::get('system_status', 'on') // Default 'on' jika belum diset admin
        ]);
    }
}