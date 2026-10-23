@extends('layouts.admin')
@section('title', 'Manajemen Cicilan Angsuran')
@section('breadcrumb')
<li class="breadcrumb-item active">Cicilan Angsuran</li>
@endsection

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <span class="icon-blue p-2 rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-credit-card-2-front-fill text-primary fs-5"></i>
            </span>
            <span>Jadwal &amp; Pembayaran Cicilan Rumah</span>
        </h4>
        <p class="text-muted small mb-0">
            @if(Auth::user()->isWarga())
                Pantau seluruh jadwal jatuh tempo, histori transfer, dan kwitansi resmi cicilan unit Anda.
            @else
                Kelola penagihan angsuran, verifikasi berkas transfer bukti bayar warga, dan pantau piutang kavling perumahan.
            @endif
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('public.report') }}" target="_blank" class="btn btn-outline-primary btn-sm shadow-2xs">
            <i class="bi bi-broadcast"></i> Portal Publik Live
        </a>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-circle"></i> Catat Pembayaran Cicilan
        </a>
        @endif
    </div>
</div>

<!-- 4 Key Financial Summary Cards for Installments -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Dana Masuk Angsuran -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-success">Total Dana Terbayar</div>
                <div class="stat-icon-wrapper icon-green">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
            <div class="stat-number text-success">
                Rp {{ number_format($stats['total_terbayar'], 0, ',', '.') }}
            </div>
            @php
                $persenTerkumpul = $stats['total_tagihan'] > 0 ? round(($stats['total_terbayar'] / $stats['total_tagihan']) * 100, 1) : 0;
            @endphp
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Lunas: <strong>{{ $stats['count_lunas'] }}</strong> dari {{ $stats['count_total'] }} tagihan</span>
                <span class="fw-bold text-dark">{{ $persenTerkumpul }}%</span>
            </div>
            <div class="progress mt-1" style="height: 5px;">
                <div class="progress-bar bg-success" style="width: {{ min(100, $persenTerkumpul) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Card 2: Sisa Piutang / Kewajiban Cicilan -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-primary">Sisa Piutang Angsuran</div>
                <div class="stat-icon-wrapper icon-blue">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
            </div>
            <div class="stat-number text-dark">
                Rp {{ number_format($stats['total_sisa'], 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Total Tagihan Berjalan:</span>
                <span class="fw-bold text-dark">Rp {{ number_format($stats['total_tagihan'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Tagihan Jatuh Tempo Bulan Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-warning">Jatuh Tempo Bulan Ini</div>
                <div class="stat-icon-wrapper" style="background: #fffbeb; color: #d97706;">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
            <div class="stat-number text-dark">
                {{ $stats['count_bulan_ini'] }} <span class="fs-6 text-muted fw-normal">Tagihan</span>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Target Bulan {{ now()->translatedFormat('M Y') }}:</span>
                <span class="fw-bold text-primary">Rp {{ number_format($stats['tagihan_bulan_ini'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Menunggak / Terlambat -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-danger">Tunggakan Terlambat</div>
                <div class="stat-icon-wrapper icon-red">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
            <div class="stat-number {{ $stats['count_terlambat'] > 0 ? 'text-danger' : 'text-success' }}">
                {{ $stats['count_terlambat'] }} <span class="fs-6 text-muted fw-normal">Tagihan</span>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Status Penagihan:</span>
                @if($stats['count_terlambat'] > 0)
                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">Perlu Ditagih</span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold">Nihil Tunggakan</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filter Toolbar & Navigation Box -->
<div class="card-custom mb-4 p-3 bg-white">
    <!-- Status Tabs / Quick Pills -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 pb-3 border-bottom">
        <span class="text-muted small fw-bold text-uppercase me-1"><i class="bi bi-funnel text-primary"></i> Status:</span>
        <a href="{{ route('pembayaran.index', array_merge(request()->except('status', 'page'), [])) }}" 
           class="btn btn-sm {{ !request()->filled('status') ? 'btn-primary' : 'btn-light border' }}">
            Semua ({{ $stats['count_total'] }})
        </a>
        <a href="{{ route('pembayaran.index', array_merge(request()->except('status', 'page'), ['status' => 'bulan_ini'])) }}" 
           class="btn btn-sm {{ request('status') === 'bulan_ini' ? 'btn-primary' : 'btn-light border' }}">
            <i class="bi bi-calendar-check"></i> Jatuh Tempo Bulan Ini ({{ $stats['count_bulan_ini'] }})
        </a>
        <a href="{{ route('pembayaran.index', array_merge(request()->except('status', 'page'), ['status' => 'terlambat'])) }}" 
           class="btn btn-sm {{ request('status') === 'terlambat' ? 'btn-danger' : ( $stats['count_terlambat'] > 0 ? 'btn-outline-danger' : 'btn-light border' ) }}">
            <i class="bi bi-clock-history"></i> Terlambat ({{ $stats['count_terlambat'] }})
        </a>
        <a href="{{ route('pembayaran.index', array_merge(request()->except('status', 'page'), ['status' => 'lunas'])) }}" 
           class="btn btn-sm {{ request('status') === 'lunas' ? 'btn-success' : 'btn-light border text-success' }}">
            <i class="bi bi-check-circle"></i> Lunas ({{ $stats['count_lunas'] }})
        </a>
        <a href="{{ route('pembayaran.index', array_merge(request()->except('status', 'page'), ['status' => 'belum_bayar'])) }}" 
           class="btn btn-sm {{ request('status') === 'belum_bayar' ? 'btn-primary' : 'btn-light border' }}">
            <i class="bi bi-hourglass-split"></i> Belum Bayar ({{ $stats['count_belum_bayar'] }})
        </a>
    </div>

    <!-- Filter Form: Range Tanggal, Bulan, Urutan & Pencarian -->
    <form action="{{ route('pembayaran.index') }}" method="GET" class="row g-2 align-items-end">
        @if(request()->filled('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <!-- Filter Bulan Dropdown -->
        <div class="col-lg-3 col-md-6">
            <label class="form-label small fw-bold text-muted mb-1 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-month text-primary me-1"></i>Filter Bulan (Jatuh Tempo)</span>
                @if(!empty($selectedBulan) && $selectedBulan === now()->format('Y-m'))
                    <span class="badge bg-primary-subtle text-primary" style="font-size:0.65rem;">BULAN AKTIF</span>
                @endif
            </label>
            <select name="bulan" class="form-select form-select-sm" id="filterBulan">
                <option value="">-- Semua Bulan --</option>
                @foreach($availableMonths as $ym)
                    @php
                        $cDate = \Carbon\Carbon::createFromFormat('Y-m', $ym);
                        $isCurrentMonth = ($ym === now()->format('Y-m'));
                    @endphp
                    <option value="{{ $ym }}" {{ $selectedBulan === $ym && !$isAll && empty($tanggalDari) ? 'selected' : '' }}>
                        {{ $cDate->translatedFormat('F Y') }} {{ $isCurrentMonth ? '⭐ (Bulan Berjalan)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filter Range Tanggal Dari -->
        <div class="col-lg-2 col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
            <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ $tanggalDari }}">
        </div>

        <!-- Filter Range Tanggal Sampai -->
        <div class="col-lg-2 col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
            <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ $tanggalSampai }}">
        </div>

        <!-- Urutkan Berdasarkan Record Terbaru (Default) -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Urutan Data</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="terbaru" {{ $sort === 'terbaru' || $sort === 'created_desc' ? 'selected' : '' }}>Record Terbaru (Default)</option>
                <option value="terlama" {{ $sort === 'terlama' || $sort === 'created_asc' ? 'selected' : '' }}>Record Terlama</option>
                <option value="tempo_asc" {{ $sort === 'tempo_asc' ? 'selected' : '' }}>Jatuh Tempo Terdekat</option>
                <option value="tempo_desc" {{ $sort === 'tempo_desc' ? 'selected' : '' }}>Jatuh Tempo Terjauh</option>
                <option value="ke_asc" {{ $sort === 'ke_asc' ? 'selected' : '' }}>Cicilan Ke 1 &rarr; N</option>
                <option value="ke_desc" {{ $sort === 'ke_desc' ? 'selected' : '' }}>Cicilan Ke N &rarr; 1</option>
            </select>
        </div>

        <!-- Cari Nama Warga / Kavling -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Pencarian</label>
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama / Kavling..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Tombol Filter & Reset -->
        <div class="col-lg-1 col-md-4 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Terapkan Filter">
                <i class="bi bi-funnel"></i>
            </button>
            <a href="{{ route('pembayaran.index', ['periode' => 'all']) }}" class="btn btn-light border btn-sm" title="Tampilkan Semua Periode / Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>

    <!-- Indikator Banner Filter Aktif -->
    <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center justify-content-between gap-2 small">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted"><i class="bi bi-info-circle text-primary me-1"></i> Tampilan Saat Ini:</span>
            @if($isAll)
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                    <i class="bi bi-clock-history me-1"></i> Semua Periode (All Time)
                </span>
            @elseif(!empty($tanggalDari) || !empty($tanggalSampai))
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="bi bi-calendar-range me-1"></i> Rentang: {{ $tanggalDari ? date('d/m/Y', strtotime($tanggalDari)) : 'Awal' }} &mdash; {{ $tanggalSampai ? date('d/m/Y', strtotime($tanggalSampai)) : 'Sekarang' }}
                </span>
            @elseif(!empty($selectedBulan))
                @php
                    $dtBulan = \Carbon\Carbon::createFromFormat('Y-m', $selectedBulan);
                    $isNow = ($selectedBulan === now()->format('Y-m'));
                @endphp
                <span class="badge bg-primary text-white px-2 py-1">
                    <i class="bi bi-calendar2-check-fill me-1"></i> Bulan: {{ $dtBulan->translatedFormat('F Y') }} {{ $isNow ? '(Bulan Aktif Sekarang)' : '' }}
                </span>
            @endif

            <span class="badge bg-light text-dark border px-2 py-1">
                <i class="bi bi-sort-down me-1"></i>
                @if($sort === 'terbaru')
                    Record Terbaru
                @elseif($sort === 'terlama')
                    Record Terlama
                @elseif($sort === 'tempo_asc')
                    Tempo Terdekat
                @elseif($sort === 'tempo_desc')
                    Tempo Terjauh
                @elseif($sort === 'ke_asc')
                    Cicilan 1 &rarr; N
                @else
                    Cicilan N &rarr; 1
                @endif
            </span>
        </div>

        <div>
            @if(!$isAll)
                <a href="{{ route('pembayaran.index', array_merge(request()->except('bulan', 'tanggal_dari', 'tanggal_sampai'), ['periode' => 'all'])) }}" class="text-decoration-none fw-semibold text-primary">
                    <i class="bi bi-eye me-1"></i> Tampilkan Semua Bulan (All Time)
                </a>
            @else
                <a href="{{ route('pembayaran.index') }}" class="text-decoration-none fw-semibold text-primary">
                    <i class="bi bi-calendar-check me-1"></i> Kembali ke Bulan Aktif Sekarang
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-custom bg-white">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark"><i class="bi bi-table text-primary me-1"></i> Daftar Jadwal &amp; Pembayaran Angsuran</span>
            <span class="badge bg-light text-primary border">{{ $angsuran->total() }} Data Ditemukan</span>
        </div>
        <div class="small text-muted">
            Halaman <strong>{{ $angsuran->currentPage() }}</strong> dari <strong>{{ $angsuran->lastPage() }}</strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 105px;">Blok Kavling</th>
                    <th>Nama Warga</th>
                    <th style="width: 95px;">Cicilan</th>
                    <th>Jatuh Tempo</th>
                    <th>Nominal Angsuran</th>
                    <th>Total Dibayar</th>
                    <th style="width: 150px;">Bukti Bayar / Berkas</th>
                    <th style="width: 130px;">Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($angsuran as $a)
                @php
                    $isOverdue = $a->status !== 'lunas' && $a->tanggal_jatuh_tempo->isPast();
                    $isDueSoon = $a->status !== 'lunas' && !$a->tanggal_jatuh_tempo->isPast() && $a->tanggal_jatuh_tempo->diffInDays(now()) <= 7;
                    $buktiFile = $a->bukti_bayar ?: ($a->kasMasuk?->bukti_file);
                    $fileExt = $buktiFile ? strtolower(pathinfo($buktiFile, PATHINFO_EXTENSION)) : '';
                    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp']);
                    $isPdf   = ($fileExt === 'pdf');
                @endphp
                <tr class="{{ $isOverdue ? 'table-danger table-opacity-10' : '' }}">
                    <!-- Blok Kavling -->
                    <td>
                        <span class="badge bg-primary text-white fw-bold px-2.5 py-1.5" style="font-size: 0.82rem; letter-spacing: 0.5px;">
                            {{ $a->penghuni->blok_kavling ?? '-' }}
                        </span>
                        <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                            {{ $a->penghuni->tipe_rumah ?? 'Tipe 36' }}
                        </div>
                    </td>

                    <!-- Nama Warga -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-light border text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                                {{ strtoupper(substr($a->penghuni->nama_lengkap ?? 'W', 0, 1)) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">{{ $a->penghuni->nama_lengkap ?? '-' }}</div>
                                <div class="text-muted d-flex align-items-center gap-2" style="font-size: 0.72rem;">
                                    @if(!empty($a->penghuni->no_hp))
                                        <span><i class="bi bi-telephone text-primary"></i> {{ $a->penghuni->no_hp }}</span>
                                    @endif
                                    @if(!empty($a->penghuni->nik))
                                        <span class="d-none d-md-inline">&bull; NIK: {{ $a->penghuni->nik }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Angsuran Ke -->
                    <td>
                        <span class="badge bg-light text-dark border fw-bold px-2.5 py-1.5" style="font-size: 0.8rem;">
                            Ke-{{ $a->angsuran_ke }}
                        </span>
                        <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                            / {{ $a->penghuni->tenor_bulan ?? '-' }} bln
                        </div>
                    </td>

                    <!-- Tanggal Jatuh Tempo -->
                    <td>
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-calendar3 {{ $isOverdue ? 'text-danger' : 'text-primary' }}" style="font-size: 0.85rem;"></i>
                            <span class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                                {{ $a->tanggal_jatuh_tempo->format('d/m/Y') }}
                            </span>
                        </div>
                        @if($a->status === 'lunas')
                            <div class="text-success small mt-0.5" style="font-size: 0.72rem;">
                                <i class="bi bi-check2-circle"></i> Bayar: {{ $a->tanggal_bayar ? $a->tanggal_bayar->format('d/m/Y') : '-' }}
                            </div>
                        @elseif($isOverdue)
                            <div class="badge bg-danger bg-opacity-10 text-danger mt-1" style="font-size: 0.68rem;">
                                <i class="bi bi-exclamation-circle"></i> Lewat {{ (int)$a->tanggal_jatuh_tempo->diffInDays(now()) }} hari
                            </div>
                        @elseif($isDueSoon)
                            <div class="badge bg-warning bg-opacity-10 text-warning mt-1" style="font-size: 0.68rem;">
                                <i class="bi bi-clock"></i> Jatuh tempo dlm {{ (int)$a->tanggal_jatuh_tempo->diffInDays(now()) }} hari
                            </div>
                        @endif
                    </td>

                    <!-- Nominal Angsuran -->
                    <td>
                        <div class="fw-bold text-dark">
                            Rp {{ number_format($a->jumlah_angsuran, 0, ',', '.') }}
                        </div>
                    </td>

                    <!-- Total Dibayar -->
                    <td>
                        @if($a->jumlah_bayar > 0)
                            <div class="text-success fw-bold">
                                Rp {{ number_format($a->jumlah_bayar, 0, ',', '.') }}
                            </div>
                            @if($a->jumlah_bayar < $a->jumlah_angsuran)
                                <div class="text-danger small" style="font-size: 0.7rem;">
                                    Kurang: Rp {{ number_format($a->jumlah_angsuran - $a->jumlah_bayar, 0, ',', '.') }}
                                </div>
                            @endif
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>

                    <!-- Bukti Bayar / Berkas Upload (Fitur Melihat Berkas di Admin) -->
                    <td>
                        @if($buktiFile)
                            <div class="d-flex align-items-center gap-2">
                                @if($isImage)
                                    <!-- Mini Thumbnail Interaktif -->
                                    <div class="position-relative overflow-hidden rounded border shadow-sm cursor-pointer btn-preview-bukti"
                                         style="width: 38px; height: 38px; flex-shrink: 0;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#modalPreviewBukti"
                                         data-img="{{ Storage::url($buktiFile) }}"
                                         data-warga="{{ $a->penghuni->nama_lengkap ?? '-' }}"
                                         data-blok="{{ $a->penghuni->blok_kavling ?? '-' }}"
                                         data-ke="{{ $a->angsuran_ke }}"
                                         data-nominal="Rp {{ number_format($a->jumlah_bayar, 0, ',', '.') }}"
                                         data-tgl="{{ $a->tanggal_bayar ? $a->tanggal_bayar->format('d/m/Y') : '-' }}"
                                         title="Klik untuk memperbesar bukti transfer">
                                        <img src="{{ Storage::url($buktiFile) }}" alt="Bukti" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-link p-0 text-success text-decoration-none fw-bold btn-preview-bukti d-block"
                                                style="font-size: 0.78rem;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalPreviewBukti"
                                                data-img="{{ Storage::url($buktiFile) }}"
                                                data-warga="{{ $a->penghuni->nama_lengkap ?? '-' }}"
                                                data-blok="{{ $a->penghuni->blok_kavling ?? '-' }}"
                                                data-ke="{{ $a->angsuran_ke }}"
                                                data-nominal="Rp {{ number_format($a->jumlah_bayar, 0, ',', '.') }}"
                                                data-tgl="{{ $a->tanggal_bayar ? $a->tanggal_bayar->format('d/m/Y') : '-' }}">
                                            <i class="bi bi-image text-success me-0.5"></i> Lihat Foto
                                        </button>
                                        <small class="text-muted" style="font-size: 0.68rem;">Tersimpan</small>
                                    </div>
                                @elseif($isPdf)
                                    <a href="{{ Storage::url($buktiFile) }}" target="_blank" class="badge bg-danger-subtle text-danger border border-danger-subtle py-1.5 px-2 text-decoration-none" title="Buka Dokumen PDF">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Buka PDF
                                    </a>
                                @else
                                    <a href="{{ Storage::url($buktiFile) }}" target="_blank" class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2 text-decoration-none" title="Buka Berkas">
                                        <i class="bi bi-paperclip me-1"></i> Lihat Berkas
                                    </a>
                                @endif
                            </div>
                        @else
                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-dash"></i> Belum Ada File
                            </span>
                        @endif
                    </td>

                    <!-- Status -->
                    <td>
                        @if($a->status === 'lunas')
                            <span class="badge badge-status bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="bi bi-check-circle-fill"></i> Lunas
                            </span>
                        @elseif($a->status === 'terlambat' || $isOverdue)
                            <span class="badge badge-status bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="bi bi-clock-history"></i> Terlambat
                            </span>
                        @elseif($a->status === 'sebagian')
                            <span class="badge badge-status bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                <i class="bi bi-pie-chart-fill"></i> Sebagian
                            </span>
                        @else
                            <span class="badge badge-status bg-secondary bg-opacity-10 text-secondary border">
                                <i class="bi bi-hourglass"></i> Belum Bayar
                            </span>
                        @endif
                    </td>

                    <!-- Aksi -->
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-1.5">
                            @if($buktiFile)
                                <a href="{{ Storage::url($buktiFile) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Buka Berkas Bukti di Tab Baru">
                                    <i class="bi bi-paperclip"></i>
                                </a>
                            @endif

                            <a href="{{ route('pembayaran.show', $a->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Kwitansi / Rincian">
                                <i class="bi bi-receipt"></i> <span class="d-none d-lg-inline">Kwitansi</span>
                            </a>

                            @if(Auth::user()->isAdmin() && $a->status !== 'lunas')
                            <a href="{{ route('pembayaran.create', ['angsuran_id' => $a->id]) }}" class="btn btn-sm btn-primary" title="Catat Pembayaran">
                                <i class="bi bi-cash-stack"></i> Bayar
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <div class="mb-2">
                            <i class="bi bi-inbox fs-1 text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark">Tidak Ada Data Cicilan Ditemukan</h6>
                        <p class="text-muted small mb-3">Tidak ada jadwal cicilan yang cocok dengan parameter filter atau pencarian saat ini.</p>
                        <a href="{{ route('pembayaran.index', ['periode' => 'all']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-counterclockwise"></i> Tampilkan Semua Periode
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Clean Pagination Footer -->
    <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div class="small text-muted">
            Menampilkan <strong>{{ $angsuran->firstItem() ?? 0 }}</strong> sampai <strong>{{ $angsuran->lastItem() ?? 0 }}</strong> dari total <strong>{{ $angsuran->total() }}</strong> jadwal angsuran
        </div>
        <div>
            {{ $angsuran->onEachSide(1)->links() }}
        </div>
    </div>
</div>

<!-- Modal Dialog Preview Bukti Bayar / Struk Transfer -->
<div class="modal fade" id="modalPreviewBukti" tabindex="-1" aria-labelledby="modalPreviewBuktiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="modalPreviewBuktiLabel">
                    <i class="bi bi-file-earmark-image me-1"></i> Pratinjau Berkas Bukti Transfer Pembayaran
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Ringkas Pembayar -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="text-muted small">Nama Warga / Kavling:</div>
                            <div class="fw-bold text-dark" id="previewWarga">-</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="text-muted small">Angsuran Ke:</div>
                            <div class="fw-bold text-primary" id="previewKe">-</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="text-muted small">Total Dibayar:</div>
                            <div class="fw-bold text-success" id="previewNominal">-</div>
                        </div>
                    </div>
                </div>

                <!-- Gambar Pratinjau Bukti -->
                <div class="text-center bg-dark bg-opacity-10 p-2 rounded-3 border overflow-hidden" style="min-height: 250px; max-height: 520px; display: flex; align-items: center; justify-content: center;">
                    <img id="previewImageElement" src="" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 500px; width: auto; object-fit: contain;">
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <a id="btnOpenNewTab" href="#" target="_blank" class="btn btn-outline-primary btn-sm rounded-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Gambar Penuh di Tab Baru
                </a>
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Event listener untuk preview bukti bayar
    $(document).on('click', '.btn-preview-bukti', function() {
        const imgUrl   = $(this).data('img');
        const warga    = $(this).data('warga') + ' (' + $(this).data('blok') + ')';
        const ke       = 'Ke-' + $(this).data('ke');
        const nominal  = $(this).data('nominal');
        const tgl      = $(this).data('tgl');

        $('#previewImageElement').attr('src', imgUrl);
        $('#btnOpenNewTab').attr('href', imgUrl);
        $('#previewWarga').text(warga);
        $('#previewKe').text(ke);
        $('#previewNominal').text(nominal + ' (' + tgl + ')');
    });
});
</script>
@endpush
