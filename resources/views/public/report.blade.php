<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Transparansi Warga (Realtime) - {{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</title>

    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LegalitasGo">
    <link rel="apple-touch-icon" href="/icon-192.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary: #1d4ed8;       /* Royal Blue */
            --primary-hover: #1e40af;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;
            --dark: #0f172a;          /* Slate 900 */
            --dark-muted: #334155;
            --border-color: #e2e8f0;
            --bg-body: #ffffff;       /* PURE WHITE */
        }

        * {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-body);
            color: var(--dark);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Header */
        .public-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 1.1rem 0;
            position: sticky;
            top: 0;
            z-index: 1040;
        }

        .pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 0.76rem;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #bfdbfe;
            letter-spacing: 0.5px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #2563eb;
            animation: pulse-ring 1.8s infinite cubic-bezier(0.4, 0, 0.6, 1);
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 1; box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
            70% { transform: scale(1.1); opacity: 0.9; box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
            100% { transform: scale(0.9); opacity: 1; box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }

        /* Hero Transparency Banner */
        .hero-banner {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 2.8rem 0 2.2rem;
        }

        .hero-title {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.8px;
            line-height: 1.25;
        }

        /* Cards */
        .metric-card-public {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.6rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease;
        }

        .metric-card-public:hover {
            border-color: var(--primary-border);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(29, 78, 216, 0.08);
        }

        .metric-card-public.highlight-green {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .metric-card-public.highlight-blue {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .metric-value-lg {
            font-size: 1.95rem;
            font-weight: 800;
            letter-spacing: -0.6px;
            margin: 0.5rem 0 0.2rem;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        }

        /* Timeline Styles */
        .public-timeline {
            position: relative;
            padding-left: 2rem;
            margin-top: 1.5rem;
        }

        .public-timeline::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 10px;
            bottom: 15px;
            width: 3px;
            background: linear-gradient(to bottom, #2563eb 0%, #93c5fd 50%, #e2e8f0 100%);
            border-radius: 3px;
        }

        .timeline-step {
            position: relative;
            padding-bottom: 2rem;
        }

        .timeline-step:last-child {
            padding-bottom: 0.5rem;
        }

        .timeline-marker {
            position: absolute;
            left: -2rem;
            top: 4px;
            width: 31px;
            height: 31px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #2563eb;
            box-shadow: 0 0 0 4px #eff6ff;
            z-index: 2;
        }

        .timeline-marker.selesai {
            background: #10b981;
            border-color: #059669;
            color: #ffffff;
            box-shadow: 0 0 0 4px #dcfce7;
        }

        .timeline-marker.proses {
            background: #2563eb;
            border-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 0 0 4px #dbeafe;
        }

        .timeline-box {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.3rem 1.5rem;
            transition: all 0.2s ease;
        }

        .timeline-box:hover {
            border-color: var(--primary-border);
            box-shadow: 0 8px 24px rgba(29, 78, 216, 0.06);
        }

        /* Tables */
        .table-custom {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid var(--border-color);
            padding: 0.95rem 1.1rem;
        }

        .table-custom tbody td {
            padding: 0.95rem 1.1rem;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid var(--border-color);
            background-color: #ffffff;
        }

        .table-custom tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Nav Pills Custom */
        .nav-pills .nav-link {
            color: #475569;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 1.25rem;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            background: #eff6ff;
            color: var(--primary);
            border-color: #bfdbfe;
        }

        .nav-pills .nav-link.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
        }
    </style>
</head>
<body>
    <!-- Top Public Navbar -->
    <header class="public-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-houses-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</h5>
                    <small class="text-muted" style="font-size: 0.75rem;">Portal Transparansi Keuangan Harian & Timeline Pemberkasan</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="pulse-badge me-2">
                    <span class="pulse-dot"></span>
                    <span>LIVE REALTIME</span>
                </div>
                <!-- Popup Modal Trigger Button -->
                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTimelinePopup">
                    <i class="bi bi-window-stack"></i> <span class="d-none d-md-inline">Lihat Timeline (Popup Modal)</span>
                </button>
                <!-- PWA Install Button Warga -->
                <button type="button" class="btn btn-outline-success btn-sm rounded-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm btn-trigger-pwa-install" title="Pasang Aplikasi di HP">
                    <i class="bi bi-phone"></i> <span class="d-none d-sm-inline">Pasang App</span>
                </button>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold d-none d-lg-inline-flex align-items-center gap-1">
                    <i class="bi bi-lock-fill"></i> Pengelola
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Transparency Banner -->
    <section class="hero-banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 mb-2 fw-semibold">
                        SISTEM TUNGGAL: {{ $perumahan->nama_perumahan ?? 'GRIYA HARMONI RESIDENCE' }}
                    </div>
                    <h1 class="hero-title">Laporan Keuangan Terbuka & Timeline Pemberkasan Legalitas</h1>
                    <p class="text-secondary mt-2 mb-0" style="font-size: 1.05rem;">
                        Penyajian total dana masuk by bulan secara akumulasi (tanpa menampilkan nama pengangsur untuk privasi warga), rekapitulasi pengeluaran kas operasional, serta timeline kepengurusan berkas resmi (Bapenda Pajak, BPN, Notaris).
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="p-3 bg-light rounded-4 border d-inline-block text-start">
                        <div class="small text-muted mb-1"><i class="bi bi-clock-history me-1"></i> Terakhir Diperbarui:</div>
                        <div class="fw-bold text-dark fs-6" id="lastUpdatedBadge">{{ $lastUpdated->translatedFormat('d F Y - H:i:s') }} WIB</div>
                        <div class="small text-success mt-1"><i class="bi bi-shield-check me-1"></i> Terkoneksi realtime ke sistem kasir</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container py-4 my-2">
        <!-- 3 Financial Transparency Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Akumulasi Dana Masuk -->
            <div class="col-md-4">
                <div class="metric-card-public highlight-blue h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="text-uppercase fw-bold text-primary small" style="letter-spacing: 0.5px;">1. Akumulasi Dana Masuk (In)</span>
                        <i class="bi bi-arrow-down-left-circle-fill text-primary fs-3"></i>
                    </div>
                    <div class="metric-value-lg text-primary" id="valTotalMasuk">
                        Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}
                    </div>
                    <div class="small text-dark fw-semibold mt-1">
                        Penerimaan Pembayaran DP & Cicilan Angsuran
                    </div>
                    <div class="progress mt-3 bg-white border" style="height: 8px;">
                        <div class="progress-bar bg-primary" id="progressBar" style="width: {{ min($progressCollecting, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted small">
                        <span>Progress Collecting:</span>
                        <strong class="text-dark" id="valProgress">{{ $progressCollecting }}%</strong>
                    </div>
                </div>
            </div>

            <!-- 2. Segala Pengeluaran Kas -->
            <div class="col-md-4">
                <div class="metric-card-public h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="text-uppercase fw-bold text-danger small" style="letter-spacing: 0.5px;">2. Pengeluaran Dana Kas (Out)</span>
                        <i class="bi bi-arrow-up-right-circle-fill text-danger fs-3"></i>
                    </div>
                    <div class="metric-value-lg text-danger" id="valTotalKeluar">
                        Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted mt-1">
                        Biaya Pengurusan Notaris, Pajak BPHTB, PNBP BPN, & Operasional
                    </div>
                    <div class="mt-3 pt-2 border-top small text-secondary">
                        <i class="bi bi-check2-circle text-success me-1"></i> Rincian pengeluaran transparan dan terverifikasi
                    </div>
                </div>
            </div>

            <!-- 3. Saldo Kas Bersih -->
            <div class="col-md-4">
                <div class="metric-card-public highlight-green h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="text-uppercase fw-bold text-success small" style="letter-spacing: 0.5px;">3. Saldo Kas Tersedia (Surplus)</span>
                        <i class="bi bi-wallet2 text-success fs-3"></i>
                    </div>
                    <div class="metric-value-lg text-success" id="valSaldoKas">
                        Rp {{ number_format($saldoKas, 0, ',', '.') }}
                    </div>
                    <div class="small text-success fw-semibold mt-1">
                        Kas Riil Tersedia Pada Rekening Pengelola
                    </div>
                    <div class="mt-3 pt-2 border-top small text-success">
                        <i class="bi bi-shield-check me-1"></i> Dana siap dialokasikan untuk kelanjutan legalitas
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BAR / TAB SELECTOR -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <ul class="nav nav-pills gap-2" id="publicReportTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-uangmasuk-btn" data-bs-toggle="pill" data-bs-target="#tabUangMasuk" type="button">
                        <i class="bi bi-calendar-check-fill me-1"></i> Akumulasi Uang Masuk By Bulan
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-timeline-btn" data-bs-toggle="pill" data-bs-target="#tabTimeline" type="button">
                        <i class="bi bi-clock-history me-1"></i> Timeline Pemberkasan Legalitas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-pengeluaran-btn" data-bs-toggle="pill" data-bs-target="#tabPengeluaran" type="button">
                        <i class="bi bi-receipt-cutoff me-1"></i> Rincian Pengeluaran Kas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-logaktivitas-btn" data-bs-toggle="pill" data-bs-target="#tabLogAktivitas" type="button">
                        <i class="bi bi-shield-check me-1"></i> Log Aktivitas Admin
                        <span class="badge bg-primary text-white ms-1" style="font-size: 0.65rem;">AUDIT</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-arsip-btn" data-bs-toggle="pill" data-bs-target="#tabArsipDokumen" type="button">
                        <i class="bi bi-archive-fill me-1"></i> Kearsipan Dokumen
                        @if($arsipPublik->count() > 0)
                        <span class="badge bg-success text-white ms-1" style="font-size: 0.65rem;">{{ $arsipPublik->count() }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <div class="d-flex gap-2">
                <!-- Button Pop-up Modal -->
                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTimelinePopup">
                    <i class="bi bi-arrows-fullscreen me-1"></i> Timeline (Popup Modal)
                </button>
                <!-- Button Tab Halaman Baru -->
                <a href="{{ route('public.timeline') }}" target="_blank" class="btn btn-light border btn-sm rounded-3 fw-bold text-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru ↗
                </a>
            </div>
        </div>

        <!-- TAB CONTENT -->
        <div class="tab-content" id="publicReportTabContent">

            <!-- TAB 1: TOTAL UANG MASUK BY BULAN SECARA AKUMULASI -->
            <div class="tab-pane fade show active" id="tabUangMasuk" role="tabpanel">
                <div class="card-custom p-4 bg-white mb-4" style="border: 2px solid #bfdbfe;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-primary text-white px-2 py-1 mb-1">REKAPITULASI DANA MASUK AKUMULATIF BY BULAN</span>
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-calendar-check-fill text-primary me-2"></i>Pencatatan Uang Masuk By Bulan (Secara Akumulasi)
                            </h5>
                            <small class="text-muted">Total penerimaan angsuran dan pembayaran warga perumahan yang dicatat per bulan secara akumulatif (tanpa menampilkan identitas perorangan demi privasi).</small>
                        </div>
                        <div class="text-md-end">
                            <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                <i class="bi bi-shield-lock-fill me-1 text-primary"></i> Data Agregat Tanpa Identitas Warga
                            </span>
                        </div>
                    </div>

                    <!-- Mini Summary KPI Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Total Akumulasi Terkumpul (In)</div>
                                <div class="fs-4 fw-bold text-primary">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</div>
                                <div class="small text-secondary mt-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Dari awal periode hingga bulan berjalan</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Total Periode Bulan Tercatat</div>
                                <div class="fs-4 fw-bold text-dark">{{ count($uangMasukByBulan) }} Bulan</div>
                                <div class="small text-secondary mt-1"><i class="bi bi-calendar-event me-1"></i> Seluruh bulan penagihan aktif</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Rata-Rata Uang Masuk / Bulan</div>
                                @php
                                    $avgMonthly = count($uangMasukByBulan) > 0 ? $totalKasMasuk / count($uangMasukByBulan) : 0;
                                @endphp
                                <div class="fs-4 fw-bold text-success">Rp {{ number_format($avgMonthly, 0, ',', '.') }}</div>
                                <div class="small text-secondary mt-1"><i class="bi bi-graph-up-arrow text-success me-1"></i> Rata-rata per periode tercatat</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Akumulasi By Bulan -->
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Periode Bulan</th>
                                    <th class="text-center">Banyak Pembayaran</th>
                                    <th class="text-end">Uang Masuk Bulan Tersebut</th>
                                    <th class="text-end">Akumulasi Kumulatif (In)</th>
                                    <th class="text-center" style="width: 175px;">Capaian Target Collecting</th>
                                    <th class="text-center">Status Pembukuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($uangMasukByBulan as $mb)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                <i class="bi bi-calendar-month-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-6">{{ $mb['bulan_nama'] }}</div>
                                                <small class="text-muted">Periode {{ $mb['periode_key'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border border-primary border-opacity-25 px-3 py-2 fw-bold">
                                            <i class="bi bi-cash-stack me-1"></i> {{ $mb['frekuensi'] }} Transaksi Masuk
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success fs-6">
                                            + Rp {{ number_format($mb['total_nominal'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-primary fs-6">
                                            Rp {{ number_format($mb['akumulasi_kumulatif'], 0, ',', '.') }}
                                        </div>
                                        <small class="text-muted">Total berjalan s.d bulan ini</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex justify-content-between small text-muted">
                                                <span>Progress:</span>
                                                <strong class="text-dark">{{ $mb['persen_target'] }}%</strong>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-primary" style="width: {{ min($mb['persen_target'], 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 small">
                                            <i class="bi bi-check2-circle me-1"></i> Masuk Rekening Kas
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Belum ada catatan uang masuk bulanan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts Section: Arus Kas In vs Out & Penggunaan Dana Kas -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-7">
                        <div class="card-custom p-4 bg-white h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Grafik Arus Kas Bulanan (Penerimaan vs Pengeluaran)</h6>
                                    <small class="text-muted">Data perbandingan 6 bulan terakhir</small>
                                </div>
                            </div>
                            <div style="height: 270px; position: relative;">
                                <canvas id="publicCashflowChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card-custom p-4 bg-white h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Komposisi Penggunaan Dana Kas</h6>
                                    <small class="text-muted">Rincian alokasi biaya perumahan</small>
                                </div>
                            </div>
                            <div style="height: 210px; position: relative;">
                                <canvas id="publicExpenseChart"></canvas>
                            </div>
                            <div class="mt-3 pt-2 border-top small">
                                @foreach($kasKeluarPerKategori as $kk)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">{{ $kk['kategori'] }}</span>
                                    <span class="fw-bold text-dark">Rp {{ number_format($kk['total'], 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TIMELINE PEMBERKASAN LEGALITAS (IN-PAGE VIEW) -->
            <div class="tab-pane fade" id="tabTimeline" role="tabpanel">
                <div class="card-custom p-4 p-md-5 mb-4 bg-white" style="border: 2px solid #bfdbfe;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-primary text-white px-3 py-1 mb-2">LIVE TRACKING KEPENGURUSAN</span>
                            <h4 class="fw-bold mb-1 text-dark">
                                <i class="bi bi-clock-history text-primary me-2"></i>Timeline Pemberkasan Legalitas & Sertifikat
                            </h4>
                            <p class="text-muted small mb-0">
                                Perjalanan tahapan berkas (Bapenda Pajak, Kantor Pertanahan BPN, Notaris/PPAT) diperbarui langsung secara realtime sesuai input pengelola.
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalTimelinePopup">
                                <i class="bi bi-arrows-fullscreen me-1"></i> Tampilkan Pop-up Modal
                            </button>
                            <a href="{{ route('public.timeline') }}" target="_blank" class="btn btn-primary btn-sm rounded-3">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru ↗
                            </a>
                        </div>
                    </div>

                    <!-- Search Input for Timeline -->
                    <div class="mb-4">
                        <input type="text" id="timelineSearch" class="form-control border-primary rounded-3 px-3 py-2" placeholder="Cari tahapan berkas (contoh: Bapenda, BPN, Notaris)...">
                    </div>

                    <!-- Timeline Items -->
                    <div class="public-timeline" id="timelineContainer">
                        @forelse($timelines as $t)
                        <div class="timeline-step timeline-item-filter" data-title="{{ strtolower($t->judul . ' ' . $t->deskripsi . ' ' . $t->kavling_terkait) }}">
                            <div class="timeline-marker {{ $t->status }}">
                                @if($t->status === 'selesai')
                                    <i class="bi bi-check-lg"></i>
                                @elseif($t->status === 'proses')
                                    <i class="bi bi-gear-fill"></i>
                                @else
                                    <i class="bi bi-hourglass-split"></i>
                                @endif
                            </div>

                            <div class="timeline-box">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <h6 class="fw-bold mb-0 text-dark fs-6">{{ $t->judul }}</h6>
                                        @if($t->kavling_terkait)
                                <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.73rem;">
                                            <i class="bi bi-tag-fill me-1"></i>{{ $t->kavling_terkait }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $t->status === 'selesai' ? 'success' : ($t->status === 'proses' ? 'primary' : 'warning') }} text-white px-3 py-1">
                                        {{ strtoupper($t->status_label) }}
                                    </span>
                                </div>

                                @if($t->deskripsi)
                                <p class="text-secondary small mb-2" style="line-height: 1.6;">
                                    {{ $t->deskripsi }}
                                </p>
                                @endif

                                <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top" style="font-size: 0.78rem;">
                                    <span class="text-muted">
                                        <i class="bi bi-calendar3 text-primary me-1"></i> Tanggal: <strong>{{ $t->tanggal->format('d F Y') }}</strong>
                                    </span>
                                    @if($t->dokumen_file)
                                    <a href="{{ Storage::url($t->dokumen_file) }}" target="_blank" class="text-primary fw-bold text-decoration-none">
                                        <i class="bi bi-paperclip me-1"></i> Lihat Dokumen / Bukti
                                    </a>
                                    @else
                                    <span class="text-muted"><i class="bi bi-check2 text-success me-1"></i> Diverifikasi Pengelola</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">Belum ada riwayat timeline pengurusan berkas yang diinput.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB 3: RINCIAN PENGELUARAN KAS -->
            <div class="tab-pane fade" id="tabPengeluaran" role="tabpanel">
                <div class="card-custom p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-file-earmark-text-fill text-danger me-2"></i>Rincian Pengeluaran Kas Terbuka
                            </h5>
                            <small class="text-muted">Daftar biaya resmi notaris, dinas perpajakan (BPHTB), BPN, serta operasional</small>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">
                            Transparan & Akuntabel
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Uraian Penggunaan Dana</th>
                                    <th>Penerima Dana</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-center">Bukti / Kuitansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($daftarPengeluaran as $dp)
                                <tr>
                                    <td class="text-muted small">{{ $dp->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $dp->kategori->nama_kategori ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $dp->keterangan }}</td>
                                    <td>
                                        <span class="text-secondary small">{{ $dp->penerima ?? 'Pihak Ketiga' }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        - Rp {{ number_format($dp->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($dp->bukti_file)
                                        <a href="{{ Storage::url($dp->bukti_file) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-3 py-0 px-2" style="font-size: 0.75rem;" title="Lihat Bukti Transaksi">
                                            <i class="bi bi-paperclip"></i> Bukti Kuitansi
                                        </a>
                                        @else
                                        <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada pengeluaran kas tercatat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: LOG AKTIVITAS ADMIN (AUDIT TRAIL PUBLIK) -->
            <div class="tab-pane fade" id="tabLogAktivitas" role="tabpanel">
                <div class="card-custom p-4 bg-white mb-4" style="border: 2px solid #bfdbfe;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-primary text-white px-2 py-1 mb-1">AUDIT TRAIL & TRANSPARANSI REALTIME</span>
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-shield-shaded text-primary me-2"></i>Log Rekam Jejak Aktivitas Pengelola
                            </h5>
                            <small class="text-muted">Setiap aksi menu, pembukuan kas, pembaruan berkas legalitas, dan perubahan data oleh pengelola tercatat secara otomatis dan bersifat permanen (tidak dapat dihapus).</small>
                        </div>
                        <div class="text-md-end">
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fw-bold">
                                <i class="bi bi-lock-fill me-1"></i> Log Permanen (Tidak Bisa Dihapus)
                            </span>
                        </div>
                    </div>

                    <!-- Mini KPI Summary untuk Warga -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Total Aktivitas Pengelola</div>
                                <div class="fs-4 fw-bold text-primary">{{ number_format($totalAdminLogs, 0, ',', '.') }} Rekaman</div>
                                <div class="small text-secondary mt-1"><i class="bi bi-shield-check text-success me-1"></i> Tercatat sejak sistem dijalankan</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Status Keamanan Log</div>
                                <div class="fs-4 fw-bold text-success">100% Append-Only</div>
                                <div class="small text-secondary mt-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Terkunci dari manipulasi / hapus</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Aktivitas Terakhir Tercatat</div>
                                <div class="fs-5 fw-bold text-dark text-truncate">
                                    {{ $adminLogs->first()?->created_at ? $adminLogs->first()->created_at->diffForHumans() : 'Belum ada aktivitas' }}
                                </div>
                                <div class="small text-secondary mt-1"><i class="bi bi-clock-history me-1"></i> Real-time update audit trail</div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Filter Pencarian Log -->
                    <div class="mb-3">
                        <input type="text" id="publicLogSearch" class="form-control rounded-3" placeholder="Filter log aktivitas (ketik kata kunci: kas, warga, sertifikat, nama admin)...">
                    </div>

                    <!-- Tabel Log Aktivitas Publik -->
                    <div class="table-responsive">
                        <table class="table table-custom align-middle" id="publicLogTable">
                            <thead>
                                <tr>
                                    <th style="width: 170px;">Waktu & Tanggal</th>
                                    <th style="width: 170px;">Pengelola (Admin)</th>
                                    <th style="width: 130px;" class="text-center">Kategori Aksi</th>
                                    <th style="width: 160px;">Modul / Fitur</th>
                                    <th>Rincian Aktivitas Operasional</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminLogs as $al)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                                            {{ $al->created_at ? $al->created_at->format('d/m/Y H:i:s') : '-' }}
                                        </div>
                                        <small class="text-muted" style="font-size: 0.74rem;">
                                            {{ $al->created_at ? $al->created_at->diffForHumans() : '' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 26px; height: 26px; font-size: 0.72rem; flex-shrink: 0;">
                                                {{ strtoupper(substr($al->admin_name ?? 'A', 0, 1)) }}
                                            </div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;">
                                                {{ $al->admin_name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $al->action_badge_class }} px-2 py-1 fw-bold" style="font-size: 0.74rem;">
                                            <i class="bi {{ $al->action_icon }} me-1"></i> {{ $al->action_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            {{ $al->module }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-semibold" style="font-size: 0.88rem; line-height: 1.35;">
                                            {{ $al->description }}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-shield-check fs-2 text-primary d-block mb-1"></i>
                                        Belum ada riwayat aktivitas pengelola yang tercatat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 5: KEARSIPAN DOKUMEN PUBLIK -->
            <div class="tab-pane fade" id="tabArsipDokumen" role="tabpanel">
                <div class="card-custom p-4 bg-white mb-4" style="border: 2px solid #bfdbfe;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <span class="badge bg-primary text-white px-2 py-1 mb-1">DOKUMEN ARSIP PERUMAHAN</span>
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-archive-fill text-primary me-2"></i>Kearsipan Dokumen &amp; Legalitas Terbuka
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Dokumen penting perumahan yang dapat diakses oleh warga untuk transparansi.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="arsipSearch" class="form-control form-control-sm rounded-3 border-primary px-3" placeholder="Cari dokumen..." style="min-width: 200px;">
                        </div>
                    </div>

                    @if($arsipPublik->count() > 0)

                    <!-- Filter Kategori -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button class="btn btn-sm btn-primary rounded-pill px-3 arsip-filter-btn active" data-filter="all">
                            <i class="bi bi-grid-fill me-1"></i> Semua ({{ $arsipPublik->count() }})
                        </button>
                        @foreach($arsipPublik->groupBy('kategori') as $kat => $docs)
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 arsip-filter-btn" data-filter="{{ $kat }}">
                            {{ $kat }} ({{ $docs->count() }})
                        </button>
                        @endforeach
                    </div>

                    <!-- Grid Dokumen Arsip -->
                    <div class="row g-3" id="arsipGrid">
                        @foreach($arsipPublik as $doc)
                        <div class="col-12 col-md-6 col-xl-4 arsip-card" data-kategori="{{ $doc->kategori }}" data-search="{{ strtolower($doc->judul_dokumen . ' ' . $doc->kategori . ' ' . $doc->instansi_penerbit . ' ' . $doc->nomor_dokumen) }}">
                            <div class="border rounded-3 p-3 h-100 bg-white" style="transition: box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(29,78,216,0.12)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='none';this.style.transform='none'">

                                <!-- Icon & Badge Atas -->
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @php
                                            $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
                                        @endphp
                                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width:52px;height:52px;background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                                            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                                <i class="bi bi-file-image-fill fs-4 text-primary"></i>
                                            @elseif($ext === 'pdf')
                                                <i class="bi bi-file-pdf-fill fs-4" style="color:#e11d48;"></i>
                                            @elseif(in_array($ext, ['doc','docx']))
                                                <i class="bi bi-file-word-fill fs-4" style="color:#2563eb;"></i>
                                            @elseif(in_array($ext, ['xls','xlsx']))
                                                <i class="bi bi-file-excel-fill fs-4" style="color:#16a34a;"></i>
                                            @elseif(in_array($ext, ['zip','rar']))
                                                <i class="bi bi-file-zip-fill fs-4" style="color:#d97706;"></i>
                                            @else
                                                <i class="bi bi-file-earmark-fill fs-4 text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge rounded-pill px-2 py-1" style="font-size:0.68rem;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">{{ $doc->kategori }}</span>
                                            @if($doc->tanggal_dokumen)
                                            <div class="text-muted mt-1" style="font-size:0.73rem;"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($doc->tanggal_dokumen)->translatedFormat('d M Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="badge bg-success text-white" style="font-size:0.68rem;"><i class="bi bi-globe2 me-1"></i>Publik</span>
                                </div>

                                <!-- Judul -->
                                <h6 class="fw-bold text-dark mb-1" style="font-size:0.9rem;line-height:1.4;">{{ $doc->judul_dokumen }}</h6>

                                @if($doc->nomor_dokumen)
                                <div class="text-muted mb-1" style="font-size:0.78rem;">
                                    <i class="bi bi-hash me-1"></i>{{ $doc->nomor_dokumen }}
                                </div>
                                @endif

                                @if($doc->instansi_penerbit)
                                <div class="text-muted mb-2" style="font-size:0.78rem;">
                                    <i class="bi bi-building me-1"></i>{{ $doc->instansi_penerbit }}
                                </div>
                                @endif

                                @if($doc->deskripsi)
                                <p class="text-secondary mb-3" style="font-size:0.8rem;line-height:1.5;">{{ Str::limit($doc->deskripsi, 100) }}</p>
                                @endif

                                <!-- Footer Dokumen -->
                                <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top gap-2">
                                    <span class="text-muted" style="font-size:0.73rem;">
                                        <i class="bi bi-hdd me-1"></i>
                                        @php
                                            $sizeKb = round($doc->file_size / 1024);
                                            echo $sizeKb >= 1024 ? round($sizeKb/1024, 1) . ' MB' : $sizeKb . ' KB';
                                        @endphp
                                    </span>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('arsip.download', $doc->id) }}" class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1" style="font-size:0.78rem;" title="Unduh Dokumen">
                                            <i class="bi bi-download me-1"></i>Unduh
                                        </a>
                                        <a href="{{ route('arsip.show', $doc->id) }}" class="btn btn-sm btn-primary rounded-2 px-2 py-1" style="font-size:0.78rem;" title="Lihat Detail">
                                            <i class="bi bi-eye me-1"></i>Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @else

                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-archive fs-1 text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Dokumen Publik</h6>
                        <p class="text-muted small mb-0">Pengelola perumahan belum mengunggah dokumen kearsipan yang dapat diakses publik.</p>
                    </div>

                    @endif
                </div>
            </div>

        </div>
    </main>

    <!-- POPUP MODAL TIMELINE PEMBERKASAN LEGALITAS -->
    <div class="modal fade" id="modalTimelinePopup" tabindex="-1" aria-labelledby="modalTimelinePopupLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom p-4">
                    <div>
                        <span class="badge bg-primary text-white px-3 py-1 mb-1">POPUP TIMELINE RESMI</span>
                        <h4 class="modal-title fw-bold text-dark" id="modalTimelinePopupLabel">
                            <i class="bi bi-clock-history text-primary me-2"></i>Timeline Pemberkasan Legalitas & Sertifikat
                        </h4>
                        <p class="text-muted small mb-0">{{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }} &bull; Diperbarui berdasarkan input pengelola</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('public.timeline') }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-3">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tab Baru ↗
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4 p-md-5">
                    <div class="mb-4">
                        <input type="text" id="modalTimelineSearch" class="form-control border-primary rounded-3 px-3 py-2" placeholder="Filter tahapan (Bapenda, BPN, Notaris)...">
                    </div>

                    <div class="public-timeline">
                        @forelse($timelines as $t)
                        <div class="timeline-step modal-timeline-item" data-title="{{ strtolower($t->judul . ' ' . $t->deskripsi . ' ' . $t->kavling_terkait) }}">
                            <div class="timeline-marker {{ $t->status }}">
                                @if($t->status === 'selesai')
                                    <i class="bi bi-check-lg"></i>
                                @elseif($t->status === 'proses')
                                    <i class="bi bi-gear-fill"></i>
                                @else
                                    <i class="bi bi-hourglass-split"></i>
                                @endif
                            </div>

                            <div class="timeline-box">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <h5 class="fw-bold mb-0 text-dark fs-6">{{ $t->judul }}</h5>
                                        @if($t->kavling_terkait)
                                        <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-tag-fill me-1"></i>{{ $t->kavling_terkait }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $t->status === 'selesai' ? 'success' : ($t->status === 'proses' ? 'primary' : 'warning') }} text-white px-3 py-1">
                                        {{ strtoupper($t->status_label) }}
                                    </span>
                                </div>

                                @if($t->deskripsi)
                                <p class="text-secondary small mb-2" style="line-height: 1.6;">
                                    {{ $t->deskripsi }}
                                </p>
                                @endif

                                <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top" style="font-size: 0.78rem;">
                                    <span class="text-muted">
                                        <i class="bi bi-calendar3 text-primary me-1"></i> Tanggal: <strong>{{ $t->tanggal->format('d F Y') }}</strong>
                                    </span>
                                    @if($t->dokumen_file)
                                    <a href="{{ Storage::url($t->dokumen_file) }}" target="_blank" class="text-primary fw-bold text-decoration-none">
                                        <i class="bi bi-paperclip me-1"></i> Lihat Dokumen / Bukti
                                    </a>
                                    @else
                                    <span class="text-muted"><i class="bi bi-check2 text-success me-1"></i> Diverifikasi Pengelola</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">Belum ada riwayat timeline pengurusan berkas.</div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer border-top p-3 bg-light">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Tutup Pop-up</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-top py-4 bg-white text-center text-muted small">
        <div class="container">
            <p class="mb-1 fw-bold text-dark">{{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</p>
            <p class="mb-0">Portal Transparansi Total Uang Masuk Per Tanggal & Timeline Pemberkasan Legalitas &bull; Realtime</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        // Live Search Filter for Tab Timeline
        const tSearch = document.getElementById('timelineSearch');
        if (tSearch) {
            tSearch.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.timeline-item-filter').forEach(item => {
                    const text = item.getAttribute('data-title');
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }

        // Live Search & Filter for Kearsipan Dokumen
        const arsipSearch = document.getElementById('arsipSearch');
        if (arsipSearch) {
            arsipSearch.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.arsip-card').forEach(card => {
                    const text = card.getAttribute('data-search');
                    card.style.display = (text && text.includes(query)) ? '' : 'none';
                });
            });
        }

        // Arsip Kategori Filter Buttons
        document.querySelectorAll('.arsip-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.arsip-filter-btn').forEach(b => {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                this.classList.add('active', 'btn-primary');
                this.classList.remove('btn-outline-primary');
                const filter = this.getAttribute('data-filter');
                document.querySelectorAll('.arsip-card').forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-kategori') === filter) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
                if (arsipSearch) arsipSearch.value = '';
            });
        });

        // Live Search Filter for Modal Timeline
        const mSearch = document.getElementById('modalTimelineSearch');
        if (mSearch) {
            mSearch.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.modal-timeline-item').forEach(item => {
                    const text = item.getAttribute('data-title');
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }

        // Charts & Realtime Polling
        document.addEventListener('DOMContentLoaded', function () {
            // Cash Flow Chart
            const monthlyData = @json($monthlyData);
            const labels = monthlyData.map(item => item.bulan);
            const masukData = monthlyData.map(item => item.masuk);
            const keluarData = monthlyData.map(item => item.keluar);

            const ctxCash = document.getElementById('publicCashflowChart').getContext('2d');
            new Chart(ctxCash, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Dana Masuk (Cicilan)',
                            data: masukData,
                            backgroundColor: '#1d4ed8',
                            borderRadius: 6,
                            barPercentage: 0.6,
                        },
                        {
                            label: 'Pengeluaran Kas',
                            data: keluarData,
                            backgroundColor: '#e11d48',
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
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(val) {
                                    return 'Rp ' + (val / 1000000) + ' Jt';
                                }
                            }
                        }
                    }
                }
            });

            // Expense Breakdown Chart
            const expenseData = @json($kasKeluarPerKategori);
            const expenseLabels = expenseData.map(i => i.kategori);
            const expenseTotals = expenseData.map(i => i.total);

            const ctxExpense = document.getElementById('publicExpenseChart').getContext('2d');
            new Chart(ctxExpense, {
                type: 'doughnut',
                data: {
                    labels: expenseLabels,
                    datasets: [{
                        data: expenseTotals,
                        backgroundColor: ['#1d4ed8', '#0284c7', '#e11d48', '#f59e0b', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '65%'
                }
            });

            // Realtime Auto-Polling (every 15 seconds)
            setInterval(function() {
                fetch('{{ route('api.public.report') }}')
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('valTotalMasuk').innerText = formatRupiah(data.totalKasMasuk);
                        document.getElementById('valTotalKeluar').innerText = formatRupiah(data.totalKasKeluar);
                        document.getElementById('valSaldoKas').innerText = formatRupiah(data.saldoKas);
                        document.getElementById('valProgress').innerText = data.progressCollecting + '%';
                        document.getElementById('progressBar').style.width = Math.min(data.progressCollecting, 100) + '%';
                        document.getElementById('lastUpdatedBadge').innerText = data.lastUpdated + ' WIB';
                    })
                    .catch(err => console.log('Polling update error:', err));
            }, 15000);

            // Filter Pencarian Log Aktivitas Publik
            const publicLogSearch = document.getElementById('publicLogSearch');
            if (publicLogSearch) {
                publicLogSearch.addEventListener('keyup', function() {
                    const q = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#publicLogTable tbody tr');
                    rows.forEach(function(r) {
                        const txt = r.textContent.toLowerCase();
                        r.style.display = txt.includes(q) ? '' : 'none';
                    });
                });
            }
        });
    </script>

    <!-- PWA Service Worker & Install Prompt for Public Report -->
    <script>
        let deferredPrompt = null;

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(reg) {
                        console.log('[PWA] Service Worker registered:', reg.scope);
                    })
                    .catch(function(err) {
                        console.warn('[PWA] Service Worker failed:', err);
                    });
            });
        }

        // Listen for install prompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA] beforeinstallprompt captured on public report');
            const btns = document.querySelectorAll('.btn-trigger-pwa-install');
            btns.forEach(b => b.classList.add('btn-success', 'text-white'));
        });

        // Trigger install on click
        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.btn-trigger-pwa-install')) {
                e.preventDefault();
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function(choice) {
                        if (choice.outcome === 'accepted') {
                            console.log('[PWA] User accepted installation');
                        }
                        deferredPrompt = null;
                    });
                } else {
                    alert('Untuk memasang aplikasi LegalitasGo di HP/Komputer:\n\n1. Di Chrome: Klik menu titik 3 di kanan atas > "Tambahkan ke Layar Utama" atau "Pasang aplikasi".\n2. Di Safari iOS: Klik ikon Share > "Add to Home Screen".');
                }
            }
        });

        window.addEventListener('appinstalled', function() {
            console.log('[PWA] Successfully installed on user device');
            deferredPrompt = null;
        });
    </script>
</body>
</html>
