<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perumahan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perumahan');
            $table->text('alamat');
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->integer('total_unit')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perumahan');
    }
};
