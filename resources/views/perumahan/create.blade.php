@extends('layouts.admin')
@section('title', isset($perumahan) ? 'Edit Perumahan' : 'Tambah Perumahan')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('perumahan.index') }}">Perumahan</a></li>
<li class="breadcrumb-item active">{{ isset($perumahan) ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header">
                <span><i class="bi bi-{{ isset($perumahan) ? 'pencil' : 'plus-circle' }} me-2"></i>{{ isset($perumahan) ? 'Edit' : 'Tambah' }} Perumahan</span>
            </div>
            <div class="card-body">
                <form action="{{ isset($perumahan) ? route('perumahan.update', $perumahan) : route('perumahan.store') }}" method="POST">
                    @csrf
                    @if(isset($perumahan)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">Nama Perumahan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_perumahan" class="form-control" value="{{ old('nama_perumahan', $perumahan->nama_perumahan ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required>{{ old('alamat', $perumahan->alamat ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kelurahan</label>
                            <input type="text" name="kelurahan" class="form-control" value="{{ old('kelurahan', $perumahan->kelurahan ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan', $perumahan->kecamatan ?? '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota/Kabupaten</label>
                            <input type="text" name="kota" class="form-control" value="{{ old('kota', $perumahan->kota ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" value="{{ old('provinsi', $perumahan->provinsi ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Unit</label>
                        <input type="number" name="total_unit" class="form-control" min="0" value="{{ old('total_unit', $perumahan->total_unit ?? 0) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $perumahan->keterangan ?? '') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                        <a href="{{ route('perumahan.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
