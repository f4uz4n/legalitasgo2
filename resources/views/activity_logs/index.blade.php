@extends('layouts.admin')
@section('title', 'Log Aktivitas Admin (Audit Trail)')
@section('breadcrumb')
<li class="breadcrumb-item active">Log Aktivitas</li>
@endsection

@push('styles')
{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.3.2/css/searchPanes.bootstrap5.min.css">
<style>
    /* DataTables custom integration */
    div.dataTables_wrapper div.dataTables_length select {
        border-radius: 8px;
        padding: 0.3rem 2rem 0.3rem 0.6rem;
        border: 1px solid #e2e8f0;
        font-size: 0.85rem;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 0.3rem 0.75rem;
        font-size: 0.85rem;
        min-width: 220px;
    }
    div.dataTables_wrapper div.dataTables_info {
        font-size: 0.82rem;
        color: #64748b;
        padding-top: 0.5rem;
    }
    div.dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-size: 0.83rem !important;
    }
    div.dataTables_paginate .paginate_button.current,
    div.dataTables_paginate .paginate_button.current:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #fff !important;
        border-radius: 8px !important;
    }
    div.dataTables_paginate .paginate_button:hover {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
    }
    /* Per-column search row */
    tfoot.dt-search-footer th input,
    tfoot.dt-search-footer th select {
        width: 100%;
        padding: 0.3rem 0.5rem;
        border-radius: 7px;
        border: 1px solid #e2e8f0;
        font-size: 0.78rem;
        background: #f8fafc;
        color: #334155;
        transition: border-color 0.15s ease;
    }
    tfoot.dt-search-footer th input:focus,
    tfoot.dt-search-footer th select:focus {
        outline: none;
        border-color: #1d4ed8;
        background: #fff;
        box-shadow: 0 0 0 2px rgba(29,78,216,0.08);
    }
    tfoot.dt-search-footer th {
        padding: 0.45rem 1.1rem 0.7rem;
        background: #f8fafc;
    }
    /* Processing overlay */
    div.dataTables_processing {
        background: rgba(255,255,255,0.92);
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        color: #1d4ed8;
        font-weight: 600;
        padding: 10px 20px;
    }
    /* Responsive row */
    td.dtr-control::before {
        background-color: #1d4ed8 !important;
    }
</style>
@endpush

@section('content')

