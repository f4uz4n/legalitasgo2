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
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicReportController extends Controller
{
    public function index()
    {
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

        // Collecting progress
        $totalTarget = Penghuni::sum('harga_rumah');
        $totalUangMuka = Penghuni::sum('uang_muka');
        $totalAngsuranTerbayar = Angsuran::where('status', 'lunas')->sum('jumlah_bayar');
        $totalTerkumpul = $totalUangMuka + $totalAngsuranTerbayar;
        $progressCollecting = $totalTarget > 0 ? round(($totalTerkumpul / $totalTarget) * 100, 1) : 0;

        // Cash flow summary
        $totalKasMasuk = KasMasuk::sum('jumlah');
        $totalKasKeluar = KasKeluar::sum('jumlah');
        $saldoKas = $totalKasMasuk - $totalKasKeluar;

        // Uang masuk by bulan secara akumulasi kumulatif (running total)
        $monthlyIncomeRaw = KasMasuk::selectRaw("YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, COUNT(*) as frekuensi, SUM(jumlah) as total_nominal")
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        $runningTotal = 0;
        $uangMasukByBulan = [];
        foreach ($monthlyIncomeRaw as $row) {
            $runningTotal += (float) $row->total_nominal;
            $carbon = Carbon::createFromDate($row->tahun, $row->bulan, 1);
            $uangMasukByBulan[] = [
                'periode_key' => sprintf('%04d-%02d', $row->tahun, $row->bulan),
                'bulan_nama' => $carbon->translatedFormat('F Y'),
                'tahun' => $row->tahun,
                'bulan' => $row->bulan,
                'frekuensi' => (int) $row->frekuensi,
                'total_nominal' => (float) $row->total_nominal,
                'akumulasi_kumulatif' => $runningTotal,
                'persen_target' => $totalTarget > 0 ? round(($runningTotal / $totalTarget) * 100, 1) : 0,
            ];
        }

        // Tampilkan dari bulan terbaru di posisi atas
        $uangMasukByBulan = array_reverse($uangMasukByBulan);

        // Rekapitulasi uang masuk harian (tetap disediakan jika warga ingin cek rincian harian)
        $uangMasukPerTanggal = KasMasuk::selectRaw('DATE(tanggal) as tgl, COUNT(*) as frekuensi, SUM(jumlah) as total_nominal')
            ->groupBy('tgl')
            ->orderBy('tgl', 'desc')
            ->take(30)
            ->get();

        // Monthly chart data (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $masuk = KasMasuk::whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');
            $keluar = KasKeluar::whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');

            $monthlyData[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'masuk' => (float) $masuk,
                'keluar' => (float) $keluar,
            ];
        }

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
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Transparent list of expenditures (Kas Keluar)
        $daftarPengeluaran = KasKeluar::with('kategori')
            ->orderBy('tanggal', 'desc')
            ->take(12)
            ->get();

        // Kas keluar per kategori
        $kasKeluarPerKategori = KasKeluar::selectRaw('kategori_kas_id, SUM(jumlah) as total')
            ->with('kategori')
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
        $arsipPublik = \App\Models\ArsipDokumen::where('is_public', true)->orderBy('created_at', 'desc')->get();

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
            'lastUpdated'
        ));
    }

    public function timelinePage()
    {
        $perumahan = Perumahan::first() ?? new Perumahan([
            'nama_perumahan' => 'Griya Harmoni Residence',
            'alamat' => 'Jl. Raya Harmoni No. 88, Cibinong, Kab. Bogor',
            'total_unit' => 40
        ]);

        $timelines = TimelineLegalitas::with('legalitas.penghuni')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $rawLast = max(
            Legalitas::max('updated_at') ?? date('Y-m-d H:i:s'),
            TimelineLegalitas::max('updated_at') ?? date('Y-m-d H:i:s')
        );
        $lastUpdated = Carbon::parse($rawLast);

        return view('public.timeline', compact('perumahan', 'timelines', 'lastUpdated'));
    }

    public function apiData()
    {
        // Realtime API endpoint
        $totalKasMasuk = KasMasuk::sum('jumlah');
        $totalKasKeluar = KasKeluar::sum('jumlah');
        $saldoKas = $totalKasMasuk - $totalKasKeluar;

        $totalTarget = Penghuni::sum('harga_rumah');
        $totalUangMuka = Penghuni::sum('uang_muka');
        $totalAngsuranTerbayar = Angsuran::where('status', 'lunas')->sum('jumlah_bayar');
        $totalTerkumpul = $totalUangMuka + $totalAngsuranTerbayar;
        $progressCollecting = $totalTarget > 0 ? round(($totalTerkumpul / $totalTarget) * 100, 1) : 0;

        return response()->json([
            'totalKasMasuk' => (float) $totalKasMasuk,
            'totalKasKeluar' => (float) $totalKasKeluar,
            'saldoKas' => (float) $saldoKas,
            'totalTerkumpul' => (float) $totalTerkumpul,
            'progressCollecting' => $progressCollecting,
            'penghuniLunas' => Penghuni::where('status', 'lunas')->count(),
            'penghuniAktif' => Penghuni::where('status', 'aktif')->count(),
            'lastUpdated' => now()->translatedFormat('d M Y H:i:s'),
        ]);
    }
}
