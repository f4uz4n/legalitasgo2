<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_kas_id')->constrained('kategori_kas')->onDelete('restrict');
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->string('penerima')->nullable();
            $table->string('no_referensi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('bukti_file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_keluar');
    }
};
