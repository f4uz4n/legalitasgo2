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
        $totalPenerimaan = $this->penerimaanAngsuran($tahunDipilih);
        $totalTerkumpul = $totalPenerimaan;
        $persenTerkumpul = 0;
        $daftarTahun = $this->daftarTahunAdmin();

        // 4. Kas In/Out & Pengeluaran
        $totalKasMasuk = KasMasuk::sum('jumlah');
        $totalKasKeluar = KasKeluar::sum('jumlah');
        $saldoKas = $totalKasMasuk - $totalKasKeluar;

        // Breakdown Pengeluaran Kas per Kategori
        $pengeluaranKategori = KasKeluar::selectRaw('kategori_kas_id, SUM(jumlah) as total')
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
        $recentKasMasuk = KasMasuk::with(['kategori', 'penghuni'])
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->get();

        $recentKasKeluar = KasKeluar::with('kategori')
            ->orderBy('tanggal', 'desc')
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

    private function penerimaanAngsuran(?int $tahun): float
    {
        $query = Angsuran::query()->where('jumlah_bayar', '>', 0)->whereNotNull('tanggal_bayar');
        if ($tahun !== null) {
            $query->whereYear('tanggal_bayar', $tahun);
        }

        return (float) $query->sum('jumlah_bayar');
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

        $data = [];
        foreach ($bulan as $date) {
            $data[] = [
                'bulan' => $date->translatedFormat($tahun === null ? 'M Y' : 'M'),
                'masuk' => (float) KasMasuk::whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month)->sum('jumlah'),
                'keluar' => (float) KasKeluar::whereYear('tanggal', $date->year)->whereMonth('tanggal', $date->month)->sum('jumlah'),
            ];
        }

        return $data;
    }
}
