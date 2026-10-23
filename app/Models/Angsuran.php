<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Angsuran extends Model
{
    use HasFactory;

    protected $table = 'angsuran';

    protected $fillable = [
        'penghuni_id',
        'angsuran_ke',
        'jumlah_angsuran',
        'tanggal_jatuh_tempo',
        'tanggal_bayar',
        'jumlah_bayar',
        'denda',
        'status',
        'keterangan',
        'bukti_bayar',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'jumlah_angsuran' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'denda' => 'decimal:2',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kasMasuk()
    {
        return $this->hasOne(KasMasuk::class);
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'belum_bayar' && $this->tanggal_jatuh_tempo->isPast();
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'belum_bayar' => 'Belum Bayar',
            'lunas' => 'Lunas',
            'sebagian' => 'Sebagian',
            'terlambat' => 'Terlambat',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'belum_bayar' => 'warning',
            'lunas' => 'success',
            'sebagian' => 'info',
            'terlambat' => 'danger',
            default => 'secondary',
        };
    }
}
