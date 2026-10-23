@extends('layouts.admin')
@section('title', 'Detail & Kwitansi Pembayaran')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
<li class="breadcrumb-item active">Detail & Kwitansi</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary rounded-3">
                <i class="bi bi-printer-fill"></i> Cetak Kwitansi
            </button>
        </div>

        <div class="content-card shadow-sm border p-4 p-md-5 bg-white rounded-4 print-area" id="kwitansiCard">
            <!-- Header Kwitansi -->
            <div class="row align-items-center pb-4 mb-4 border-bottom">
                <div class="col-sm-7">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-primary">LEGALITAS GO</h4>
                            <p class="text-muted small mb-0">Sistem Manajemen Perumahan & Legalitas</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                        BUKTI ANGSURAN #{{ str_pad($angsuran->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="small text-muted mt-1">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</div>
                </div>
            </div>

            <!-- Status Pembayaran Badge -->
            <div class="text-center my-3">
                <span class="badge bg-{{ $angsuran->status_color }} bg-opacity-10 text-{{ $angsuran->status_color }} fs-5 px-4 py-2 rounded-pill border border-{{ $angsuran->status_color }}">
                    STATUS: {{ strtoupper($angsuran->status_label) }}
                </span>
            </div>

            <!-- Detail Info -->
            <div class="row g-4 my-2">
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded-3">
                        <div class="text-muted small text-uppercase fw-semibold mb-2">Informasi Pembayar / Penghuni</div>
                        <h6 class="fw-bold mb-1">{{ $angsuran->penghuni->nama_lengkap ?? '-' }}</h6>
                        <div class="text-secondary small mb-1"><i class="bi bi-telephone me-1"></i> {{ $angsuran->penghuni->nomor_telepon ?? '-' }}</div>
                        <div class="text-secondary small mb-1"><i class="bi bi-house me-1"></i> {{ $angsuran->penghuni->perumahan->nama_perumahan ?? '-' }}</div>
                        <div class="text-secondary small"><i class="bi bi-geo-alt me-1"></i> Blok/Kav: <strong>{{ $angsuran->penghuni->blok_kavling ?? '-' }}</strong> (Tipe {{ $angsuran->penghuni->tipe_rumah ?? '-' }})</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded-3">
                        <div class="text-muted small text-uppercase fw-semibold mb-2">Rincian Angsuran</div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary small">Angsuran Ke:</span>
                            <span class="fw-bold text-dark">Ke-{{ $angsuran->angsuran_ke }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary small">Jatuh Tempo:</span>
                            <span class="fw-semibold text-dark">{{ $angsuran->tanggal_jatuh_tempo ? $angsuran->tanggal_jatuh_tempo->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary small">Tanggal Bayar:</span>
                            <span class="fw-semibold text-dark">{{ $angsuran->tanggal_bayar ? $angsuran->tanggal_bayar->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary small">Keterangan:</span>
                            <span class="text-dark small">{{ $angsuran->keterangan ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Tagihan -->
            <div class="table-responsive my-4">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Deskripsi Transaksi</th>
                            <th class="text-end" style="width: 200px;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>Tagihan Angsuran Rumah Bulan ini (Ke-{{ $angsuran->angsuran_ke }})</strong>
                                <br><small class="text-muted">Kavling {{ $angsuran->penghuni->blok_kavling ?? '-' }} - {{ $angsuran->penghuni->perumahan->nama_perumahan ?? '-' }}</small>
                            </td>
                            <td class="text-end fw-semibold">Rp {{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold">Total Pembayaran Diterima</td>
                            <td class="text-end fw-bold text-success fs-5">Rp {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</td>
                        </tr>
                        @php
                            $sisa = $angsuran->jumlah_angsuran - $angsuran->jumlah_bayar;
                        @endphp
                        @if($sisa > 0)
                        <tr>
                            <td class="text-end fw-bold text-danger">Kekurangan Tagihan</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Riwayat Kas Masuk Terkait -->
            @if($angsuran->kasMasuk)
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-clock-history me-1"></i> Riwayat Transaksi Kas</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle small">
                        <thead>
                            <tr class="table-light">
                                <th>Tanggal</th>
                                <th>Metode Bayar</th>
                                <th>No. Referensi</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $angsuran->kasMasuk->tanggal ? $angsuran->kasMasuk->tanggal->format('d/m/Y') : '-' }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($angsuran->kasMasuk->metode_bayar ?? 'Tunai') }}</span></td>
                                <td><code>{{ $angsuran->kasMasuk->no_referensi ?? '-' }}</code></td>
                                <td class="text-end fw-bold text-success">+ Rp {{ number_format($angsuran->kasMasuk->jumlah, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            <!-- Bukti Pembayaran / Struk Transfer -->
            @if($angsuran->bukti_bayar)
            <div class="p-3 my-4 rounded-3 border bg-light d-print-none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark"><i class="bi bi-paperclip text-primary me-1"></i> Lampiran Bukti Pembayaran / Struk Transfer</span>
                    <a href="{{ Storage::url($angsuran->bukti_bayar) }}" target="_blank" class="btn btn-sm btn-primary rounded-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka File Lengkap
                    </a>
                </div>
                @php
                    $ext = strtolower(pathinfo($angsuran->bukti_bayar, PATHINFO_EXTENSION));
                @endphp
                @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                    <div class="text-center mt-2">
                        <img src="{{ Storage::url($angsuran->bukti_bayar) }}" alt="Bukti Pembayaran" class="img-fluid rounded-3 border shadow-sm" style="max-height: 380px;">
                    </div>
                @elseif($ext === 'pdf')
                    <div class="d-flex align-items-center gap-2 p-3 bg-white rounded-3 border mt-2">
                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-2"></i>
                        <div>
                            <div class="fw-bold text-dark">Dokumen Bukti Transfer Pembayaran (Format PDF)</div>
                            <small class="text-muted">File dokumen PDF tersimpan aman di sistem penyimpanan pengelola.</small>
                        </div>
                    </div>
                @endif
            </div>
            @endif

            <!-- Tanda Tangan -->
            <div class="row pt-4 mt-4 border-top text-center">
                <div class="col-6">
                    <div class="small text-muted mb-5">Penyetor / Penghuni</div>
                    <div class="fw-bold text-decoration-underline">{{ $angsuran->penghuni->nama_lengkap ?? '(.........................)' }}</div>
                </div>
                <div class="col-6">
                    <div class="small text-muted mb-5">Petugas / Kasir Pengelola</div>
                    <div class="fw-bold text-decoration-underline">{{ auth()->user()->name ?? 'Administrator' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #kwitansiCard, #kwitansiCard * {
        visibility: visible;
    }
    #kwitansiCard {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
    }
    .btn {
        display: none !important;
    }
}
</style>
@endsection
