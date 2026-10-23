<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasMasuk extends Model
{
    use HasFactory;

    protected $table = 'kas_masuk';

    protected $fillable = [
        'kategori_kas_id',
        'penghuni_id',
        'angsuran_id',
        'tanggal',
        'jumlah',
        'metode_bayar',
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

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function angsuran()
    {
        return $this->belongsTo(Angsuran::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
