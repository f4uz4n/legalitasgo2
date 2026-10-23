@extends('layouts.admin')
@section('title', 'Kearsipan Dokumen Perumahan')
@section('breadcrumb')
<li class="breadcrumb-item active">Kearsipan Dokumen</li>
@endsection

@section('content')
<!-- Header Page -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="bi bi-archive-fill text-primary me-2"></i>Kearsipan Dokumen Perumahan
            </h4>
            <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">ARSIP DIGITAL</span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Pusat penyimpanan digital dan pengarsipan berkas penting perumahan {{ $sharedPerumahan->nama_perumahan ?? '' }} (Perizinan, Masterplan, Siteplan, HGB Induk, Notaris, AMDAL/PSU).
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('arsip.create') }}" class="btn btn-primary btn-sm fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-cloud-arrow-up-fill fs-6"></i> Upload Dokumen Baru
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm mb-4" role="alert">
    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
    <div class="small fw-semibold">{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- KPI Metrik Kearsipan -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Total Berkas Arsip</span>
                <span class="badge bg-light text-primary border"><i class="bi bi-files"></i></span>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($totalDokumen, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Total kapasitas: <strong class="text-primary">{{ $totalSizeFormatted }}</strong></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Perizinan Induk</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-patch-check-fill"></i></span>
            </div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($totalPerizinan, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">IMB/PBG, Izin Lokasi, KRK</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Pertanahan &amp; Sertifikat</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-award-fill"></i></span>
            </div>
            <div class="fs-3 fw-bold text-success">{{ number_format($totalSertifikat, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">HGB Induk, SPL, BPN</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Akses Publik Warga</span>
                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-eye-fill"></i></span>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($totalPublik, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Dapat diakses warga di portal</div>
        </div>
    </div>
</div>

<!-- Filter Toolbar Card -->
<div class="content-card mb-4 bg-white p-3">
    <form action="{{ route('arsip.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-lg-3 col-md-6">
            <label class="form-label small fw-bold text-muted mb-1">Pencarian Kata Kunci</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Judul, No. Dokumen, Instansi..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label small fw-bold text-muted mb-1">Kategori Dokumen</label>
            <select name="kategori" class="form-select form-select-sm">
                <option value="">-- Semua Kategori --</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Hak Akses</label>
            <select name="akses" class="form-select form-select-sm">
                <option value="">Semua Akses</option>
                <option value="publik" {{ request('akses') === 'publik' ? 'selected' : '' }}>Publik Warga</option>
                <option value="internal" {{ request('akses') === 'internal' ? 'selected' : '' }}>Khusus Internal Pengelola</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Urutan Data</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Waktu Upload Terbaru</option>
                <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Waktu Upload Terlama</option>
                <option value="tgl_dokumen_desc" {{ $sort === 'tgl_dokumen_desc' ? 'selected' : '' }}>Tgl Dokumen Terkini</option>
                <option value="tgl_dokumen_asc" {{ $sort === 'tgl_dokumen_asc' ? 'selected' : '' }}>Tgl Dokumen Terlama</option>
                <option value="judul_asc" {{ $sort === 'judul_asc' ? 'selected' : '' }}>Judul (A &rarr; Z)</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-4 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Terapkan Filter">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="{{ route('arsip.index') }}" class="btn btn-light border btn-sm" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabel Daftar Dokumen Arsip -->
<div class="content-card bg-white">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-folder2-open text-primary"></i> Berkas Arsip Penting Perumahan
            </h6>
            <span class="badge bg-light text-primary border">{{ $dokumenList->total() }} Dokumen</span>
        </div>
        <span class="text-muted small">Menampilkan {{ $dokumenList->firstItem() ?? 0 }} - {{ $dokumenList->lastItem() ?? 0 }} dari {{ $dokumenList->total() }} berkas</span>
    </div>

    <div class="table-responsive">
        <table class="table table-modern align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>Berkas &amp; Judul Dokumen</th>
                    <th style="width: 170px;">Kategori</th>
                    <th style="width: 190px;">Nomor &amp; Instansi</th>
                    <th style="width: 130px;">Tgl Dokumen</th>
                    <th style="width: 130px;" class="text-center">Akses</th>
                    <th style="width: 150px;">Pengunggah</th>
                    <th style="width: 130px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dokumenList as $doc)
                @php
                    $badgeInfo = $doc->kategori_badge;
                @endphp
                <tr>
                    <!-- Berkas & Judul -->
                    <td>
                        <div class="d-flex align-items-start gap-2.5">
                            <div class="fs-2 mt-0.5" style="line-height: 1;">
                                <i class="bi {{ $doc->file_icon }}"></i>
                            </div>
                            <div class="overflow-hidden">
                                <a href="{{ route('arsip.show', $doc) }}" class="fw-bold text-dark text-decoration-none hover-primary d-block" style="line-height: 1.3;">
                                    {{ $doc->judul_dokumen }}
                                </a>
                                <div class="text-muted small mt-1 d-flex align-items-center gap-2" style="font-size: 0.74rem;">
                                    <span class="text-truncate" style="max-width: 200px;" title="{{ $doc->file_name }}">
                                        {{ $doc->file_name }}
                                    </span>
                                    <span>&bull;</span>
                                    <span class="fw-semibold text-secondary">{{ $doc->file_size_formatted }}</span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Kategori -->
                    <td>
                        <span class="badge {{ $badgeInfo['class'] }} px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                            <i class="bi {{ $badgeInfo['icon'] }}"></i> {{ $doc->kategori }}
                        </span>
                    </td>

                    <!-- Nomor & Instansi -->
                    <td>
                        <div class="fw-semibold text-dark text-truncate" style="max-width: 180px;" title="{{ $doc->nomor_dokumen ?? '-' }}">
                            {{ $doc->nomor_dokumen ?: '-' }}
                        </div>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.73rem; max-width: 180px;" title="{{ $doc->instansi_penerbit ?? '-' }}">
                            <i class="bi bi-building me-1"></i> {{ $doc->instansi_penerbit ?: '-' }}
                        </small>
                    </td>

                    <!-- Tanggal Dokumen -->
                    <td>
                        @if($doc->tanggal_dokumen)
                            <div class="fw-semibold text-dark">
                                {{ $doc->tanggal_dokumen->format('d/m/Y') }}
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                {{ $doc->tanggal_dokumen->translatedFormat('F Y') }}
                            </small>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>

                    <!-- Hak Akses -->
                    <td class="text-center">
                        @if($doc->is_public)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.73rem;">
                                <i class="bi bi-eye-fill me-1"></i> Publik Warga
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.73rem;">
                                <i class="bi bi-lock-fill me-1"></i> Internal Pengelola
                            </span>
                        @endif
                    </td>

                    <!-- Pengunggah -->
                    <td>
                        <div class="fw-semibold text-dark small" style="font-size: 0.82rem;">
                            {{ $doc->uploader->name ?? 'Admin' }}
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ $doc->created_at->diffForHumans() }}
                        </small>
                    </td>

                    <!-- Aksi -->
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-1">
                            <!-- Tombol Pratinjau Modal -->
                            <button type="button" class="btn btn-sm btn-outline-primary btn-preview-doc py-1 px-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPreviewDokumen"
                                    data-judul="{{ $doc->judul_dokumen }}"
                                    data-kategori="{{ $doc->kategori }}"
                                    data-nomor="{{ $doc->nomor_dokumen ?: '-' }}"
                                    data-instansi="{{ $doc->instansi_penerbit ?: '-' }}"
                                    data-url="{{ $doc->file_url }}"
                                    data-download="{{ route('arsip.download', $doc) }}"
                                    data-isimage="{{ $doc->is_image ? '1' : '0' }}"
                                    data-ispdf="{{ $doc->is_pdf ? '1' : '0' }}"
                                    data-filename="{{ $doc->file_name }}"
                                    title="Pratinjau Dokumen">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Download -->
                            <a href="{{ route('arsip.download', $doc) }}" class="btn btn-sm btn-outline-success py-1 px-2" title="Unduh Berkas">
                                <i class="bi bi-download"></i>
                            </a>

                            <!-- Edit -->
                            <a href="{{ route('arsip.edit', $doc) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Informasi">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Hapus -->
                            <form action="{{ route('arsip.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip dokumen ini? Berkas fisik akan dihapus dari server.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Berkas">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-archive text-muted fs-1 d-block mb-2"></i>
                        <div class="fw-bold fs-6">Belum Ada Dokumen yang Diunggah</div>
                        <p class="small text-muted mb-3">Arsipkan berkas penting perumahan seperti IMB, Siteplan, HGB, dan Akta Notaris agar tersimpan rapi dan aman.</p>
                        <a href="{{ route('arsip.create') }}" class="btn btn-primary btn-sm fw-bold">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Dokumen Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dokumenList->hasPages())
    <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            Halaman {{ $dokumenList->currentPage() }} dari {{ $dokumenList->lastPage() }}
        </div>
        <div>
            {{ $dokumenList->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal Pratinjau Dokumen Arsip Interaktif -->
<div class="modal fade" id="modalPreviewDokumen" tabindex="-1" aria-labelledby="modalPreviewDokumenLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="modalPreviewDokumenLabel">
                    <i class="bi bi-file-earmark-text me-1"></i> Pratinjau Dokumen Arsip Perumahan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Meta Info Strip -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="small text-muted">Judul Dokumen:</div>
                            <div class="fw-bold text-dark fs-6" id="modalDocJudul">-</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Kategori:</div>
                            <div class="fw-semibold text-primary" id="modalDocKategori">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Nomor / Instansi:</div>
                            <div class="text-secondary small" id="modalDocNomorInstansi">-</div>
                        </div>
                    </div>
                </div>

                <!-- Preview Area Container -->
                <div id="modalPreviewContainer" class="rounded-3 border overflow-hidden bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="min-height: 480px; max-height: 650px;">
                    <!-- Diisi dinamis oleh JS: PDF embed, Image tag, atau Fallback download -->
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <a id="modalBtnDownload" href="#" class="btn btn-success btn-sm px-3 fw-bold rounded-3">
                        <i class="bi bi-download me-1"></i> Unduh Berkas
                    </a>
                    <a id="modalBtnOpenNewTab" href="#" target="_blank" class="btn btn-outline-primary btn-sm px-3 rounded-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                    </a>
                </div>
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.btn-preview-doc', function() {
        const judul     = $(this).data('judul');
        const kategori  = $(this).data('kategori');
        const nomor     = $(this).data('nomor');
        const instansi  = $(this).data('instansi');
        const url       = $(this).data('url');
        const download  = $(this).data('download');
        const isImage   = $(this).data('isimage') === 1;
        const isPdf     = $(this).data('ispdf') === 1;
        const fileName  = $(this).data('filename');

        $('#modalDocJudul').text(judul);
        $('#modalDocKategori').text(kategori);
        $('#modalDocNomorInstansi').text(nomor + ' / ' + instansi);
        $('#modalBtnDownload').attr('href', download);
        $('#modalBtnOpenNewTab').attr('href', url);

        const container = $('#modalPreviewContainer');
        container.empty();

        if (isImage) {
            container.html('<img src="' + url + '" alt="' + judul + '" class="img-fluid rounded" style="max-height: 600px; object-fit: contain;">');
        } else if (isPdf) {
            container.html('<iframe src="' + url + '#toolbar=1" width="100%" height="580px" style="border:none;"></iframe>');
        } else {
            container.html(
                '<div class="text-center p-5 bg-white rounded-3 shadow-sm border my-4">' +
                '<i class="bi bi-file-earmark-arrow-down-fill text-primary" style="font-size: 4rem;"></i>' +
                '<h5 class="fw-bold mt-3 mb-1 text-dark">' + fileName + '</h5>' +
                '<p class="text-muted small mb-4">Berkas dengan tipe ini tidak dapat dipratinjau langsung di browser. Silakan unduh untuk membukanya.</p>' +
                '<a href="' + download + '" class="btn btn-primary px-4 fw-bold shadow-sm">' +
                '<i class="bi bi-download me-1"></i> Unduh Berkas Sekarang' +
                '</a></div>'
            );
        }
    });
});
</script>
@endpush
