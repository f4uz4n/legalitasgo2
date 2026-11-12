<?php

namespace App\Console\Commands;

use App\Services\ImporRekapPengangsur as ImporRekapPengangsurService;
use Illuminate\Console\Command;

class ImporRekapPengangsur extends Command
{
    protected $signature = 'rekap:import';

    protected $description = 'Masukkan data rekap pengangsur Grand Oriental Tahap 2 beserta jadwal sisa tempo';

    public function handle(ImporRekapPengangsurService $impor): int
    {
        $hasil = $impor->jalankan();

        $this->info("Rekap pengangsur: {$hasil['masuk']} data baru, {$hasil['lewati']} sudah ada, dari {$hasil['total']} baris.");

        return self::SUCCESS;
    }
}
