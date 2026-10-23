@extends('layouts.admin')
@section('title', 'Upload Dokumen Arsip Baru')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('arsip.index') }}">Kearsipan</a></li>
<li class="breadcrumb-item active">Upload Baru</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip
            </a>
        </div>

        <div class="content-card bg-white p-4 p-md-5 shadow-sm border">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <div class="p-2 rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Form Pengarsipan Dokumen Perumahan</h5>
                    <small class="text-muted">Simpan salinan digital berkas perizinan, sertifikat induk, masterplan, dan akta notaris perumahan.</small>
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

            <form action="{{ route('arsip.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Informasi Utama Dokumen -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">
                            Judul Dokumen Arsip <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="judul_dokumen" class="form-control @error('judul_dokumen') is-invalid @enderror" value="{{ old('judul_dokumen') }}" required placeholder="Contoh: Izin Mendirikan Bangunan (IMB / PBG) Induk Tahap 1">
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
                                <option value="{{ $kat }}" {{ old('kategori') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Pilih Berkas Dokumen <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_dokumen" class="form-control @error('file_dokumen') is-invalid @enderror" required accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.zip,.rar">
                        <div class="form-text small" style="font-size: 0.72rem;">Format: PDF, JPG, PNG, WEBP, DOCX, XLSX, ZIP (Maks. 25 MB).</div>
                        @error('file_dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Detail Identitas Dokumen Resmi -->
                <div class="pt-3 border-top mb-4">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3" style="letter-spacing: 0.5px;">
                        <i class="bi bi-file-earmark-ruled text-primary me-1"></i> Rincian Legalitas / Surat Resmi (Opsional)
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nomor Dokumen / SK / Akta</label>
                            <input type="text" name="nomor_dokumen" class="form-control" value="{{ old('nomor_dokumen') }}" placeholder="Contoh: 503/042/IMB/DPMPTSP/2024">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tanggal Dokumen Diterbitkan</label>
                            <input type="date" name="tanggal_dokumen" class="form-control" value="{{ old('tanggal_dokumen') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Instansi / Lembaga / Notaris Penerbit</label>
                            <input type="text" name="instansi_penerbit" class="form-control" value="{{ old('instansi_penerbit') }}" placeholder="Contoh: Dinas Penanaman Modal dan PTSP, Kantor Pertanahan (BPN), Notaris">
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
                                    Jika diaktifkan, warga dapat melihat salinan berkas ini di halaman Laporan Publik untuk transparansi perumahan (misal Site Plan, Izin Lokasi, Fasum).
                                </div>
                            </div>
                            <input class="form-check-input ms-3 fs-5" type="checkbox" name="is_public" value="1" id="switchPublic" {{ old('is_public') ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Keterangan / Catatan Tambahan (Opsional)</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan catatan atau ringkasan isi dokumen arsip ini...">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('arsip.index') }}" class="btn btn-light border px-3">
                        Batalkan
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle fs-5"></i> Simpan &amp; Arsipkan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
