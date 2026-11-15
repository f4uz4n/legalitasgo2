@extends('layouts.admin')
@section('title', 'Tampilan Portal Warga')
@section('breadcrumb')
<li class="breadcrumb-item active">Tampilan Portal Warga</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Tampilan Portal Warga</h4>
        <p class="text-muted small mb-0">Atur bagian yang tampil di laporan publik, dan seberapa besar angka dana masuk yang dilihat warga.</p>
    </div>
    <a href="{{ route('public.report') }}" target="_blank" class="btn btn-outline-primary">
        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Portal Warga
    </a>
</div>

<form method="POST" action="{{ route('portal.pengaturan.update') }}">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="content-card bg-white p-4 h-100">
                <h6 class="fw-bold mb-1">Dana masuk yang ditampilkan</h6>
                <p class="text-muted small">100 menampilkan angka penuh. 50 menampilkan setengahnya. Angka di kas pengelola tetap utuh.</p>
                <label class="form-label fw-semibold" for="persen_dana_masuk">Persentase</label>
                <div class="input-group mb-3" style="max-width: 220px;">
                    <input type="number" name="persen_dana_masuk" id="persen_dana_masuk" class="form-control" min="0" max="100" value="{{ old('persen_dana_masuk', $persen) }}" required>
                    <span class="input-group-text">%</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('persen_dana_masuk').value = 100">100%</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('persen_dana_masuk').value = 50">50%</button>
                </div>
                @error('persen_dana_masuk')
                <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-lg-7">
            <div class="content-card bg-white p-4 h-100">
                <h6 class="fw-bold mb-1">Bagian portal</h6>
                <p class="text-muted small">Bagian yang tidak dicentang disembunyikan dari portal warga.</p>
                <div class="row g-2">
                    @foreach($labelBagian as $kunci => $label)
                    <div class="col-md-6">
                        <div class="form-check border rounded-3 p-3 h-100">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="bagian_{{ $kunci }}" id="bagian_{{ $kunci }}" value="1" @checked(old('bagian_'.$kunci, $bagian[$kunci]))>
                            <label class="form-check-label fw-semibold" for="bagian_{{ $kunci }}">{{ $label }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="content-card bg-white p-4 mt-4">
        <h6 class="fw-bold mb-2">Tampil publik per data</h6>
        <ul class="text-muted small mb-3">
            <li>Kas masuk dan kas keluar punya tombol mata di daftar kas. Data yang disembunyikan tidak ikut angka portal.</li>
            <li>Setiap tahapan timeline bisa disembunyikan dari portal lewat kolom Portal di legalitas.</li>
            <li>Dokumen kearsipan memakai centang publik yang sudah ada di halaman arsip.</li>
        </ul>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Pengaturan</button>
    </div>
</form>
@endsection
