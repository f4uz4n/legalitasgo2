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
        Schema::create('arsip_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahan_id')->nullable()->constrained('perumahan')->onDelete('cascade');
            $table->string('judul_dokumen');
            $table->string('kategori'); // Perizinan & Legalitas Induk, Sertifikat & Pertanahan, Masterplan & Teknis, Notaris & Kerjasama, Lingkungan & PSU, Administrasi & Perpajakan, Lain-lain
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->string('instansi_penerbit')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->string('file_type', 50)->nullable(); // pdf, image, docx, etc.
            $table->boolean('is_public')->default(false); // apakah dapat dilihat oleh warga di portal transparansi
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_dokumen');
    }
};
