<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\KasMasuk;
use App\Models\KasKeluar;
use App\Models\Legalitas;
use App\Models\Penghuni;
use App\Models\Perumahan;
use App\Models\TimelineLegalitas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportPdfController extends Controller
{
    /**
     * Tampilkan form konfigurasi laporan PDF (nama penandatangan, periode, dll)
     */
    public function form(Request $request)
    {
        $perumahan = Perumahan::first();

        // Daftar bulan yang tersedia dari data KasMasuk
        $bulanTersedia = KasMasuk::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan_key, DATE_FORMAT(tanggal, '%M %Y') as bulan_label")
            ->groupBy('bulan_key', 'bulan_label')
            ->orderBy('bulan_key', 'desc')
            ->get();

        return view('report-pdf.form', compact('perumahan', 'bulanTersedia'));
    }

    /**
     * Generate halaman cetak laporan PDF berdasarkan parameter form
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'ketua'                => ['nullable', 'string', 'max:150'],
            'bendahara_1'          => ['nullable', 'string', 'max:150'],
            'bendahara_2'          => ['nullable', 'string', 'max:150'],
            'anggota'              => ['nullable', 'string', 'max:500'],
            'periode_dari'         => ['nullable', 'date'],
            'periode_sampai'       => ['nullable', 'date'],
            'jenis_laporan'        => ['nullable', 'string'],
            'kota_tanda_tangan'    => ['nullable', 'string', 'max:100'],
            'tanggal_tanda_tangan' => ['nullable', 'date'],
        ]);

        $perumahan    = Perumahan::first();
        $jenis        = $request->get('jenis_laporan', 'lengkap');
        $periodeDari  = $request->filled('periode_dari')  ? Carbon::parse($request->periode_dari)  : null;
        $periodeSampai = $request->filled('periode_sampai') ? Carbon::parse($request->periode_sampai) : null;

        // ── Penghuni Metrics ───────────────────────────────────────────────
        $totalPenghuni    = Penghuni::count();
        $penghuniAktif    = Penghuni::where('status', 'aktif')->count();
        $penghuniLunas    = Penghuni::where('status', 'lunas')->count();
        $totalNilaiRumah  = Penghuni::sum('harga_rumah');
        $totalUangMuka    = Penghuni::sum('uang_muka');

        // ── Kas Masuk ──────────────────────────────────────────────────────
        $kasMasukQuery = KasMasuk::query();
        if ($periodeDari)   $kasMasukQuery->whereDate('tanggal', '>=', $periodeDari);
        if ($periodeSampai) $kasMasukQuery->whereDate('tanggal', '<=', $periodeSampai);
        $totalKasMasuk = $kasMasukQuery->sum('jumlah');

        // Rincian uang masuk by bulan
        $uangMasukByBulan = KasMasuk::selectRaw("YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, COUNT(*) as frekuensi, SUM(jumlah) as total_nominal")
            ->when($periodeDari,   fn($q) => $q->whereDate('tanggal', '>=', $periodeDari))
            ->when($periodeSampai, fn($q) => $q->whereDate('tanggal', '<=', $periodeSampai))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get()
            ->map(function ($row) {
                $carbon = Carbon::createFromDate($row->tahun, $row->bulan, 1);
                return [
                    'bulan_nama'    => $carbon->translatedFormat('F Y'),
                    'frekuensi'     => (int) $row->frekuensi,
                    'total_nominal' => (float) $row->total_nominal,
                ];
            });

        // ── Kas Keluar ─────────────────────────────────────────────────────
        $kasKeluarQuery = KasKeluar::query();
        if ($periodeDari)   $kasKeluarQuery->whereDate('tanggal', '>=', $periodeDari);
        if ($periodeSampai) $kasKeluarQuery->whereDate('tanggal', '<=', $periodeSampai);
        $totalKasKeluar = $kasKeluarQuery->sum('jumlah');
        $saldoKas       = $totalKasMasuk - $totalKasKeluar;

        // Rincian kas keluar per kategori
        $kasKeluarPerKategori = KasKeluar::selectRaw('kategori_kas_id, SUM(jumlah) as total')
            ->with('kategori')
            ->when($periodeDari,   fn($q) => $q->whereDate('tanggal', '>=', $periodeDari))
            ->when($periodeSampai, fn($q) => $q->whereDate('tanggal', '<=', $periodeSampai))
            ->groupBy('kategori_kas_id')
            ->get()
            ->map(fn($item) => [
                'kategori' => $item->kategori->nama_kategori ?? 'Lainnya',
                'total'    => (float) $item->total,
            ]);

        // Rincian kas keluar (detail rows)
        $kasKeluarDetail = KasKeluar::with('kategori')
            ->when($periodeDari,   fn($q) => $q->whereDate('tanggal', '>=', $periodeDari))
            ->when($periodeSampai, fn($q) => $q->whereDate('tanggal', '<=', $periodeSampai))
            ->orderBy('tanggal', 'asc')
            ->get();

        // ── Angsuran / Cicilan ─────────────────────────────────────────────
        $totalAngsuranTerbayar = Angsuran::where('status', 'lunas')
            ->when($periodeDari,   fn($q) => $q->whereDate('tanggal_bayar', '>=', $periodeDari))
            ->when($periodeSampai, fn($q) => $q->whereDate('tanggal_bayar', '<=', $periodeSampai))
            ->sum('jumlah_bayar');

        $totalTerkumpul    = $totalUangMuka + $totalAngsuranTerbayar;
        $progressCollecting = $totalNilaiRumah > 0 ? round(($totalTerkumpul / $totalNilaiRumah) * 100, 1) : 0;

        // ── Legalitas Summary ──────────────────────────────────────────────
        $legalitasStats = [
            'total'          => Legalitas::count(),
            'belum_proses'   => Legalitas::where('status', 'belum_proses')->count(),
            'pengajuan'      => Legalitas::where('status', 'pengajuan')->count(),
            'verifikasi'     => Legalitas::where('status', 'verifikasi')->count(),
            'proses_bpn'     => Legalitas::where('status', 'proses_bpn')->count(),
            'proses_notaris' => Legalitas::where('status', 'proses_notaris')->count(),
            'selesai'        => Legalitas::where('status', 'selesai')->count(),
        ];

        // ── Data Penghuni Lengkap ──────────────────────────────────────────
        $daftarPenghuni = Penghuni::with(['angsuran'])->orderBy('nama_penghuni')->get();

        // ── Data Angsuran yang Sudah Dibayar ──────────────────────────────
        $angsuranByPenghuni = Angsuran::with('penghuni')
            ->where('status', 'lunas')
            ->when($periodeDari,   fn($q) => $q->whereDate('tanggal_bayar', '>=', $periodeDari))
            ->when($periodeSampai, fn($q) => $q->whereDate('tanggal_bayar', '<=', $periodeSampai))
            ->orderBy('tanggal_bayar', 'asc')
            ->get();

        // ── Penandatangan ──────────────────────────────────────────────────
        $ketua       = $request->get('ketua', '');
        $bendahara1  = $request->get('bendahara_1', '');
        $bendahara2  = $request->get('bendahara_2', '');
        $anggotaRaw  = $request->get('anggota', '');
        $anggotaList = array_filter(array_map('trim', explode("\n", $anggotaRaw)));
        $kotaTtd     = $request->get('kota_tanda_tangan', 'Bogor');
        $tanggalTtd  = $request->filled('tanggal_tanda_tangan')
            ? Carbon::parse($request->tanggal_tanda_tangan)->translatedFormat('d F Y')
            : Carbon::now()->translatedFormat('d F Y');

        // Label periode laporan
        $labelPeriode = 'Semua Periode';
        if ($periodeDari && $periodeSampai) {
            $labelPeriode = $periodeDari->translatedFormat('d F Y') . ' s/d ' . $periodeSampai->translatedFormat('d F Y');
        } elseif ($periodeDari) {
            $labelPeriode = 'Mulai ' . $periodeDari->translatedFormat('d F Y');
        } elseif ($periodeSampai) {
            $labelPeriode = 'Sampai ' . $periodeSampai->translatedFormat('d F Y');
        }

        return view('report-pdf.preview', compact(
            'perumahan', 'jenis', 'labelPeriode',
            'totalPenghuni', 'penghuniAktif', 'penghuniLunas',
            'totalNilaiRumah', 'totalUangMuka', 'totalAngsuranTerbayar',
            'totalTerkumpul', 'progressCollecting',
            'totalKasMasuk', 'totalKasKeluar', 'saldoKas',
            'uangMasukByBulan', 'kasKeluarPerKategori', 'kasKeluarDetail',
            'legalitasStats', 'daftarPenghuni', 'angsuranByPenghuni',
            'ketua', 'bendahara1', 'bendahara2', 'anggotaList',
            'kotaTtd', 'tanggalTtd'
        ));
    }
}
