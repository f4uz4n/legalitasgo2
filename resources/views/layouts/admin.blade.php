<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'LegalitasGo') }}</title>

    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LegalitasGo">
    <link rel="apple-touch-icon" href="/icon-192.png">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary: #1d4ed8;       /* Royal Blue */
            --primary-hover: #1e40af; /* Deep Royal */
            --primary-light: #eff6ff; /* Ice Blue */
            --primary-border: #bfdbfe;
            --accent-cyan: #0284c7;
            --dark: #0f172a;          /* Slate 900 (High Contrast) */
            --dark-muted: #334155;    /* Slate 700 */
            --gray-text: #475569;     /* Slate 600 */
            --border-color: #e2e8f0;  /* Slate 200 */
            --bg-body: #ffffff;       /* PURE WHITE */
            --bg-card: #ffffff;
            --bg-subtle: #f8fafc;
            --sidebar-width: 270px;
        }

        * { 
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-body);
            color: var(--dark);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-size: 0.93rem;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar Styling (Clean White & High-Contrast Blue) */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            height: 100dvh;
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1050;
            transition: all 0.25s ease-in-out;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-brand {
            padding: 1.25rem 1.4rem;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
            flex-shrink: 0;
        }

        .sidebar-brand h5 {
            color: var(--dark);
            font-weight: 800;
            margin: 0;
            font-size: 1.22rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand .brand-badge {
            background: #eff6ff;
            color: var(--primary);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid var(--primary-border);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-top: 5px;
        }

        .sidebar-nav {
            padding: 0.85rem 0.85rem;
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Slim elegant custom scrollbar */
        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        .sidebar-footer {
            flex-shrink: 0;
            padding: 0.85rem 1rem;
            border-top: 1px solid var(--border-color);
            background: #f8fafc;
        }

        .sidebar .nav-section {
            padding: 0.9rem 0.8rem 0.35rem;
            color: #64748b;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 700;
        }

        .sidebar .nav-link {
            color: var(--dark-muted);
            padding: 0.68rem 1rem;
            margin: 3px 0;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
            color: #64748b;
            transition: all 0.18s ease;
            width: 22px;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            background: #f1f5f9;
            color: var(--primary);
            border-color: #e2e8f0;
        }

        .sidebar .nav-link:hover i {
            color: var(--primary);
        }

        .sidebar .nav-link.active {
            background: var(--primary);
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(29, 78, 216, 0.35);
        }

        .sidebar .nav-link.active i {
            color: #ffffff !important;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease-in-out;
        }

        /* Top Navbar */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1040;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .breadcrumb {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #64748b;
        }

        /* Content Area */
        .content-area {
            padding: 2rem 2.2rem;
            flex-grow: 1;
            background: #ffffff;
        }

        /* Elegant App Footer */
        .app-footer {
            border-top: 1px solid var(--border-color);
            background: #f8fafc;
            padding: 1.15rem 2.2rem;
            font-size: 0.84rem;
            color: #64748b;
            margin-top: auto;
            flex-shrink: 0;
        }

        .app-footer a:hover {
            color: var(--primary) !important;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        /* Cards & Components */
        /* Cards & Components */
        .card-custom, .content-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .card-custom:hover, .content-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .content-card .card-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.4rem;
            font-weight: 700;
            color: var(--dark);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-card .card-body {
            padding: 1.4rem;
        }

        .stat-card-white {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.4rem;
            position: relative;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease;
        }

        .stat-card-white:hover {
            border-color: var(--primary-border);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(29, 78, 216, 0.08);
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        /* High-Contrast Colors */
        .icon-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .icon-green { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .icon-amber { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .icon-purple { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .icon-rose { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }

        .stat-number {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.6px;
            line-height: 1.2;
        }

        .stat-sublabel {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Form Controls */
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: 9px;
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
            transition: all 0.15s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        /* Buttons & Icon Alignment */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            font-weight: 600;
            line-height: 1.5;
            vertical-align: middle;
            border-radius: 10px;
            padding: 0.55rem 1.15rem;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-sm {
            padding: 0.38rem 0.75rem;
            font-size: 0.835rem;
            gap: 0.35rem;
            border-radius: 8px;
        }

        .btn-lg {
            padding: 0.75rem 1.45rem;
            font-size: 1.05rem;
            gap: 0.55rem;
            border-radius: 12px;
        }

        .btn i, .btn svg {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 1.05em;
            margin: 0 !important;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(29, 78, 216, 0.2);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(29, 78, 216, 0.35);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary-border);
            background: #ffffff;
        }

        .btn-outline-primary:hover {
            background-color: #eff6ff;
            color: var(--primary-hover);
            border-color: var(--primary);
        }

        .btn-light-blue {
            background: #eff6ff;
            color: var(--primary);
            border: 1px solid var(--primary-border);
        }

        .btn-light-blue:hover {
            background: #dbeafe;
            color: var(--primary-hover);
        }

        /* Action Icon Button Square Helper */
        .btn-icon, .btn-icon-sm {
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            aspect-ratio: 1 / 1;
        }
        .btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
        }
        .btn-icon-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        /* Tables */
        .table-custom, .table-modern {
            margin-bottom: 0;
            font-size: 0.9rem;
            width: 100%;
        }

        .table-custom thead th, .table-modern thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid var(--border-color);
            padding: 0.9rem 1.1rem;
            white-space: nowrap;
        }

        .table-custom tbody td, .table-modern tbody td {
            padding: 0.95rem 1.1rem;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid var(--border-color);
            background-color: #ffffff;
        }

        .table-custom tbody tr:hover td, .table-modern tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge-status {
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Clean Smooth Pagination */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            gap: 4px;
            margin: 0;
            align-items: center;
            flex-wrap: wrap;
        }

        .pagination .page-item .page-link {
            border-radius: 8px !important;
            border: 1px solid var(--border-color);
            color: var(--dark);
            font-weight: 600;
            padding: 0.42rem 0.8rem;
            font-size: 0.84rem;
            min-width: 34px;
            text-align: center;
            background: #ffffff;
            transition: all 0.15s ease;
        }

        .pagination .page-item .page-link:hover {
            background: #eff6ff;
            color: var(--primary);
            border-color: var(--primary-border);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(29, 78, 216, 0.35);
        }

        .pagination .page-item.disabled .page-link {
            background-color: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
        }

        .pagination svg {
            width: 1rem;
            height: 1rem;
        }

        /* Timeline styling */
        .timeline {
            list-style: none;
            padding-left: 0;
            position: relative;
            margin: 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 15px;
            bottom: 15px;
            left: 20px;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1.75rem;
            padding-left: 50px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-icon {
            position: absolute;
            left: 6px;
            top: 2px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.85rem;
            box-shadow: 0 0 0 4px #ffffff, 0 2px 6px rgba(0,0,0,0.1);
            z-index: 2;
        }
        .timeline-content {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        .timeline-date {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            }
            .main-content {
                margin-left: 0;
            }
        }

        /* Custom Select2 Styling Smooth Blue/White */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.95rem;
            min-height: 42px;
            padding: 0.375rem 0.75rem;
            background-color: #ffffff;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15);
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
            overflow: hidden;
            z-index: 1065;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.45rem 0.75rem;
            font-size: 0.9rem;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field:focus {
            border-color: #1d4ed8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(29, 78, 216, 0.15);
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: #1d4ed8;
            color: #ffffff;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h5>
                <div class="icon-blue p-2 rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-houses-fill fs-5"></i>
                </div>
                <span>LegalitasGo</span>
            </h5>
            <div class="brand-badge text-truncate" title="{{ $sharedPerumahan->nama_perumahan ?? 'Perumahan' }}">
                @if(Auth::check() && Auth::user()->isWarga())
                    <i class="bi bi-house-door-fill me-1"></i> Blok {{ Auth::user()->penghuni->blok_kavling ?? 'Warga' }} &bull; {{ $sharedPerumahan->nama_perumahan ?? 'Perumahan' }}
                @else
                    <i class="bi bi-geo-alt-fill me-1"></i> {{ $sharedPerumahan->nama_perumahan ?? 'Perumahan' }}
                @endif
            </div>
        </div>

        <nav class="sidebar-nav">
            @if(Auth::check() && Auth::user()->isWarga())
                <div class="nav-section">Portal Warga</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill"></i> Dashboard Unit Saya
                </a>
                <a href="{{ route('pembayaran.index') }}" class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i> Riwayat Cicilan Saya
                </a>

                <div class="nav-section">Transparansi Perumahan</div>
                <a href="{{ route('public.report') }}" class="nav-link text-primary fw-bold" target="_blank">
                    <i class="bi bi-broadcast-pin text-primary"></i> Laporan Live Warga
                    <span class="badge bg-primary text-white ms-auto" style="font-size: 0.65rem;">LIVE</span>
                </a>

                <div class="nav-section">Akun Pengguna</div>
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i> Ubah Password & Profil
                </a>

                <div class="nav-section">Aplikasi PWA</div>
                <a href="javascript:void(0)" class="nav-link text-success fw-semibold btn-trigger-pwa-install">
                    <i class="bi bi-phone-fill text-success"></i> Pasang Aplikasi di HP
                    <span class="badge bg-success text-white ms-auto" style="font-size: 0.65rem;">INSTALL</span>
                </a>
            @else
                <div class="nav-section">Ringkasan Utama</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard Finansial
                </a>

                <div class="nav-section">Collecting & Warga</div>
                <a href="{{ route('pembayaran.index') }}" class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-2-front-fill"></i> Cicilan Angsuran
                </a>
                <a href="{{ route('penghuni.index') }}" class="nav-link {{ request()->routeIs('penghuni.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Data Warga / Kavling
                </a>

                <div class="nav-section">Arus Kas & Pengeluaran</div>
                <a href="{{ route('kas.index') }}" class="nav-link {{ request()->routeIs('kas.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i> Kas Masuk & Keluar
                </a>

                <div class="nav-section">Sertifikat &amp; Dokumen</div>
                <a href="{{ route('legalitas.index') }}" class="nav-link {{ request()->routeIs('legalitas.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-ruled-fill"></i> Legalitas Sertifikat
                </a>
                <a href="{{ route('arsip.index') }}" class="nav-link {{ request()->routeIs('arsip.*') ? 'active' : '' }}">
                    <i class="bi bi-archive-fill"></i> Kearsipan Dokumen
                    <span class="badge bg-light text-primary border ms-auto" style="font-size: 0.65rem;">ARSIP</span>
                </a>

                <div class="nav-section">Audit & Transparansi</div>
                <a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Log Aktivitas Admin
                    <span class="badge bg-secondary text-white ms-auto" style="font-size: 0.65rem;">AUDIT</span>
                </a>
                <a href="{{ route('public.report') }}" class="nav-link text-primary fw-bold" target="_blank">
                    <i class="bi bi-broadcast-pin text-primary"></i> Laporan Live Warga
                    <span class="badge bg-primary text-white ms-auto" style="font-size: 0.65rem;">LIVE</span>
                </a>

                <div class="nav-section">Pengaturan &amp; Sistem</div>
                <a href="{{ route('perumahan.index') }}" class="nav-link {{ request()->routeIs('perumahan.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-wide-connected"></i> Profil Perumahan
                </a>
                <a href="{{ route('system.index') }}" class="nav-link {{ request()->routeIs('system.*') ? 'active' : '' }}">
                    <i class="bi bi-database-gear"></i> Backup &amp; Reset Data
                    <span class="badge bg-light text-primary border ms-auto" style="font-size: 0.65rem;">SISTEM</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> Akun Pengelola
                </a>

                <div class="nav-section">Aplikasi PWA</div>
                <a href="javascript:void(0)" class="nav-link text-success fw-semibold btn-trigger-pwa-install">
                    <i class="bi bi-phone-fill text-success"></i> Pasang Aplikasi di HP
                    <span class="badge bg-success text-white ms-auto" style="font-size: 0.65rem;">INSTALL</span>
                </a>
            @endif
        </nav>

        <!-- Sidebar Fixed Bottom Footer -->
        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden text-truncate">
                        <div class="fw-bold text-dark small text-truncate" style="line-height: 1.2;">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">
                            {{ Auth::user()->isWarga() ? 'Warga Kavling' : 'Administrator' }}
                        </div>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-3 text-start px-2 py-1.5 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.8rem;">
                    <i class="bi bi-box-arrow-left"></i> Logout {{ Auth::user()->isWarga() ? 'Warga' : 'Pengelola' }}
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-3" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $sharedPerumahan->nama_perumahan ?? 'Dashboard' }}</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <!-- Action Shortcut Buttons Pengelola -->
                    <a href="{{ route('pembayaran.create') }}" class="btn btn-sm btn-primary rounded-3 d-none d-sm-inline-flex align-items-center gap-1">
                        <i class="bi bi-plus-circle"></i> Catat Cicilan
                    </a>
                    <a href="{{ route('kas.index') }}" class="btn btn-sm btn-light-blue rounded-3 d-none d-md-inline-flex align-items-center gap-1">
                        <i class="bi bi-dash-circle"></i> Catat Pengeluaran
                    </a>
                @endif
                <a href="{{ route('public.report') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-globe2"></i> <span class="d-none d-md-inline">Portal Warga</span>
                </a>

                <!-- PWA Install Button Top Navbar -->
                <button type="button" class="btn btn-sm btn-outline-success rounded-3 d-inline-flex align-items-center gap-1 btn-trigger-pwa-install shadow-sm" title="Pasang LegalitasGo di HP atau Laptop">
                    <i class="bi bi-phone"></i> <span class="d-none d-sm-inline">Pasang App</span>
                </button>

                <div class="border-start ps-3 ms-2 text-end d-none d-sm-block">
                    <div class="fw-bold small text-dark">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                    @if(Auth::check() && Auth::user()->isWarga())
                        <div class="text-primary fw-semibold" style="font-size: 0.72rem;">Warga Blok {{ Auth::user()->penghuni->blok_kavling ?? '-' }}</div>
                    @else
                        <div class="text-muted" style="font-size: 0.72rem;">Pengelola Perumahan</div>
                    @endif
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                        <strong>{{ session('success') }}</strong>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: #fff1f2; color: #9f1239; border-left: 4px solid #ef4444 !important;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                        <strong>{{ session('error') }}</strong>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: #fff1f2; color: #9f1239; border-left: 4px solid #ef4444 !important;">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                    <strong>Periksa kembali data yang dimasukkan:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Elegant App Footer -->
        <footer class="app-footer">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <div class="icon-blue p-1 rounded-2 d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                        <i class="bi bi-houses-fill" style="font-size: 0.85rem;"></i>
                    </div>
                    <span>&copy; {{ date('Y') }} <strong class="text-dark">LegalitasGo</strong> &bull; Sistem Manajemen Properti & Transparansi Angsuran</span>
                </div>
                <div class="d-flex align-items-center gap-3 small flex-wrap justify-content-center">
                    <span class="badge bg-white text-primary border px-2.5 py-1.5 shadow-sm">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $sharedPerumahan->nama_perumahan ?? 'Perumahan' }}
                    </span>
                    <a href="{{ route('public.report') }}" target="_blank" class="text-secondary text-decoration-none hover-primary fw-semibold">
                        <i class="bi bi-globe2 me-1"></i> Portal Warga Live
                    </a>
                    <span class="text-muted">&bull;</span>
                    <span class="text-success fw-semibold d-inline-flex align-items-center gap-1.5">
                        <span class="badge-dot bg-success"></span> Sistem Online
                    </span>
                </div>
            </div>
        </footer>
    </div>

    <!-- jQuery & Bootstrap 5 Bundle JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        $(document).ready(function() {
            // Inisialisasi otomatis semua elemen .select2
            $('.select2').each(function() {
                var $this = $(this);
                var modalParent = $this.closest('.modal');
                $this.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: modalParent.length ? modalParent : $(document.body),
                    placeholder: $this.attr('placeholder') || $this.data('placeholder') || '-- Silakan Pilih --',
                    allowClear: Boolean($this.data('allow-clear'))
                });
            });
        });
    </script>

    <!-- PWA Service Worker & Install Script -->
    <script>
        let deferredPrompt = null;

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(reg) {
                        console.log('[PWA] Service Worker registered with scope:', reg.scope);
                    })
                    .catch(function(err) {
                        console.warn('[PWA] Service Worker registration failed:', err);
                    });
            });
        }

        // Capture BeforeInstallPrompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA] beforeinstallprompt event captured');
            $('.btn-trigger-pwa-install').addClass('pulse-install');
        });

        // Trigger PWA Installation
        $(document).on('click', '.btn-trigger-pwa-install', function(e) {
            e.preventDefault();
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA] User accepted the install prompt');
                    } else {
                        console.log('[PWA] User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            } else {
                // If browser doesn't support or already installed
                alert('Untuk memasang aplikasi LegalitasGo di HP/Desktop:\n\n1. Di Google Chrome: Klik ikon titik tiga di kanan atas > pilih "Pasang aplikasi" atau "Tambahkan ke Layar Utama".\n2. Di Safari iOS: Klik tombol Share > pilih "Add to Home Screen".');
            }
        });

        window.addEventListener('appinstalled', function() {
            console.log('[PWA] LegalitasGo was successfully installed!');
            deferredPrompt = null;
        });
    </script>

    @stack('scripts')
</body>
</html>
