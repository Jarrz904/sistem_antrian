<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $fillable = ['nama_layanan', 'prefix', 'is_nik_required,icon'];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
    
}