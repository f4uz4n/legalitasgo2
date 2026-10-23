@extends('layouts.admin')
@section('title', $perumahan->nama_perumahan)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('perumahan.index') }}">Perumahan</a></li>
<li class="breadcrumb-item active">{{ $perumahan->nama_perumahan }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-houses-fill me-2"></i>{{ $perumahan->nama_perumahan }}</h4>
    <a href="{{ route('perumahan.edit', $perumahan) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="content-card h-100">
            <div class="card-header"><span>Informasi Perumahan</span></div>
            <div class="card-body">
                <p><strong>Alamat:</strong><br>{{ $perumahan->alamat }}</p>
                <p><strong>Kelurahan:</strong> {{ $perumahan->kelurahan ?? '-' }}</p>
                <p><strong>Kecamatan:</strong> {{ $perumahan->kecamatan ?? '-' }}</p>
                <p><strong>Kota:</strong> {{ $perumahan->kota ?? '-' }}</p>
                <p><strong>Provinsi:</strong> {{ $perumahan->provinsi ?? '-' }}</p>
                <p><strong>Total Unit:</strong> {{ $perumahan->total_unit }}</p>
                <p><strong>Terisi:</strong> {{ $perumahan->penghuni->count() }} unit</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="content-card">
            <div class="card-header">
                <span>Daftar Penghuni</span>
                <a href="{{ route('penghuni.create') }}?perumahan_id={{ $perumahan->id }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Blok</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Legalitas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($perumahan->penghuni as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->nama_lengkap }}</td>
                                <td>{{ $p->blok_kavling }}</td>
                                <td>{{ $p->tipe_rumah ?? '-' }}</td>
                                <td>
                                    @if($p->status === 'lunas')
                                        <span class="badge badge-status bg-success bg-opacity-10 text-success">Lunas</span>
                                    @elseif($p->status === 'aktif')
                                        <span class="badge badge-status bg-primary bg-opacity-10 text-primary">Aktif</span>
                                    @else
                                        <span class="badge badge-status bg-danger bg-opacity-10 text-danger">Bermasalah</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->legalitas)
                                        <span class="badge badge-status" style="background: {{ $p->legalitas->status_color === 'purple' ? '#8b5cf6' : '' }}; color: #fff;">
                                            {{ $p->legalitas->status_label }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('penghuni.show', $p) }}" class="btn btn-sm btn-outline-primary rounded-3"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penghuni</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
