<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKas extends Model
{
    use HasFactory;

    protected $table = 'kategori_kas';

    protected $fillable = [
        'nama_kategori',
        'jenis',
        'keterangan',
    ];

    public function kasMasuk()
    {
        return $this->hasMany(KasMasuk::class);
    }

    public function kasKeluar()
    {
        return $this->hasMany(KasKeluar::class);
    }
}
