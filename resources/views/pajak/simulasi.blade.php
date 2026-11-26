@extends('layouts.admin')
@section('title', 'Simulasi BPHTB')
@section('breadcrumb')
<li class="breadcrumb-item active">Simulasi BPHTB</li>
@endsection

@section('content')
@php
    $rupiahInput = function ($nilai) {
        return number_format((int) round((float) $nilai), 0, ',', '.');
    };
    $nilaiJual = $rupiahInput(old('nilai_jual', $ubah->nilai_jual ?? 0));
    $namaPenjual = old('nama_penjual', $ubah->nama_penjual ?? '');
    $tampilPersen = function ($nilai) {
        $nilai = str_replace(',', '.', (string) $nilai);
        if (!is_numeric($nilai)) {
            return $nilai;
        }
        return rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.');
    };
    $nilaiPersen = $tampilPersen(old('persentase', $ubah->persentase ?? 5));
    $nilaiPersenPembeli = $tampilPersen(old('persentase_pembeli', $ubah->persentase_pembeli ?? 5));
    $potongan = \App\Models\SimulasiBphtb::POTONGAN_PEMBELI;
@endphp
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Simulasi Pajak Jual Beli / BPHTB</h4>
        <p class="text-muted small mb-0">Tarif pajak penjual dan tarif pajak pembeli dipilih masing-masing. Pajak pembeli memakai harga jual dikurangi Rp {{ number_format($potongan, 0, ',', '.') }}.</p>
    </div>
</div>

<form method="POST" action="{{ $ubah ? route('pajak.simulasi.update', $ubah) : route('pajak.simulasi.store') }}">
    @csrf
    @if($ubah) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="content-card bg-white p-4">
                <h6 class="fw-bold mb-3">{{ $ubah ? 'Ubah perhitungan' : 'Data perhitungan' }}</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="nama-penjual">Nama penjual</label>
                    <input type="text" name="nama_penjual" id="nama-penjual" class="form-control @error('nama_penjual') is-invalid @enderror" value="{{ $namaPenjual }}" maxlength="255" required>
                    @error('nama_penjual')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="nilai-jual">Harga jual</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="nilai_jual" id="nilai-jual" class="form-control input-rupiah @error('nilai_jual') is-invalid @enderror" inputmode="numeric" autocomplete="off" value="{{ $nilaiJual }}" required>
                    </div>
                    @error('nilai_jual')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="persentase">Tarif pajak penjual</label>
                    <div class="input-group" style="max-width: 220px;">
                        <input type="number" name="persentase" id="persentase" class="form-control @error('persentase') is-invalid @enderror" min="0" max="100" step="0.01" value="{{ $nilaiPersen }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('persentase')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-sasaran="persentase" data-persen="5">5%</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-sasaran="persentase" data-persen="2.5">2,5%</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-sasaran="persentase" data-persen="1">1%</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="persentase-pembeli">Tarif pajak pembeli</label>
                    <div class="input-group" style="max-width: 220px;">
                        <input type="number" name="persentase_pembeli" id="persentase-pembeli" class="form-control @error('persentase_pembeli') is-invalid @enderror" min="0" max="100" step="0.01" value="{{ $nilaiPersenPembeli }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('persentase_pembeli')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-sasaran="persentase-pembeli" data-persen="5">5%</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-sasaran="persentase-pembeli" data-persen="2.5">2,5%</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-sasaran="persentase-pembeli" data-persen="1">1%</button>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ $ubah ? 'Perbarui' : 'Simpan ke tabel' }}
                    </button>
                    @if($ubah)
                    <a href="{{ route('pajak.simulasi') }}" class="btn btn-light border">Batal</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="content-card bg-white p-4 h-100">
                <h6 class="fw-bold mb-3">Hasil simulasi</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">Pajak penjual</div>
                            <div class="fw-bold fs-5" id="hasil-penjual">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">Pajak pembeli</div>
                            <div class="fw-bold fs-5" id="hasil-pembeli">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-primary bg-opacity-10">
                            <div class="text-primary small fw-bold">Total</div>
                            <div class="fw-bold fs-5 text-primary" id="hasil-total">Rp 0</div>
                        </div>
                    </div>
                </div>
                <p class="small text-muted mt-3 mb-1" id="rumus-penjual">Pajak penjual = harga jual × tarif.</p>
                <p class="small text-muted mb-0" id="rumus-pembeli">Pajak pembeli = (harga jual − Rp {{ number_format($potongan, 0, ',', '.') }}) × tarif.</p>
            </div>
        </div>
    </div>
