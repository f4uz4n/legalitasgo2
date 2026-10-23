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
    public function index()
    {
        $user = auth()->user();
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

            return view('warga.dashboard', compact('penghuni'));
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
        $totalTerkumpul = $totalUangMuka + $totalAngsuranTerbayar;
        $persenTerkumpul = $totalNilaiRumah > 0 ? round(($totalTerkumpul / $totalNilaiRumah) * 100, 1) : 0;
        $totalSisaPiutang = Penghuni::sum('sisa_angsuran');

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

        // Monthly cash flow (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');

            $masuk = KasMasuk::whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');

            $keluar = KasKeluar::whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->sum('jumlah');

            $monthlyData[] = [
                'bulan' => $monthLabel,
                'masuk' => (float) $masuk,
                'keluar' => (float) $keluar,
            ];
        }

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
            'recentActivityLogs'
        ));
    }
}
