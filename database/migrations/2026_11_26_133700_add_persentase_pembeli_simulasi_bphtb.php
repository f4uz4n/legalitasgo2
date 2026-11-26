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
            $table->decimal('persentase_pembeli', 8, 2)->default(0)->after('persentase');
        });

        DB::table('simulasi_bphtb')->update([
            'persentase_pembeli' => DB::raw('persentase'),
        ]);
    }

    public function down(): void
    {
        Schema::table('simulasi_bphtb', function (Blueprint $table) {
            $table->dropColumn('persentase_pembeli');
        });
    }
};
