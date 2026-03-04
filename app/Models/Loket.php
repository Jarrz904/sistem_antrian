<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loket extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'lokets';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'nama_loket',
        'keterangan', // Opsional, jika Anda ingin menambah deskripsi loket
    ];

    /**
     * Relasi ke User (Petugas)
     * Satu loket bisa ditempati oleh satu atau lebih petugas (tergantung sistem shift)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'loket_id');
    }

    /**
     * Relasi ke Queue (Antrian)
     * Satu loket melayani banyak antrian
     */
    public function queues()
    {
        return $this->hasMany(Queue::class, 'loket_id');
    }
}