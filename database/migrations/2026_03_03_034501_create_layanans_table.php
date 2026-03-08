<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('layanans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_layanan');
            $table->text('deskripsi')->nullable(); // Menampung keterangan layanan (Akte Kematian, dll)
            $table->string('icon')->nullable();    // Menampung class FontAwesome
            $table->char('prefix', 1);             // Prefix nomor antrian (A, B, C)
            
            /** * Kolom Kunci Sinkronisasi: 
             * true  = Layanan wajib NIK (Perkawinan/Perceraian Non-Muslim)
             * false = Layanan tidak butuh NIK (Akte Kematian Tanpa NIK)
             */
            $table->boolean('is_nik_required')->default(true); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};