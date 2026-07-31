<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Informasi tambahan untuk tiap konsesi PBPH.
     *
     * Data spasial PBPH tidak ada di database aplikasi — dilayani GeoServer
     * (layer jagamkampung:PBPH_JULI2026), jadi tabel ini hanya menyimpan atribut
     * kurasi dan dikaitkan lewat kode_pbph.
     */
    public function up(): void
    {
        Schema::create('pbph_info', function (Blueprint $table) {
            $table->id();
            // kunci ke layer PBPH; sampel data 13 karakter, 572 kode unik dari 575 poligon
            $table->string('kode_pbph', 30)->unique();
            $table->string('nama_perusahaan')->nullable();
            $table->text('izin_pertama')->nullable();
            $table->text('izin_saat_ini')->nullable();
            $table->decimal('luas', 15, 2)->nullable();
            $table->text('komisaris')->nullable();
            $table->text('direktur_utama')->nullable();
            $table->text('direktur')->nullable();
            $table->timestamps();
        });

        // ponytail: pivot, bukan kolom konflik_id — satu konsesi bisa punya banyak
        // konflik dan sebaliknya. 8 baris sekarang, nol perubahan skema nanti.
        Schema::create('konflik_pbph', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pbph_info_id');
            $table->unsignedBigInteger('konflik_id');
            $table->timestamps();

            $table->unique(['pbph_info_id', 'konflik_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konflik_pbph');
        Schema::dropIfExists('pbph_info');
    }
};
