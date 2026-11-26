@extends('layouts.admin')
@section('title', 'Kas Masuk / Keluar')
@section('breadcrumb')
<li class="breadcrumb-item active">Kas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2"></i>Cash Flow - Kas Masuk / Keluar</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalKasMasuk"><i class="bi bi-plus-lg me-1"></i> Kas Masuk</button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalKasKeluar"><i class="bi bi-dash-lg me-1"></i> Kas Keluar</button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalKategori"><i class="bi bi-tags me-1"></i> Kategori</button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card-white">
            <div class="stat-sublabel text-primary">Kas Masuk (Bulan Ini)</div>
            <div class="stat-number text-primary fs-4 mt-1">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</div>
            <small class="text-muted">Penerimaan cicilan & DP</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-white">
            <div class="stat-sublabel text-danger">Kas Keluar (Bulan Ini)</div>
            <div class="stat-number text-danger fs-4 mt-1">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</div>
            <small class="text-muted">Operasional, notaris, BPN</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-white">
            <div class="stat-sublabel text-dark">Surplus / Defisit Bulan Ini</div>
            <div class="stat-number {{ $saldo >= 0 ? 'text-success' : 'text-danger' }} fs-4 mt-1">Rp {{ number_format($saldo, 0, ',', '.') }}</div>
            <small class="text-muted">Kas masuk - kas keluar</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-white" style="background-color: #f0fdf4 !important; border-color: #bbf7d0 !important;">
            <div class="stat-sublabel text-success">Saldo Kas Total (All Time)</div>
            <div class="stat-number text-success fs-4 mt-1">Rp {{ number_format($allTimeSaldo, 0, ',', '.') }}</div>
            <small class="text-success fw-semibold"><i class="bi bi-wallet2 me-1"></i>Kas riil tersedia</small>
        </div>
    </div>
</div>