</form>

<div class="content-card bg-white mt-4">
    <div class="px-4 pt-4 pb-2">
        <h6 class="fw-bold mb-1">Tabel hasil perhitungan</h6>
        <p class="text-muted small mb-0">{{ $daftar->count() }} perhitungan tersimpan.</p>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama penjual</th>
                    <th class="text-end">Pajak penjual</th>
                    <th class="text-end">Pajak pembeli</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $i => $baris)
                @php $totalBaris = (float) $baris->pajak_penjual + (float) $baris->pajak_pembeli; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $baris->nama_penjual ?: '—' }}</td>
                    <td class="text-end">Rp {{ number_format($baris->pajak_penjual, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($baris->pajak_pembeli, 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($totalBaris, 0, ',', '.') }}</td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('pajak.simulasi.edit', $baris) }}" class="btn btn-sm btn-outline-secondary" title="Ubah"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('pajak.simulasi.destroy', $baris) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus hasil perhitungan ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada hasil perhitungan. Isi nama penjual, harga jual, dan tarif, lalu simpan ke tabel.</td>
                </tr>
                @endforelse
            </tbody>
            @if($daftar->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td class="text-end">Rp {{ number_format($totalPenjual, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($totalPembeli, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($totalPenjual + $totalPembeli, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
<style>
    .table-custom tfoot td {
        background: #b7e1cd;
        font-weight: 800;
        border-top: 2px solid #86c5a8;
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const potonganPembeli = {{ (int) $potongan }};
    const nilaiJual = document.getElementById('nilai-jual');
    const persentase = document.getElementById('persentase');
    const persentasePembeli = document.getElementById('persentase-pembeli');

    function digit(teks) {
        return String(teks || '').replace(/\D/g, '');
    }

    function formatRupiah(el) {
        const angka = digit(el.value);
        el.value = angka ? Number(angka).toLocaleString('id-ID') : '';
    }

    function angka(el) {
        if (el.classList.contains('input-rupiah')) {
            const nilai = parseInt(digit(el.value), 10);
            return Number.isFinite(nilai) && nilai > 0 ? nilai : 0;
        }
        const nilai = parseFloat(String(el.value).replace(',', '.'));
        return Number.isFinite(nilai) && nilai > 0 ? nilai : 0;
    }

    function rupiah(nilai) {
        return 'Rp ' + Math.round(nilai).toLocaleString('id-ID');
    }

    function hitung() {
        const jual = angka(nilaiJual);
        let persenPenjual = angka(persentase);
        let persenPembeli = angka(persentasePembeli);
        if (persenPenjual > 100) persenPenjual = 100;
        if (persenPembeli > 100) persenPembeli = 100;
        const dasarPembeli = Math.max(0, jual - potonganPembeli);
        const pajakPenjual = Math.round(jual * persenPenjual / 100);
        const pajakPembeli = Math.round(dasarPembeli * persenPembeli / 100);
        document.getElementById('hasil-penjual').textContent = rupiah(pajakPenjual);
        document.getElementById('hasil-pembeli').textContent = rupiah(pajakPembeli);
        document.getElementById('hasil-total').textContent = rupiah(pajakPenjual + pajakPembeli);
        document.getElementById('rumus-penjual').textContent =
            'Pajak penjual = ' + rupiah(jual) + ' × ' + persenPenjual.toLocaleString('id-ID') + '% = ' + rupiah(pajakPenjual) + '.';
        document.getElementById('rumus-pembeli').textContent =
            'Pajak pembeli = (' + rupiah(jual) + ' − ' + rupiah(potonganPembeli) + ') × ' + persenPembeli.toLocaleString('id-ID') + '% = ' + rupiah(pajakPembeli) + '.';
    }

    document.querySelectorAll('.input-rupiah').forEach(function (el) {
        formatRupiah(el);
        el.addEventListener('input', function () {
            formatRupiah(el);
            hitung();
        });
    });
    persentase.addEventListener('input', hitung);
    persentasePembeli.addEventListener('input', hitung);
    document.querySelector('form').addEventListener('submit', function () {
        document.querySelectorAll('.input-rupiah').forEach(function (el) {
            el.value = digit(el.value);
        });
    });
    document.querySelectorAll('[data-persen]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            document.getElementById(tombol.getAttribute('data-sasaran')).value = tombol.getAttribute('data-persen');
            hitung();
        });
    });
    hitung();
});
</script>
@endpush
