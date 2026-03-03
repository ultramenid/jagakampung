<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('konflik', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('kabkota');
            $table->string('kecamatan');
            $table->string('desa');
            $table->string('lat');
            $table->string('long');
            $table->integer('luas');
            $table->integer('kk');
            $table->string('group');
            $table->string('perusahaan');
            $table->string('status');
            $table->text('deskripsikonflik');
            $table->text('deskripsiperjuangan');
            $table->timestamps();
        });

        Schema::create('konflik_lembaga', function (Blueprint $table) {
            $table->id();
            $table->integer('konflik_id');
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('konflik_lampiran', function (Blueprint $table) {
            $table->id();
            $table->integer('konflik_id');
            $table->string('nama');
            $table->string('file');
            $table->timestamps();
        });

        Schema::create('konflik_gambar', function (Blueprint $table) {
            $table->id();
            $table->integer('konflik_id');
            $table->string('nama');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konflik');
        Schema::dropIfExists('konflik_lembaga');
        Schema::dropIfExists('konflik_lampiran');
        Schema::dropIfExists('konflik_gambar');

    }
};
