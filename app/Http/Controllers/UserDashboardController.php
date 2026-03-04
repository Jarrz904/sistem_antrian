<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Queue, Layanan, Loket};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserDashboardController extends Controller
{
    public function index() {
        $lokets = Loket::all();
        $layanans = Layanan::all();
        return view('public.dashboard_user', compact('lokets', 'layanans'));
    }

    public function store(Request $request) {
        // 1. Inisialisasi Waktu Jakarta
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);
        $today = $now->toDateString();

        // 2. Ambil data layanan terlebih dahulu untuk pengecekan syarat NIK
        $layanan = Layanan::findOrFail($request->layanan_id);
        
        // Cek apakah ini layanan kematian (bisa berdasarkan nama atau kolom khusus)
        // Di sini saya asumsikan pengecekan berdasarkan nama layanan
        $isKematian = str_contains(strtolower($layanan->nama_layanan), 'kematian');

        // 3. Validasi Input Dinamis
        $rules = [
            'nama' => 'required|string|max:255',
            'layanan_id' => 'required|exists:layanans,id',
        ];

        // Jika BUKAN layanan kematian, NIK wajib 16 digit
        if (!$isKematian) {
            $rules['nik'] = 'required|digits:16';
        } else {
            $rules['nik'] = 'nullable|digits:16'; // Untuk kematian, boleh kosong atau diisi 16 digit
        }

        $request->validate($rules);

        // 4. Hitung urutan antrian per layanan KHUSUS HARI INI
        $count = Queue::where('layanan_id', $layanan->id)
                      ->whereDate('created_at', $today)
                      ->count();
        
        $nomorUrut = $count + 1;
        $nomorAntrian = $layanan->prefix . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);

        // 5. Simpan ke database
        $queue = Queue::create([
            'nomor_antrian'  => $nomorAntrian,
            'nama_pendaftar' => $request->nama,
            'nik'            => $isKematian && empty($request->nik) ? null : $request->nik,
            'layanan_id'     => $request->layanan_id,
            'loket_id'       => null, 
            'status'         => 'menunggu',
            'created_at'     => $now, 
            'updated_at'     => $now,
        ]);

        // 6. Alihkan kembali dengan data sukses
        return redirect()->back()->with('success_data', [
            'nomor'   => $nomorAntrian,
            'nama'    => $request->nama,
            'nik'     => $queue->nik ?? '--- (Pelayanan Kematian)',
            'layanan' => $layanan->nama_layanan,
            'waktu'   => $now->translatedFormat('d M Y | H:i') . ' WIB'
        ]);
    }
}