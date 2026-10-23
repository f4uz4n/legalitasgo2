@extends('layouts.admin')
@section('title', 'Catat Pembayaran Cicilan')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Cicilan Angsuran</a></li>
<li class="breadcrumb-item active">Catat Pembayaran</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom bg-white p-4 p-md-5">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                <div class="p-2 rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-cash-coin fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Form Pencatatan Pembayaran Cicilan</h5>
                    <small class="text-muted">Pencatatan pembayaran angsuran warga akan otomatis masuk ke kas penerimaan.</small>
                </div>
            </div>

            @if($angsuran)
            <!-- Tagihan Information Box -->
            <div class="p-3 mb-4 rounded-3 border" style="background-color: #eff6ff; border-color: #bfdbfe !important;">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="text-uppercase small fw-bold text-primary mb-1">Kavling & Pembayar</div>
                        <div class="fw-bold text-dark fs-6">{{ $angsuran->penghuni->nama_lengkap }}</div>
                        <div class="text-secondary small">Blok Kavling: <strong>{{ $angsuran->penghuni->blok_kavling }}</strong> &bull; Tipe {{ $angsuran->penghuni->tipe_rumah ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-uppercase small fw-bold text-muted mb-1">Tagihan Angsuran Ke-{{ $angsuran->angsuran_ke }}</div>
                        <div class="fw-bold text-primary fs-5">Rp {{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}</div>
                        <div class="small {{ $angsuran->tanggal_jatuh_tempo->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                            Jatuh Tempo: {{ $angsuran->tanggal_jatuh_tempo->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($angsuran)
                    <input type="hidden" name="angsuran_id" value="{{ $angsuran->id }}">
                @else
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Warga & Jadwal Angsuran <span class="text-danger">*</span></label>
                        <select name="angsuran_id" class="form-select select2" id="selectAngsuran" required data-placeholder="-- Cari Nama Warga atau Blok Kavling --">
                            <option value="">-- Cari Nama Warga atau Blok Kavling --</option>
                            @foreach($penghuniList as $p)
                                @foreach($p->angsuran as $a)
                                <option value="{{ $a->id }}" data-nominal="{{ $a->jumlah_angsuran }}" {{ old('angsuran_id') == $a->id ? 'selected' : '' }}>
                                    Kavling {{ $p->blok_kavling }} - {{ $p->nama_lengkap }} (Angsuran Ke-{{ $a->angsuran_ke }} - Rp {{ number_format($a->jumlah_angsuran, 0, ',', '.') }})
                                </option>
                                @endforeach
                            @endforeach
                        </select>
                        <small class="text-muted"><i class="bi bi-search me-1"></i> Ketik nama warga atau blok kavling untuk pencarian cepat.</small>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jumlah Uang Dibayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_bayar" id="inputJumlahBayar" class="form-control" value="{{ old('jumlah_bayar', $angsuran->jumlah_angsuran ?? '') }}" required placeholder="Contoh: 2500000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_bayar" class="form-control" value="{{ old('tanggal_bayar', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_bayar" class="form-select" required>
                            <option value="Transfer Bank" selected>Transfer Bank (BCA/Mandiri/BRI)</option>
                            <option value="Tunai">Tunai Kasir / Kantor</option>
                            <option value="QRIS">QRIS / E-Wallet</option>
                            <option value="Cek / Giro">Cek / Bilyet Giro</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor Referensi / Bukti Transfer</label>
                        <input type="text" name="no_referensi" class="form-control" value="{{ old('no_referensi') }}" placeholder="Contoh: TRX-20261022-001">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i> Upload Lampiran Bukti Pembayaran / Struk Transfer (Opsional)
                        </label>
                        <input type="file" name="bukti_bayar" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format: JPG, PNG, atau PDF (Maksimal 5 MB). Struk ATM, tangkapan layar m-banking, atau foto kuitansi.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Catatan / Keterangan Pembayaran (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan pembayaran cicilan...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-light border px-3">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle"></i> Simpan Pembayaran Cicilan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#selectAngsuran').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Cari Nama Warga atau Blok Kavling --',
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.nominal) {
                $('#inputJumlahBayar').val(selectedOpt.dataset.nominal);
            }
        });
    });
</script>
@endpush
