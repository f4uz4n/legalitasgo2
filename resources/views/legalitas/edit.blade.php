@extends('layouts.admin')
@section('title', 'Edit Legalitas')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('legalitas.index') }}">Legalitas</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="content-card">
            <div class="card-header"><span><i class="bi bi-pencil me-2"></i>Edit Legalitas: {{ $legalita->penghuni->nama_lengkap ?? '' }}</span></div>
            <div class="card-body">
                <form action="{{ route('legalitas.update', $legalita) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penghuni</label>
                            <input type="text" class="form-control" value="{{ $legalita->penghuni->nama_lengkap ?? '' }} - {{ $legalita->penghuni->blok_kavling ?? '' }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Sertifikat</label>
                            <select name="jenis_sertifikat" class="form-select">
                                <option value="">Pilih...</option>
                                @foreach(['SHM', 'SHGB', 'AJB', 'PPJB'] as $j)
                                <option value="{{ $j }}" {{ old('jenis_sertifikat', $legalita->jenis_sertifikat) == $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. Sertifikat</label>
                            <input type="text" name="no_sertifikat" class="form-control" value="{{ old('no_sertifikat', $legalita->no_sertifikat) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. AJB</label>
                            <input type="text" name="no_ajb" class="form-control" value="{{ old('no_ajb', $legalita->no_ajb) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal AJB</label>
                            <input type="date" name="tanggal_ajb" class="form-control" value="{{ old('tanggal_ajb', $legalita->tanggal_ajb?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Notaris</label>
                            <input type="text" name="notaris" class="form-control" value="{{ old('notaris', $legalita->notaris) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. IMB/PBG</label>
                            <input type="text" name="no_imb" class="form-control" value="{{ old('no_imb', $legalita->no_imb) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. PBB</label>
                            <input type="text" name="no_pbb" class="form-control" value="{{ old('no_pbb', $legalita->no_pbb) }}">
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['belum_proses' => 'Belum Diproses', 'pengajuan' => 'Pengajuan', 'verifikasi' => 'Verifikasi', 'proses_bpn' => 'Proses BPN', 'proses_notaris' => 'Proses Notaris', 'selesai' => 'Selesai'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $legalita->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $legalita->tanggal_mulai?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estimasi Selesai</label>
                            <input type="date" name="estimasi_selesai" class="form-control" value="{{ old('estimasi_selesai', $legalita->estimasi_selesai?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $legalita->tanggal_selesai?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biaya Pengurusan</label>
                            <input type="number" name="biaya_pengurusan" class="form-control" value="{{ old('biaya_pengurusan', $legalita->biaya_pengurusan) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biaya Terbayar</label>
                            <input type="number" name="biaya_terbayar" class="form-control" value="{{ old('biaya_terbayar', $legalita->biaya_terbayar) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $legalita->keterangan) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update</button>
                        <a href="{{ route('legalitas.show', $legalita) }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
