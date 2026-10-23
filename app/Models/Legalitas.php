<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Legalitas extends Model
{
    use HasFactory;

    protected $table = 'legalitas';

    protected $fillable = [
        'penghuni_id',
        'jenis_sertifikat',
        'no_sertifikat',
        'no_ajb',
        'tanggal_ajb',
        'notaris',
        'no_imb',
        'no_pbb',
        'status',
        'progress_persen',
        'tanggal_mulai',
        'tanggal_selesai',
        'estimasi_selesai',
        'biaya_pengurusan',
        'biaya_terbayar',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_ajb' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'estimasi_selesai' => 'date',
        'biaya_pengurusan' => 'decimal:2',
        'biaya_terbayar' => 'decimal:2',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function timeline()
    {
        return $this->hasMany(TimelineLegalitas::class)->orderBy('tanggal', 'desc');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'belum_proses' => 'Belum Diproses',
            'pengajuan' => 'Pengajuan',
            'verifikasi' => 'Verifikasi',
            'proses_bpn' => 'Proses BPN',
            'proses_notaris' => 'Proses Notaris',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'belum_proses' => 'secondary',
            'pengajuan' => 'info',
            'verifikasi' => 'primary',
            'proses_bpn' => 'warning',
            'proses_notaris' => 'purple',
            'selesai' => 'success',
            default => 'secondary',
        };
    }

    public function getSisaBiayaAttribute()
    {
        return $this->biaya_pengurusan - $this->biaya_terbayar;
    }
}
