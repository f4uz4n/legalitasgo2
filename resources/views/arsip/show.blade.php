@extends('layouts.admin')
@section('title', 'Detail Arsip - ' . $arsip->judul_dokumen)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('arsip.index') }}">Kearsipan</a></li>
<li class="breadcrumb-item active">Detail Dokumen</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip
        </a>
        <h4 class="fw-bold mb-1 text-dark">{{ $arsip->judul_dokumen }}</h4>
        <div class="d-flex flex-wrap align-items-center gap-2 small">
            @php $badge = $arsip->kategori_badge; @endphp
            <span class="badge {{ $badge['class'] }} px-2 py-1 fw-semibold">
                <i class="bi {{ $badge['icon'] }} me-1"></i> {{ $arsip->kategori }}
            </span>
            <span class="text-muted">&bull;</span>
            <span class="text-muted">Diunggah: {{ $arsip->created_at->translatedFormat('d F Y H:i') }}</span>
            <span class="text-muted">&bull;</span>
            @if($arsip->is_public)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-eye-fill me-1"></i> Akses Publik Warga
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                    <i class="bi bi-lock-fill me-1"></i> Khusus Internal Pengelola
                </span>
            @endif
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('arsip.download', $arsip) }}" class="btn btn-success btn-sm fw-bold px-3 py-2 shadow-sm rounded-3">
            <i class="bi bi-download me-1"></i> Unduh Berkas ({{ $arsip->file_size_formatted }})
        </a>
        <a href="{{ route('arsip.edit', $arsip) }}" class="btn btn-outline-primary btn-sm rounded-3">
            <i class="bi bi-pencil me-1"></i> Edit Informasi
        </a>
        <form action="{{ route('arsip.destroy', $arsip) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip dokumen ini secara permanen?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                <i class="bi bi-trash me-1"></i> Hapus
            </button>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Metadata Dokumen -->
    <div class="col-lg-4">
        <div class="content-card bg-white p-4 shadow-sm border mb-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="bi bi-info-circle text-primary me-1"></i> Rincian Informasi Dokumen
            </h6>

            <div class="mb-3">
                <div class="text-muted small">Nomor Dokumen / SK:</div>
                <div class="fw-bold text-dark fs-6">{{ $arsip->nomor_dokumen ?: 'Tidak dicantumkan' }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Tanggal Terbit / Disahkan:</div>
                <div class="fw-semibold text-dark">
                    {{ $arsip->tanggal_dokumen ? $arsip->tanggal_dokumen->translatedFormat('d F Y') : 'Tidak dicantumkan' }}
                </div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Instansi / Lembaga Penerbit:</div>
                <div class="fw-semibold text-dark">{{ $arsip->instansi_penerbit ?: 'Tidak dicantumkan' }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Nama File Asli:</div>
                <div class="small fw-semibold text-break text-secondary">{{ $arsip->file_name }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Ukuran &amp; Format:</div>
                <div class="small fw-semibold text-dark">
                    <span class="badge bg-light text-dark border me-1">{{ strtoupper($arsip->file_type) }}</span>
                    {{ $arsip->file_size_formatted }}
                </div>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Pengunggah Dokumen:</div>
                <div class="small fw-semibold text-dark">{{ $arsip->uploader->name ?? 'Administrator' }}</div>
            </div>

            @if($arsip->deskripsi)
            <div class="pt-3 border-top">
                <div class="text-muted small mb-1">Catatan / Deskripsi:</div>
                <div class="small text-dark p-2.5 rounded-3 bg-light border">
                    {{ $arsip->deskripsi }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Pratinjau Dokumen -->
    <div class="col-lg-8">
        <div class="content-card bg-white p-4 shadow-sm border">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-text text-primary"></i> Pratinjau Dokumen
                </h6>
                <a href="{{ $arsip->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-2">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Layar Penuh
                </a>
            </div>

            <div class="rounded-3 border overflow-hidden bg-light d-flex align-items-center justify-content-center" style="min-height: 550px;">
                @if($arsip->is_pdf)
                    <iframe src="{{ $arsip->file_url }}#toolbar=1" width="100%" height="700px" style="border:none;"></iframe>
                @elseif($arsip->is_image)
                    <div class="p-3 text-center w-100">
                        <img src="{{ $arsip->file_url }}" alt="{{ $arsip->judul_dokumen }}" class="img-fluid rounded shadow-sm" style="max-height: 700px; object-fit: contain;">
                    </div>
                @else
                    <div class="text-center p-5">
                        <i class="bi {{ $arsip->file_icon }}" style="font-size: 5rem;"></i>
                        <h5 class="fw-bold mt-3 mb-1 text-dark">{{ $arsip->file_name }}</h5>
                        <p class="text-muted small mb-4">Format berkas ini ({{ strtoupper($arsip->file_type) }}) memerlukan aplikasi eksternal untuk dibuka.</p>
                        <a href="{{ route('arsip.download', $arsip) }}" class="btn btn-primary px-4 fw-bold shadow-sm rounded-3">
                            <i class="bi bi-download me-1"></i> Unduh Berkas Ini Sekarang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
