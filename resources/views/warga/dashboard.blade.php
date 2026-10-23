@extends('layouts.admin')
@section('title', 'Portal Warga - ' . ($penghuni->nama_lengkap ?? Auth::user()->name))
@section('breadcrumb')
<li class="breadcrumb-item active">Portal Warga</li>
@endsection

@section('content')
@if(!$penghuni)
<div class="card-custom p-5 text-center bg-white">
    <div class="mb-3 text-warning">
        <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
    </div>
    <h4 class="fw-bold text-dark">Data Kavling Belum Terhubung</h4>
    <p class="text-muted max-w-md mx-auto mb-4">
        Akun Anda telah terdaftar sebagai Warga, namun data unit kavling belum dikaitkan oleh Pengelola Perumahan. Silakan hubungi admin pengelola untuk menghubungkan kavling Anda.
    </p>
    <a href="{{ route('public.report') }}" class="btn btn-primary px-4">
        <i class="bi bi-broadcast"></i> Lihat Laporan Transparansi Publik
    </a>
</div>
@else
<!-- Welcome Banner Warga -->
<div class="card-custom p-4 mb-4 bg-white" style="border-left: 5px solid var(--primary) !important;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.72rem;">PORTAL WARGA</span>
                <h4 class="fw-bold mb-0 text-dark">Selamat Datang, {{ $penghuni->nama_lengkap }}</h4>
            </div>
            <p class="text-secondary small mb-0">
                <i class="bi bi-house-door-fill text-primary me-1"></i> Unit: <strong>Blok {{ $penghuni->blok_kavling }}</strong> &bull; 
                Perumahan: <strong>{{ $penghuni->perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</strong> &bull; 
                Tipe: <strong>{{ $penghuni->tipe_rumah ?? '36/72' }}</strong>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('public.report') }}" target="_blank" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-globe2"></i> Laporan Live Transparansi
            </a>
            <a href="{{ route('profile.edit') }}" class="btn btn-light-blue btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-person-gear"></i> Ubah Password
            </a>
        </div>
    </div>
</div>

@php
    $totalAngsuranTerbayar = $penghuni->angsuran->where('status', 'lunas')->sum('jumlah_bayar');
    $totalTerkumpul = $penghuni->uang_muka + $totalAngsuranTerbayar;
    $persenBayar = $penghuni->harga_rumah > 0 ? round(($totalTerkumpul / $penghuni->harga_rumah) * 100, 1) : 0;
    $sisaAngsuran = max(0, $penghuni->harga_rumah - $totalTerkumpul);
    $angsuranBerikutnya = $penghuni->angsuran->where('status', 'belum_bayar')->first();
@endphp

