@extends('layouts.admin')
@section('title', 'Data Perumahan')
@section('breadcrumb')
<li class="breadcrumb-item active">Perumahan</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-houses-fill me-2"></i>Data Perumahan</h4>
    <a href="{{ route('perumahan.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Perumahan</a>
</div>

<div class="content-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Perumahan</th>
                        <th>Alamat</th>
                        <th>Kota</th>
                        <th>Total Unit</th>
                        <th>Penghuni</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perumahan as $idx => $p)
                    <tr>
                        <td>{{ $perumahan->firstItem() + $idx }}</td>
                        <td class="fw-semibold">{{ $p->nama_perumahan }}</td>
                        <td>{{ Str::limit($p->alamat, 40) }}</td>
                        <td>{{ $p->kota ?? '-' }}</td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $p->total_unit }} unit</span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success">{{ $p->penghuni_count }} orang</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('perumahan.show', $p) }}" class="btn btn-outline-primary rounded-start-3"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('perumahan.edit', $p) }}" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('perumahan.destroy', $p) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm rounded-end-3"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data perumahan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3 px-4">
        {{ $perumahan->onEachSide(1)->links() }}
    </div>
</div>
@endsection
