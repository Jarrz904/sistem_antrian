<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   // DatabaseSeeder.php
public function run(): void {
    // 1. Seed Layanan
  $layanans = [
        [
            'nama_layanan' => 'Pelayanan Rekam KTP', 
            'prefix' => 'A', 
            'is_nik_required' => true, 
            'icon' => 'fas fa-camera-retro',
            'deskripsi' => 'Pengurusan administrasi kependudukan untuk layanan Pelayanan Rekam KTP.'
        ],
        [
            'nama_layanan' => 'Pelayanan KTP dan KIA', 
            'prefix' => 'B', 
            'is_nik_required' => true, 
            'icon' => 'fas fa-print',
            'deskripsi' => 'Cetak KTP dan KIA,Perekaman Ulang, Tanda tangan Ulang.'
        ],
        [
            'nama_layanan' => 'Pelayanan Adminduk', 
            'prefix' => 'C', 
            'is_nik_required' => true, 
            'icon' => 'fas fa-star',
            'deskripsi' => 'Pengurusan administrasi kependudukan untuk layanan Pelayanan Adminduk.'
        ],
        [
            'nama_layanan' => 'Pelayanan BAKAK', 
            'prefix' => 'D', 
            'is_nik_required' => true, 
            'icon' => 'fas fa-file-alt',
            'deskripsi' => 'Pengurusan administrasi kependudukan untuk layanan Pelayanan BAKAK.'
        ],
        [
            'nama_layanan' => 'Pelayanan Pencatatan Sipil Khusus', 
            'prefix' => 'E', 
            'is_nik_required' => false, 
            'icon' => 'fas fa-user-times',
            'deskripsi' => 'Akte kematian tanpa NIK,Perkawinan Non Muslim dan Perceraian Non Muslim.'
        ],
    ];
    foreach($layanans as $l) \App\Models\Layanan::create($l);

    // 2. Seed Loket
    for($i=1; $i<=10; $i++) \App\Models\Loket::create(['nama_loket' => 'Loket ' . $i]);

    // 3. Seed Admin (Login: admin / admin123)
    \App\Models\User::create([
        'name' => 'Administrator',
        'username' => 'admin',
        'password' => bcrypt('admin123'),
        'role' => 'admin'
    ]);

    // 4. Seed Petugas Contoh (Login: petugas1 / password)
    \App\Models\User::create([
        'name' => 'Budi Petugas',
        'username' => 'petugas1',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 1, // Melayani Rekam KTP
        'loket_id' => 1    // Di Loket 1
    ]);

     \App\Models\User::create([
        'name' => 'Petugas 2',
        'username' => 'petugas2',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 2, // Melayani KTP dan KIA
        'loket_id' => 2    // Di Loket 2
    ]);

     \App\Models\User::create([
        'name' => 'Petugas 3',
        'username' => 'petugas3',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 3, // Melayani Adminduk
        'loket_id' => 3    // Di Loket 3
    ]);

     \App\Models\User::create([
        'name' => 'Petugas 4',
        'username' => 'petugas4',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 3, // Melayani Adminduk
        'loket_id' => 4    // Di Loket 4
    ]);

     \App\Models\User::create([
        'name' => 'Petugas 5',
        'username' => 'petugas5',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 3, // Melayani Adminduk
        'loket_id' => 5    // Di Loket 5
    ]);

     \App\Models\User::create([
        'name' => 'Petugas 6',
        'username' => 'petugas6',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 4, // Melayani BAKAK
        'loket_id' => 6    // Di Loket 6
    ]);
     \App\Models\User::create([
        'name' => 'Petugas 7',
        'username' => 'petugas7',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'layanan_id' => 5, // Melayani Pencatatan Sipil Khusus
        'loket_id' => 7    // Di Loket 7
    ]);
}
}
