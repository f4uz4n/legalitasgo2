<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ArsipDokumen extends Model
{
    use HasFactory;

    protected $table = 'arsip_dokumen';

    protected $fillable = [
        'perumahan_id',
        'judul_dokumen',
        'kategori',
        'nomor_dokumen',
        'tanggal_dokumen',
        'instansi_penerbit',
        'deskripsi',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'is_public',
        'uploaded_by',
    ];

    protected $casts = [
        'tanggal_dokumen' => 'date',
        'file_size'       => 'integer',
        'is_public'       => 'boolean',
    ];

    public function perumahan()
    {
        return $this->belongsTo(Perumahan::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'                      => 'bi-file-earmark-pdf-fill text-danger',
            'jpg', 'jpeg', 'png', 'webp', 'svg' => 'bi-file-earmark-image-fill text-primary',
            'doc', 'docx'              => 'bi-file-earmark-word-fill text-info',
            'xls', 'xlsx', 'csv'       => 'bi-file-earmark-excel-fill text-success',
            'zip', 'rar', '7z'         => 'bi-file-earmark-zip-fill text-warning',
            default                    => 'bi-file-earmark-text-fill text-secondary',
        };
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg']);
    }

    public function getIsPdfAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return $ext === 'pdf';
    }

    public function getKategoriBadgeAttribute(): array
    {
        return match ($this->kategori) {
            'Perizinan & Legalitas Induk' => ['class' => 'bg-primary-subtle text-primary border border-primary-subtle', 'icon' => 'bi-patch-check-fill'],
            'Sertifikat & Pertanahan'     => ['class' => 'bg-success-subtle text-success border border-success-subtle', 'icon' => 'bi-award-fill'],
            'Masterplan & Teknis'         => ['class' => 'bg-info-subtle text-info border border-info-subtle', 'icon' => 'bi-diagram-3-fill'],
            'Notaris & Kerjasama'         => ['class' => 'bg-warning-subtle text-warning border border-warning-subtle', 'icon' => 'bi-journal-bookmark-fill'],
            'Lingkungan & PSU'            => ['class' => 'bg-teal-subtle text-teal border', 'icon' => 'bi-tree-fill'],
            'Administrasi & Perpajakan'   => ['class' => 'bg-purple-subtle text-purple border', 'icon' => 'bi-receipt-cutoff'],
            default                       => ['class' => 'bg-secondary-subtle text-secondary border', 'icon' => 'bi-folder-fill'],
        };
    }
}
