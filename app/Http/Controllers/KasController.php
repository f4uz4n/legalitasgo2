<?php

namespace App\Http\Controllers;

use App\Models\KasMasuk;
use App\Models\KasKeluar;
use App\Models\KategoriKas;
use App\Models\Penghuni;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KasController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('jenis', 'semua');
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));

        $year = Carbon::parse($bulan . '-01')->year;
        $month = Carbon::parse($bulan . '-01')->month;

        $kasMasuk = KasMasuk::with(['kategori', 'penghuni'])
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal', 'desc')
            ->get();

        $kasKeluar = KasKeluar::with('kategori')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalMasuk = $kasMasuk->sum('jumlah');
        $totalKeluar = $kasKeluar->sum('jumlah');
        $saldo = $totalMasuk - $totalKeluar;

        // All-time totals
        $allTimeMasuk = KasMasuk::sum('jumlah');
        $allTimeKeluar = KasKeluar::sum('jumlah');
        $allTimeSaldo = $allTimeMasuk - $allTimeKeluar;

        $kategoriMasuk = KategoriKas::where('jenis', 'masuk')->get();
        $kategoriKeluar = KategoriKas::where('jenis', 'keluar')->get();

        return view('kas.index', compact(
            'kasMasuk', 'kasKeluar', 'totalMasuk', 'totalKeluar', 'saldo',
            'allTimeMasuk', 'allTimeKeluar', 'allTimeSaldo',
            'bulan', 'filter', 'kategoriMasuk', 'kategoriKeluar'
        ));
    }

    public function storeMasuk(Request $request)
    {
        $validated = $request->validate([
            'kategori_kas_id' => 'required|exists:kategori_kas,id',
            'penghuni_id' => 'nullable|exists:penghuni,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'metode_bayar' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('bukti_file')) {
            $validated['bukti_file'] = $request->file('bukti_file')->store('bukti-kas', 'public');
        }

        $validated['created_by'] = auth()->id();
        KasMasuk::create($validated);

        return redirect()->route('kas.index')->with('success', 'Kas masuk berhasil ditambahkan.');
    }

    public function storeKeluar(Request $request)
    {
        $validated = $request->validate([
            'kategori_kas_id' => 'required|exists:kategori_kas,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'penerima' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('bukti_file')) {
            $validated['bukti_file'] = $request->file('bukti_file')->store('bukti-kas', 'public');
        }

        $validated['created_by'] = auth()->id();
        KasKeluar::create($validated);

        return redirect()->route('kas.index')->with('success', 'Kas keluar berhasil ditambahkan.');
    }

    public function storeKategori(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'jenis' => 'required|in:masuk,keluar',
            'keterangan' => 'nullable|string',
        ]);

        KategoriKas::create($validated);
        return redirect()->route('kas.index')->with('success', 'Kategori kas berhasil ditambahkan.');
    }

    public function destroyMasuk(KasMasuk $kasMasuk)
    {
        $kasMasuk->delete();
        return redirect()->route('kas.index')->with('success', 'Kas masuk berhasil dihapus.');
    }

    public function destroyKeluar(KasKeluar $kasKeluar)
    {
        $kasKeluar->delete();
        return redirect()->route('kas.index')->with('success', 'Kas keluar berhasil dihapus.');
    }
}
