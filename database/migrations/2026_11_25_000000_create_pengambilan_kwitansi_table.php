<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengambilan_kwitansi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('angsuran_id')->unique()->constrained('angsuran')->cascadeOnDelete();
            $table->foreignId('penghuni_id')->constrained('penghuni')->cascadeOnDelete();
            $table->date('tanggal_ambil');
            $table->string('nama_pengambil');
            $table->string('hubungan', 30);
            $table->string('no_hp', 30)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengambilan_kwitansi');
    }
};
