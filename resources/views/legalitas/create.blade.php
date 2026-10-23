@extends('layouts.admin')
@section('title', 'Tambah Legalitas')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('legalitas.index') }}">Legalitas</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="content-card">
            <div class="card-header"><span><i class="bi bi-file-earmark-plus me-2"></i>Tambah Data Legalitas</span></div>
            <div class="card-body">
                <form action="{{ route('legalitas.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penghuni <span class="text-danger">*</span></label>
                            <select name="penghuni_id" class="form-select select2" required data-placeholder="-- Pilih / Cari Nama Penghuni --">
                                <option value="">-- Pilih / Cari Nama Penghuni --</option>
                                @foreach($penghuniList as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_lengkap }} - {{ $p->blok_kavling }} ({{ $p->perumahan->nama_perumahan ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Sertifikat</label>
                            <select name="jenis_sertifikat" class="form-select">
                                <option value="">Pilih...</option>
                                <option value="SHM">SHM (Sertifikat Hak Milik)</option>
                                <option value="SHGB">SHGB (Sertifikat Hak Guna Bangunan)</option>
                                <option value="AJB">AJB (Akta Jual Beli)</option>
                                <option value="PPJB">PPJB (Perjanjian Pengikatan Jual Beli)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. AJB</label>
                            <input type="text" name="no_ajb" class="form-control" value="{{ old('no_ajb') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal AJB</label>
                            <input type="date" name="tanggal_ajb" class="form-control" value="{{ old('tanggal_ajb') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Notaris</label>
                            <input type="text" name="notaris" class="form-control" value="{{ old('notaris') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. IMB/PBG</label>
                            <input type="text" name="no_imb" class="form-control" value="{{ old('no_imb') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. PBB</label>
                            <input type="text" name="no_pbb" class="form-control" value="{{ old('no_pbb') }}">
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="belum_proses">Belum Diproses</option>
                                <option value="pengajuan">Pengajuan</option>
                                <option value="verifikasi">Verifikasi</option>
                                <option value="proses_bpn">Proses BPN</option>
                                <option value="proses_notaris">Proses Notaris</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estimasi Selesai</label>
                            <input type="date" name="estimasi_selesai" class="form-control" value="{{ old('estimasi_selesai') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biaya Pengurusan</label>
                            <input type="number" name="biaya_pengurusan" class="form-control" min="0" value="{{ old('biaya_pengurusan', 0) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                        <a href="{{ route('legalitas.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
