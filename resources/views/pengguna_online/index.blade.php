@extends('layouts.admin')
@section('title', 'Pengguna Online')
@section('breadcrumb')
<li class="breadcrumb-item active">Pengguna Online</li>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Pengguna yang Sedang Online</h4>
        <p class="text-muted small mb-0">Akun yang sedang masuk dan alamat IP yang mengakses sistem dalam {{ $menitOnline }} menit terakhir. Termasuk akun pengangsur dan pengunjung tanpa login.</p>
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
        <i class="bi bi-arrow-clockwise"></i> Segarkan
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card-white h-100">
            <div class="stat-sublabel text-primary">Akun Online</div>
            <div class="stat-number text-primary">{{ $akunOnline->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-white h-100">
            <div class="stat-sublabel text-success">Akun Pengangsur</div>
            <div class="stat-number text-success">{{ $jumlahPengangsur }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-white h-100">
            <div class="stat-sublabel text-dark">Alamat IP</div>
            <div class="stat-number text-dark">{{ $ipOnline->count() }}</div>
        </div>
    </div>
</div>

<div class="card-custom bg-white mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <span class="fw-bold text-dark"><i class="bi bi-person-check text-primary me-1"></i> Akun yang sedang masuk</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Peran</th>
                    <th>Kavling</th>
                    <th>IP</th>
                    <th>Perangkat</th>
                    <th>Terakhir aktif</th>
                </tr>
            </thead>
            <tbody>
                @forelse($akunOnline as $baris)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $baris['user']->penghuni->nama_lengkap ?? $baris['user']->name }}</div>
                        <div class="small text-muted">{{ $baris['user']->email }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $baris['peran'] === 'Pengangsur' ? 'bg-success' : 'bg-primary' }}">{{ $baris['peran'] }}</span>
                        @if($baris['ini_saya'])
                            <span class="badge bg-light text-dark border">Anda</span>
                        @endif
                    </td>
                    <td>{{ $baris['user']->penghuni->blok_kavling ?? '-' }}</td>
                    <td><code>{{ $baris['ip'] }}</code></td>
                    <td class="small">{{ $baris['perangkat'] }}@if($baris['sesi'] > 1) · {{ $baris['sesi'] }} sesi @endif</td>
                    <td class="small text-muted">{{ $baris['terakhir']->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada akun yang aktif pada {{ $menitOnline }} menit terakhir.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-custom bg-white">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <span class="fw-bold text-dark"><i class="bi bi-hdd-network text-primary me-1"></i> Alamat IP yang mengakses sistem</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Alamat IP</th>
                    <th>Akun terkait</th>
                    <th>Perangkat</th>
                    <th>Sesi</th>
                    <th>Terakhir aktif</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ipOnline as $baris)
                <tr>
                    <td><code>{{ $baris['ip'] }}</code></td>
                    <td>
                        @forelse($baris['akun'] as $akun)
                            <div class="fw-semibold text-dark">{{ $akun->penghuni->nama_lengkap ?? $akun->name }}</div>
                            <div class="small text-muted">{{ $akun->isWarga() || $akun->penghuni ? 'Pengangsur' : $akun->peranLabel() }}</div>
                        @empty
                            @if($baris['tamu'])
                                <span class="badge bg-light text-dark border">Tamu, tanpa login</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        @endforelse
                        @if($baris['akun']->isNotEmpty() && $baris['tamu'])
                            <div class="small text-muted">Juga ada akses tanpa login</div>
                        @endif
                    </td>
                    <td class="small">{{ $baris['perangkat'] }}</td>
                    <td>{{ $baris['sesi'] }}</td>
                    <td class="small text-muted">{{ $baris['terakhir']->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Belum ada alamat IP yang mengakses sistem pada {{ $menitOnline }} menit terakhir.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
setTimeout(function () { window.location.reload(); }, 20000);
</script>
@endpush
