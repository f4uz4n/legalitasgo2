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
    $nilaiNjop = $rupiahInput(old('njop', $ubah->njop ?? 0));
    $nilaiBebas = $rupiahInput(old('npoptkp', $ubah->npoptkp ?? 0));
    $nilaiPersen = old('persentase', $ubah->persentase ?? 5);
    if (is_numeric(str_replace(',', '.', (string) $nilaiPersen))) {
        $nilaiPersen = rtrim(rtrim(number_format((float) str_replace(',', '.', (string) $nilaiPersen), 2, '.', ''), '0'), '.');
    }
@endphp
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Simulasi Pajak Jual Beli / BPHTB</h4>
        <p class="text-muted small mb-0">Perkiraan bea perolehan hak atas tanah dan bangunan. Nilai jual dan persentase bisa diubah sesuai transaksi.</p>
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
                    <label class="form-label small fw-bold" for="nilai-jual">Nilai jual / harga transaksi</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="nilai_jual" id="nilai-jual" class="form-control input-rupiah @error('nilai_jual') is-invalid @enderror" inputmode="numeric" autocomplete="off" value="{{ $nilaiJual }}" required>
                    </div>
                    @error('nilai_jual')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="njop">NJOP (opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="njop" id="njop" class="form-control input-rupiah" inputmode="numeric" autocomplete="off" value="{{ $nilaiNjop }}">
                    </div>
                    <div class="form-text">Dasar hitung memakai angka yang lebih tinggi antara nilai jual dan NJOP.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="npoptkp">NPOPTKP (opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="npoptkp" id="npoptkp" class="form-control input-rupiah" inputmode="numeric" autocomplete="off" value="{{ $nilaiBebas }}">
                    </div>
                    <div class="form-text">Nilai yang tidak kena pajak. Isi 0 bila ingin menghitung langsung dari nilai jual.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="persentase">Persentase tarif</label>
                    <div class="input-group" style="max-width: 220px;">
                        <input type="number" name="persentase" id="persentase" class="form-control @error('persentase') is-invalid @enderror" min="0" max="100" step="0.01" value="{{ $nilaiPersen }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('persentase')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-persen="5">5%</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-persen="2.5">2,5%</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-persen="1">1%</button>
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
                            <div class="text-muted small">NPOP</div>
                            <div class="fw-bold fs-5" id="hasil-npop">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted small">Dasar pengenaan</div>
                            <div class="fw-bold fs-5" id="hasil-dasar">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-primary bg-opacity-10">
                            <div class="text-primary small fw-bold">Perkiraan pajak</div>
                            <div class="fw-bold fs-5 text-primary" id="hasil-pajak">Rp 0</div>
                        </div>
                    </div>
                </div>
                <p class="small text-muted mt-3 mb-0" id="rumus-pajak">BPHTB = (NPOP − NPOPTKP) × persentase.</p>
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
                    <th class="text-end">Nilai jual</th>
                    <th class="text-end">NJOP</th>
                    <th class="text-end">NPOPTKP</th>
                    <th class="text-end">Persentase</th>
                    <th class="text-end">NPOP</th>
                    <th class="text-end">Dasar pengenaan</th>
                    <th class="text-end">Pajak</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $i => $baris)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-end">Rp {{ number_format($baris->nilai_jual, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($baris->njop, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($baris->npoptkp, 0, ',', '.') }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($baris->persentase, 2, ',', '.'), '0'), ',') }}%</td>
                    <td class="text-end">Rp {{ number_format($baris->npop, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($baris->dasar_pengenaan, 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($baris->jumlah_pajak, 0, ',', '.') }}</td>
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
                    <td colspan="9" class="text-center text-muted py-4">Belum ada hasil perhitungan. Isi nilai jual dan persentase, lalu simpan ke tabel.</td>
                </tr>
                @endforelse
            </tbody>
            @if($daftar->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="7" class="text-end">TOTAL</td>
                    <td class="text-end">Rp {{ number_format($totalPajak, 0, ',', '.') }}</td>
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
    const nilaiJual = document.getElementById('nilai-jual');
    const njop = document.getElementById('njop');
    const npoptkp = document.getElementById('npoptkp');
    const persentase = document.getElementById('persentase');

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
        const nilaiNjop = angka(njop);
        const bebas = angka(npoptkp);
        let persen = angka(persentase);
        if (persen > 100) persen = 100;
        const npop = Math.max(jual, nilaiNjop);
        const dasar = Math.max(0, npop - bebas);
        const pajak = Math.round(dasar * persen / 100);
        document.getElementById('hasil-npop').textContent = rupiah(npop);
        document.getElementById('hasil-dasar').textContent = rupiah(dasar);
        document.getElementById('hasil-pajak').textContent = rupiah(pajak);
        document.getElementById('rumus-pajak').textContent =
            'BPHTB = (' + rupiah(npop) + ' − ' + rupiah(bebas) + ') × ' + persen.toLocaleString('id-ID') + '% = ' + rupiah(pajak) + '.';
    }

    document.querySelectorAll('.input-rupiah').forEach(function (el) {
        formatRupiah(el);
        el.addEventListener('input', function () {
            formatRupiah(el);
            hitung();
        });
    });
    persentase.addEventListener('input', hitung);
    document.querySelector('form').addEventListener('submit', function () {
        document.querySelectorAll('.input-rupiah').forEach(function (el) {
            el.value = digit(el.value);
        });
    });
    document.querySelectorAll('[data-persen]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            persentase.value = tombol.getAttribute('data-persen');
            hitung();
        });
    });
    hitung();
});
</script>
@endpush
