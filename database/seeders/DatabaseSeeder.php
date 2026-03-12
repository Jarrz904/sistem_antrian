<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Layanan;
use App\Models\Loket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Layanan (Hanya layanan pendaftaran utama)
        $layanans = [
            [
                'nama_layanan' => 'Pelayanan Pencatatan Sipil Khusus',
                'prefix' => 'A',
                'is_nik_required' => false,
                'icon' => 'fas fa-user-shield',
                'deskripsi' => 'Layanan Akte Kematian (Tanpa NIK), Perkawinan, dan Perceraian Non-Muslim.'
            ],
            [
                'nama_layanan' => 'Pelayanan BAKAK',
                'prefix' => 'B',
                'is_nik_required' => true,
                'icon' => 'fas fa-file-alt',
                'deskripsi' => 'Pengurusan administrasi kependudukan untuk layanan Pelayanan BAKAK.'
            ],
            [
                'nama_layanan' => 'Pelayanan Adminduk',
                'prefix' => 'C',
                'is_nik_required' => true,
                'icon' => 'fas fa-star',
                'deskripsi' => 'KK, AKTE Kelahiran, Kematian dan Surat Pindah.'
            ],
            [
                'nama_layanan' => 'Pelayanan KTP dan KIA',
                'prefix' => 'D',
                'is_nik_required' => true,
                'icon' => 'fas fa-print',
                'deskripsi' => 'Cetak KTP dan KIA, Perekaman Ulang, Tanda tangan Ulang.'
            ],
            [
                'nama_layanan' => 'Pelayanan Rekam KTP',
                'prefix' => 'E',
                'is_nik_required' => true,
                'icon' => 'fas fa-camera-retro',
                'deskripsi' => 'Pengurusan administrasi kependudukan untuk layanan Pelayanan Rekam KTP.'
            ],
        ];

        foreach ($layanans as $l) {
            Layanan::create($l);
        }

        // 2. Seed Loket (10 loket standar + 1 loket pengambilan)
        for ($i = 1; $i <= 10; $i++) {
            Loket::create(['nama_loket' => 'Loket ' . $i]);
        }

        // Loket fisik khusus untuk pengambilan berkas
        $loketPengambilan = Loket::create(['nama_loket' => 'Loket Pengambilan']);

        // 3. Seed Admin
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('admin3328'),
            'role' => 'admin'
        ]);

        // 4. Seed Petugas Layanan (Terikat ke Layanan tertentu)
        User::create([
            'name' => 'Petugas Loket 1',
            'username' => 'loket1',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 1,
            'loket_id' => 1
        ]);

        User::create([
            'name' => 'Petugas Loket 2',
            'username' => 'loket2',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 2,
            'loket_id' => 2
        ]);

        User::create([
            'name' => 'Petugas Loket 3',
            'username' => 'loket3',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 3,
            'loket_id' => 3
        ]);

        User::create([
            'name' => 'Petugas Loket 4',
            'username' => 'loket4',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 3,
            'loket_id' => 4
        ]);

        User::create([
            'name' => 'Petugas Loket 5',
            'username' => 'loket5',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 3,
            'loket_id' => 5
        ]);

        User::create([
            'name' => 'Petugas Loket 6',
            'username' => 'loket6',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 4,
            'loket_id' => 6
        ]);

        User::create([
            'name' => 'Petugas Loket 7',
            'username' => 'loket7',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => 5,
            'loket_id' => 7
        ]);

        // 5. Seed Petugas Pengambilan Dokumen
        // role tetap 'petugas', loket tetap ada, tapi layanan_id disetel NULL
        User::create([
            'name' => 'Petugas Loket Pengambilan Berkas dan Dokumen',
            'username' => 'loketberkas',
            'password' => bcrypt('petugas3328'),
            'role' => 'petugas',
            'layanan_id' => null, // Tidak terikat layanan mana pun
            'loket_id' => $loketPengambilan->id
        ]);
    }
}
