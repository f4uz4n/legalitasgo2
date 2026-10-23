<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penghuni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahan_id')->constrained('perumahan')->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->string('nik', 16)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('blok_kavling');
            $table->string('tipe_rumah')->nullable();
            $table->decimal('luas_tanah', 10, 2)->nullable();
            $table->decimal('luas_bangunan', 10, 2)->nullable();
            $table->decimal('harga_rumah', 15, 2)->default(0);
            $table->decimal('uang_muka', 15, 2)->default(0);
            $table->decimal('sisa_angsuran', 15, 2)->default(0);
            $table->integer('tenor_bulan')->default(0);
            $table->date('tanggal_akad')->nullable();
            $table->enum('status', ['aktif', 'lunas', 'bermasalah'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penghuni');
    }
};
