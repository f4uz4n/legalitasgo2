@extends('layouts.admin')
@section('title', 'Detail Legalitas')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('legalitas.index') }}">Legalitas</a></li>
<li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-ruled-fill me-2"></i>Legalitas - {{ $legalita->penghuni->nama_lengkap ?? '' }}</h4>
    <a href="{{ route('legalitas.edit', $legalita) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
</div>

<div class="row g-4 mb-4">
    <!-- Info -->
    <div class="col-md-5">
        <div class="content-card h-100">
            <div class="card-header"><span>Informasi Legalitas</span></div>
            <div class="card-body">
                <p><strong>Penghuni:</strong> {{ $legalita->penghuni->nama_lengkap ?? '-' }}</p>
                <p><strong>Blok:</strong> {{ $legalita->penghuni->blok_kavling ?? '-' }}</p>
                <p><strong>Perumahan:</strong> {{ $legalita->penghuni->perumahan->nama_perumahan ?? '-' }}</p>
                <hr>
                <p><strong>Jenis Sertifikat:</strong> {{ $legalita->jenis_sertifikat ?? '-' }}</p>
                <p><strong>No. Sertifikat:</strong> {{ $legalita->no_sertifikat ?? '-' }}</p>
                <p><strong>No. AJB:</strong> {{ $legalita->no_ajb ?? '-' }}</p>
                <p><strong>Tanggal AJB:</strong> {{ $legalita->tanggal_ajb?->format('d/m/Y') ?? '-' }}</p>
                <p><strong>Notaris:</strong> {{ $legalita->notaris ?? '-' }}</p>
                <p><strong>No. IMB:</strong> {{ $legalita->no_imb ?? '-' }}</p>
                <p><strong>No. PBB:</strong> {{ $legalita->no_pbb ?? '-' }}</p>
                <hr>
                <p><strong>Status:</strong> <span class="badge bg-{{ $legalita->status_color }}">{{ $legalita->status_label }}</span></p>
                <div class="mt-2">
                    <small class="text-muted fw-semibold">Progress</small>
                    <div class="progress mt-1" style="height:12px">
                        <div class="progress-bar bg-success" style="width:{{ $legalita->progress_persen }}%">{{ $legalita->progress_persen }}%</div>
                    </div>
                </div>
                <p class="mt-3"><strong>Tanggal Mulai:</strong> {{ $legalita->tanggal_mulai?->format('d/m/Y') ?? '-' }}</p>
                <p><strong>Estimasi Selesai:</strong> {{ $legalita->estimasi_selesai?->format('d/m/Y') ?? '-' }}</p>
                <p><strong>Tanggal Selesai:</strong> {{ $legalita->tanggal_selesai?->format('d/m/Y') ?? '-' }}</p>
                <hr>
                <p><strong>Biaya Pengurusan:</strong> Rp {{ number_format($legalita->biaya_pengurusan, 0, ',', '.') }}</p>
                <p><strong>Terbayar:</strong> Rp {{ number_format($legalita->biaya_terbayar, 0, ',', '.') }}</p>
                <p><strong>Sisa:</strong> <span class="text-danger fw-bold">Rp {{ number_format($legalita->sisa_biaya, 0, ',', '.') }}</span></p>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="col-md-7">
        <div class="content-card">
            <div class="card-header">
                <span><i class="bi bi-clock-history me-2"></i>Timeline Pengurusan</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTimeline"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
            </div>
            <div class="card-body">
                @if($legalita->timeline->count() > 0)
                <ul class="timeline">
                    @foreach($legalita->timeline as $t)
                    <li class="timeline-item">
                        <div class="timeline-icon" style="background: {{ $t->warna ?? '#0d6efd' }}">
                            <i class="bi {{ $t->icon ?? 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0" style="font-size:0.9rem">{{ $t->judul }}</h6>
                                <span class="badge badge-status bg-{{ $t->status === 'selesai' ? 'success' : ($t->status === 'proses' ? 'primary' : ($t->status === 'ditolak' ? 'danger' : 'warning')) }} bg-opacity-10 text-{{ $t->status === 'selesai' ? 'success' : ($t->status === 'proses' ? 'primary' : ($t->status === 'ditolak' ? 'danger' : 'warning')) }}">{{ $t->status_label }}</span>
                            </div>
                            @if($t->deskripsi)
                            <p class="text-muted small mb-1">{{ $t->deskripsi }}</p>
                            @endif
                            <span class="timeline-date"><i class="bi bi-calendar3 me-1"></i>{{ $t->tanggal->format('d M Y') }}</span>
                            @if($t->dokumen_file)
                            <a href="{{ Storage::url($t->dokumen_file) }}" target="_blank" class="ms-2 small"><i class="bi bi-paperclip"></i> Dokumen</a>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Belum ada timeline</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Timeline -->
<div class="modal fade" id="modalTimeline" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Timeline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('legalitas.timeline.store', $legalita) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" required placeholder="Contoh: Dokumen diserahkan ke BPN">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="proses">Dalam Proses</option>
                            <option value="selesai">Selesai</option>
                            <option value="pending">Pending</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dokumen (Opsional)</label>
                        <input type="file" name="dokumen_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
