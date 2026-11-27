<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use App\Models\Perumahan;
use App\Models\KasMasuk;
use App\Models\KasKeluar;
use App\Models\Angsuran;
use App\Models\Legalitas;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tahunDipilih = $this->tahunDariRequest($request);

        if ($user && $user->isWarga()) {
            $penghuni = $user->penghuni()
                ->with([
                    'perumahan',
                    'angsuran' => function ($q) {
                        $q->orderBy('angsuran_ke');
                    },
                    'legalitas.timeline',
                    'kasMasuk.kategori'
                ])
                ->first();

            $daftarTahun = $this->daftarTahunWarga($penghuni);
            $angsuranTampil = $this->angsuranPadaTahun($penghuni, $tahunDipilih);
            $penerimaanAngsuran = $this->penerimaanAngsuranWarga($penghuni, $tahunDipilih);

            return view('warga.dashboard', compact(
                'penghuni',
                'tahunDipilih',
                'daftarTahun',
                'angsuranTampil',
                'penerimaanAngsuran'
            ));
        }

        // 1. Single Perumahan Info
        $perumahan = Perumahan::first() ?? new Perumahan([
            'nama_perumahan' => 'Griya Harmoni Residence',
            'alamat' => 'Jl. Raya Harmoni No. 88, Cibinong, Kab. Bogor',
            'total_unit' => 40
        ]);

        // 2. Warga / Penghuni Metrics
        $totalPenghuni = Penghuni::count();
        $penghuniAktif = Penghuni::where('status', 'aktif')->count();
        $penghuniLunas = Penghuni::where('status', 'lunas')->count();
        $penghuniBermasalah = Penghuni::where('status', 'bermasalah')->count();

        // 3. Collecting Dana Angsuran Cicilan
        $totalNilaiRumah = Penghuni::sum('harga_rumah');
        $totalUangMuka = Penghuni::sum('uang_muka');
        $totalAngsuranTerbayar = Angsuran::where('status', 'lunas')->sum('jumlah_bayar');
        $totalSisaPiutang = Penghuni::sum('sisa_angsuran');
        $kasMasukPeriode = KasMasuk::query();
        $kasKeluarPeriode = KasKeluar::query();
        if ($tahunDipilih !== null) {
            $kasMasukPeriode->whereYear('tanggal', $tahunDipilih);
            $kasKeluarPeriode->whereYear('tanggal', $tahunDipilih);
        }
        $totalKasMasuk = (float) (clone $kasMasukPeriode)->sum('jumlah');
        $totalKasKeluar = (float) (clone $kasKeluarPeriode)->sum('jumlah');
        $totalAngsuranDiLuarKas = (float) $this->angsuranDiLuarKas($tahunDipilih)->sum('jumlah_bayar');
        $totalPenerimaan = $totalKasMasuk + $totalAngsuranDiLuarKas;
        $totalTerkumpul = $totalPenerimaan;
        $saldoKas = $totalPenerimaan - $totalKasKeluar;
        $persenTerkumpul = 0;
        $daftarTahun = $this->daftarTahunAdmin();

        // Breakdown Pengeluaran Kas per Kategori
        $pengeluaranKategori = (clone $kasKeluarPeriode)
            ->selectRaw('kategori_kas_id, SUM(jumlah) as total')
            ->with('kategori')
            ->groupBy('kategori_kas_id')
            ->orderBy('total', 'desc')
            ->get();

        $monthlyData = $this->arusKasBulanan($tahunDipilih);

        // Angsuran cicilan jatuh tempo bulan ini
        $angsuranJatuhTempo = Angsuran::with('penghuni')
            ->where('status', 'belum_bayar')
            ->whereMonth('tanggal_jatuh_tempo', Carbon::now()->month)
            ->whereYear('tanggal_jatuh_tempo', Carbon::now()->year)
            ->orderBy('tanggal_jatuh_tempo')
            ->take(8)
            ->get();

        // Angsuran terlambat
        $angsuranTerlambat = Angsuran::with('penghuni')
            ->where('status', 'belum_bayar')
            ->where('tanggal_jatuh_tempo', '<', Carbon::now())
            ->count();

        // Legalitas summary
        $legalitasStats = [
            'total' => Legalitas::count(),
            'belum_proses' => Legalitas::where('status', 'belum_proses')->count(),
            'pengajuan' => Legalitas::where('status', 'pengajuan')->count(),
            'verifikasi' => Legalitas::where('status', 'verifikasi')->count(),
            'proses_bpn' => Legalitas::where('status', 'proses_bpn')->count(),
            'proses_notaris' => Legalitas::where('status', 'proses_notaris')->count(),
            'selesai' => Legalitas::where('status', 'selesai')->count(),
        ];

        // Recent transactions
        $riwayatAngsuran = Angsuran::with('penghuni')
            ->where('jumlah_bayar', '>', 0)
            ->whereNotNull('tanggal_bayar')
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal_bayar', $tahunDipilih))
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $recentKasMasuk = KasMasuk::with(['kategori', 'penghuni'])
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal', $tahunDipilih))
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentKasKeluar = KasKeluar::with('kategori')
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal', $tahunDipilih))
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentActivityLogs = ActivityLog::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'perumahan',
            'totalPenghuni',
            'penghuniAktif',
            'penghuniLunas',
            'penghuniBermasalah',
            'totalNilaiRumah',
            'totalUangMuka',
            'totalAngsuranTerbayar',
            'totalTerkumpul',
            'totalPenerimaan',
            'persenTerkumpul',
            'totalSisaPiutang',
            'totalKasMasuk',
            'totalKasKeluar',
            'saldoKas',
            'pengeluaranKategori',
            'monthlyData',
            'angsuranJatuhTempo',
            'angsuranTerlambat',
            'legalitasStats',
            'riwayatAngsuran',
            'recentKasMasuk',
            'recentKasKeluar',
            'recentActivityLogs',
            'tahunDipilih',
            'daftarTahun'
        ));
    }

    private function tahunDariRequest(Request $request): ?int
    {
        $nilai = $request->query('tahun');
        if ($nilai === 'semua') {
            return null;
        }
        if ($nilai === null || $nilai === '' || !ctype_digit((string) $nilai)) {
            return (int) now()->year;
        }

        $tahun = (int) $nilai;
        if ($tahun < 1990 || $tahun > (int) now()->year + 40) {
            return (int) now()->year;
        }

        return $tahun;
    }

    private function daftarTahunAdmin()
    {
        $dariKasMasuk = KasMasuk::query()->whereNotNull('tanggal')->selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun');
        $dariKasKeluar = KasKeluar::query()->whereNotNull('tanggal')->selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun');
        $dariBayar = Angsuran::query()->whereNotNull('tanggal_bayar')->selectRaw('YEAR(tanggal_bayar) as tahun')->distinct()->pluck('tahun');

        return collect([$dariKasMasuk, $dariKasKeluar, $dariBayar, [(int) now()->year]])
            ->flatten()
            ->map(fn ($tahun) => (int) $tahun)
            ->filter(fn ($tahun) => $tahun >= 1990)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function daftarTahunWarga(?Penghuni $penghuni)
    {
        $tahun = collect([(int) now()->year]);
        if ($penghuni) {
            if ($penghuni->tahun_pembelian) {
                $tahun->push((int) $penghuni->tahun_pembelian);
            }
            foreach ($penghuni->angsuran as $angsuran) {
                if ($angsuran->tanggal_jatuh_tempo) {
                    $tahun->push((int) $angsuran->tanggal_jatuh_tempo->year);
                }
                if ($angsuran->tanggal_bayar) {
                    $tahun->push((int) $angsuran->tanggal_bayar->year);
                }
            }
        }

        return $tahun->filter(fn ($nilai) => $nilai >= 1990)->unique()->sortDesc()->values();
    }

    private function angsuranPadaTahun(?Penghuni $penghuni, ?int $tahun)
    {
        if (!$penghuni) {
            return collect();
        }
        if ($tahun === null) {
            return $penghuni->angsuran;
        }

        return $penghuni->angsuran->filter(function ($angsuran) use ($tahun) {
            $jatuhTempo = $angsuran->tanggal_jatuh_tempo ? (int) $angsuran->tanggal_jatuh_tempo->year : null;
            $tanggalBayar = $angsuran->tanggal_bayar ? (int) $angsuran->tanggal_bayar->year : null;

            return $jatuhTempo === $tahun || $tanggalBayar === $tahun;
        })->values();
    }

    private function penerimaanAngsuranWarga(?Penghuni $penghuni, ?int $tahun): float
    {
        if (!$penghuni) {
            return 0;
        }

        return (float) $penghuni->angsuran
            ->filter(function ($angsuran) use ($tahun) {
                if ((float) $angsuran->jumlah_bayar <= 0 || !$angsuran->tanggal_bayar) {
                    return false;
                }

                return $tahun === null || (int) $angsuran->tanggal_bayar->year === $tahun;
            })
            ->sum('jumlah_bayar');
    }

    private function arusKasBulanan(?int $tahun): array
    {
        $bulan = [];
        if ($tahun === null) {
            for ($i = 11; $i >= 0; $i--) {
                $bulan[] = Carbon::now()->startOfMonth()->subMonths($i);
            }
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $bulan[] = Carbon::create($tahun, $i, 1)->startOfMonth();
            }
        }

        $kasMasuk = KasMasuk::query()
            ->when($tahun !== null, fn ($q) => $q->whereYear('tanggal', $tahun))
            ->selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, SUM(jumlah) as total')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn ($baris) => sprintf('%04d-%02d', $baris->tahun, $baris->bulan));
        $angsuranMasuk = $this->angsuranDiLuarKas($tahun)
            ->selectRaw('YEAR(tanggal_bayar) as tahun, MONTH(tanggal_bayar) as bulan, SUM(jumlah_bayar) as total')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn ($baris) => sprintf('%04d-%02d', $baris->tahun, $baris->bulan));
        $kasKeluar = KasKeluar::query()
            ->when($tahun !== null, fn ($q) => $q->whereYear('tanggal', $tahun))
            ->selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, SUM(jumlah) as total')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn ($baris) => sprintf('%04d-%02d', $baris->tahun, $baris->bulan));

        $data = [];
        foreach ($bulan as $date) {
            $kunci = $date->format('Y-m');
            $data[] = [
                'bulan' => $date->translatedFormat($tahun === null ? 'M Y' : 'M'),
                'masuk' => (float) ($kasMasuk[$kunci]->total ?? 0) + (float) ($angsuranMasuk[$kunci]->total ?? 0),
                'keluar' => (float) ($kasKeluar[$kunci]->total ?? 0),
            ];
        }

        return $data;
    }

    private function angsuranDiLuarKas(?int $tahun)
    {
        return Angsuran::query()
            ->where('jumlah_bayar', '>', 0)
            ->whereNotNull('tanggal_bayar')
            ->whereDoesntHave('kasMasuk')
            ->when($tahun !== null, fn ($q) => $q->whereYear('tanggal_bayar', $tahun));
    }
}
