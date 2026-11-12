<?php

use App\Services\ImporRekapPengangsur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        app(ImporRekapPengangsur::class)->jalankan();
    }

    public function down(): void
    {
        $baris = require database_path('data/rekap_pengangsur.php');

        foreach ($baris as $row) {
            $penghuni = DB::table('penghuni')
                ->where('nama_lengkap', $row['nama_lengkap'])
                ->where('blok_kavling', $row['blok_kavling'])
                ->first();

            if (!$penghuni) {
                continue;
            }

            $sudahBayar = DB::table('angsuran')
                ->where('penghuni_id', $penghuni->id)
                ->where('jumlah_bayar', '>', 0)
                ->exists();

            if ($sudahBayar) {
                continue;
            }

            DB::table('penghuni')->where('id', $penghuni->id)->delete();
        }
    }
};
