<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penghuni', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun_pembelian')->nullable()->after('blok_kavling');
            $table->string('jenis_pembayaran', 20)->default('inhouse')->after('tahun_pembelian');
            $table->decimal('nominal_angsuran', 15, 2)->default(0)->after('uang_muka');
            $table->unsignedInteger('sisa_tempo')->nullable()->after('tenor_bulan');
            $table->unsignedInteger('tunggakan')->nullable()->after('sisa_tempo');
        });

        $today = now()->toDateString();

        DB::table('penghuni')->orderBy('id')->get()->each(function ($row) use ($today) {
            $tenor = (int) $row->tenor_bulan;
            $harga = (float) $row->harga_rumah;
            $uangMuka = (float) $row->uang_muka;
            $nominal = $tenor > 0 ? round(($harga - $uangMuka) / $tenor, 2) : 0;

            $totalJadwal = DB::table('angsuran')->where('penghuni_id', $row->id)->count();
            $belumLunas = DB::table('angsuran')
                ->where('penghuni_id', $row->id)
                ->where('status', '!=', 'lunas')
                ->count();
            $terlambat = DB::table('angsuran')
                ->where('penghuni_id', $row->id)
                ->where(function ($q) use ($today) {
                    $q->where('status', 'terlambat')
                        ->orWhere(function ($q2) use ($today) {
                            $q2->where('status', 'belum_bayar')
                                ->whereDate('tanggal_jatuh_tempo', '<', $today);
                        });
                })
                ->count();

            if ($totalJadwal > 0) {
                $sisaTempo = $belumLunas;
            } else {
                $sisaTempo = ((float) $row->sisa_angsuran) > 0 ? $tenor : 0;
            }

            $tahun = $row->tanggal_akad ? (int) date('Y', strtotime($row->tanggal_akad)) : null;

            DB::table('penghuni')->where('id', $row->id)->update([
                'tahun_pembelian' => $tahun,
                'jenis_pembayaran' => 'inhouse',
                'nominal_angsuran' => $nominal,
                'sisa_tempo' => $sisaTempo,
                'tunggakan' => $terlambat > 0 ? $terlambat : null,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('penghuni', function (Blueprint $table) {
            $table->dropColumn([
                'tahun_pembelian',
                'jenis_pembayaran',
                'nominal_angsuran',
                'sisa_tempo',
                'tunggakan',
            ]);
        });
    }
};
