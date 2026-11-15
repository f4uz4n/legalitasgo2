<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas_masuk', function (Blueprint $table) {
            $table->boolean('tampil_publik')->default(true)->after('keterangan');
        });

        Schema::table('kas_keluar', function (Blueprint $table) {
            $table->boolean('tampil_publik')->default(true)->after('keterangan');
        });

        Schema::table('timeline_legalitas', function (Blueprint $table) {
            $table->boolean('tampil_publik')->default(true)->after('warna');
        });
    }

    public function down(): void
    {
        Schema::table('kas_masuk', function (Blueprint $table) {
            $table->dropColumn('tampil_publik');
        });
        Schema::table('kas_keluar', function (Blueprint $table) {
            $table->dropColumn('tampil_publik');
        });
        Schema::table('timeline_legalitas', function (Blueprint $table) {
            $table->dropColumn('tampil_publik');
        });
    }
};
