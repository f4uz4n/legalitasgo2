@extends('layouts.admin')
@section('title', $penghuni->nama_lengkap)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('penghuni.index') }}">Penghuni</a></li>
<li class="breadcrumb-item active">{{ $penghuni->nama_lengkap }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-fill text-primary"></i> {{ $penghuni->nama_lengkap }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('penghuni.edit', $penghuni) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil-square"></i> Edit Penghuni
        </a>
        <a href="{{ route('pembayaran.create', ['angsuran_id' => $penghuni->angsuran->where('status', 'belum_bayar')->first()?->id]) }}" class="btn btn-primary">
            <i class="bi bi-credit-card-2-front"></i> Bayar Angsuran
        </a>
    </div>
</div>

<!-- Info Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header">
                <span><i class="bi bi-person-vcard me-2"></i>Data Pribadi & Akun</span>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>NIK:</strong> {{ $penghuni->nik ?? '-' }}</p>
                <p class="mb-2"><strong>No. HP:</strong> {{ $penghuni->no_hp ?? '-' }}</p>
                <p class="mb-2"><strong>Email Kontak:</strong> {{ $penghuni->email ?? '-' }}</p>
                <p class="mb-2"><strong>Perumahan:</strong> {{ $penghuni->perumahan->nama_perumahan ?? '-' }}</p>
                <p class="mb-2"><strong>Blok/Kavling:</strong> <span class="badge bg-light text-primary border fw-bold">{{ $penghuni->blok_kavling }}</span></p>
                <p class="mb-2"><strong>Tipe:</strong> {{ $penghuni->tipe_rumah ?? '-' }}</p>
                <p class="mb-3"><strong>Status Unit:</strong>
                    @if($penghuni->status === 'lunas')
                        <span class="badge bg-success">Lunas</span>
                    @elseif($penghuni->status === 'aktif')
                        <span class="badge bg-primary">Aktif</span>
                    @else
                        <span class="badge bg-danger">Bermasalah</span>
                    @endif
                </p>

                <div class="pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark small"><i class="bi bi-shield-lock-fill text-primary me-1"></i> Akun Login Portal</span>
                        @if($penghuni->user)
                            <span class="badge bg-success bg-opacity-10 text-success fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold">Belum Ada</span>
                        @endif
                    </div>
                    @if($penghuni->user)
                        <div class="p-2 rounded-2 bg-light border">
                            <div class="small text-muted">Email Login:</div>
                            <div class="fw-bold small text-dark text-break">{{ $penghuni->user->email }}</div>
                            <div class="mt-1 d-flex align-items-center justify-content-between">
                                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.7rem;">Role: Warga</span>
                                <a href="{{ route('penghuni.edit', $penghuni) }}" class="small text-primary text-decoration-none">
                                    <i class="bi bi-key"></i> Reset Password
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-2 rounded-2 bg-light text-center border">
                            <span class="small text-muted d-block mb-1">Belum memiliki akun login.</span>
                            <a href="{{ route('penghuni.edit', $penghuni) }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.78rem;">
                                <i class="bi bi-person-plus"></i> Buat Akun Warga
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header"><span>Data Keuangan</span></div>
            <div class="card-body">
                <p><strong>Harga Rumah:</strong><br><span class="fs-5 fw-bold text-primary">Rp {{ number_format($penghuni->harga_rumah, 0, ',', '.') }}</span></p>
                <p><strong>Uang Muka:</strong> Rp {{ number_format($penghuni->uang_muka, 0, ',', '.') }}</p>
                <p><strong>Sisa Angsuran:</strong> Rp {{ number_format($penghuni->sisa_angsuran, 0, ',', '.') }}</p>
                <p><strong>Tenor:</strong> {{ $penghuni->tenor_bulan }} bulan</p>
                <p><strong>Angsuran/Bulan:</strong> Rp {{ number_format($penghuni->angsuran_bulanan, 0, ',', '.') }}</p>
                <p><strong>Tanggal Akad:</strong> {{ $penghuni->tanggal_akad?->format('d/m/Y') ?? '-' }}</p>
                <div class="mt-3">
                    <small class="text-muted fw-semibold">Progress Pembayaran</small>
                    <div class="progress mt-1" style="height:10px">
                        <div class="progress-bar bg-{{ $penghuni->progress_bayar >= 100 ? 'success' : 'primary' }}" style="width:{{ $penghuni->progress_bayar }}%"></div>
                    </div>
                    <small class="fw-bold">{{ $penghuni->progress_bayar }}%</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header"><span>Status Legalitas</span></div>
            <div class="card-body">
                @if($penghuni->legalitas)
                    <p><strong>Jenis:</strong> {{ $penghuni->legalitas->jenis_sertifikat ?? '-' }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-{{ $penghuni->legalitas->status_color }}">{{ $penghuni->legalitas->status_label }}</span></p>
                    <p><strong>Progress:</strong></p>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar bg-success" style="width:{{ $penghuni->legalitas->progress_persen }}%"></div>
                    </div>
                    <small class="fw-bold">{{ $penghuni->legalitas->progress_persen }}%</small>
                    <p class="mt-2"><strong>Notaris:</strong> {{ $penghuni->legalitas->notaris ?? '-' }}</p>
                    <a href="{{ route('legalitas.show', $penghuni->legalitas) }}" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-eye me-1"></i> Detail Legalitas</a>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada data legalitas</p>
                        <a href="{{ route('legalitas.create') }}" class="btn btn-sm btn-primary">Tambah Legalitas</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Jadwal Angsuran -->
<div class="content-card">
    <div class="card-header">
        <span><i class="bi bi-calendar3 me-2"></i>Jadwal Angsuran</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Ke-</th>
                        <th>Jatuh Tempo</th>
                        <th>Jumlah</th>
                        <th>Tgl Bayar</th>
                        <th>Dibayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penghuni->angsuran as $a)
                    <tr>
                        <td><span class="badge bg-light text-dark">{{ $a->angsuran_ke }}</span></td>
                        <td>{{ $a->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                        <td>Rp {{ number_format($a->jumlah_angsuran, 0, ',', '.') }}</td>
                        <td>{{ $a->tanggal_bayar?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $a->jumlah_bayar > 0 ? 'Rp ' . number_format($a->jumlah_bayar, 0, ',', '.') : '-' }}</td>
                        <td><span class="badge badge-status bg-{{ $a->status_color }} bg-opacity-10 text-{{ $a->status_color }}">{{ $a->status_label }}</span></td>
                        <td>
                            @if($a->status !== 'lunas')
                            <a href="{{ route('pembayaran.create', ['angsuran_id' => $a->id]) }}" class="btn btn-sm btn-primary rounded-3"><i class="bi bi-cash me-1"></i> Bayar</a>
                            @else
                            <span class="text-success"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
