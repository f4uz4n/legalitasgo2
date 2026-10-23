<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'admin_name',
        'admin_email',
        'action',
        'module',
        'description',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Perlindungan integritas: Log aktivitas bersifat permanen dan TIDAK BISA DIHAPUS atau DIUBAH.
     */
    protected static function booted()
    {
        static::deleting(function ($model) {
            throw new RuntimeException('Log aktivitas audit bersifat permanen dan tidak dapat dihapus demi menjaga integritas data.');
        });

        static::updating(function ($model) {
            throw new RuntimeException('Log aktivitas audit bersifat permanen dan tidak dapat dimodifikasi.');
        });
    }

    public function delete()
    {
        throw new RuntimeException('Log aktivitas audit bersifat permanen dan tidak dapat dihapus demi menjaga integritas data.');
    }

    public function forceDelete()
    {
        throw new RuntimeException('Log aktivitas audit bersifat permanen dan tidak dapat dihapus demi menjaga integritas data.');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label representasi aksi
     */
    public function getActionLabelAttribute(): string
    {
        return match (strtoupper($this->action)) {
            'INSERT' => 'Tambah Data',
            'UPDATE' => 'Perbarui Data',
            'DELETE' => 'Hapus Data',
            'VIEW'   => 'Akses Menu',
            default  => $this->action,
        };
    }

    /**
     * Badge CSS class untuk aksi
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match (strtoupper($this->action)) {
            'INSERT' => 'bg-success text-white',
            'UPDATE' => 'bg-warning text-dark',
            'DELETE' => 'bg-danger text-white',
            'VIEW'   => 'bg-primary text-white',
            default  => 'bg-secondary text-white',
        };
    }

    /**
     * Icon Bootstrap untuk aksi
     */
    public function getActionIconAttribute(): string
    {
        return match (strtoupper($this->action)) {
            'INSERT' => 'bi-plus-circle-fill',
            'UPDATE' => 'bi-pencil-square',
            'DELETE' => 'bi-trash3-fill',
            'VIEW'   => 'bi-eye-fill',
            default  => 'bi-activity',
        };
    }
}
