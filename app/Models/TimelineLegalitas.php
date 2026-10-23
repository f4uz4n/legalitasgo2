<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineLegalitas extends Model
{
    use HasFactory;

    protected $table = 'timeline_legalitas';

    protected $fillable = [
        'legalitas_id',
        'kavling_terkait',
        'tanggal',
        'judul',
        'deskripsi',
        'status',
        'dokumen_file',
        'icon',
        'warna',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function legalitas()
    {
        return $this->belongsTo(Legalitas::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'proses' => 'Dalam Proses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => $this->status,
        };
    }
}
