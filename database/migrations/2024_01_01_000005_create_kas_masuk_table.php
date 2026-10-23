<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_masuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_kas_id')->constrained('kategori_kas')->onDelete('restrict');
            $table->foreignId('penghuni_id')->nullable()->constrained('penghuni')->onDelete('set null');
            $table->foreignId('angsuran_id')->nullable()->constrained('angsuran')->onDelete('set null');
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->string('metode_bayar')->nullable();
            $table->string('no_referensi')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_masuk');
    }
};
