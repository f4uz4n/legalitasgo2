<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulasi_bphtb', function (Blueprint $table) {
            $table->string('nama_penjual')->nullable()->after('id');
            $table->decimal('pajak_penjual', 15, 2)->default(0)->after('jumlah_pajak');
            $table->decimal('pajak_pembeli', 15, 2)->default(0)->after('pajak_penjual');
        });

        DB::table('simulasi_bphtb')->orderBy('id')->each(function ($baris) {
            $jual = (float) $baris->nilai_jual;
            $persen = (float) $baris->persentase;
            DB::table('simulasi_bphtb')->where('id', $baris->id)->update([
                'pajak_penjual' => round($jual * $persen / 100),
                'pajak_pembeli' => round(max(0, $jual - 60000000) * $persen / 100),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('simulasi_bphtb', function (Blueprint $table) {
            $table->dropColumn(['nama_penjual', 'pajak_penjual', 'pajak_pembeli']);
        });
    }
};
