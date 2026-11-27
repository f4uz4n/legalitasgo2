@extends('layouts.admin')
@section('title', 'Dashboard Finansial & Cicilan')
@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Project Welcome Banner (Single Housing Project Focus) -->
<div class="card-custom p-4 mb-4 bg-white" style="border-left: 5px solid var(--primary) !important;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">{{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</h4>
                <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.72rem;">SISTEM TUNGGAL</span>
            </div>
            <p class="text-secondary small mb-0">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $perumahan->alamat ?? 'Cibinong, Kab. Bogor' }} &bull; 
                Total Kapasitas: <strong>{{ $perumahan->total_unit ?? 40 }} Kavling</strong> &bull; 
                Warga Terdaftar: <strong>{{ $totalPenghuni }} Warga</strong>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
                <label for="tahun" class="small text-muted mb-0">Tahun</label>
                <select id="tahun" name="tahun" class="form-select form-select-sm" style="width: 8.5rem;" onchange="this.form.submit()">
                    <option value="semua" @selected($tahunDipilih === null)>Semua tahun</option>
                    @foreach($daftarTahun as $tahun)
                        <option value="{{ $tahun }}" @selected($tahunDipilih === (int) $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-lg"></i> Catat Cicilan Warga
            </a>
            <a href="{{ route('kas.index') }}" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-dash-lg"></i> Catat Pengeluaran
            </a>
            <a href="{{ route('public.report') }}" target="_blank" class="btn btn-light-blue btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-broadcast text-primary"></i> Laporan Live Warga
            </a>
            <a href="{{ route('report-pdf.form') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Download Laporan PDF
            </a>
        </div>
    </div>
</div>

<!-- 4 High-Contrast Key Financial Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Dana Masuk / Collecting -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-primary">Total Penerimaan</div>
                <div class="stat-icon-wrapper icon-blue">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
            <div class="stat-number text-primary">
                Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Periode:</span>
                <span class="fw-bold text-dark">{{ $tahunDipilih ? $tahunDipilih : 'Semua tahun' }}</span>
            </div>
            <div class="text-secondary small mt-1">
                <i class="bi bi-info-circle text-muted me-1"></i> Kas masuk, pembayaran angsuran, dan kas keluar pada periode ini. Uang muka tidak dihitung.
            </div>
        </div>
    </div>

    <!-- Card 2: Total Segala Pengeluaran Kas -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-danger">Total Pengeluaran Kas</div>
                <div class="stat-icon-wrapper icon-rose">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
            </div>
            <div class="stat-number text-danger">
                Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Alokasi Utama:</span>
                <span class="fw-bold text-dark">Notaris & BPN</span>
            </div>
            <div class="text-secondary small mt-1">
                <i class="bi bi-info-circle text-muted me-1"></i> Biaya AJB, BPHTB, & PNBP BPN
            </div>
        </div>
    </div>

    <!-- Card 3: Saldo Kas Tersedia (Net Cash) -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100" style="background-color: #f0fdf4 !important; border-color: #bbf7d0 !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-success">Saldo Kas Bersih Saat Ini</div>
                <div class="stat-icon-wrapper icon-green">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="stat-number text-success">
                Rp {{ number_format($saldoKas, 0, ',', '.') }}
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small" style="border-color: #dcfce7 !important;">
                <span class="text-muted">Status Kas:</span>
                <span class="badge bg-success text-white">Surplus / Aktif</span>
            </div>
            <div class="text-success small mt-1 fw-medium">
                <i class="bi bi-shield-check me-1"></i> Siap dialokasikan untuk operasional
            </div>
        </div>
    </div>

    <!-- Card 4: Legalitas Sertifikat -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-white h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="stat-sublabel text-secondary">Progres Sertifikat Rumah</div>
                <div class="stat-icon-wrapper icon-purple">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
            <div class="stat-number text-dark">
                {{ $legalitasStats['selesai'] ?? 0 }} <span class="fs-6 text-muted fw-normal">/ {{ $legalitasStats['total'] ?? 15 }} Berkas</span>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted">Sedang Berjalan di BPN:</span>
                <span class="fw-bold text-primary">{{ $legalitasStats['proses_bpn'] ?? 0 }} Kavling</span>
            </div>
            <div class="text-muted small mt-1">
                <a href="{{ route('legalitas.index') }}" class="text-decoration-none text-primary fw-semibold">
                    Lihat timeline lengkap &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section: Arus Kas & Pengeluaran -->
<div class="row g-4 mb-4">
    <!-- Chart: Cashflow Bulanan -->
    <div class="col-lg-8">
        <div class="card-custom p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Arus Kas Bulanan (Uang Masuk vs Uang Keluar)</h6>
                    <small class="text-muted">{{ $tahunDipilih ? 'Januari–Desember ' . $tahunDipilih : '12 bulan terakhir' }}</small>
                </div>
                <span class="badge bg-light text-primary border px-3 py-2">
                    <i class="bi bi-graph-up-arrow me-1"></i> Realtime Cash Flow
                </span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Alokasi Pengeluaran -->
    <div class="col-lg-4">
        <div class="card-custom p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Penggunaan Dana</h6>
                    <small class="text-muted">Rincian kategori pengeluaran</small>
                </div>
                <a href="{{ route('kas.index') }}" class="btn btn-sm btn-outline-secondary rounded-2 py-0 px-2" style="font-size: 0.75rem;">
                    Detail
                </a>
            </div>
            <div style="height: 220px; position: relative;">
                <canvas id="expensePieChart"></canvas>
            </div>
            <div class="mt-3 pt-2 border-top small">
                @foreach($pengeluaranKategori->take(3) as $pk)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted text-truncate me-2">{{ $pk->kategori->nama_kategori ?? '-' }}</span>
                    <span class="fw-bold text-dark">Rp {{ number_format($pk->total, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Two Core Workflow Tables: Cicilan Menunggu Bayar & Pengeluaran Kas Terbaru -->
<div class="row g-4">
    <!-- Tabel 1: Cicilan Jatuh Tempo / Belum Bayar -->
    <div class="col-lg-7">
        <div class="card-custom bg-white">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-amber p-1 rounded-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Cicilan Jatuh Tempo Bulan Ini</h6>
                        <small class="text-muted">Daftar warga yang perlu konfirmasi pembayaran</small>
                    </div>
                </div>
                <a href="{{ route('pembayaran.index', ['status' => 'belum_bayar']) }}" class="btn btn-sm btn-light-blue">
                    Lihat Semua ({{ $angsuranJatuhTempo->count() }})
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Kavling</th>
                            <th>Nama Warga</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($angsuranJatuhTempo as $jt)
                        <tr>
                            <td><span class="badge bg-primary text-white fw-bold">{{ $jt->penghuni->blok_kavling ?? '-' }}</span></td>
                            <td class="fw-semibold text-dark">{{ $jt->penghuni->nama_lengkap ?? '-' }}</td>
                            <td>
                                @if($jt->tanggal_jatuh_tempo->isPast())
                                    <span class="badge bg-danger text-white">Terlambat: {{ $jt->tanggal_jatuh_tempo->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted">{{ $jt->tanggal_jatuh_tempo->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($jt->jumlah_angsuran, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('pembayaran.create', ['angsuran_id' => $jt->id]) }}" class="btn btn-sm btn-primary rounded-3 py-1 px-2" title="Catat Bayar">
                                    <i class="bi bi-cash me-1"></i> Bayar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                Semua cicilan bulan ini telah tertagih dan tercatat rapi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-custom bg-white mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-green p-1 rounded-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Riwayat Pembayaran Angsuran</h6>
                        <small class="text-muted">Pembayaran cicilan yang sudah dicatat pada periode ini</small>
                    </div>
                </div>
                <a href="{{ route('pembayaran.index', ['periode' => 'all', 'status' => 'lunas']) }}" class="btn btn-sm btn-outline-success">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pengangsur</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatAngsuran as $bayar)
                        <tr>
                            <td class="text-muted small">{{ $bayar->tanggal_bayar->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold text-dark small text-truncate" style="max-width: 170px;">
                                    {{ $bayar->penghuni->nama_lengkap ?? 'Pengangsur' }}
                                </div>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                    {{ $bayar->penghuni->blok_kavling ?? '-' }} · ke-{{ $bayar->angsuran_ke }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">+ Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada pembayaran angsuran pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-custom bg-white mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-blue p-1 rounded-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Kas Masuk Terbaru</h6>
                        <small class="text-muted">Termasuk angsuran dan pemasukan lain pada periode ini</small>
                    </div>
                </div>
                <a href="{{ route('kas.index') }}" class="btn btn-sm btn-outline-primary">Kelola Kas</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentKasMasuk as $rkm)
                        <tr>
                            <td class="text-muted small">{{ $rkm->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold text-dark small text-truncate" style="max-width: 170px;" title="{{ $rkm->keterangan }}">
                                    {{ $rkm->keterangan ?: ($rkm->kategori->nama_kategori ?? 'Kas masuk') }}
                                </div>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                    {{ $rkm->kategori->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">+ Rp {{ number_format($rkm->jumlah, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada kas masuk pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Tabel 2: Catatan Pengeluaran Kas Terbaru (Penggunaan Dana Transparan) -->
        <div class="card-custom bg-white">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-rose p-1 rounded-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Pengeluaran Kas Terbaru</h6>
                        <small class="text-muted">Catatan penggunaan dana perumahan</small>
                    </div>
                </div>
                <a href="{{ route('kas.index') }}" class="btn btn-sm btn-outline-secondary">
                    Kelola Kas
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan / Penerima</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentKasKeluar as $rkk)
                        <tr>
                            <td class="text-muted small">{{ $rkk->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold text-dark small text-truncate" style="max-width: 170px;" title="{{ $rkk->keterangan }}">
                                    {{ $rkk->keterangan }}
                                </div>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                    {{ $rkk->penerima ?? 'Pihak Ketiga' }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                - Rp {{ number_format($rkk->jumlah, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada pengeluaran kas tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Widget Log Aktivitas Pengelola Terbaru (Audit Trail) -->
<div class="content-card mb-4 bg-white" style="border: 1px solid #bfdbfe;">
    <div class="card-header bg-white py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-shaded text-primary me-1"></i> Log Aktivitas Pengelola Terbaru
                </h6>
                <span class="badge bg-primary text-white" style="font-size: 0.68rem;">AUDIT TRAIL</span>
                <span class="badge bg-light text-danger border border-danger-subtle" style="font-size: 0.68rem;">
                    <i class="bi bi-lock-fill"></i> Tidak Dapat Dihapus
                </span>
            </div>
            <small class="text-muted">Akses menu, pencatatan kas, dan perubahan berkas terekam otomatis dan dapat dipantau oleh warga.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
                Buka Log Lengkap <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <a href="{{ route('public.report') }}" target="_blank" class="btn btn-light border btn-sm fw-bold text-primary">
                <i class="bi bi-broadcast me-1"></i> Laporan Warga ↗
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 160px;">Waktu</th>
                        <th style="width: 160px;">Administrator</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                        <th style="width: 150px;">Modul</th>
                        <th>Keterangan Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivityLogs as $log)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark small">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark small">{{ $log->admin_name }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $log->admin_email ?? 'Pengelola' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $log->action_badge_class }} px-2 py-0.5" style="font-size: 0.72rem;">
                                {{ $log->action_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 0.74rem;">
                                {{ $log->module }}
                            </span>
                        </td>
                        <td>
                            <div class="text-dark small fw-medium text-truncate" style="max-width: 420px;" title="{{ $log->description }}">
                                {{ $log->description }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">
                            <i class="bi bi-shield-check text-primary fs-3 d-block mb-1"></i>
                            Belum ada aktivitas operasional tercatat. Setiap aksi admin akan otomatis terekam permanen di sini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function labelSumbuRupiah(nilai) {
        const angka = Number(nilai) || 0;
        const tanda = angka < 0 ? '-' : '';
        const abs = Math.abs(angka);
        if (abs >= 1000000000) {
            return tanda + 'Rp ' + rapikanAngka(abs / 1000000000) + ' M';
        }
        if (abs >= 1000000) {
            return tanda + 'Rp ' + rapikanAngka(abs / 1000000) + ' Jt';
        }
        if (abs >= 1000) {
            return tanda + 'Rp ' + rapikanAngka(abs / 1000) + ' Rb';
        }
        return tanda + 'Rp ' + Math.round(abs).toLocaleString('id-ID');
    }

    function rapikanAngka(nilai) {
        const bulat = Math.round(nilai * 10) / 10;
        return bulat.toLocaleString('id-ID', { maximumFractionDigits: Number.isInteger(bulat) ? 0 : 1 });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Cash Flow Bar/Line Chart
        const monthlyData = @json($monthlyData);
        const labels = monthlyData.map(item => item.bulan);
        const masukData = monthlyData.map(item => item.masuk);
        const keluarData = monthlyData.map(item => item.keluar);

        const puncakKas = Math.max(0, ...masukData, ...keluarData);
        const sumbuY = {
            beginAtZero: true,
            grace: '8%',
            grid: { color: '#f1f5f9' },
            ticks: {
                maxTicksLimit: 5,
                padding: 8,
                color: '#64748b',
                font: { family: 'Plus Jakarta Sans', size: 11 },
                callback: function (val) {
                    return labelSumbuRupiah(val);
                }
            }
        };
        if (puncakKas <= 0) {
            sumbuY.suggestedMax = 1000000;
            sumbuY.ticks.stepSize = 250000;
        }

        const ctxCash = document.getElementById('cashflowChart').getContext('2d');
        new Chart(ctxCash, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Uang Masuk',
                        data: masukData,
                        backgroundColor: '#1d4ed8',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Pengeluaran Kas (Kas Keluar)',
                        data: keluarData,
                        backgroundColor: '#f43f5e',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Plus Jakarta Sans', weight: '600', size: 12 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                layout: { padding: { left: 4, right: 8 } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            maxRotation: 0,
                            autoSkip: true
                        }
                    },
                    y: sumbuY
                }
            }
        });

        // Expense Pie Chart
        const expenseCategories = @json($pengeluaranKategori);
        const expenseLabels = expenseCategories.map(item => item.kategori ? item.kategori.nama_kategori : 'Lainnya');
        const expenseTotals = expenseCategories.map(item => item.total);

        const ctxPie = document.getElementById('expensePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: expenseLabels,
                datasets: [{
                    data: expenseTotals,
                    backgroundColor: [
                        '#1d4ed8', // Royal Blue
                        '#0284c7', // Sky Blue
                        '#e11d48', // Rose
                        '#f59e0b', // Amber
                        '#8b5cf6'  // Purple
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    });
</script>
@endpush
