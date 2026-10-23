<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penghuni extends Model
{
    use HasFactory;

    protected $table = 'penghuni';

    protected $fillable = [
        'user_id',
        'perumahan_id',
        'nama_lengkap',
        'nik',
        'no_hp',
        'email',
        'blok_kavling',
        'tipe_rumah',
        'luas_tanah',
        'luas_bangunan',
        'harga_rumah',
        'uang_muka',
        'sisa_angsuran',
        'tenor_bulan',
        'tanggal_akad',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_akad' => 'date',
        'harga_rumah' => 'decimal:2',
        'uang_muka' => 'decimal:2',
        'sisa_angsuran' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perumahan()
    {
        return $this->belongsTo(Perumahan::class);
    }

    public function angsuran()
    {
        return $this->hasMany(Angsuran::class);
    }

    public function kasMasuk()
    {
        return $this->hasMany(KasMasuk::class);
    }

    public function legalitas()
    {
        return $this->hasOne(Legalitas::class);
    }

    public function getTotalBayarAttribute()
    {
        return $this->angsuran()->where('status', 'lunas')->sum('jumlah_bayar');
    }

    public function getAngsuranBulananAttribute()
    {
        if ($this->tenor_bulan > 0) {
            return ($this->harga_rumah - $this->uang_muka) / $this->tenor_bulan;
        }
        return 0;
    }

    public function getProgressBayarAttribute()
    {
        if ($this->harga_rumah > 0) {
            $total = $this->uang_muka + $this->total_bayar;
            return min(100, round(($total / $this->harga_rumah) * 100, 1));
        }
        return 0;
    }
}
