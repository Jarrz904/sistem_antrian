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
        Schema::table('users', function (Blueprint $table) {
            // Role user: admin atau petugas
            $table->enum('role', ['admin', 'petugas'])->default('petugas')->after('password');
            
            // Kolom loket_id: Loket fisik tempat petugas duduk (Loket 1, Loket 2, dsb)
            $table->foreignId('loket_id')->nullable()->after('role')->constrained('lokets')->onDelete('set null');
            
            // Kolom layanan_id: Jenis layanan yang ditangani petugas ini (KTP, KK, dsb)
            // Ini kunci agar pemanggilan antrian tidak tercampur (tidak universal)
            $table->foreignId('layanan_id')->nullable()->after('loket_id')->constrained('layanans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum hapus kolom
            $table->dropForeign(['loket_id']);
            $table->dropForeign(['layanan_id']);
            
            // Hapus kolom-kolom yang ditambahkan
            $table->dropColumn(['role', 'loket_id', 'layanan_id']);
        });
    }
};