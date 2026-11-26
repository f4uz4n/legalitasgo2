<?php

namespace App\Http\Controllers;

use App\Models\KasMasuk;
use App\Models\KasKeluar;
use App\Models\KategoriKas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'tampil_publik' => 'nullable|boolean',
        ]);

        if ($request->hasFile('bukti_file')) {
            $validated['bukti_file'] = $request->file('bukti_file')->store('bukti-kas', 'public');
        }

        $validated['created_by'] = auth()->id();
        $validated['tampil_publik'] = $request->boolean('tampil_publik');
        KasMasuk::create($validated);

        return redirect()->route('kas.index', ['bulan' => Carbon::parse($validated['tanggal'])->format('Y-m')])->with('success', 'Kas masuk berhasil ditambahkan.');
    }

    public function updateMasuk(Request $request, KasMasuk $kasMasuk)
    {
        $validated = $this->validasiMasuk($request);
        $validated['tampil_publik'] = $request->boolean('tampil_publik');
        $validated['bukti_file'] = $this->simpanBukti($request, $kasMasuk->bukti_file);
        $kasMasuk->update($validated);

        return redirect()->route('kas.index', ['bulan' => $request->input('bulan', Carbon::parse($validated['tanggal'])->format('Y-m'))])
            ->with('success', 'Kas masuk berhasil diperbarui.');
    }

    public function buktiMasuk(KasMasuk $kasMasuk)
    {
        return $this->tampilkanBukti($kasMasuk->bukti_file);
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
            'tampil_publik' => 'nullable|boolean',
        ]);

        if ($request->hasFile('bukti_file')) {
            $validated['bukti_file'] = $request->file('bukti_file')->store('bukti-kas', 'public');
        }

        $validated['created_by'] = auth()->id();
        $validated['tampil_publik'] = $request->boolean('tampil_publik');
        KasKeluar::create($validated);

        return redirect()->route('kas.index', ['bulan' => Carbon::parse($validated['tanggal'])->format('Y-m')])->with('success', 'Kas keluar berhasil ditambahkan.');
    }

    public function updateKeluar(Request $request, KasKeluar $kasKeluar)
    {
        $validated = $this->validasiKeluar($request);
        $validated['tampil_publik'] = $request->boolean('tampil_publik');
        $validated['bukti_file'] = $this->simpanBukti($request, $kasKeluar->bukti_file);
        $kasKeluar->update($validated);

        return redirect()->route('kas.index', ['bulan' => $request->input('bulan', Carbon::parse($validated['tanggal'])->format('Y-m'))])
            ->with('success', 'Kas keluar berhasil diperbarui.');
    }

    public function buktiKeluar(KasKeluar $kasKeluar)
    {
        return $this->tampilkanBukti($kasKeluar->bukti_file);
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

    public function publikMasuk(KasMasuk $kasMasuk)
    {
        $kasMasuk->tampil_publik = !$kasMasuk->tampil_publik;
        $kasMasuk->save();

        return redirect()->back()->with('success', $kasMasuk->tampil_publik
            ? 'Kas masuk ditampilkan di portal warga.'
            : 'Kas masuk disembunyikan dari portal warga.');
    }

    public function publikKeluar(KasKeluar $kasKeluar)
    {
        $kasKeluar->tampil_publik = !$kasKeluar->tampil_publik;
        $kasKeluar->save();

        return redirect()->back()->with('success', $kasKeluar->tampil_publik
            ? 'Kas keluar ditampilkan di portal warga.'
            : 'Kas keluar disembunyikan dari portal warga.');
    }

    public function destroyMasuk(KasMasuk $kasMasuk)
    {
        $this->hapusBuktiSendiri($kasMasuk->bukti_file);
        $kasMasuk->delete();
        return redirect()->route('kas.index')->with('success', 'Kas masuk berhasil dihapus.');
    }

    public function destroyKeluar(KasKeluar $kasKeluar)
    {
        $this->hapusBuktiSendiri($kasKeluar->bukti_file);
        $kasKeluar->delete();
        return redirect()->route('kas.index')->with('success', 'Kas keluar berhasil dihapus.');
    }

    private function validasiMasuk(Request $request): array
    {
        return $request->validate([
            'kategori_kas_id' => 'required|exists:kategori_kas,id',
            'penghuni_id' => 'nullable|exists:penghuni,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'metode_bayar' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
    }

    private function validasiKeluar(Request $request): array
    {
        return $request->validate([
            'kategori_kas_id' => 'required|exists:kategori_kas,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'penerima' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
    }

    private function simpanBukti(Request $request, ?string $lama): ?string
    {
        if (!$request->hasFile('bukti_file')) {
            return $lama;
        }

        $this->hapusBuktiSendiri($lama);

        return $request->file('bukti_file')->store('bukti-kas', 'public');
    }

    private function hapusBuktiSendiri(?string $path): void
    {
        if ($path && str_starts_with($path, 'bukti-kas/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function tampilkanBukti(?string $path)
    {
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}
