<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulasiBphtb extends Model
{
    protected $table = 'simulasi_bphtb';

    protected $fillable = [
        'nilai_jual',
        'njop',
        'npoptkp',
        'persentase',
        'npop',
        'dasar_pengenaan',
        'jumlah_pajak',
        'created_by',
    ];

    protected $casts = [
        'nilai_jual' => 'decimal:2',
        'njop' => 'decimal:2',
        'npoptkp' => 'decimal:2',
        'persentase' => 'decimal:2',
        'npop' => 'decimal:2',
        'dasar_pengenaan' => 'decimal:2',
        'jumlah_pajak' => 'decimal:2',
    ];
}
