<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulasiBphtb extends Model
{
    protected $table = 'simulasi_bphtb';

    public const POTONGAN_PEMBELI = 60000000;

    protected $fillable = [
        'nama_penjual',
        'nilai_jual',
        'njop',
        'npoptkp',
        'persentase',
        'persentase_pembeli',
        'npop',
        'dasar_pengenaan',
        'jumlah_pajak',
        'pajak_penjual',
        'pajak_pembeli',
        'created_by',
    ];

    protected $casts = [
        'nilai_jual' => 'decimal:2',
        'njop' => 'decimal:2',
        'npoptkp' => 'decimal:2',
        'persentase' => 'decimal:2',
        'persentase_pembeli' => 'decimal:2',
        'npop' => 'decimal:2',
        'dasar_pengenaan' => 'decimal:2',
        'jumlah_pajak' => 'decimal:2',
        'pajak_penjual' => 'decimal:2',
        'pajak_pembeli' => 'decimal:2',
    ];
}
