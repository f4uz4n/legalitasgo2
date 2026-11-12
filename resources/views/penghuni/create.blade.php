@extends('layouts.admin')
@section('title', 'Tambah Pengangsur')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('penghuni.index') }}">Pengangsur</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>Tambah Pengangsur</h4>
    <a href="{{ route('penghuni.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Kembali ke rekap</a>
</div>

<div class="content-card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Periksa isian berikut:</div>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('penghuni.store') }}" method="POST">
            @csrf
            @include('penghuni._form_rekap')

            <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold text-primary mb-1">
                            <i class="bi bi-shield-lock-fill me-1"></i> Akun Login Portal Warga
                        </h6>
                        <p class="text-muted small mb-0">Aktifkan agar warga dapat login dan memantau cicilan serta progres sertifikatnya.</p>
                    </div>
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="create_user_account" value="1" id="createAccountSwitch" checked onchange="toggleAccountFields(this)">
                    </div>
                </div>

                <div id="accountFields" class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Email Login <span class="text-muted">(opsional)</span></label>
                        <input type="email" name="login_email" class="form-control" placeholder="Kosongkan untuk dibuat otomatis" value="{{ old('login_email') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Password Awal <span class="text-muted">(opsional, min. 6 karakter)</span></label>
                        <input type="text" name="login_password" class="form-control" placeholder="Default: NIK atau warga123" value="{{ old('login_password') }}">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg"></i> Simpan Pengangsur
                </button>
                <a href="{{ route('penghuni.index') }}" class="btn btn-light px-3">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAccountFields(checkbox) {
    document.getElementById('accountFields').style.display = checkbox.checked ? 'flex' : 'none';
}
</script>
@endsection
