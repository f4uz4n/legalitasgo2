<?php

namespace App\Support;

use App\Models\Pengaturan;

class PortalWarga
{
    public const BAGIAN = [
        'dana_masuk' => 'Dana masuk',
        'pengeluaran' => 'Pengeluaran kas',
        'saldo' => 'Saldo kas',
        'timeline' => 'Timeline pemberkasan',
        'kearsipan' => 'Kearsipan dokumen',
        'log' => 'Log aktivitas pengelola',
    ];

    public function persen(): int
    {
        $nilai = (int) Pengaturan::getValue('portal_persen_dana_masuk', 100);

        return max(0, min(100, $nilai));
    }

    public function bagian(string $kunci): bool
    {
        return Pengaturan::getValue('portal_bagian_' . $kunci, '1') === '1';
    }

    public function semuanya(): array
    {
        $bagian = [];
        foreach (array_keys(self::BAGIAN) as $kunci) {
            $bagian[$kunci] = $this->bagian($kunci);
        }

        return $bagian;
    }

    public function nominalMasuk(float $jumlah): float
    {
        return round($jumlah * $this->persen() / 100);
    }
}
