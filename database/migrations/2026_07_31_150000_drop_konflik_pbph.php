<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Buang pivot konflik_pbph — relasi PBPH↔konflik tidak lagi dipakai; konflik
 * cukup dikaitkan lewat nama perusahaan. Tabel pbph_info tetap utuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('konflik_pbph');
    }

    public function down(): void
    {
        // tidak menyangkal: skema lama sudah dihapus permanen.
    }
};