<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Loket, Layanan, Queue};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class PetugasController extends Controller
{
    /**
     * Menampilkan halaman Kelola Petugas (Admin)
     */
    public function index()
    {
        $petugas = User::where('role', 'petugas')->with(['loket', 'layanan'])->get();
        $lokets = Loket::all();
        $layanans = Layanan::all();

        return view('admin.petugas_index', compact('petugas', 'lokets', 'layanans'));
    }

    /**
     * Simpan Petugas Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'username'   => 'required|string|unique:users',
            'password'   => 'required|min:6',
            'loket_id'   => 'required|exists:lokets,id',
            'layanan_id' => 'required|exists:layanans,id',
        ]);

        User::create([
            'name'       => $request->name,
            'username'   => $request->username,
            'password'   => Hash::make($request->password),
            'role'       => 'petugas',
            'loket_id'   => $request->loket_id,
            'layanan_id' => $request->layanan_id,
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return back()->with('success', 'Petugas berhasil ditambahkan.');
    }

    /**
     * Update Data Petugas
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'username'   => 'required|string|unique:users,username,' . $id,
            'loket_id'   => 'required|exists:lokets,id',
            'layanan_id' => 'required|exists:layanans,id',
        ]);

        $data = [
            'name'       => $request->name,
            'username'   => $request->username,
            'loket_id'   => $request->loket_id,
            'layanan_id' => $request->layanan_id,
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data petugas berhasil diperbarui!');
    }

    /**
     * Hapus Petugas
     */
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Akun petugas telah dihapus.');
    }

    /**
     * Fitur Eksport Data Antrian (CSV) - Versi Perbaikan Kolom
     */
    public function exportMasyarakat()
    {
        $data = Queue::with(['layanan', 'user', 'loket'])->orderBy('created_at', 'desc')->get();
        
        $filename = "laporan_antrian_" . Carbon::now('Asia/Jakarta')->format('Ymd_Hi') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Tambahkan BOM agar Excel tidak berantakan
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header sesuai permintaan: No antrian, Nama, NIK, Layanan, Waktu daftar, loket, petugas, status
            fputcsv($handle, [
                'No. Antrian', 
                'Nama Pemohon', 
                'NIK', 
                'Layanan', 
                'Waktu Daftar', 
                'Loket', 
                'Petugas yang Menanggapi', 
                'Status'
            ]);

            foreach ($data as $row) {
                $waktu = Carbon::parse($row->created_at)->timezone('Asia/Jakarta');
                
                fputcsv($handle, [
                    $row->nomor_antrian,
                    $row->nama_pendaftar,
                    "'" . $row->nik, // Menjaga NIK agar tidak jadi angka ilmiah di Excel
                    $row->layanan->nama_layanan ?? '-',
                    $waktu->translatedFormat('d F Y') . ' ' . $waktu->format('H:i'), // Waktu Daftar Digabung
                    $row->loket->nama_loket ?? '-',
                    $row->user->name ?? 'Belum Diproses',
                    ucfirst($row->status)
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update Nama Loket (Opsional jika diperlukan admin)
     */
    public function updateLoket(Request $request, $id)
    {
        $request->validate(['nama_loket' => 'required|string|max:50']);
        $loket = Loket::findOrFail($id);
        $loket->update([
            'nama_loket' => $request->nama_loket,
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return back()->with('success', 'Nama loket diperbarui.');
    }
}