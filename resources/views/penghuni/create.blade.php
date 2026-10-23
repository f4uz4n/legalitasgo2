@extends('layouts.admin')
@section('title', 'Tambah Penghuni')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('penghuni.index') }}">Penghuni</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="content-card">
            <div class="card-header"><span><i class="bi bi-person-plus me-2"></i>Tambah Penghuni Baru</span></div>
            <div class="card-body">
                <form action="{{ route('penghuni.store') }}" method="POST">
                    @csrf
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-1"></i> Data Pribadi</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" class="form-control" maxlength="16" value="{{ old('nik') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-house me-1"></i> Data Properti</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perumahan</label>
                            @php
                                $singlePerumahanId = $perumahan->id ?? (count($perumahanList) ? array_key_first($perumahanList->toArray()) : 1);
                                $singlePerumahanNama = $perumahan->nama_perumahan ?? ($perumahanList[$singlePerumahanId] ?? ($sharedPerumahan->nama_perumahan ?? 'Perumahan'));
                            @endphp
                            <input type="hidden" name="perumahan_id" value="{{ $singlePerumahanId }}">
                            <div class="form-control bg-light fw-bold text-primary">
                                <i class="bi bi-houses-fill me-1"></i> {{ $singlePerumahanNama }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blok/Kavling <span class="text-danger">*</span></label>
                            <input type="text" name="blok_kavling" class="form-control" value="{{ old('blok_kavling') }}" required placeholder="Contoh: A-01">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipe Rumah</label>
                            <input type="text" name="tipe_rumah" class="form-control" value="{{ old('tipe_rumah') }}" placeholder="Contoh: 36/72">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Luas Tanah (m²)</label>
                            <input type="number" name="luas_tanah" class="form-control" step="0.01" value="{{ old('luas_tanah') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Luas Bangunan (m²)</label>
                            <input type="number" name="luas_bangunan" class="form-control" step="0.01" value="{{ old('luas_bangunan') }}">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash me-1"></i> Data Keuangan</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga Rumah <span class="text-danger">*</span></label>
                            <input type="number" name="harga_rumah" class="form-control" min="0" value="{{ old('harga_rumah') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Uang Muka (DP)</label>
                            <input type="number" name="uang_muka" class="form-control" min="0" value="{{ old('uang_muka', 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tenor (Bulan) <span class="text-danger">*</span></label>
                            <input type="number" name="tenor_bulan" class="form-control" min="1" value="{{ old('tenor_bulan', 120) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akad</label>
                        <input type="date" name="tanggal_akad" class="form-control" value="{{ old('tanggal_akad') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                    </div>

                    <hr class="my-4">
                    <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-primary mb-1">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Akun Login Portal Warga
                                </h6>
                                <p class="text-muted small mb-0">Aktifkan agar warga dapat login dan memantau cicilan serta progres sertifikatnya secara mandiri.</p>
                            </div>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="create_user_account" value="1" id="createAccountSwitch" checked onchange="toggleAccountFields(this)">
                            </div>
                        </div>

                        <div id="accountFields" class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Email Login <span class="text-muted">(Opsional, default otomatis)</span></label>
                                <input type="email" name="login_email" id="login_email" class="form-control" placeholder="Contoh: ahmad@warga.id atau biarkan kosong" value="{{ old('login_email') }}">
                                <div class="form-text small">Jika dikosongkan, akan otomatis menggunakan email pribadi warga di atas atau kode kavling.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Password Awal <span class="text-muted">(Opsional, min. 6 karakter)</span></label>
                                <input type="text" name="login_password" class="form-control" placeholder="Default: NIK atau warga123" value="{{ old('login_password') }}">
                                <div class="form-text small">Default password adalah NIK warga atau <code>warga123</code>. Warga dapat menggantinya setelah login.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
                        <span>Jadwal angsuran akan otomatis dibuat sesuai tenor yang ditentukan setelah data disimpan.</span>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg"></i> Simpan & Buat Akun Warga
                        </button>
                        <a href="{{ route('penghuni.index') }}" class="btn btn-light px-3">
                            <i class="bi bi-x-lg"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAccountFields(checkbox) {
    const fields = document.getElementById('accountFields');
    if (checkbox.checked) {
        fields.style.display = 'flex';
    } else {
        fields.style.display = 'none';
    }
}
</script>
@endsection
