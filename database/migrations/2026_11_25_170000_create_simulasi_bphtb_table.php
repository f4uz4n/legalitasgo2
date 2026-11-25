<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulasi_bphtb', function (Blueprint $table) {
            $table->id();
            $table->decimal('nilai_jual', 15, 2)->default(0);
            $table->decimal('njop', 15, 2)->default(0);
            $table->decimal('npoptkp', 15, 2)->default(0);
            $table->decimal('persentase', 8, 2)->default(0);
            $table->decimal('npop', 15, 2)->default(0);
            $table->decimal('dasar_pengenaan', 15, 2)->default(0);
            $table->decimal('jumlah_pajak', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulasi_bphtb');
    }
};
