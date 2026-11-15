<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\KasMasuk;
use App\Models\KasKeluar;
use App\Models\Legalitas;
use App\Models\Penghuni;
use App\Models\Perumahan;
use App\Models\TimelineLegalitas;
use App\Models\ActivityLog;
use App\Support\PortalWarga;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicReportController extends Controller
{
    public function index(Request $request, PortalWarga $portalWarga)
    {
        $tahunDipilih = $this->tahunDariRequest($request);
        $daftarTahun = $this->daftarTahun();
        $ringkasAngsuran = $this->ringkasanAngsuran($tahunDipilih);
        $portal = $portalWarga->semuanya();
        $tabAwal = collect([
            'uangmasuk' => $portal['dana_masuk'],
            'timeline' => $portal['timeline'],
            'pengeluaran' => $portal['pengeluaran'],
            'log' => $portal['log'],
            'arsip' => $portal['kearsipan'],
        ])->filter()->keys()->first();

        // Single Perumahan
        $perumahan = Perumahan::first() ?? new Perumahan([
            'nama_perumahan' => 'Griya Harmoni Residence',
            'alamat' => 'Jl. Raya Harmoni No. 88, Cibinong, Kab. Bogor',
            'total_unit' => 40
        ]);

        $totalUnit = $perumahan->total_unit ?? 40;
        $totalPenghuni = Penghuni::count();
        $penghuniLunas = Penghuni::where('status', 'lunas')->count();
        $penghuniAktif = Penghuni::where('status', 'aktif')->count();

        // Progress hanya dari angsuran yang sudah diinput, tanpa uang muka.
        $totalTarget = $ringkasAngsuran['target'];
        $totalTerkumpul = $ringkasAngsuran['terbayar'];
        $progressCollecting = $ringkasAngsuran['persen'];

        // Cash flow summary
        $kasMasuk = KasMasuk::query()->where('tampil_publik', true);
        $kasKeluar = KasKeluar::query()->where('tampil_publik', true);
        if ($tahunDipilih !== null) {
            $kasMasuk->whereYear('tanggal', $tahunDipilih);
            $kasKeluar->whereYear('tanggal', $tahunDipilih);
        }
        $totalKasMasuk = $portalWarga->nominalMasuk((float) (clone $kasMasuk)->sum('jumlah'));
        $totalKasKeluar = (float) (clone $kasKeluar)->sum('jumlah');
        $saldoKas = $totalKasMasuk - $totalKasKeluar;

        // Uang masuk by bulan secara akumulasi kumulatif (running total)
        $pemasukanBulanan = KasMasuk::selectRaw("YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, COUNT(*) as frekuensi, SUM(jumlah) as total_nominal")
            ->where('tampil_publik', true)
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal', $tahunDipilih))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc');
        $monthlyIncomeRaw = $pemasukanBulanan->get();

        $runningTotal = 0;
        $uangMasukByBulan = [];
        foreach ($monthlyIncomeRaw as $row) {
            $nominal = $portalWarga->nominalMasuk((float) $row->total_nominal);
            $runningTotal += $nominal;
            $carbon = Carbon::createFromDate($row->tahun, $row->bulan, 1);
            $uangMasukByBulan[] = [
                'periode_key' => sprintf('%04d-%02d', $row->tahun, $row->bulan),
                'bulan_nama' => $carbon->translatedFormat('F Y'),
                'tahun' => $row->tahun,
                'bulan' => $row->bulan,
                'frekuensi' => (int) $row->frekuensi,
                'total_nominal' => $nominal,
                'akumulasi_kumulatif' => $runningTotal,
                'persen_target' => $totalTarget > 0 ? round(($runningTotal / $totalTarget) * 100, 1) : 0,
            ];
        }

        // Tampilkan dari bulan terbaru di posisi atas
        $uangMasukByBulan = array_reverse($uangMasukByBulan);

        // Rekapitulasi uang masuk harian (tetap disediakan jika warga ingin cek rincian harian)
        $uangMasukPerTanggal = KasMasuk::selectRaw('DATE(tanggal) as tgl, COUNT(*) as frekuensi, SUM(jumlah) as total_nominal')
            ->where('tampil_publik', true)
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal', $tahunDipilih))
            ->groupBy('tgl')
            ->orderBy('tgl', 'desc')
            ->take(30)
            ->get();

        $monthlyData = $this->arusKasBulanan($tahunDipilih, $portalWarga);

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

        // Timeline kepengurusan berkas legalitas yang diinput oleh admin
        $timelines = TimelineLegalitas::with('legalitas.penghuni')
            ->where('tampil_publik', true)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Transparent list of expenditures (Kas Keluar)
        $daftarPengeluaran = KasKeluar::with('kategori')
            ->where('tampil_publik', true)
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal', $tahunDipilih))
            ->orderBy('tanggal', 'desc')
            ->take(12)
            ->get();

        // Kas keluar per kategori
        $kasKeluarPerKategori = KasKeluar::selectRaw('kategori_kas_id, SUM(jumlah) as total')
            ->with('kategori')
            ->where('tampil_publik', true)
            ->when($tahunDipilih !== null, fn ($q) => $q->whereYear('tanggal', $tahunDipilih))
            ->groupBy('kategori_kas_id')
            ->get()
            ->map(function ($item) {
                return [
                    'kategori' => $item->kategori->nama_kategori ?? 'Lainnya',
                    'total' => (float) $item->total,
                ];
            });

        $rawLast = max(
            KasMasuk::max('updated_at') ?? date('Y-m-d H:i:s'),
            KasKeluar::max('updated_at') ?? date('Y-m-d H:i:s'),
            Legalitas::max('updated_at') ?? date('Y-m-d H:i:s'),
            TimelineLegalitas::max('updated_at') ?? date('Y-m-d H:i:s'),
            ActivityLog::max('created_at') ?? date('Y-m-d H:i:s')
        );
        $lastUpdated = Carbon::parse($rawLast);

        // Rekam Jejak / Log Aktivitas Admin untuk Transparansi Warga (Immutable & Append-Only)
        $adminLogs = ActivityLog::orderBy('created_at', 'desc')->take(25)->get();
        $totalAdminLogs = ActivityLog::count();

        // Dokumen Kearsipan Perumahan yang bersifat Publik untuk Warga
        $arsipPublik = $portal['kearsipan']
            ? \App\Models\ArsipDokumen::where('is_public', true)->orderBy('created_at', 'desc')->get()
            : collect();

        return view('public.report', compact(
            'perumahan',
            'totalUnit', 'totalPenghuni', 'penghuniLunas', 'penghuniAktif',
            'totalTarget', 'totalTerkumpul', 'progressCollecting',
            'totalKasMasuk', 'totalKasKeluar', 'saldoKas',
            'uangMasukByBulan',
            'uangMasukPerTanggal',
            'monthlyData', 'legalitasStats',
            'timelines', 'daftarPengeluaran', 'kasKeluarPerKategori',
            'adminLogs', 'totalAdminLogs',
            'arsipPublik',
            'lastUpdated',
            'tahunDipilih',
            'daftarTahun',
            'portal',
            'tabAwal'
        ));
    }

    public function timelinePage(PortalWarga $portalWarga)
    {
        $perumahan = Perumahan::first() ?? new Perumahan([
            'nama_perumahan' => 'Griya Harmoni Residence',
            'alamat' => 'Jl. Raya Harmoni No. 88, Cibinong, Kab. Bogor',
            'total_unit' => 40
        ]);

        $timelines = TimelineLegalitas::with('legalitas.penghuni')
            ->where('tampil_publik', true)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $rawLast = max(
            Legalitas::max('updated_at') ?? date('Y-m-d H:i:s'),
            TimelineLegalitas::max('updated_at') ?? date('Y-m-d H:i:s')
        );
        $lastUpdated = Carbon::parse($rawLast);
        $tampilTimeline = $portalWarga->bagian('timeline');
        if (!$tampilTimeline) {
            $timelines = collect();
        }

        return view('public.timeline', compact('perumahan', 'timelines', 'lastUpdated', 'tampilTimeline'));
    }

    public function apiData(Request $request, PortalWarga $portalWarga)
    {
        $tahunDipilih = $this->tahunDariRequest($request);
        $ringkasAngsuran = $this->ringkasanAngsuran($tahunDipilih);

        $kasMasuk = KasMasuk::query()->where('tampil_publik', true);
        $kasKeluar = KasKeluar::query()->where('tampil_publik', true);
        if ($tahunDipilih !== null) {
            $kasMasuk->whereYear('tanggal', $tahunDipilih);
            $kasKeluar->whereYear('tanggal', $tahunDipilih);
        }
        $totalKasMasuk = $portalWarga->nominalMasuk((float) $kasMasuk->sum('jumlah'));
        $totalKasKeluar = (float) $kasKeluar->sum('jumlah');
        $saldoKas = $totalKasMasuk - $totalKasKeluar;

        return response()->json([
            'totalKasMasuk' => $totalKasMasuk,
            'totalKasKeluar' => $totalKasKeluar,
            'saldoKas' => $saldoKas,
            'totalTerkumpul' => $ringkasAngsuran['terbayar'],
            'progressCollecting' => $ringkasAngsuran['persen'],
            'penghuniLunas' => Penghuni::where('status', 'lunas')->count(),
            'penghuniAktif' => Penghuni::where('status', 'aktif')->count(),
            'lastUpdated' => now()->translatedFormat('d M Y H:i:s'),
        ]);
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
        if ($tahun < 1990 || $tahun > (int) now()->year + 5) {
            return (int) now()->year;
        }

        return $tahun;
    }

    private function daftarTahun()
    {
        $dariMasuk = KasMasuk::query()->whereNotNull('tanggal')->selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun');
        $dariKeluar = KasKeluar::query()->whereNotNull('tanggal')->selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun');
        $dariBayar = Angsuran::query()->whereNotNull('tanggal_bayar')->selectRaw('YEAR(tanggal_bayar) as tahun')->distinct()->pluck('tahun');

        return collect([$dariMasuk, $dariKeluar, $dariBayar, [(int) now()->year]])
            ->flatten()
            ->map(fn ($tahun) => (int) $tahun)
            ->filter(fn ($tahun) => $tahun >= 1990)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function ringkasanAngsuran(?int $tahun): array
    {
        $terbayarQuery = Angsuran::query()->where('jumlah_bayar', '>', 0)->whereNotNull('tanggal_bayar');
        $targetQuery = Angsuran::query();
        if ($tahun !== null) {
            $terbayarQuery->whereYear('tanggal_bayar', $tahun);
            $targetQuery->whereYear('tanggal_jatuh_tempo', $tahun);
        }

        $terbayar = (float) $terbayarQuery->sum('jumlah_bayar');
        $target = (float) $targetQuery->sum('jumlah_angsuran');

        return [
            'terbayar' => $terbayar,
            'target' => $target,
            'persen' => $target > 0 ? round(($terbayar / $target) * 100, 1) : 0,
        ];
    }

    private function arusKasBulanan(?int $tahun, PortalWarga $portalWarga): array
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
            $masuk = (float) KasMasuk::where('tampil_publik', true)
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');
            $keluar = (float) KasKeluar::where('tampil_publik', true)
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');
            $data[] = [
                'bulan' => $date->translatedFormat($tahun === null ? 'M Y' : 'M'),
                'masuk' => $portalWarga->nominalMasuk($masuk),
                'keluar' => $keluar,
            ];
        }

        return $data;
    }
}
