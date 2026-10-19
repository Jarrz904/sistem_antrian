<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Layanan extends Model
{
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

    /**
     * Accessor untuk mendapatkan kuota efektif hari ini.
     * Menggunakan nilai dari database jika diisi, atau kuota otomatis berdasarkan hari.
     */
    public function getKuotaEfektifAttribute(): int
    {
        // 1. Jika Admin mengisi kuota_harian secara manual (selain null/0), gunakan nilai tersebut
        if (!is_null($this->kuota_harian) && $this->kuota_harian > 0) {
            return (int) $this->kuota_harian;
        }

        // 2. Jika kuota_harian kosong/null/0, jalankan logika otomatis
        $dayOfWeek = Carbon::now()->dayOfWeekIso; // 1 = Senin, 5 = Jumat, 7 = Minggu

        return match (true) {
            $dayOfWeek >= 1 && $dayOfWeek <= 4 => 60, // Senin - Kamis
            $dayOfWeek === 5                  => 40, // Jumat
            default                           => 0,  // Sabtu - Minggu (Libur)
        };
    }
}
