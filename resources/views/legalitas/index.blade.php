@extends('layouts.admin')
@section('title', 'Manajemen Legalitas & Timeline')
@section('breadcrumb')
<li class="breadcrumb-item active">Legalitas & Timeline Berkas</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-file-earmark-ruled-fill text-primary me-2"></i>Legalitas Sertifikat & Timeline Berkas</h4>
        <p class="text-muted small mb-0">Kelola berkas sertifikat kavling dan input tahapan timeline yang tampil di laporan publik warga.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTimelineGlobal">
            <i class="bi bi-plus-circle me-1"></i> Input Update Timeline Berkas
        </button>
        <a href="{{ route('legalitas.create') }}" class="btn btn-outline-primary">
            <i class="bi bi-folder-plus me-1"></i> Tambah Berkas Kavling
        </a>
    </div>
</div>

<!-- Nav Tabs: Daftar Berkas & Riwayat Timeline Publik -->
<ul class="nav nav-pills mb-4 gap-2" id="legalitasTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active px-4 py-2 rounded-3 fw-bold" id="berkas-tab" data-bs-toggle="pill" data-bs-target="#tabBerkas" type="button">
            <i class="bi bi-files me-1"></i> Berkas Sertifikat Per Kavling ({{ $legalitas->total() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link px-4 py-2 rounded-3 fw-bold" id="timeline-tab" data-bs-toggle="pill" data-bs-target="#tabTimeline" type="button">
            <i class="bi bi-clock-history me-1"></i> Timeline Kepengurusan Publik (Live di Portal Warga)
        </button>
    </li>
</ul>

<div class="tab-content" id="legalitasTabContent">
    <!-- TAB 1: Berkas Sertifikat Per Kavling -->
    <div class="tab-pane fade show active" id="tabBerkas" role="tabpanel">
        <div class="card-custom mb-4 p-3 bg-white">
            <form action="{{ route('legalitas.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama warga, blok kavling, atau notaris..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status Berkas</option>
                        <option value="belum_proses" {{ request('status') == 'belum_proses' ? 'selected' : '' }}>Belum Diproses</option>
                        <option value="pengajuan" {{ request('status') == 'pengajuan' ? 'selected' : '' }}>Pengajuan</option>
                        <option value="verifikasi" {{ request('status') == 'verifikasi' ? 'selected' : '' }}>Verifikasi</option>
                        <option value="proses_bpn" {{ request('status') == 'proses_bpn' ? 'selected' : '' }}>Proses BPN</option>
                        <option value="proses_notaris" {{ request('status') == 'proses_notaris' ? 'selected' : '' }}>Proses Notaris</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai / Terbit</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary me-2"><i class="bi bi-search me-1"></i> Filter</button>
                    <a href="{{ route('legalitas.index') }}" class="btn btn-light border">Reset</a>
                </div>
            </form>
        </div>

        <div class="card-custom bg-white">
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Kavling</th>
                            <th>Nama Pemilik</th>
                            <th>Jenis Sertifikat</th>
                            <th>Status Tahap</th>
                            <th>Progress</th>
                            <th>Notaris Rekanan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($legalitas as $l)
                        <tr>
                            <td><span class="badge bg-primary text-white fw-bold fs-6">{{ $l->penghuni->blok_kavling ?? '-' }}</span></td>
                            <td class="fw-semibold text-dark">{{ $l->penghuni->nama_lengkap ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $l->jenis_sertifikat ?? 'SHM' }}</span></td>
                            <td>
                                <span class="badge bg-{{ $l->status_color }} bg-opacity-10 text-{{ $l->status_color }} border border-{{ $l->status_color }} border-opacity-25">
                                    {{ strtoupper($l->status_label) }}
                                </span>
                            </td>
                            <td style="min-width: 140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:7px">
                                        <div class="progress-bar bg-success" style="width:{{ $l->progress_persen }}%"></div>
                                    </div>
                                    <small class="fw-bold text-dark">{{ $l->progress_persen }}%</small>
                                </div>
                            </td>
                            <td class="small text-secondary">{{ $l->notaris ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('legalitas.show', $l) }}" class="btn btn-sm btn-outline-primary rounded-3 me-1" title="Detail & Timeline"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('legalitas.edit', $l) }}" class="btn btn-sm btn-outline-warning rounded-3" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data legalitas kavling.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $legalitas->onEachSide(1)->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: Timeline Kepengurusan Publik (Live di Portal Warga) -->
    <div class="tab-pane fade" id="tabTimeline" role="tabpanel">
        <div class="card-custom p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-broadcast text-primary me-2"></i>Daftar Update Timeline yang Ditampilkan ke Portal Warga</h6>
                    <small class="text-muted">Setiap milestone tahapan berkas yang Anda input di sini langsung tampil transparan ke seluruh warga.</small>
                </div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTimelineGlobal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tahapan Baru
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul Tahapan Berkas</th>
                            <th>Berkas / Kavling Terkait</th>
                            <th>Status Tahap</th>
                            <th>Uraian / Deskripsi Milestone</th>
                            <th>Bukti File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTimelines as $rt)
                        <tr>
                            <td class="text-muted small fw-semibold">{{ $rt->tanggal->format('d/m/Y') }}</td>
                            <td class="fw-bold text-dark">{{ $rt->judul }}</td>
                            <td>
                                <span class="badge bg-light text-primary border border-primary border-opacity-25">
                                    {{ $rt->kavling_terkait ?? ($rt->legalitas->penghuni->blok_kavling ?? 'Proyek Perumahan') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $rt->status === 'selesai' ? 'success' : ($rt->status === 'proses' ? 'primary' : ($rt->status === 'ditolak' ? 'danger' : 'warning')) }} text-white">
                                    {{ strtoupper($rt->status_label) }}
                                </span>
                            </td>
                            <td class="small text-secondary" style="max-width: 320px;">
                                {{ $rt->deskripsi ?? '-' }}
                            </td>
                            <td>
                                @if($rt->dokumen_file)
                                <a href="{{ Storage::url($rt->dokumen_file) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-3 py-0 px-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-paperclip"></i> Lampiran
                                </a>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada update timeline yang diinput.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Update Timeline Berkas (Berdasarkan Input Admin) -->
<div class="modal fade" id="modalTimelineGlobal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Input Update Tahapan Kepengurusan Berkas</h5>
                    <small class="text-muted">Milestone ini akan otomatis dipublikasikan ke Portal Laporan Publik Warga secara realtime.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('timeline.store.direct') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Kegiatan / Pengajuan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Tahapan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="selesai">Selesai / Diverifikasi</option>
                                <option value="proses" selected>Sedang Dalam Proses</option>
                                <option value="pending">Menunggu Antrian / Jadwal</option>
                                <option value="ditolak">Perlu Revisi / Ditolak</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Judul Tahapan / Kegiatan Berkas <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Pengajuan Berkas Validasi Pajak ke Bapenda, Pengecekan BPN, dll." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Terkait Kavling / Lingkup Berkas</label>
                            <input type="text" name="kavling_terkait" class="form-control" placeholder="Contoh: Seluruh Kavling, Blok A & B, atau Kavling A-01">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Atau Tautkan ke Data Sertifikat Tertentu (Opsional)</label>
                            <select name="legalitas_id" class="form-select select2" data-placeholder="-- Cari Kavling atau Nama Warga --">
                                <option value="">-- Berkas Proyek Umum / Tanpa Tautan Khusus --</option>
                                @foreach($legalitasAll as $la)
                                <option value="{{ $la->id }}">Kavling {{ $la->penghuni->blok_kavling ?? '-' }} ({{ $la->penghuni->nama_lengkap ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Uraian / Catatan Detail Admin untuk Warga</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan nomor tanda terima berkas, dinas/instansi terkait, estimasi jadwal, atau arahan untuk warga..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload Dokumen Bukti / Tanda Terima (Opsional, PDF/Gambar Max 5MB)</label>
                            <input type="file" name="dokumen_file" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan & Publikasikan ke Warga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
