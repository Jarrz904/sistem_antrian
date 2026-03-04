<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_antrian'); 
            $table->string('nama_pendaftar');
            $table->string('nik', 16)->nullable();
            $table->foreignId('layanan_id')->constrained()->onDelete('cascade');
            $table->foreignId('loket_id')->nullable()->constrained()->onDelete('set null');
            
            // TAMBAHKAN INI: Menghubungkan ke tabel users (petugas)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('status', ['menunggu', 'dipanggil', 'lewat', 'selesai'])->default('menunggu');
            $table->timestamp('panggil_at')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};