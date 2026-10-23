@extends('layouts.admin')
@section('title', 'Download Laporan PDF')
@section('breadcrumb')
<li class="breadcrumb-item active">Download Laporan PDF</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Download Laporan Keuangan PDF
            </h4>
            <span class="badge bg-danger text-white px-2 py-1" style="font-size: 0.72rem;">CETAK</span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Konfigurasi laporan keuangan & administrasi perumahan siap cetak / unduh PDF.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('report-pdf.preview') }}" target="_blank" id="formReportPdf">
@csrf

<div class="row g-4">

    {{-- ─── Kolom Kiri: Konfigurasi Periode & Jenis Laporan ──────────────── --}}
    <div class="col-lg-6">

        {{-- Jenis Laporan --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-list-check text-primary me-2"></i>Jenis Konten Laporan
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    @php
                        $jenisOptions = [
                            'lengkap'   => ['label' => 'Laporan Lengkap', 'desc' => 'Semua seksi: keuangan, cicilan, legalitas, penghuni', 'icon' => 'bi-files', 'color' => 'primary'],
                            'keuangan'  => ['label' => 'Laporan Keuangan', 'desc' => 'Arus kas masuk & keluar, saldo, ringkasan bulan', 'icon' => 'bi-cash-coin', 'color' => 'success'],
                            'cicilan'   => ['label' => 'Laporan Cicilan', 'desc' => 'Rincian angsuran/cicilan warga yang terbayar', 'icon' => 'bi-calendar-check', 'color' => 'info'],
                            'penghuni'  => ['label' => 'Data Penghuni', 'desc' => 'Daftar penghuni dan status kepemilikan unit', 'icon' => 'bi-people-fill', 'color' => 'warning'],
                        ];
                    @endphp
                    @foreach($jenisOptions as $val => $opt)
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="jenis_laporan" id="jenis_{{ $val }}" value="{{ $val }}" {{ $val === 'lengkap' ? 'checked' : '' }}>
                        <label class="btn btn-outline-{{ $opt['color'] }} w-100 text-start p-3 rounded-3" for="jenis_{{ $val }}" style="height: auto;">
                            <i class="bi {{ $opt['icon'] }} fs-5 d-block mb-1"></i>
                            <span class="fw-bold d-block" style="font-size: 0.88rem;">{{ $opt['label'] }}</span>
                            <small class="text-muted" style="font-size: 0.75rem; white-space: normal;">{{ $opt['desc'] }}</small>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Periode Laporan --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-range text-primary me-2"></i>Periode Laporan
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                {{-- Shortcut Periode --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="text-muted small fw-semibold me-1">Shortcut:</span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="setPeriodeBulanIni()">Bulan Ini</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPeriodeBulanLalu()">Bulan Lalu</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPeriodeTahunIni()">Tahun Ini</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="clearPeriode()">Semua</button>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold small">Dari Tanggal</label>
                        <input type="date" name="periode_dari" id="periodeDari" class="form-control rounded-3" placeholder="Pilih tanggal awal">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small">Sampai Tanggal</label>
                        <input type="date" name="periode_sampai" id="periodeSampai" class="form-control rounded-3" placeholder="Pilih tanggal akhir">
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Kosongkan untuk menampilkan semua data.</small>
                </div>
            </div>
        </div>

        {{-- Kota & Tanggal Tanda Tangan --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>Tempat & Tanggal Tanda Tangan
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold small">Kota / Tempat</label>
                        <input type="text" name="kota_tanda_tangan" class="form-control rounded-3" value="{{ $perumahan->kota ?? 'Bogor' }}" placeholder="Contoh: Bogor">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small">Tanggal Tanda Tangan</label>
                        <input type="date" name="tanggal_tanda_tangan" class="form-control rounded-3" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ─── Kolom Kanan: Penandatangan ──────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pen-fill text-primary me-2"></i>Penandatangan Laporan
                </h6>
                <small class="text-muted">Nama akan tampil di halaman tanda tangan laporan PDF</small>
            </div>
            <div class="card-body px-4 py-3">

                {{-- Ketua --}}
                <div class="mb-4 p-3 rounded-3 border border-primary border-opacity-25" style="background:#eff6ff;">
                    <label class="form-label fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill px-2">Ketua</span> Nama Ketua Perumahan / Pengurus
                    </label>
                    <input type="text"
                           name="ketua"
                           id="inputKetua"
                           class="form-control rounded-3 fw-semibold"
                           placeholder="Contoh: Budi Santoso"
                           value="">
                    <small class="text-muted mt-1 d-block">Jabatan: Ketua Pengelola / Ketua RT</small>
                </div>

                {{-- Bendahara 1 --}}
                <div class="mb-4 p-3 rounded-3 border border-success border-opacity-25" style="background:#f0fdf4;">
                    <label class="form-label fw-bold text-success mb-2 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-pill px-2">Bendahara 1</span> Nama Bendahara Pertama
                    </label>
                    <input type="text"
                           name="bendahara_1"
                           id="inputBendahara1"
                           class="form-control rounded-3 fw-semibold"
                           placeholder="Contoh: Siti Rahayu"
                           value="">
                    <small class="text-muted mt-1 d-block">Jabatan: Bendahara I / Bendahara Utama</small>
                </div>

                {{-- Bendahara 2 --}}
                <div class="mb-4 p-3 rounded-3 border border-warning border-opacity-50" style="background:#fffbeb;">
                    <label class="form-label fw-bold text-warning mb-2 d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark rounded-pill px-2">Bendahara 2</span> Nama Bendahara Kedua
                    </label>
                    <input type="text"
                           name="bendahara_2"
                           id="inputBendahara2"
                           class="form-control rounded-3 fw-semibold"
                           placeholder="Contoh: Ahmad Fauzi"
                           value="">
                    <small class="text-muted mt-1 d-block">Jabatan: Bendahara II / Wakil Bendahara</small>
                </div>

                {{-- Anggota --}}
                <div class="mb-3 p-3 rounded-3 border" style="background:#fafafa;">
                    <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                        <span class="badge bg-secondary rounded-pill px-2">Anggota</span> Nama Anggota / Pengurus Lain
                    </label>
                    <textarea name="anggota"
                              id="inputAnggota"
                              class="form-control rounded-3"
                              rows="4"
                              placeholder="Tuliskan satu nama per baris, contoh:&#10;Hendra Gunawan&#10;Dewi Lestari&#10;Agus Prabowo"></textarea>
                    <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Satu nama per baris. Opsional.</small>
                </div>

                {{-- Preview Penandatangan --}}
                <div class="border rounded-3 p-3 bg-white mt-3" id="previewPenandatangan">
                    <div class="text-muted small fw-bold text-uppercase mb-2"><i class="bi bi-eye me-1"></i> Preview Blok Tanda Tangan</div>
                    <div class="row g-2 text-center" style="font-size: 0.82rem;">
                        <div class="col-4">
                            <div class="border-top pt-2 mt-5 fw-semibold" id="prevKetua" style="border-top: 1.5px solid #374151 !important;">...</div>
                            <div class="text-muted">Ketua</div>
                        </div>
                        <div class="col-4">
                            <div class="border-top pt-2 mt-5 fw-semibold" id="prevBend1" style="border-top: 1.5px solid #374151 !important;">...</div>
                            <div class="text-muted">Bendahara I</div>
                        </div>
                        <div class="col-4">
                            <div class="border-top pt-2 mt-5 fw-semibold" id="prevBend2" style="border-top: 1.5px solid #374151 !important;">...</div>
                            <div class="text-muted">Bendahara II</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ─── Tombol Submit ────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body px-4 py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
            <div>
                <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Siap Generate Laporan PDF</h6>
                <p class="text-muted small mb-0">Laporan akan terbuka di tab baru. Gunakan Ctrl+P / Cmd+P di browser untuk menyimpan sebagai PDF.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-3 px-4">
                    <i class="bi bi-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold shadow-sm" id="btnGenerate">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>Generate & Cetak PDF
                </button>
            </div>
        </div>
    </div>
</div>

</form>

@push('scripts')
<script>
    // Shortcut periode
    function setPeriodeBulanIni() {
        const now = new Date();
        const y = now.getFullYear(), m = String(now.getMonth()+1).padStart(2,'0');
        const lastDay = new Date(y, now.getMonth()+1, 0).getDate();
        document.getElementById('periodeDari').value = `${y}-${m}-01`;
        document.getElementById('periodeSampai').value = `${y}-${m}-${String(lastDay).padStart(2,'0')}`;
    }
    function setPeriodeBulanLalu() {
        const now = new Date();
        const last = new Date(now.getFullYear(), now.getMonth(), 0);
        const y = last.getFullYear(), m = String(last.getMonth()+1).padStart(2,'0');
        const lastDay = new Date(y, last.getMonth()+1, 0).getDate();
        document.getElementById('periodeDari').value = `${y}-${m}-01`;
        document.getElementById('periodeSampai').value = `${y}-${m}-${String(lastDay).padStart(2,'0')}`;
    }
    function setPeriodeTahunIni() {
        const y = new Date().getFullYear();
        document.getElementById('periodeDari').value = `${y}-01-01`;
        document.getElementById('periodeSampai').value = `${y}-12-31`;
    }
    function clearPeriode() {
        document.getElementById('periodeDari').value = '';
        document.getElementById('periodeSampai').value = '';
    }

    // Live preview penandatangan
    function syncPreview() {
        const k = document.getElementById('inputKetua').value.trim() || '...';
        const b1 = document.getElementById('inputBendahara1').value.trim() || '...';
        const b2 = document.getElementById('inputBendahara2').value.trim() || '...';
        document.getElementById('prevKetua').textContent = k;
        document.getElementById('prevBend1').textContent = b1;
        document.getElementById('prevBend2').textContent = b2;
    }
    ['inputKetua','inputBendahara1','inputBendahara2'].forEach(id => {
        document.getElementById(id).addEventListener('input', syncPreview);
    });

    // Default periode = bulan ini
    setPeriodeBulanIni();
</script>
@endpush

@endsection
