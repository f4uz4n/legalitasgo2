@extends('layouts.admin')
@section('title', 'Rekap Pengangsur')
@section('breadcrumb')
<li class="breadcrumb-item active">Rekap Pengangsur</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Rekap Pembayaran Pembelian Unit</h4>
        <div class="text-muted small" id="rekap-ringkas">{{ $sharedPerumahan->nama_perumahan ?? 'Perumahan' }} · {{ number_format($totalPengangsur) }} pengangsur</div>
    </div>
    <div class="d-flex gap-2">
        <a id="unduh-rekap" href="{{ route('penghuni.unduh', request()->only(['nama', 'rumah', 'search', 'status', 'jenis_pembayaran', 'tahun_pembelian', 'perumahan_id'])) }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i> Unduh Data
        </a>
        <a href="{{ route('penghuni.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Pengangsur</a>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-body py-3">
        <form id="form-cari-penghuni" action="{{ route('penghuni.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label class="form-label small mb-1">Nama</label>
                <input type="text" name="nama" class="form-control" placeholder="Ketik nama atau NIK" value="{{ request('nama') }}" autocomplete="off">
            </div>
            <div class="col-lg-3">
                <label class="form-label small mb-1">Rumah / Blok</label>
                <input type="text" name="rumah" class="form-control" placeholder="Ketik blok, contoh A-01" value="{{ request('rumah') }}" autocomplete="off">
            </div>
            <div class="col-lg-2">
                <label class="form-label small mb-1">Pembayaran</label>
                <select name="jenis_pembayaran" class="form-select">
                    <option value="">Semua</option>
                    <option value="inhouse" {{ request('jenis_pembayaran') == 'inhouse' ? 'selected' : '' }}>INHOUSE</option>
                    <option value="cash_lunak" {{ request('jenis_pembayaran') == 'cash_lunak' ? 'selected' : '' }}>CASH LUNAK</option>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small mb-1">Tahun</label>
                <select name="tahun_pembelian" class="form-select">
                    <option value="">Semua</option>
                    @foreach($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ (string) request('tahun_pembelian') === (string) $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="bermasalah" {{ request('status') == 'bermasalah' ? 'selected' : '' }}>Bermasalah</option>
                </select>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-primary me-2"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('penghuni.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="content-card" id="hasil-penghuni">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-rekap table-hover mb-0">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NAMA</th>
                        <th>BLOK</th>
                        <th>TAHUN PEMBELIAN</th>
                        <th>PEMBAYARAN</th>
                        <th>HARGA</th>
                        <th>UANG MUKA</th>
                        <th>ANGSURAN</th>
                        <th>TEMPO</th>
                        <th>SISA TEMPO</th>
                        <th>TUNGGAKAN</th>
                        <th>SISA PEMBAYARAN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penghuni as $idx => $p)
                    <tr>
                        <td class="text-center">{{ $penghuni->firstItem() + $idx }}</td>
                        <td class="fw-semibold">{{ $p->nama_lengkap }}</td>
                        <td class="text-center fw-bold">{{ $p->blok_kavling }}</td>
                        <td class="text-center">{{ $p->tahun_pembelian ?? '-' }}</td>
                        <td class="text-center">{{ $p->jenis_pembayaran_label }}</td>
                        <td class="text-end">Rp {{ number_format($p->harga_rumah, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($p->uang_muka, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($p->angsuran_bulanan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $p->tempo_label }}</td>
                        <td class="text-center">{{ $p->sisa_tempo_label }}</td>
                        <td class="text-center {{ (int) $p->tunggakan > 0 ? 'text-danger fw-bold' : '' }}">{{ $p->tunggakan_label }}</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($p->sisa_angsuran, 0, ',', '.') }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('penghuni.show', $p) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('penghuni.edit', $p) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('penghuni.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus data pengangsur ini?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="text-center text-muted py-4">Belum ada data pengangsur</td></tr>
                    @endforelse
                </tbody>
                @if($totalPengangsur > 0)
                <tfoot>
                    <tr>
                        <td colspan="11" class="text-center">JUMLAH</td>
                        <td class="text-end">Rp {{ number_format($totalSisaPembayaran, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">Jumlah sisa pembayaran menghitung seluruh hasil filter, termasuk halaman lain.</span>
        {{ $penghuni->onEachSide(1)->links() }}
    </div>
</div>
@include('partials.cari-ajax', ['formId' => 'form-cari-penghuni', 'hasilId' => 'hasil-penghuni', 'sinkron' => ['rekap-ringkas'], 'unduhId' => 'unduh-rekap', 'unduhUrl' => route('penghuni.unduh')])

<style>
    .table-rekap { font-size: 0.78rem; }
    .table-rekap thead th {
        background: #ffe08a;
        color: #292524;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        white-space: nowrap;
        border-color: #f3d98a;
        text-align: center;
        vertical-align: middle;
    }
    .table-rekap td { white-space: nowrap; vertical-align: middle; }
    .table-rekap tfoot td {
        background: #b7e1cd;
        font-weight: 800;
        border-top: 2px solid #86c5a8;
    }
</style>
@endsection
