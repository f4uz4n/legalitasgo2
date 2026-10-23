@extends('layouts.admin')
@section('title', 'Edit Dokumen Arsip')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('arsip.index') }}">Kearsipan</a></li>
<li class="breadcrumb-item active">Edit Dokumen</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip
            </a>
            <a href="{{ route('arsip.show', $arsip) }}" class="btn btn-outline-primary btn-sm rounded-3">
                <i class="bi bi-eye me-1"></i> Lihat Detail Berkas
            </a>
        </div>

        <div class="content-card bg-white p-4 p-md-5 shadow-sm border">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <div class="p-2 rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-pencil-square fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Edit Informasi Dokumen Arsip</h5>
                    <small class="text-muted">Perbarui rincian metadata dokumen atau unggah berkas pengganti.</small>
                </div>
            </div>

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="fw-bold small mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Periksa kembali data yang dimasukkan:</div>
                <ul class="mb-0 small ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form action="{{ route('arsip.update', $arsip) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Informasi Berkas Saat Ini -->
                <div class="p-3 bg-light rounded-3 border mb-4 d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                        <div class="fs-2 text-primary" style="line-height: 1;">
                            <i class="bi {{ $arsip->file_icon }}"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold text-dark text-truncate">{{ $arsip->file_name }}</div>
                            <small class="text-muted">Ukuran: {{ $arsip->file_size_formatted }} &bull; Diunggah {{ $arsip->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ $arsip->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka File
                        </a>
                        <a href="{{ route('arsip.download', $arsip) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                </div>

                <!-- Informasi Utama Dokumen -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">
                            Judul Dokumen Arsip <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="judul_dokumen" class="form-control @error('judul_dokumen') is-invalid @enderror" value="{{ old('judul_dokumen', $arsip->judul_dokumen) }}" required>
                        @error('judul_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Kategori Dokumen <span class="text-danger">*</span>
                        </label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori Dokumen --</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori', $arsip->kategori) === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Ganti Berkas Dokumen <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <input type="file" name="file_dokumen" class="form-control @error('file_dokumen') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.zip,.rar">
                        <div class="form-text small" style="font-size: 0.72rem;">Kosongkan jika tidak ingin mengganti berkas fisik saat ini.</div>
                        @error('file_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Detail Identitas Dokumen Resmi -->
                <div class="pt-3 border-top mb-4">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3" style="letter-spacing: 0.5px;">
                        <i class="bi bi-file-earmark-ruled text-primary me-1"></i> Rincian Legalitas / Surat Resmi
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nomor Dokumen / SK / Akta</label>
                            <input type="text" name="nomor_dokumen" class="form-control" value="{{ old('nomor_dokumen', $arsip->nomor_dokumen) }}" placeholder="Nomor surat/izin resmi">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tanggal Dokumen Diterbitkan</label>
                            <input type="date" name="tanggal_dokumen" class="form-control" value="{{ old('tanggal_dokumen', $arsip->tanggal_dokumen ? $arsip->tanggal_dokumen->format('Y-m-d') : '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Instansi / Lembaga / Notaris Penerbit</label>
                            <input type="text" name="instansi_penerbit" class="form-control" value="{{ old('instansi_penerbit', $arsip->instansi_penerbit) }}" placeholder="Instansi atau notaris penerbit">
                        </div>
                    </div>
                </div>

                <!-- Hak Akses & Keterangan -->
                <div class="pt-3 border-top mb-4">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3" style="letter-spacing: 0.5px;">
                        <i class="bi bi-shield-lock text-primary me-1"></i> Pengaturan Akses &amp; Ringkasan
                    </h6>

                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                            <div>
                                <label class="form-check-label small fw-bold text-dark mb-0" for="switchPublic">
                                    <i class="bi bi-globe2 text-primary me-1"></i> Tampilkan Dokumen Ini di Portal Laporan Publik Warga
                                </label>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    Bila diaktifkan, warga dapat melihat berkas ini secara terbuka di halaman portal warga.
                                </div>
                            </div>
                            <input class="form-check-input ms-3 fs-5" type="checkbox" name="is_public" value="1" id="switchPublic" {{ old('is_public', $arsip->is_public) ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Keterangan / Catatan Tambahan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan catatan tambahan...">{{ old('deskripsi', $arsip->deskripsi) }}</textarea>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('arsip.index') }}" class="btn btn-light border px-3">
                        Batalkan
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle fs-5"></i> Simpan Perubahan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
