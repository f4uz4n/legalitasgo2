<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legalitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghuni')->onDelete('cascade');
            $table->string('jenis_sertifikat')->nullable(); // SHM, SHGB, AJB, dll
            $table->string('no_sertifikat')->nullable();
            $table->string('no_ajb')->nullable();
            $table->date('tanggal_ajb')->nullable();
            $table->string('notaris')->nullable();
            $table->string('no_imb')->nullable();
            $table->string('no_pbb')->nullable();
            $table->enum('status', [
                'belum_proses',
                'pengajuan',
                'verifikasi',
                'proses_bpn',
                'proses_notaris',
                'selesai'
            ])->default('belum_proses');
            $table->integer('progress_persen')->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->date('estimasi_selesai')->nullable();
            $table->decimal('biaya_pengurusan', 15, 2)->default(0);
            $table->decimal('biaya_terbayar', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legalitas');
    }
};
