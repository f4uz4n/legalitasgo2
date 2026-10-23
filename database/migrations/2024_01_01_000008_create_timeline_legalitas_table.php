<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_legalitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legalitas_id')->nullable()->constrained('legalitas')->onDelete('cascade');
            $table->string('kavling_terkait')->nullable();
            $table->date('tanggal');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['pending', 'proses', 'selesai', 'ditolak'])->default('proses');
            $table->string('dokumen_file')->nullable();
            $table->string('icon')->nullable();
            $table->string('warna')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_legalitas');
    }
};
