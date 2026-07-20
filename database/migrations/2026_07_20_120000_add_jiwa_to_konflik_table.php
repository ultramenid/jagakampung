<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konflik', function (Blueprint $table) {
            $table->integer('jiwa')->nullable()->after('kk');
        });
    }

    public function down(): void
    {
        Schema::table('konflik', function (Blueprint $table) {
            $table->dropColumn('jiwa');
        });
    }
};
