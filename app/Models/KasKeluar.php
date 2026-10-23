<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasKeluar extends Model
{
    use HasFactory;

    protected $table = 'kas_keluar';

    protected $fillable = [
        'kategori_kas_id',
        'tanggal',
        'jumlah',
        'penerima',
        'no_referensi',
        'keterangan',
        'bukti_file',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKas::class, 'kategori_kas_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
