<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perumahan extends Model
{
    use HasFactory;

    protected $table = 'perumahan';

    protected $fillable = [
        'nama_perumahan',
        'alamat',
        'kecamatan',
        'kelurahan',
        'kota',
        'provinsi',
        'total_unit',
        'keterangan',
    ];

    public function penghuni()
    {
        return $this->hasMany(Penghuni::class);
    }

    public function getTotalPenghuniAttribute()
    {
        return $this->penghuni()->count();
    }

    public function getTotalLunasAttribute()
    {
        return $this->penghuni()->where('status', 'lunas')->count();
    }
}