<!-- Filter Bulan -->
<div class="content-card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('kas.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Periode Bulan</label>
                <input type="month" name="bulan" class="form-control" value="{{ $bulan }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Kas Masuk -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <span class="text-success"><i class="bi bi-arrow-down-circle-fill me-2"></i>Kas Masuk</span>
                <span class="badge bg-success">{{ $kasMasuk->count() }} transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Bukti</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasMasuk as $km)
                            <tr>
                                <td>{{ $km->tanggal->format('d/m') }}</td>
                                <td><span class="badge bg-success bg-opacity-10 text-success">{{ $km->kategori->nama_kategori ?? '-' }}</span></td>
                                <td><small>{{ Str::limit($km->keterangan, 30) }}</small></td>
                                <td class="fw-semibold text-success">+{{ number_format($km->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    @if($km->bukti_file)
                                    @php $extMasuk = strtolower(pathinfo($km->bukti_file, PATHINFO_EXTENSION)); @endphp
                                    <a href="{{ route('kas.masuk.bukti', $km) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-3 py-0 px-2" style="font-size:0.75rem;" title="Lihat Bukti">
                                        @if(in_array($extMasuk, ['jpg', 'jpeg', 'png']))
                                        <img src="{{ route('kas.masuk.bukti', $km) }}" alt="Bukti" style="width:36px;height:36px;object-fit:cover;border-radius:6px">
                                        @else
                                        <i class="bi bi-paperclip"></i> Bukti
                                        @endif
                                    </a>
                                    @else
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 btn-edit-masuk" style="padding:2px 6px" title="Ubah"
                                        data-bs-toggle="modal" data-bs-target="#modalEditMasuk"
                                        data-action="{{ route('kas.masuk.update', $km) }}"
                                        data-kategori="{{ $km->kategori_kas_id }}"
                                        data-tanggal="{{ $km->tanggal->format('Y-m-d') }}"
                                        data-jumlah="{{ (int) $km->jumlah }}"
                                        data-metode="{{ $km->metode_bayar }}"
                                        data-keterangan="{{ e(preg_replace('/\s+/', ' ', (string) $km->keterangan)) }}"
                                        data-publik="{{ $km->tampil_publik ? '1' : '0' }}"
                                        data-bukti="{{ $km->bukti_file ? route('kas.masuk.bukti', $km) : '' }}">
                                        <i class="bi bi-pencil" style="font-size:0.7rem"></i>
                                    </button>
                                    <form action="{{ route('kas.masuk.publik', $km) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm {{ $km->tampil_publik ? 'btn-outline-primary' : 'btn-outline-secondary' }} rounded-3" style="padding:2px 6px" title="{{ $km->tampil_publik ? 'Tampil di portal warga' : 'Disembunyikan dari portal warga' }}">
                                            <i class="bi bi-eye{{ $km->tampil_publik ? '' : '-slash' }}" style="font-size:0.7rem"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('kas.masuk.destroy', $km) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-3" style="padding:2px 6px"><i class="bi bi-trash" style="font-size:0.7rem"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kas Keluar -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <span class="text-danger"><i class="bi bi-arrow-up-circle-fill me-2"></i>Kas Keluar</span>
                <span class="badge bg-danger">{{ $kasKeluar->count() }} transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Penerima</th>
                                <th>Jumlah</th>
                                <th>Bukti</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kasKeluar as $kk)
                            <tr>
                                <td>{{ $kk->tanggal->format('d/m') }}</td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger">{{ $kk->kategori->nama_kategori ?? '-' }}</span></td>
                                <td><small>{{ Str::limit($kk->penerima ?? $kk->keterangan, 30) }}</small></td>
                                <td class="fw-semibold text-danger">-{{ number_format($kk->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    @if($kk->bukti_file)
                                    @php $extKeluar = strtolower(pathinfo($kk->bukti_file, PATHINFO_EXTENSION)); @endphp
                                    <a href="{{ route('kas.keluar.bukti', $kk) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-3 py-0 px-2" style="font-size:0.75rem;" title="Lihat Bukti Pengeluaran">
                                        @if(in_array($extKeluar, ['jpg', 'jpeg', 'png']))
                                        <img src="{{ route('kas.keluar.bukti', $kk) }}" alt="Bukti" style="width:36px;height:36px;object-fit:cover;border-radius:6px">
                                        @else
                                        <i class="bi bi-paperclip"></i> Bukti
                                        @endif
                                    </a>
                                    @else
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 btn-edit-keluar" style="padding:2px 6px" title="Ubah"
                                        data-bs-toggle="modal" data-bs-target="#modalEditKeluar"
                                        data-action="{{ route('kas.keluar.update', $kk) }}"
                                        data-kategori="{{ $kk->kategori_kas_id }}"
                                        data-tanggal="{{ $kk->tanggal->format('Y-m-d') }}"
                                        data-jumlah="{{ (int) $kk->jumlah }}"
                                        data-penerima="{{ $kk->penerima }}"
                                        data-keterangan="{{ e(preg_replace('/\s+/', ' ', (string) $kk->keterangan)) }}"
                                        data-publik="{{ $kk->tampil_publik ? '1' : '0' }}"
                                        data-bukti="{{ $kk->bukti_file ? route('kas.keluar.bukti', $kk) : '' }}">
                                        <i class="bi bi-pencil" style="font-size:0.7rem"></i>
                                    </button>
                                    <form action="{{ route('kas.keluar.publik', $kk) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm {{ $kk->tampil_publik ? 'btn-outline-primary' : 'btn-outline-secondary' }} rounded-3" style="padding:2px 6px" title="{{ $kk->tampil_publik ? 'Tampil di portal warga' : 'Disembunyikan dari portal warga' }}">
                                            <i class="bi bi-eye{{ $kk->tampil_publik ? '' : '-slash' }}" style="font-size:0.7rem"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('kas.keluar.destroy', $kk) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-3" style="padding:2px 6px"><i class="bi bi-trash" style="font-size:0.7rem"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kas Masuk -->
<div class="modal fade" id="modalKasMasuk" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-down-circle text-success me-2"></i>Tambah Kas Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kas.masuk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_kas_id" class="form-select select2" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoriMasuk as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Bayar</label>
                        <select name="metode_bayar" class="form-select">
                            <option>Transfer Bank</option>
                            <option>Tunai</option>
                            <option>QRIS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Transaksi (Opsional)</label>
                        <input type="file" name="bukti_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format: JPG, PNG, atau PDF (Maks. 5 MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tampil_publik" id="masuk_tampil_publik" value="1" checked>
                        <label class="form-check-label" for="masuk_tampil_publik">Tampil di portal warga</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kas Keluar -->
<div class="modal fade" id="modalKasKeluar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-up-circle text-danger me-2"></i>Tambah Kas Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kas.keluar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_kas_id" class="form-select select2" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoriKeluar as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerima</label>
                        <input type="text" name="penerima" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Pengeluaran (Opsional)</label>
                        <input type="file" name="bukti_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Format: JPG, PNG, atau PDF (Maks. 5 MB). Kuitansi notaris, BPN, nota belanja.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tampil_publik" id="keluar_tampil_publik" value="1" checked>
                        <label class="form-check-label" for="keluar_tampil_publik">Tampil di portal warga</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Kas Masuk -->
<div class="modal fade" id="modalEditMasuk" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil text-success me-2"></i>Ubah Kas Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditMasuk" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_kas_id" class="form-select" required>
                            @foreach($kategoriMasuk as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" min="0" step="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Bayar</label>
                        <select name="metode_bayar" class="form-select">
                            <option>Transfer Bank</option>
                            <option>Tunai</option>
                            <option>QRIS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Transaksi</label>
                        <div id="bukti-masuk-saat-ini" class="small mb-2 text-muted">Belum ada bukti.</div>
                        <input type="file" name="bukti_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Kosongkan jika bukti yang ada tetap dipakai. JPG, PNG, atau PDF, maksimal 5 MB.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tampil_publik" id="edit_masuk_tampil_publik" value="1">
                        <label class="form-check-label" for="edit_masuk_tampil_publik">Tampil di portal warga</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Kas Keluar -->
<div class="modal fade" id="modalEditKeluar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil text-danger me-2"></i>Ubah Kas Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKeluar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_kas_id" class="form-select" required>
                            @foreach($kategoriKeluar as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" min="0" step="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerima</label>
                        <input type="text" name="penerima" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Pengeluaran</label>
                        <div id="bukti-keluar-saat-ini" class="small mb-2 text-muted">Belum ada bukti.</div>
                        <input type="file" name="bukti_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Kosongkan jika bukti yang ada tetap dipakai. JPG, PNG, atau PDF, maksimal 5 MB.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tampil_publik" id="edit_keluar_tampil_publik" value="1">
                        <label class="form-check-label" for="edit_keluar_tampil_publik">Tampil di portal warga</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-tags me-2"></i>Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kas.kategori.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="masuk">Kas Masuk</option>
                            <option value="keluar">Kas Keluar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pratinjauBukti(tautan) {
    if (!tautan) {
        return 'Belum ada bukti.';
    }
    if (/\.pdf($|\?)/i.test(tautan)) {
        return '<a href="' + tautan + '" target="_blank">Lihat bukti saat ini</a>';
    }
    return '<a href="' + tautan + '" target="_blank"><img src="' + tautan + '" alt="Bukti" style="max-height:140px;border-radius:8px"></a>';
}

document.querySelectorAll('.btn-edit-masuk').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const form = document.getElementById('formEditMasuk');
        form.action = btn.dataset.action;
        form.querySelector('[name=kategori_kas_id]').value = btn.dataset.kategori;
        form.querySelector('[name=tanggal]').value = btn.dataset.tanggal;
        form.querySelector('[name=jumlah]').value = btn.dataset.jumlah;
        const metode = form.querySelector('[name=metode_bayar]');
        const nilaiMetode = btn.dataset.metode || 'Transfer Bank';
        if (![...metode.options].some(function (opt) { return opt.value === nilaiMetode; })) {
            metode.add(new Option(nilaiMetode, nilaiMetode));
        }
        metode.value = nilaiMetode;
        form.querySelector('[name=keterangan]').value = btn.dataset.keterangan || '';
        form.querySelector('[name=tampil_publik]').checked = btn.dataset.publik === '1';
        form.querySelector('[name=bukti_file]').value = '';
        const info = document.getElementById('bukti-masuk-saat-ini');
        info.innerHTML = pratinjauBukti(btn.dataset.bukti);
    });
});

document.querySelectorAll('.btn-edit-keluar').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const form = document.getElementById('formEditKeluar');
        form.action = btn.dataset.action;
        form.querySelector('[name=kategori_kas_id]').value = btn.dataset.kategori;
        form.querySelector('[name=tanggal]').value = btn.dataset.tanggal;
        form.querySelector('[name=jumlah]').value = btn.dataset.jumlah;
        form.querySelector('[name=penerima]').value = btn.dataset.penerima || '';
        form.querySelector('[name=keterangan]').value = btn.dataset.keterangan || '';
        form.querySelector('[name=tampil_publik]').checked = btn.dataset.publik === '1';
        form.querySelector('[name=bukti_file]').value = '';
        const info = document.getElementById('bukti-keluar-saat-ini');
        info.innerHTML = pratinjauBukti(btn.dataset.bukti);
    });
});
</script>
@endpush
