<?php

namespace App\Services;

use App\Models\Penghuni;
use App\Models\Perumahan;
use Illuminate\Support\Facades\DB;

class ImporRekapPengangsur
{
    public function jalankan(): array
    {
        $baris = require database_path('data/rekap_pengangsur.php');
        $masuk = 0;
        $lewati = 0;

        DB::transaction(function () use ($baris, &$masuk, &$lewati) {
            $perumahan = Perumahan::query()->first() ?? Perumahan::create([
                'nama_perumahan' => 'Grandoriental II',
                'alamat' => 'Desa Gerogol, Kec. Tulangan, Kab. Sidoarjo',
                'kecamatan' => 'Tulangan',
                'kelurahan' => 'Gerogol',
                'kota' => 'Kabupaten Sidoarjo',
                'provinsi' => 'Jawa Timur',
                'total_unit' => 150,
            ]);

            foreach ($baris as $row) {
                $sudahAda = Penghuni::query()
                    ->where('nama_lengkap', $row['nama_lengkap'])
                    ->where('blok_kavling', $row['blok_kavling'])
                    ->exists();

                if ($sudahAda) {
                    $lewati++;
                    continue;
                }

                $penghuni = Penghuni::create([
                    'perumahan_id' => $perumahan->id,
                    'nama_lengkap' => $row['nama_lengkap'],
                    'blok_kavling' => $row['blok_kavling'],
                    'tahun_pembelian' => $row['tahun_pembelian'],
                    'jenis_pembayaran' => $row['jenis_pembayaran'],
                    'harga_rumah' => $row['harga_rumah'],
                    'uang_muka' => $row['uang_muka'],
                    'nominal_angsuran' => $row['nominal_angsuran'],
                    'sisa_angsuran' => $row['sisa_angsuran'],
                    'tenor_bulan' => $row['tenor_bulan'],
                    'sisa_tempo' => $row['sisa_tempo'],
                    'tunggakan' => $row['tunggakan'],
                    'tanggal_akad' => sprintf('%04d-01-01', (int) $row['tahun_pembelian']),
                    'status' => ((int) $row['sisa_angsuran'] <= 0 && (int) $row['sisa_tempo'] <= 0) ? 'lunas' : 'aktif',
                    'keterangan' => $row['keterangan'],
                ]);

                $penghuni->buatJadwalSisa();
                $masuk++;
            }
        });

        return [
            'masuk' => $masuk,
            'lewati' => $lewati,
            'total' => count($baris),
        ];
    }
}
