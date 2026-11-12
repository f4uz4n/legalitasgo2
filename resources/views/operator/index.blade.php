@extends('layouts.admin')
@section('title', 'Akun Operator')
@section('breadcrumb')
<li class="breadcrumb-item active">Akun Operator</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Akun Operator Pengelolaan</h4>
        <p class="text-muted small mb-0">Operator login sendiri dan memakai fitur pengelolaan perumahan yang sama dengan superadmin. Pengaturan akun operator hanya bisa dilakukan superadmin.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card bg-white p-4">
            <h6 class="fw-bold mb-3">Tambah Operator</h6>
            <form method="POST" action="{{ route('operator.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="name">Nama</label>
                    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="email">Email login</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="password_confirmation">Ulangi kata sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Operator</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card bg-white">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Daftar Operator</span>
                <span class="badge bg-light text-dark border">{{ $operator->count() }} akun</span>
            </div>
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operator as $akun)
                        <tr>
                            <td class="fw-semibold">{{ $akun->name }}</td>
                            <td>{{ $akun->email }}</td>
                            <td>
                                @if($akun->is_active)
                                    <span class="badge bg-success-subtle text-success border">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editOperator{{ $akun->id }}">Ubah</button>
                                <form method="POST" action="{{ route('operator.destroy', $akun) }}" class="d-inline" onsubmit="return confirm('Hapus akun operator ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada akun operator.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($operator as $akun)
<div class="modal fade" id="editOperator{{ $akun->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('operator.update', $akun) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Ubah {{ $akun->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ $akun->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email login</label>
                    <input type="email" name="email" class="form-control" value="{{ $akun->email }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kata sandi baru</label>
                    <input type="password" name="password" class="form-control" minlength="6" placeholder="Kosongkan jika tidak diganti">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Ulangi kata sandi baru</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="6">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="aktif{{ $akun->id }}" @checked($akun->is_active)>
                    <label class="form-check-label" for="aktif{{ $akun->id }}">Akun aktif dan boleh login</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
