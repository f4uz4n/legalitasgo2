<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Timeline Pemberkasan Legalitas - {{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1d4ed8;
            --primary-hover: #1e40af;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;
            --dark: #0f172a;
            --border-color: #e2e8f0;
            --bg-body: #ffffff;
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

        .public-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 1.1rem 0;
            position: sticky;
            top: 0;
            z-index: 1040;
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
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <header class="public-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('public.report') }}" class="btn btn-outline-secondary btn-sm rounded-3 me-2">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard Publik
                </a>
                <div class="p-2 rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-file-earmark-ruled-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Timeline Pemberkasan Legalitas</h5>
                    <small class="text-muted" style="font-size: 0.75rem;">{{ $perumahan->nama_perumahan ?? 'Griya Harmoni Residence' }}</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-primary border px-3 py-2 small">
                    <i class="bi bi-clock-history me-1"></i> Live Update
                </span>
            </div>
        </div>
    </header>

    <div class="container py-4 my-2">
        <div class="card-custom p-4 p-md-5 bg-white" style="border: 2px solid #bfdbfe;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <span class="badge bg-primary text-white px-3 py-1 mb-2">TAB DEDIKASI TIMELINE</span>
                    <h3 class="fw-bold mb-1 text-dark">Perjalanan Pengurusan Berkas & Sertifikat</h3>
                    <p class="text-muted small mb-0">Semua milestone tahapan berkas (Bapenda Pajak, BPN, Notaris/PPAT) tercatat kronologis berdasarkan input pengelola.</p>
                </div>
                <div>
                    <input type="text" id="timelineSearch" class="form-control border-primary rounded-3 px-3 py-2" placeholder="Cari tahapan berkas (Bapenda, BPN)..." style="min-width: 260px;">
                </div>
            </div>

            <!-- Timeline List -->
            <div class="public-timeline">
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
                                <i class="bi bi-paperclip me-1"></i> Lihat Dokumen / Tanda Terima
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('timelineSearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.timeline-item-filter').forEach(item => {
                const text = item.getAttribute('data-title');
                item.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
