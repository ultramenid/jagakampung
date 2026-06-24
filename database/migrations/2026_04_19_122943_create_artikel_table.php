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
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konflik_id')->constrained('konflik')->onDelete('cascade');
            $table->string('judul_id');
            $table->string('judul_en');
            $table->string('slug')->unique();
            $table->longText('deskripsi_id')->nullable();
            $table->longText('deskripsi_en')->nullable();
            $table->string('gambar')->nullable();
            $table->string('sumber')->nullable();
            $table->date('tanggal_publish')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
