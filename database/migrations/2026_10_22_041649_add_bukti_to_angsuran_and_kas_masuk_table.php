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
        Schema::table('angsuran', function (Blueprint $table) {
            $table->string('bukti_bayar')->nullable()->after('keterangan');
        });

        Schema::table('kas_masuk', function (Blueprint $table) {
            $table->string('bukti_file')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('angsuran', function (Blueprint $table) {
            $table->dropColumn('bukti_bayar');
        });

        Schema::table('kas_masuk', function (Blueprint $table) {
            $table->dropColumn('bukti_file');
        });
    }
};
