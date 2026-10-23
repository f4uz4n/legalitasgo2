<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Koneksi Terputus - LegalitasGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 20px;
        }
        .offline-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.2);
            border: 2px solid rgba(239, 68, 68, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: #f87171;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="icon-circle">
            <i class="bi bi-wifi-off"></i>
        </div>
        <h4 class="fw-bold mb-2">Sedang Mode Offline</h4>
        <p class="text-white-50 small mb-4">
            Perangkat Anda tidak terhubung ke jaringan internet. Aplikasi LegalitasGo telah terpasang di perangkat Anda dan akan kembali online secara otomatis saat koneksi tersedia.
        </p>
        <div class="d-flex flex-column gap-2">
            <button onclick="window.location.reload()" class="btn btn-primary fw-semibold py-2 rounded-3 shadow">
                <i class="bi bi-arrow-clockwise me-1"></i> Coba Muat Ulang
            </button>
            <a href="{{ route('public.report') }}" class="btn btn-outline-light btn-sm rounded-3 py-2">
                <i class="bi bi-house-door me-1"></i> Kembali ke Beranda Warga
            </a>
        </div>
        <div class="mt-4 pt-3 border-top border-white-10 text-white-50 small">
            &copy; {{ date('Y') }} LegalitasGo &bull; {{ $sharedPerumahan->nama_perumahan ?? 'Perumahan' }}
        </div>
    </div>
</body>
</html>
