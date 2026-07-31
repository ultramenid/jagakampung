<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lampiran dokumen untuk tiap info PBPH (SK izin, akta, dsb).
     * Bentuknya sengaja sama dengan konflik_lampiran: nama tampil + nama file
     * di disk `public`, folder pbph-lampiran/.
     */
    public function up(): void
    {
        Schema::create('pbph_lampiran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pbph_info_id')->index();
            $table->string('nama');
            $table->string('file');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbph_lampiran');
    }
};
