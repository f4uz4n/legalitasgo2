@extends('layouts.admin')
@section('title', 'Data Penghuni')
@section('breadcrumb')
<li class="breadcrumb-item active">Penghuni</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>Data Penghuni</h4>
    <a href="{{ route('penghuni.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Penghuni</a>
</div>

<!-- Filter -->
<div class="content-card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('penghuni.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari nama warga, blok kavling, atau NIK..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status Cicilan</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif Cicilan</option>
                    <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="bermasalah" {{ request('status') == 'bermasalah' ? 'selected' : '' }}>Bermasalah</option>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary me-2"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('penghuni.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="content-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Blok Kavling</th>
                        <th>Nama Warga</th>
                        <th>Kontak</th>
                        <th>Tipe</th>
                        <th>Harga Rumah</th>
                        <th>Sisa Cicilan</th>
                        <th>Progress Angsuran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penghuni as $idx => $p)
                    <tr>
                        <td>
                            <span class="badge bg-primary text-white fw-bold fs-6 px-2 py-1">{{ $p->blok_kavling }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $p->nama_lengkap }}</div>
                            <small class="text-muted">NIK: {{ $p->nik ?? '-' }}</small>
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-telephone me-1 text-primary"></i>{{ $p->no_hp ?? '-' }}</div>
                            @if($p->user)
                                @if($p->user->is_active ?? true)
                                    <div class="text-success mt-1 d-flex align-items-center gap-1" style="font-size: 0.73rem;" title="Akun login aktif">
                                        <i class="bi bi-shield-check"></i> {{ $p->user->email }}
                                    </div>
                                @else
                                    <div class="text-danger mt-1 d-flex align-items-center gap-1" style="font-size: 0.73rem;" title="Akun login dinonaktifkan">
                                        <i class="bi bi-pause-circle-fill"></i> {{ $p->user->email }} <span class="badge bg-danger-subtle text-danger py-0 px-1">Nonaktif</span>
                                    </div>
                                @endif
                            @else
                                <div class="text-muted" style="font-size: 0.73rem;">{{ $p->email ?? 'Belum ada akun' }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $p->tipe_rumah ?? '-' }}</span></td>
                        <td class="fw-semibold">Rp {{ number_format($p->harga_rumah, 0, ',', '.') }}</td>
                        <td class="text-danger fw-semibold">
                            {{ $p->sisa_angsuran > 0 ? 'Rp ' . number_format($p->sisa_angsuran, 0, ',', '.') : '-' }}
                        </td>
                        <td style="min-width:130px">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:7px">
                                    <div class="progress-bar bg-{{ $p->progress_bayar >= 100 ? 'success' : ($p->progress_bayar > 50 ? 'primary' : 'warning') }}" style="width:{{ $p->progress_bayar }}%"></div>
                                </div>
                                <small class="text-dark fw-bold">{{ $p->progress_bayar }}%</small>
                            </div>
                        </td>
                        <td>
                            @if($p->status === 'lunas')
                                <span class="badge badge-status bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                            @elseif($p->status === 'aktif')
                                <span class="badge badge-status bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"><i class="bi bi-hourglass-split me-1"></i> Aktif</span>
                            @else
                                <span class="badge badge-status bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Bermasalah</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('penghuni.show', $p) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('penghuni.edit', $p) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('penghuni.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus data warga ini?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data warga</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3 px-4">
        {{ $penghuni->onEachSide(1)->links() }}
    </div>
</div>
@endsection
