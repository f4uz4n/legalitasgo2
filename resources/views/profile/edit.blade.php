@extends('layouts.admin')
@section('title', 'Profil & Pengaturan Akun')
@section('breadcrumb')
<li class="breadcrumb-item active">Profil &amp; Akun</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        
        <!-- Header Page -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                    <span class="icon-blue p-2 rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-person-gear text-primary fs-5"></i>
                    </span>
                    <span>Pengaturan Profil &amp; Akun</span>
                </h4>
                <p class="text-muted small mb-0">
                    Kelola nama lengkap, alamat email, dan kata sandi keamanan akun Anda dalam satu formulir terpadu.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Ke Dashboard
            </a>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div class="small fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
            <div class="fw-bold small mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat data yang belum sesuai:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Profil Ringkas Banner -->
        <div class="content-card p-4 bg-white mb-4 shadow-sm border">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 58px; height: 58px; font-size: 1.5rem; flex-shrink: 0;">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2 small">
                        <span class="text-muted"><i class="bi bi-envelope me-1"></i> {{ $user->email }}</span>
                        <span class="text-muted">&bull;</span>
                        @if($user->isWarga())
                            <span class="badge bg-primary text-white px-2 py-1">
                                <i class="bi bi-house-door-fill me-1"></i> Warga Kavling: {{ $user->penghuni->blok_kavling ?? '-' }}
                            </span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="bi bi-shield-lock-fill me-1"></i> Administrator Pengelola
                            </span>
                        @endif
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Status: Akun Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULIR TUNGGAL (ONE UNIFIED FORM) UPDATE PROFIL & PASSWORD -->
        <div class="content-card bg-white p-4 p-md-5 mb-4 shadow-sm border">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <div class="p-2 rounded-2 bg-light text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="bi bi-pencil-square fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Formulir Pembaruan Profil &amp; Keamanan Akun</h6>
                    <small class="text-muted">Perbarui data profil dan kata sandi Anda secara langsung di bawah ini.</small>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('patch')

                <!-- BAGIAN 1: INFORMASI IDENTITAS -->
                <h6 class="fw-bold text-dark small text-uppercase mb-3 d-flex align-items-center gap-1.5" style="letter-spacing: 0.5px;">
                    <i class="bi bi-person-badge text-primary"></i> 1. Informasi Identitas Pengguna
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required placeholder="Nama lengkap Anda">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Alamat Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required placeholder="email@domain.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Hak Akses Sistem</label>
                        <input type="text" class="form-control bg-light" value="{{ $user->isWarga() ? 'Warga Penghuni (Portal Unit & Angsuran)' : 'Administrator Pengelola Perumahan' }}" readonly disabled>
                    </div>
                </div>

                <!-- BAGIAN 2: GANTI KATA SANDI (OPSIONAL DALAM SATU FORM) -->
                <div class="pt-3 border-top mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark small text-uppercase mb-0 d-flex align-items-center gap-1.5" style="letter-spacing: 0.5px;">
                            <i class="bi bi-shield-lock text-primary"></i> 2. Ganti Kata Sandi (Opsional)
                        </h6>
                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Opsional</span>
                    </div>
                    <p class="text-muted small mb-3">
                        Kosongkan bagian ini jika Anda <strong>tidak ingin mengubah kata sandi</strong>. Jika ingin mengubah kata sandi, isi kata sandi saat ini dan masukkan kata sandi baru.
                    </p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">
                                Kata Sandi Saat Ini <span class="text-muted fw-normal">(Diperlukan jika ingin mengubah sandi)</span>
                            </label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Masukkan kata sandi lama Anda" autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Ketik ulang kata sandi baru" autocomplete="new-password">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- TOMBOL SIMPAN TUNGGAL -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('dashboard') }}" class="btn btn-light border px-3">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle fs-5"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- FITUR NONAKTIFKAN AKUN (PENGGANTI DELETE ACCOUNT) -->
        <div class="content-card bg-white p-4 shadow-sm border border-warning-subtle">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <div>
                    <h6 class="fw-bold text-warning-emphasis mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-pause-circle-fill text-warning fs-5"></i>
                        <span>Nonaktifkan Akun Saya</span>
                    </h6>
                    <p class="text-muted small mb-0">
                        Jika Anda menonaktifkan akun, sesi Anda akan ditutup dan akun tidak dapat digunakan untuk masuk sampai diaktifkan kembali oleh pengelola. Seluruh data riwayat pembayaran dan unit tetap tersimpan aman.
                    </p>
                </div>
                <button type="button" class="btn btn-outline-warning btn-sm fw-bold px-3 py-2 text-nowrap rounded-3" data-bs-toggle="modal" data-bs-target="#modalDeactivateAccount">
                    <i class="bi bi-power me-1"></i> Nonaktifkan Akun
                </button>
            </div>
        </div>

    </div>
</div>

<!-- MODAL KONFIRMASI NONAKTIFKAN AKUN -->
<div class="modal fade" id="modalDeactivateAccount" tabindex="-1" aria-labelledby="modalDeactivateAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold" id="modalDeactivateAccountLabel">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Konfirmasi Penonaktifan Akun
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.deactivate') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center text-warning fs-1 mb-2">
                            <i class="bi bi-pause-circle"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Apakah Anda Yakin Ingin Menonaktifkan Akun?</h6>
                        <p class="text-muted small mb-0">
                            Anda akan otomatis keluar dari sistem. Untuk mengaktifkannya kembali, silakan hubungi administrator perumahan.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Konfirmasi Kata Sandi Akun Anda:
                        </label>
                        <input type="password" name="confirm_password" class="form-control form-control-sm" required placeholder="Masukkan kata sandi saat ini" autocomplete="current-password">
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold rounded-3">
                        <i class="bi bi-power me-1"></i> Ya, Nonaktifkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
