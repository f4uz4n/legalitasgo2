<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angsuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghuni')->onDelete('cascade');
            $table->integer('angsuran_ke');
            $table->decimal('jumlah_angsuran', 15, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('denda', 15, 2)->default(0);
            $table->enum('status', ['belum_bayar', 'lunas', 'sebagian', 'terlambat'])->default('belum_bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsuran');
    }
};
