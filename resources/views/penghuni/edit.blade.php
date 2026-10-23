@extends('layouts.admin')
@section('title', 'Edit Penghuni')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('penghuni.index') }}">Penghuni</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="content-card">
            <div class="card-header"><span><i class="bi bi-pencil me-2"></i>Edit Penghuni: {{ $penghuni->nama_lengkap }}</span></div>
            <div class="card-body">
                <form action="{{ route('penghuni.update', $penghuni) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $penghuni->nama_lengkap) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" class="form-control" maxlength="16" value="{{ old('nik', $penghuni->nik) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $penghuni->no_hp) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $penghuni->email) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perumahan</label>
                            <input type="hidden" name="perumahan_id" value="{{ $penghuni->perumahan_id }}">
                            <div class="form-control bg-light fw-bold text-primary">
                                <i class="bi bi-houses-fill me-1"></i> {{ $penghuni->perumahan->nama_perumahan ?? ($sharedPerumahan->nama_perumahan ?? 'Perumahan') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blok/Kavling <span class="text-danger">*</span></label>
                            <input type="text" name="blok_kavling" class="form-control" value="{{ old('blok_kavling', $penghuni->blok_kavling) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipe Rumah</label>
                            <input type="text" name="tipe_rumah" class="form-control" value="{{ old('tipe_rumah', $penghuni->tipe_rumah) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Luas Tanah</label>
                            <input type="number" name="luas_tanah" class="form-control" step="0.01" value="{{ old('luas_tanah', $penghuni->luas_tanah) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Luas Bangunan</label>
                            <input type="number" name="luas_bangunan" class="form-control" step="0.01" value="{{ old('luas_bangunan', $penghuni->luas_bangunan) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga Rumah <span class="text-danger">*</span></label>
                            <input type="number" name="harga_rumah" class="form-control" value="{{ old('harga_rumah', $penghuni->harga_rumah) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Uang Muka</label>
                            <input type="number" name="uang_muka" class="form-control" value="{{ old('uang_muka', $penghuni->uang_muka) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tenor (Bulan) <span class="text-danger">*</span></label>
                            <input type="number" name="tenor_bulan" class="form-control" value="{{ old('tenor_bulan', $penghuni->tenor_bulan) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Akad</label>
                            <input type="date" name="tanggal_akad" class="form-control" value="{{ old('tanggal_akad', $penghuni->tanggal_akad?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" {{ old('status', $penghuni->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="lunas" {{ old('status', $penghuni->status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="bermasalah" {{ old('status', $penghuni->status) == 'bermasalah' ? 'selected' : '' }}>Bermasalah</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $penghuni->keterangan) }}</textarea>
                    </div>

                    <hr class="my-4">
                    <div class="p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-primary mb-1">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Akun Login Portal Warga
                                </h6>
                                <p class="text-muted small mb-0">
                                    @if($penghuni->user)
                                        Akun login saat ini: <strong class="text-dark">{{ $penghuni->user->email }}</strong> 
                                        <span class="badge bg-success bg-opacity-10 text-success ms-1">Aktif</span>
                                    @else
                                        Warga ini belum memiliki akun login ke sistem.
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
                                <input type="email" name="login_email" class="form-control" placeholder="Email untuk login" value="{{ old('login_email', $penghuni->user?->email ?? $penghuni->email) }}">
                                <div class="form-text small">Warga menggunakan email ini untuk masuk ke portal.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Reset / Set Password <span class="text-muted">(Kosongkan jika tidak diubah)</span></label>
                                <input type="text" name="login_password" class="form-control" placeholder="{{ $penghuni->user ? 'Kosongkan jika password tetap' : 'Min. 6 karakter (cth: warga123)' }}" value="{{ old('login_password') }}">
                                <div class="form-text small">{{ $penghuni->user ? 'Isi hanya jika ingin mengganti password warga.' : 'Default: warga123 jika dikosongkan.' }}</div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="login_is_active" value="1" id="switchLoginActive" {{ ($penghuni->user?->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-dark" for="switchLoginActive">
                                        Status Akun Aktif (Dapat Login ke Portal)
                                    </label>
                                    <div class="form-text small">Bila dinonaktifkan, akun warga tidak dapat digunakan untuk masuk ke portal.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg"></i> Perbarui Data & Akun
                        </button>
                        <a href="{{ route('penghuni.show', $penghuni) }}" class="btn btn-light px-3">
                            <i class="bi bi-x-lg"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleEditAccountFields(checkbox) {
    const fields = document.getElementById('editAccountFields');
    if (checkbox.checked) {
        fields.style.display = 'flex';
    } else {
        fields.style.display = 'none';
    }
}
</script>
@endsection
