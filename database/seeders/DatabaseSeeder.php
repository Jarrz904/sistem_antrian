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
        'icon' => 'fas fa-camera-retro' // Ikon kamera untuk rekam
    ],
    [
        'nama_layanan' => 'Pelayanan Cetak KTP', 
        'prefix' => 'B', 
        'is_nik_required' => true, 
        'icon' => 'fas fa-print' // Ikon printer untuk cetak
    ],
    [
        'nama_layanan' => 'Pelayanan Penuh', 
        'prefix' => 'C', 
        'is_nik_required' => true, 
        'icon' => 'fas fa-star' // Ikon bintang untuk layanan penuh
    ],
    [
        'nama_layanan' => 'Pelayanan BAKAK', 
        'prefix' => 'D', 
        'is_nik_required' => true, 
        'icon' => 'fas fa-file-alt' // Ikon dokumen untuk BAKAK
    ],
    [
        'nama_layanan' => 'Pelayanan Kematian Tanpa NIK', 
        'prefix' => 'E', 
        'is_nik_required' => false, 
        'icon' => 'fas fa-user-times' // Ikon monumen/nisan untuk kematian
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
}
}
