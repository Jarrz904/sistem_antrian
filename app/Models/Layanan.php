<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $fillable = ['nama_layanan', 'deskripsi', 'icon', 'prefix', 'is_nik_required'];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
    
}