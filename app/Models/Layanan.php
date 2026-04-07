<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    // Perbaikan: Pastikan pemisah antar kolom hanya menggunakan satu koma dan tanda petik yang benar
    protected $fillable = [
        'nama_layanan',
        'deskripsi',
        'icon',
        'prefix',
        'is_nik_required',
        'is_active',
        'kuota_harian'
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}
