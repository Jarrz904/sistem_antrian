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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_antrian'); 
            $table->string('nama_pendaftar');
            
            /** * PERBAIKAN: NIK dibuat nullable karena tidak digunakan.
             * Panjang 16 digit tetap dipertahankan sebagai cadangan struktur.
             */
            $table->string('nik', 16)->nullable();
            
            $table->foreignId('layanan_id')->constrained()->onDelete('cascade');
            $table->foreignId('loket_id')->nullable()->constrained()->onDelete('set null');
            
            // Menghubungkan ke tabel users (petugas yang memproses antrian)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            /** * ALUR 6 STATUS (String Based):
             * 1. menunggu            : Antrian baru terdaftar.
             * 2. dipanggil           : Sedang dipanggil oleh petugas loket pelayanan.
             * 3. dilewati            : Masyarakat tidak hadir saat dipanggil loket pelayanan.
             * 4. diproses            : Petugas selesai melayani & dokumen sedang diproduksi/dikerjakan.
             * 5. pengambilan_dokumen : Sedang dipanggil oleh petugas loket pengambilan.
             * 6. selesai             : Dokumen sudah diterima, antrian selesai sepenuhnya.
             */
            $table->string('status')->default('menunggu');
            
            $table->timestamp('panggil_at')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};