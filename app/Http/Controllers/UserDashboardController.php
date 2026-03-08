<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan, Loket};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserDashboardController extends Controller
{
    /**
     * Menampilkan halaman utama untuk publik/masyarakat
     */
    public function index() {
        $lokets = Loket::all();
        $layanans = Layanan::all();
        
        // Pastikan path view sesuai dengan struktur folder Anda
        return view('public.dashboard_user', compact('lokets', 'layanans'));
    }

    /**
     * Menyimpan pendaftaran antrian baru dari masyarakat
     */
    public function store(Request $request) {
        // 1. Inisialisasi Waktu Jakarta
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $today = $now->toDateString();

        // 2. Ambil data layanan untuk pengecekan syarat NIK
        $layanan = Layanan::findOrFail($request->layanan_id);
        
        /**
         * LOGIKA SINKRONISASI NIK:
         * Menentukan apakah NIK wajib diisi berdasarkan kolom 'is_nik_required' 
         * dan pengecekan khusus kata 'kematian'
         */
        $isKematian = str_contains(strtolower($layanan->nama_layanan), 'kematian');
        $nikWajib = $layanan->is_nik_required && !$isKematian;

        // 3. Validasi Input Dinamis
        $rules = [
            'nama' => 'required|string|max:255',
            'layanan_id' => 'required|exists:layanans,id',
        ];

        if ($nikWajib) {
            // Wajib NIK: Harus ada, harus angka, dan harus tepat 16 digit
            $rules['nik'] = 'required|numeric|digits:16';
        } else {
            // Tidak Wajib NIK: Boleh kosong, tapi jika diisi harus angka 16 digit
            $rules['nik'] = 'nullable|numeric|digits:16';
        }

        $request->validate($rules, [
            'nik.required' => 'NIK wajib diisi untuk layanan ' . $layanan->nama_layanan . '.',
            'nik.digits'   => 'NIK harus berjumlah tepat 16 digit angka.',
            'nik.numeric'  => 'NIK harus berupa angka (0-9).',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'layanan_id.required' => 'Jenis layanan tidak valid.'
        ]);

        // 4. Hitung urutan antrian per layanan KHUSUS HARI INI
        $count = Queue::where('layanan_id', $layanan->id)
                      ->whereDate('created_at', $today)
                      ->count();
        
        $nomorUrut = $count + 1;

        /** * FORMAT NOMOR ANTRIAN:
         * Menggabungkan Prefix Layanan (A, B, C, dst) dengan 3 digit urutan.
         * Contoh: A001, B012
         */
        $nomorAntrian = $layanan->prefix . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);

        // 5. Simpan ke database
        $queue = Queue::create([
            'nomor_antrian'  => $nomorAntrian,
            'nama_pendaftar' => $request->nama,
            'nik'            => $request->filled('nik') ? $request->nik : null,
            'layanan_id'     => $request->layanan_id,
            'loket_id'       => null, 
            'status'         => 'menunggu',
            'created_at'     => $now, 
            'updated_at'     => $now,
        ]);

        /**
         * 6. REDIRECT KE WELCOME (HALAMAN UTAMA)
         * Agar popup sukses muncul di halaman welcome dan saat tombol cetak/tutup diklik
         * user tetap berada di alur menu utama.
         */
        return redirect()->route('welcome')->with('success_data', [
            'id'      => $queue->id,
            'nomor'   => $nomorAntrian,
            'nama'    => $request->nama,
            'nik'     => $queue->nik ?? '--- (Tanpa NIK)',
            'layanan' => $layanan->nama_layanan,
            'waktu'   => $now->format('H:i') . ' WIB',
            'tanggal' => $now->translatedFormat('d F Y')
        ]);
    }
}