<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengambilanKwitansi extends Model
{
    protected $table = 'pengambilan_kwitansi';

    protected $fillable = [
        'angsuran_id',
        'penghuni_id',
        'tanggal_ambil',
        'nama_pengambil',
        'hubungan',
        'no_hp',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_ambil' => 'date',
    ];

    public const HUBUNGAN = [
        'pemilik' => 'Pemilik / pengangsur',
        'keluarga' => 'Keluarga',
        'kuasa' => 'Kuasa',
        'lain' => 'Lainnya',
    ];

    public function angsuran()
    {
        return $this->belongsTo(Angsuran::class);
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function getHubunganLabelAttribute(): string
    {
        return self::HUBUNGAN[$this->hubungan] ?? $this->hubungan;
    }
}
