@extends('layouts.admin')
@section('title', 'Backup & Reset Data')
@section('breadcrumb')
<li class="breadcrumb-item active">Backup &amp; Reset Data</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="bi bi-database-gear text-primary me-2"></i>Backup &amp; Reset Data Sistem
            </h4>
            <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">SISTEM</span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Cadangkan basis data secara berkala atau atur ulang data operasional perumahan sesuai kebutuhan pengujian maupun siklus baru.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm mb-4" role="alert">
    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
    <div class="small fw-semibold">{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
    <div class="small fw-semibold">{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
    <div class="fw-bold small mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Terjadi Kesalahan Validasi:</div>
    <ul class="mb-0 small ps-3">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Metrik Data Aktif -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="content-card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase mb-1">
                <span>Warga &amp; Kavling</span>
                <i class="bi bi-people text-primary"></i>
            </div>
            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['penghuni'], 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Data penghuni aktif</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="content-card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase mb-1">
                <span>Catatan Angsuran</span>
                <i class="bi bi-credit-card text-success"></i>
            </div>
            <div class="fs-4 fw-bold text-success">{{ number_format($stats['angsuran'], 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Jadwal &amp; pembayaran cicilan</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="content-card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase mb-1">
                <span>Transaksi Kas</span>
                <i class="bi bi-cash-coin text-warning"></i>
            </div>
            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['kas_masuk'] + $stats['kas_keluar'], 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">{{ $stats['kas_masuk'] }} masuk &bull; {{ $stats['kas_keluar'] }} keluar</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="content-card p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center text-muted small fw-bold text-uppercase mb-1">
                <span>Legalitas &amp; Timeline</span>
                <i class="bi bi-file-earmark-check text-info"></i>
            </div>
            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['legalitas'], 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">{{ $stats['timeline'] }} rekaman progres sertifikat</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom 1: Backup Data Database -->
    <div class="col-lg-6">
        <div class="content-card bg-white h-100">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="icon-blue p-2 rounded-2 d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                        <i class="bi bi-cloud-arrow-down-fill text-primary"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Backup Database (SQL)</h6>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aman &amp; Cepat</span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    Fitur ini mengekspor seluruh struktur dan data tabel dalam database (warga, angsuran, buku kas, sertifikat, log aktivitas) menjadi file <code>.sql</code> standar yang dapat diunduh kapan saja atau di-restore kembali via phpMyAdmin/MySQL.
                </p>

                <div class="alert alert-light border d-flex gap-2 p-3 rounded-3 mb-4">
                    <i class="bi bi-info-circle text-primary fs-5 mt-0.5"></i>
                    <div class="small text-secondary">
                        File backup disimpan secara terlindungi di folder <code>storage/app/backups/</code> dan hanya dapat diakses oleh administrator pengelola.
                    </div>
                </div>

                <form action="{{ route('system.backup') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Ketik kata konfirmasi <code class="text-primary fw-bold">BACKUP</code> di bawah ini:
                        </label>
                        <input type="text" name="konfirmasi_backup" class="form-control form-control-sm" placeholder="Ketik: BACKUP" required autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm">
                        <i class="bi bi-download me-1"></i> Buat &amp; Simpan Backup Database Baru
                    </button>
                </form>

                <h6 class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                    <i class="bi bi-clock-history text-secondary"></i> Riwayat Arsip File Backup
                </h6>
                <div class="table-responsive border rounded-3" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Nama File</th>
                                <th style="width: 80px;">Ukuran</th>
                                <th style="width: 130px;">Waktu Dibuat</th>
                                <th style="width: 90px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backupFiles as $backup)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-truncate text-dark" style="max-width: 170px;" title="{{ $backup['name'] }}">
                                        <i class="bi bi-filetype-sql text-primary me-1"></i> {{ $backup['name'] }}
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-secondary border">{{ $backup['size'] }}</span></td>
                                <td class="text-muted small">{{ $backup['created'] }}</td>
                                <td class="text-center">
                                    <a href="{{ route('system.backup.download', $backup['name']) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Unduh File SQL">
                                        <i class="bi bi-download"></i> Unduh
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-archive text-muted fs-4 d-block mb-1"></i>
                                    Belum ada file backup yang dibuat. Silakan buat backup pertama Anda di atas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom 2: Reset Data (Danger Zone) -->
    <div class="col-lg-6">
        <div class="content-card bg-white h-100 border-danger-subtle">
            <div class="card-header bg-white py-3 px-4 border-bottom border-danger-subtle d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-2 d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger" style="width:34px;height:34px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-danger">Reset Data Transaksi (Danger Zone)</h6>
                </div>
                <span class="badge bg-danger text-white px-2 py-1">Tindakan Berbahaya</span>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-danger bg-danger-subtle border-danger-subtle d-flex gap-2 p-3 rounded-3 mb-3">
                    <i class="bi bi-shield-slash-fill text-danger fs-5 mt-0.5"></i>
                    <div class="small text-danger-emphasis">
                        <strong>PERHATIAN PENTING:</strong> Tindakan reset akan menghapus atau mengosongkan data transaksi yang Anda pilih secara permanen. Pastikan Anda telah mengunduh <strong>Backup Database</strong> di sebelah kiri sebelum melakukan tindakan ini!
                    </div>
                </div>

                <form id="formResetData" action="{{ route('system.reset') }}" method="POST">
                    @csrf
                    <label class="form-label small fw-bold text-dark mb-2">Pilih Data yang Ingin Direset / Dikosongkan:</label>

                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_reset[]" value="angsuran" id="reset_angsuran">
                            <label class="form-check-label small fw-semibold text-dark" for="reset_angsuran">
                                <i class="bi bi-credit-card-2-front text-primary me-1"></i> Reset Status Cicilan Angsuran
                                <span class="d-block text-muted" style="font-size: 0.75rem;">Mengembalikan status cicilan seluruh warga ke belum bayar &amp; mengosongkan riwayat pembayaran tanpa menghapus kavling warga.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_reset[]" value="kas_masuk" id="reset_kas_masuk">
                            <label class="form-check-label small fw-semibold text-dark" for="reset_kas_masuk">
                                <i class="bi bi-cash-stack text-success me-1"></i> Kosongkan Seluruh Kas Masuk
                                <span class="d-block text-muted" style="font-size: 0.75rem;">Menghapus seluruh rekaman arus kas masuk perumahan.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_reset[]" value="kas_keluar" id="reset_kas_keluar">
                            <label class="form-check-label small fw-semibold text-dark" for="reset_kas_keluar">
                                <i class="bi bi-receipt text-danger me-1"></i> Kosongkan Seluruh Kas Keluar (Biaya Operasional)
                                <span class="d-block text-muted" style="font-size: 0.75rem;">Menghapus seluruh catatan pengeluaran, nota, dan biaya proyek.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_reset[]" value="timeline" id="reset_timeline">
                            <label class="form-check-label small fw-semibold text-dark" for="reset_timeline">
                                <i class="bi bi-clock-history text-info me-1"></i> Kosongkan Riwayat Timeline Legalitas
                                <span class="d-block text-muted" style="font-size: 0.75rem;">Menghapus riwayat perkembangan timeline sertifikat tanpa menghapus data induk legalitas.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="target_reset[]" value="legalitas" id="reset_legalitas">
                            <label class="form-check-label small fw-semibold text-dark" for="reset_legalitas">
                                <i class="bi bi-file-earmark-x text-warning me-1"></i> Hapus Seluruh Dokumen Legalitas &amp; Sertifikat
                                <span class="d-block text-muted" style="font-size: 0.75rem;">Menghapus seluruh berkas legalitas dan riwayat pengurusan sertifikat warga.</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="target_reset[]" value="penghuni" id="reset_penghuni">
                            <label class="form-check-label small fw-semibold text-danger" for="reset_penghuni">
                                <i class="bi bi-person-x-fill me-1"></i> Hapus Seluruh Data Warga / Kavling (Total Data Bersih)
                                <span class="d-block text-muted" style="font-size: 0.75rem;">Menghapus data warga, angsuran, buku kas, dan sertifikat yang terkait. Akun admin dan profil perumahan tetap aman.</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Ketik kalimat konfirmasi pengamanan:
                            <code class="text-danger fw-bold user-select-all">HAPUS SEMUA DATA</code>
                        </label>
                        <input type="text" name="konfirmasi_reset" id="inputKonfirmasiReset" class="form-control form-control-sm border-danger-subtle" placeholder="Ketik persis: HAPUS SEMUA DATA" required autocomplete="off">
                    </div>

                    <button type="button" class="btn btn-danger btn-sm w-100 fw-bold py-2 shadow-sm" onclick="showResetConfirmModal()">
                        <i class="bi bi-trash3-fill me-1"></i> Eksekusi Reset Data Terpilih
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Ganda Reset -->
<div class="modal fade" id="modalResetConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Terakhir Reset Data
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="text-danger fs-1 mb-2">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Apakah Anda Sangat Yakin?</h5>
                <p class="text-muted small mb-0">
                    Data yang sudah direset tidak dapat dikembalikan secara otomatis kecuali Anda merestore-nya dari file backup database yang sudah diunduh sebelumnya.
                </p>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batalkan</button>
                <button type="button" class="btn btn-danger btn-sm px-4 fw-bold" onclick="document.getElementById('formResetData').submit();">
                    Ya, Reset Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showResetConfirmModal() {
    const checkboxes = document.querySelectorAll('input[name="target_reset[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Silakan centang minimal satu jenis data yang ingin direset.');
        return;
    }

    const konfirmasi = document.getElementById('inputKonfirmasiReset').value.trim();
    if (konfirmasi !== 'HAPUS SEMUA DATA') {
        alert('Kalimat konfirmasi belum sesuai. Silakan ketik persis: HAPUS SEMUA DATA');
        document.getElementById('inputKonfirmasiReset').focus();
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('modalResetConfirm'));
    modal.show();
}
</script>
@endpush
