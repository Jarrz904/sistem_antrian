<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    protected $fillable = [
        'nomor_antrian',
        'nama_pendaftar',
        'nik',
        'layanan_id',
        'loket_id',
        'user_id', // Menambahkan user_id petugas
        'status',
        'panggil_at'
    ];

    /**
     * Relasi ke data Layanan
     */
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    /**
     * Relasi ke data Loket
     */
    public function loket(): BelongsTo
    {
        return $this->belongsTo(Loket::class);
    }

    /**
     * Relasi ke data Petugas (User)
     * Mengambil nama petugas dari kolom 'name' di tabel users
     */
   public function petugas()
    {
        // Parameter kedua adalah 'user_id' sesuai nama kolom di database Anda
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}