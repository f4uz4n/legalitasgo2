@extends('layouts.admin')
@section('title', 'Edit Pengangsur')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('penghuni.index') }}">Pengangsur</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2"></i>Edit Pengangsur</h4>
    <a href="{{ route('penghuni.show', $penghuni) }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
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

        <form action="{{ route('penghuni.update', $penghuni) }}" method="POST">
            @csrf @method('PUT')
            @include('penghuni._form_rekap', ['penghuni' => $penghuni])

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="aktif" {{ old('status', $penghuni->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lunas" {{ old('status', $penghuni->status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="bermasalah" {{ old('status', $penghuni->status) == 'bermasalah' ? 'selected' : '' }}>Bermasalah</option>
                    </select>
                </div>
            </div>

            <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold text-primary mb-1">
                            <i class="bi bi-shield-lock-fill me-1"></i> Akun Login Portal Warga
                        </h6>
                        <p class="text-muted small mb-0">
                            @if($penghuni->user)
                                Akun login saat ini: <strong class="text-dark">{{ $penghuni->user->email }}</strong>
                            @else
                                Warga ini belum memiliki akun login.
                            @endif
                        </p>
                    </div>
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="create_user_account" value="1" id="editAccountSwitch" {{ $penghuni->user ? 'checked' : '' }} onchange="toggleEditAccountFields(this)">
                    </div>
                </div>

                <div id="editAccountFields" class="row" style="display: {{ $penghuni->user ? 'flex' : 'none' }};">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Email Login</label>
                        <input type="email" name="login_email" class="form-control" value="{{ old('login_email', $penghuni->user?->email ?? $penghuni->email) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Password <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                        <input type="text" name="login_password" class="form-control" placeholder="{{ $penghuni->user ? 'Kosongkan jika password tetap' : 'Min. 6 karakter' }}" value="{{ old('login_password') }}">
                    </div>
                    <div class="col-12 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="login_is_active" value="1" id="switchLoginActive" {{ ($penghuni->user?->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold" for="switchLoginActive">Akun dapat login ke portal</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg"></i> Perbarui Pengangsur
                </button>
                <a href="{{ route('penghuni.show', $penghuni) }}" class="btn btn-light px-3">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEditAccountFields(checkbox) {
    document.getElementById('editAccountFields').style.display = checkbox.checked ? 'flex' : 'none';
}
</script>
@endsection