{{-- ============================================================ --}}
{{-- Header --}}
{{-- ============================================================ --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="bi bi-shield-shaded text-primary me-2"></i>Log Aktivitas Pengelola
            </h4>
            <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">AUDIT TRAIL</span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Rekam jejak seluruh akses menu, insert, update, dan delete oleh administrator — tercatat otomatis dan transparan secara real-time.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-danger border border-danger-subtle px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-lock-fill text-danger"></i> Tidak Dapat Dihapus (Permanen)
        </span>
        <a href="{{ route('public.report') }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
            <i class="bi bi-broadcast-pin me-1"></i> Cek Laporan Publik Warga ↗
        </a>
    </div>
</div>

{{-- ============================================================ --}}
{{-- Alert Integritas --}}
{{-- ============================================================ --}}
<div class="alert alert-primary bg-primary-subtle border-primary-subtle d-flex align-items-start gap-3 p-3 rounded-3 mb-4" role="alert">
    <div class="text-primary fs-4 mt-1"><i class="bi bi-shield-check"></i></div>
    <div class="small text-dark">
        <strong class="d-block text-primary mb-1">Integritas Audit Trail &amp; Transparansi Perumahan</strong>
        Seluruh aktivitas operasional pengelola perumahan tercatat secara <em>append-only</em> dan
        <strong>tidak memiliki fungsi penghapusan atau perubahan</strong>. Log ini dapat dilihat juga oleh warga
        di halaman Laporan Publik sebagai bentuk akuntabilitas dan transparansi pengelolaan perumahan.
    </div>
</div>

{{-- ============================================================ --}}
{{-- KPI Ringkas --}}
{{-- ============================================================ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Total Seluruh Log</span>
                <span class="badge bg-light text-primary border"><i class="bi bi-database"></i></span>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($totalLogs, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-check2-circle text-success me-1"></i> Rekaman aktivitas permanen</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Transaksi &amp; Modifikasi</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-pencil-fill"></i></span>
            </div>
            <div class="fs-3 fw-bold text-success">{{ number_format($totalChanges, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Aksi Insert, Update, &amp; Delete</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Akses Menu / Navigasi</span>
                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-eye-fill"></i></span>
            </div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($totalViews, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Kunjungan menu oleh admin</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="content-card h-100 p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small fw-bold text-uppercase">Aktivitas Hari Ini</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-calendar-event"></i></span>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($todayLogs, 0, ',', '.') }}</div>
            <div class="small text-muted mt-1">Interaksi dalam 24 jam terakhir</div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- DataTables Card --}}
{{-- ============================================================ --}}
<div class="content-card bg-white">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-table text-primary"></i> Daftar Riwayat Aktivitas Administrator
            <span class="badge bg-light text-secondary border ms-1" style="font-size:0.68rem;">DataTables Server-Side</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-danger border border-danger-subtle fw-bold" style="font-size:0.7rem;">
                <i class="bi bi-lock-fill me-1"></i>Tidak Ada Fungsi Hapus
            </span>
        </div>
    </div>
    <div class="card-body p-3">

        {{-- Tabel utama — tfoot dipakai untuk per-column search --}}
        <div class="table-responsive">
            <table id="activityLogTable" class="table table-modern align-middle w-100" style="font-size:0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width:155px;">Waktu &amp; Tanggal</th>
                        <th style="width:175px;">Administrator</th>
                        <th style="width:130px;" class="text-center">Aksi</th>
                        <th style="width:160px;">Modul / Fitur</th>
                        <th>Keterangan Aktivitas</th>
                        <th style="width:150px;">Perangkat &amp; IP</th>
                    </tr>
                </thead>
                <tfoot class="dt-search-footer">
                    <tr>
                        <th><input type="text" id="col-search-tanggal" placeholder="Cari tanggal/waktu..." autocomplete="off"></th>
                        <th><input type="text" id="col-search-admin" placeholder="Cari nama/email admin..." autocomplete="off"></th>
                        <th>
                            <select id="col-search-action">
                                <option value="">Semua Aksi</option>
                                <option value="VIEW">Akses Menu (VIEW)</option>
                                <option value="INSERT">Tambah Data (INSERT)</option>
                                <option value="UPDATE">Perbarui Data (UPDATE)</option>
                                <option value="DELETE">Hapus Data (DELETE)</option>
                            </select>
                        </th>
                        <th>
                            <select id="col-search-module">
                                <option value="">Semua Modul</option>
                                @foreach($availableModules as $mod)
                                    <option value="{{ $mod }}">{{ $mod }}</option>
                                @endforeach
                            </select>
                        </th>
                        <th><input type="text" id="col-search-desc" placeholder="Cari keterangan/URL..." autocomplete="off"></th>
                        <th><input type="text" id="col-search-ip" placeholder="Cari IP address..." autocomplete="off"></th>
                    </tr>
                </tfoot>
                <tbody>
                    {{-- Diisi oleh DataTables via AJAX --}}
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection

@push('scripts')
{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    var ajaxUrl = "{{ route('admin.activity-logs.data') }}";

    // Variabel search per kolom
    var colSearch = {
        tanggal : '',
        admin   : '',
        action  : '',
        module  : '',
        desc    : '',
        ip      : '',
    };

    var table = $('#activityLogTable').DataTable({
        processing  : true,
        serverSide  : true,
        responsive  : true,
        language    : {
            processing   : '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm text-primary"></div> <span>Memuat data...</span></div>',
            search       : 'Cari global:',
            lengthMenu   : 'Tampilkan _MENU_ data',
            info         : 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
            infoEmpty    : 'Tidak ada data yang tersedia',
            infoFiltered : '(disaring dari _MAX_ total data)',
            paginate     : {
                first    : '<i class="bi bi-chevron-double-left"></i>',
                previous : '<i class="bi bi-chevron-left"></i>',
                next     : '<i class="bi bi-chevron-right"></i>',
                last     : '<i class="bi bi-chevron-double-right"></i>',
            },
            zeroRecords  : '<div class="text-center py-4 text-muted"><i class="bi bi-journal-x fs-3 d-block mb-2"></i>Tidak ada data yang cocok dengan pencarian.</div>',
            emptyTable   : '<div class="text-center py-4 text-muted"><i class="bi bi-shield-check fs-3 text-primary d-block mb-2"></i>Belum ada riwayat log aktivitas.</div>',
        },
        lengthMenu  : [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength  : 25,
        order       : [[0, 'desc']],
        columns     : [
            { data: 'created_at',  orderable: true,  searchable: false },
            { data: 'admin_name',  orderable: true,  searchable: false },
            { data: 'action',      orderable: true,  searchable: false, className: 'text-center' },
            { data: 'module',      orderable: true,  searchable: false },
            { data: 'description', orderable: false, searchable: false },
            { data: 'ip_address',  orderable: true,  searchable: false },
        ],
        ajax: {
            url  : ajaxUrl,
            type : 'GET',
            data : function (d) {
                d.search_tanggal = colSearch.tanggal;
                d.search_admin   = colSearch.admin;
                d.search_action  = colSearch.action;
                d.search_module  = colSearch.module;
                d.search_desc    = colSearch.desc;
                d.search_ip      = colSearch.ip;
            },
            error: function (xhr, error, code) {
                console.error('DataTables AJAX error:', error, code);
            }
        },
        drawCallback: function () {
            // Re-render tooltip Bootstrap jika ada
        }
    });

    // ——— Per-column search debounce helpers ———
    function debounce(fn, delay) {
        var t;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, delay);
        };
    }

    $('#col-search-tanggal').on('keyup', debounce(function () {
        colSearch.tanggal = this.value;
        table.draw();
    }, 400));

    $('#col-search-admin').on('keyup', debounce(function () {
        colSearch.admin = this.value;
        table.draw();
    }, 400));

    $('#col-search-action').on('change', function () {
        colSearch.action = this.value;
        table.draw();
    });

    $('#col-search-module').on('change', function () {
        colSearch.module = this.value;
        table.draw();
    });

    $('#col-search-desc').on('keyup', debounce(function () {
        colSearch.desc = this.value;
        table.draw();
    }, 400));

    $('#col-search-ip').on('keyup', debounce(function () {
        colSearch.ip = this.value;
        table.draw();
    }, 400));
});
</script>
@endpush