<!-- 4 Key Resident Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Harga Unit -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-primary">Nilai Properti Unit</div>
                <div class="stat-icon-wrapper icon-blue">
                    <i class="bi bi-house-check-fill"></i>
                </div>
            </div>
            <div class="stat-number text-dark">
                Rp {{ number_format($penghuni->harga_rumah, 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Uang Muka (DP):</span>
                <span class="fw-bold text-success">Rp {{ number_format($penghuni->uang_muka, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Telah Terbayar -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-success">Total Dana Terbayar</div>
                <div class="stat-icon-wrapper icon-green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="stat-number text-success">
                Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Progres Angsuran:</span>
                <span class="fw-bold text-dark">{{ $persenBayar }}%</span>
            </div>
            <div class="progress mt-1" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($persenBayar, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Card 3: Sisa Angsuran -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-danger">Sisa Kewajiban Cicilan</div>
                <div class="stat-icon-wrapper icon-red">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-number text-danger">
                Rp {{ number_format($sisaAngsuran, 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Cicilan per Bulan:</span>
                <span class="fw-bold text-dark">Rp {{ number_format($penghuni->angsuran_bulanan, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Progres Legalitas Sertifikat -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-primary">Status Sertifikat Tanah</div>
                <div class="stat-icon-wrapper icon-blue">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
            </div>
            <div class="stat-number text-primary" style="font-size: 1.35rem;">
                {{ $penghuni->legalitas ? $penghuni->legalitas->status_label : 'Menunggu Proses' }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Progres Legalitas:</span>
                <span class="fw-bold text-dark">{{ $penghuni->legalitas ? $penghuni->legalitas->progress_persen : 0 }}%</span>
            </div>
            <div class="progress mt-1" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $penghuni->legalitas ? $penghuni->legalitas->progress_persen : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Unit & Legalitas -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="content-card h-100">
            <div class="card-header">
                <span><i class="bi bi-card-heading me-2"></i>Rincian Unit Kavling Anda</span>
                <span class="badge bg-primary bg-opacity-10 text-primary">Blok {{ $penghuni->blok_kavling }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <small class="text-muted d-block">Luas Bangunan / Tanah</small>
                            <span class="fw-bold text-dark fs-6">{{ $penghuni->luas_bangunan ?? '-' }} m² / {{ $penghuni->luas_tanah ?? '-' }} m²</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <small class="text-muted d-block">Tenor Cicilan</small>
                            <span class="fw-bold text-dark fs-6">{{ $penghuni->tenor_bulan }} Bulan</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <small class="text-muted d-block">Tanggal Akad</small>
                            <span class="fw-bold text-dark fs-6">{{ $penghuni->tanggal_akad ? $penghuni->tanggal_akad->format('d F Y') : '-' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <small class="text-muted d-block">Status Kepemilikan</small>
                            <span class="badge bg-{{ $penghuni->status === 'lunas' ? 'success' : 'primary' }} mt-1">
                                {{ ucfirst($penghuni->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($angsuranBerikutnya)
                <div class="alert alert-warning mt-4 mb-0 d-flex align-items-center justify-content-between p-3 rounded-3 border-0" style="background-color: #fffbeb; color: #92400e;">
                    <div>
                        <div class="fw-bold small mb-1"><i class="bi bi-bell-fill me-1"></i> Tagihan Angsuran Terdekat (Ke-{{ $angsuranBerikutnya->angsuran_ke }})</div>
                        <div class="small">Jatuh Tempo: <strong>{{ $angsuranBerikutnya->tanggal_jatuh_tempo->format('d M Y') }}</strong> &bull; Nominal: <strong>Rp {{ number_format($angsuranBerikutnya->jumlah_angsuran, 0, ',', '.') }}</strong></div>
                    </div>
                    <a href="{{ route('pembayaran.show', $angsuranBerikutnya) }}" class="btn btn-sm btn-primary rounded-3 text-nowrap">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Legalitas Sertifikat -->
    <div class="col-lg-6" id="status-legalitas">
        <div class="content-card h-100">
            <div class="card-header">
                <span><i class="bi bi-award-fill me-2 text-warning"></i>Proses Legalitas & Sertifikat</span>
                @if($penghuni->legalitas)
                    <span class="badge bg-{{ $penghuni->legalitas->status_color }}">{{ $penghuni->legalitas->status_label }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($penghuni->legalitas)
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Jenis Sertifikat</small>
                            <strong class="text-dark">{{ $penghuni->legalitas->jenis_sertifikat ?? 'SHM' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Notaris Rekanan</small>
                            <strong class="text-dark">{{ $penghuni->legalitas->notaris ?? '-' }}</strong>
                        </div>
                        <div class="col-6 mt-2">
                            <small class="text-muted d-block">No. Sertifikat</small>
                            <strong class="text-dark">{{ $penghuni->legalitas->no_sertifikat ?? 'Dalam Proses' }}</strong>
                        </div>
                        <div class="col-6 mt-2">
                            <small class="text-muted d-block">No. AJB</small>
                            <strong class="text-dark">{{ $penghuni->legalitas->no_ajb ?? 'Dalam Proses' }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Progres Pengurusan:</span>
                            <span class="fw-bold text-success">{{ $penghuni->legalitas->progress_persen }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $penghuni->legalitas->progress_persen }}%"></div>
                        </div>
                    </div>

                    @if($penghuni->legalitas->timeline && $penghuni->legalitas->timeline->count() > 0)
                    <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-clock-history me-1"></i> Riwayat Progres BPN & Notaris:</h6>
                    <ul class="list-unstyled mb-0 small">
                        @foreach($penghuni->legalitas->timeline->take(3) as $t)
                        <li class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
                            <i class="bi bi-check2-circle text-success fs-6 mt-1"></i>
                            <div>
                                <strong class="text-dark">{{ $t->judul }}</strong>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $t->tanggal->format('d M Y') }} &bull; {{ $t->keterangan }}</div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('legalitas.show', $penghuni->legalitas) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-journal-check"></i> Lihat Selengkapnya Buku Legalitas
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-lock text-muted" style="font-size: 2.5rem;"></i>
                        <p class="text-muted small mt-2 mb-0">Berkas legalitas unit Anda sedang dalam proses verifikasi awal developer & notaris.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Tabel Jadwal & Riwayat Angsuran -->
<div class="content-card mb-4" id="jadwal-angsuran">
    <div class="card-header">
        <span><i class="bi bi-calendar-check me-2"></i>Jadwal & Riwayat Pembayaran Angsuran</span>
        <span class="badge bg-light text-dark border">{{ $penghuni->angsuran->count() }} Kali Cicilan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Ke-</th>
                        <th>Jatuh Tempo</th>
                        <th>Nominal Angsuran</th>
                        <th>Tanggal Bayar</th>
                        <th>Jumlah Dibayar</th>
                        <th>Status</th>
                        <th>Bukti / Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penghuni->angsuran as $a)
                    <tr>
                        <td>
                            <span class="badge bg-light text-dark fw-bold border">{{ $a->angsuran_ke }}</span>
                        </td>
                        <td>{{ $a->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                        <td class="fw-semibold">Rp {{ number_format($a->jumlah_angsuran, 0, ',', '.') }}</td>
                        <td>{{ $a->tanggal_bayar ? $a->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($a->jumlah_bayar > 0)
                                <span class="text-success fw-bold">Rp {{ number_format($a->jumlah_bayar, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-status bg-{{ $a->status_color }} bg-opacity-10 text-{{ $a->status_color }}">
                                <i class="bi bi-{{ $a->status === 'lunas' ? 'check-circle' : 'hourglass-split' }} me-1"></i>
                                {{ $a->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('pembayaran.show', $a) }}" class="btn btn-sm btn-outline-primary" title="Lihat Kwitansi / Rincian">
                                <i class="bi bi-receipt"></i> Rincian
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada jadwal angsuran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
