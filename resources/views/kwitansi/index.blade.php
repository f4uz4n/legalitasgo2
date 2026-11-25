@extends('layouts.admin')
@section('title', 'Pengambilan Kwitansi')
@section('breadcrumb')
<li class="breadcrumb-item active">Pengambilan Kwitansi</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pengambilan Kwitansi Pembayaran</h4>
        <p class="text-muted small mb-0">Catat saat pengangsur atau kuasanya mengambil kwitansi cicilan yang sudah dibayar.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card bg-white p-4">
            <h6 class="fw-bold mb-3">Catat Pengambilan</h6>
            @if($siapDiambil->isEmpty())
                <p class="text-muted small mb-0">Belum ada kwitansi yang menunggu diambil. Kwitansi muncul setelah pembayaran cicilan dicatat.</p>
            @else
            <form method="POST" action="{{ route('kwitansi.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="angsuran_id">Kwitansi</label>
                    <select name="angsuran_id" id="angsuran_id" class="form-select @error('angsuran_id') is-invalid @enderror" required>
                        <option value="">Pilih cicilan</option>
                        @foreach($siapDiambil as $a)
                            <option value="{{ $a->id }}"
                                data-nama="{{ $a->penghuni->nama_lengkap ?? '' }}"
                                data-hp="{{ $a->penghuni->no_hp ?? '' }}"
                                @selected((string) old('angsuran_id', $dipilih) === (string) $a->id)>
                                {{ $a->penghuni->blok_kavling ?? '-' }} · {{ $a->penghuni->nama_lengkap ?? '-' }} · ke-{{ $a->angsuran_ke }} · Rp {{ number_format($a->jumlah_bayar, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('angsuran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="tanggal_ambil">Tanggal diambil</label>
                    <input type="date" name="tanggal_ambil" id="tanggal_ambil" class="form-control @error('tanggal_ambil') is-invalid @enderror" value="{{ old('tanggal_ambil', date('Y-m-d')) }}" required>
                    @error('tanggal_ambil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="nama_pengambil">Nama pengambil</label>
                    <input type="text" name="nama_pengambil" id="nama_pengambil" class="form-control @error('nama_pengambil') is-invalid @enderror" value="{{ old('nama_pengambil') }}" required>
                    @error('nama_pengambil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="hubungan">Hubungan</label>
                    <select name="hubungan" id="hubungan" class="form-select" required>
                        @foreach(\App\Models\PengambilanKwitansi::HUBUNGAN as $kunci => $label)
                            <option value="{{ $kunci }}" @selected(old('hubungan', 'pemilik') === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="no_hp">No. HP</label>
                    <input type="text" name="no_hp" id="no_hp" class="form-control" value="{{ old('no_hp') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="keterangan">Catatan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Pengambilan</button>
            </form>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card bg-white p-4 mb-3">
            <form id="form-cari-kwitansi" action="{{ route('kwitansi.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1">Nama</label>
                    <input type="text" name="nama" class="form-control" placeholder="Ketik nama pengangsur atau pengambil" value="{{ request('nama') }}" autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Rumah / Blok</label>
                    <input type="text" name="rumah" class="form-control" placeholder="Ketik blok" value="{{ request('rumah') }}" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <a href="{{ route('kwitansi.index') }}" class="btn btn-light border">Reset</a>
                </div>
            </form>
        </div>

        <div class="content-card bg-white" id="hasil-kwitansi">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Blok</th>
                            <th>Pengangsur</th>
                            <th>Cicilan</th>
                            <th>Diambil oleh</th>
                            <th>Petugas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                        <tr>
                            <td>{{ $r->tanggal_ambil->format('d/m/Y') }}</td>
                            <td class="fw-bold">{{ $r->penghuni->blok_kavling ?? '-' }}</td>
                            <td>{{ $r->penghuni->nama_lengkap ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pembayaran.show', $r->angsuran_id) }}">Ke-{{ $r->angsuran->angsuran_ke ?? '-' }}</a>
                                <div class="small text-muted">Rp {{ number_format($r->angsuran->jumlah_bayar ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $r->nama_pengambil }}</div>
                                <div class="small text-muted">{{ $r->hubungan_label }}{{ $r->no_hp ? ' · '.$r->no_hp : '' }}</div>
                                @if($r->keterangan)
                                    <div class="small text-secondary">{{ $r->keterangan }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $r->petugas->name ?? '-' }}</td>
                            <td>
                                <form action="{{ route('kwitansi.destroy', $r) }}" method="POST" onsubmit="return confirm('Hapus catatan pengambilan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada pengambilan kwitansi yang dicatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3">
                {{ $riwayat->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>
@include('partials.cari-ajax', ['formId' => 'form-cari-kwitansi', 'hasilId' => 'hasil-kwitansi'])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pilih = document.getElementById('angsuran_id');
    const nama = document.getElementById('nama_pengambil');
    const hp = document.getElementById('no_hp');
    if (!pilih || !nama) return;
    function isi() {
        const opsi = pilih.options[pilih.selectedIndex];
        if (!opsi || !opsi.value) return;
        if (!nama.value) nama.value = opsi.getAttribute('data-nama') || '';
        if (hp && !hp.value) hp.value = opsi.getAttribute('data-hp') || '';
    }
    pilih.addEventListener('change', function () {
        const opsi = pilih.options[pilih.selectedIndex];
        nama.value = opsi ? (opsi.getAttribute('data-nama') || '') : '';
        if (hp) hp.value = opsi ? (opsi.getAttribute('data-hp') || '') : '';
    });
    isi();
});
</script>
@endpush
