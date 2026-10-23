<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\KasMasuk;
use App\Models\KategoriKas;
use App\Models\Penghuni;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Angsuran::with(['penghuni.perumahan', 'kasMasuk']);

        if ($user && $user->isWarga()) {
            $penghuniId = $user->penghuni?->id ?? 0;
            $query->where('penghuni_id', $penghuniId);
        }

        // Base query untuk statistik ringkasan
        $baseStats = Angsuran::query();
        if ($user && $user->isWarga()) {
            $baseStats->where('penghuni_id', $user->penghuni?->id ?? 0);
        }

        $now = Carbon::now();
        $today = Carbon::today();

        // Daftar bulan yang tersedia di database untuk dropdown
        $availableMonths = Angsuran::selectRaw("DATE_FORMAT(tanggal_jatuh_tempo, '%Y-%m') as ym")
            ->distinct()
            ->orderBy('ym', 'desc')
            ->pluck('ym');

        // Parameter filter tanggal & bulan
        $isAll = $request->get('periode') === 'all';
        $selectedBulan = $request->get('bulan');
        $tanggalDari = $request->get('tanggal_dari');
        $tanggalSampai = $request->get('tanggal_sampai');

        // DEFAULT: jika tidak memilih 'all', dan tidak mengisi range tanggal serta bulan,
        // maka otomatis defaultkan ke bulan aktif saat ini (now: YYYY-MM)
        if (!$isAll && empty($tanggalDari) && empty($tanggalSampai) && empty($selectedBulan)) {
            $selectedBulan = $now->format('Y-m');
        }

        // Terapkan filter range tanggal jika diisi
        if (!empty($tanggalDari) && !empty($tanggalSampai)) {
            $query->whereBetween('tanggal_jatuh_tempo', [$tanggalDari, $tanggalSampai]);
        } elseif (!empty($tanggalDari)) {
            $query->whereDate('tanggal_jatuh_tempo', '>=', $tanggalDari);
        } elseif (!empty($tanggalSampai)) {
            $query->whereDate('tanggal_jatuh_tempo', '<=', $tanggalSampai);
        } elseif (!empty($selectedBulan)) {
            // Terapkan filter per bulan (YYYY-MM)
            $parts = explode('-', $selectedBulan);
            if (count($parts) === 2) {
                $query->whereYear('tanggal_jatuh_tempo', $parts[0])
                      ->whereMonth('tanggal_jatuh_tempo', $parts[1]);
            }
        }

        $stats = [
            'total_tagihan' => (clone $baseStats)->sum('jumlah_angsuran'),
            'total_terbayar' => (clone $baseStats)->sum('jumlah_bayar'),
            'total_sisa' => max(0, (clone $baseStats)->sum('jumlah_angsuran') - (clone $baseStats)->sum('jumlah_bayar')),
            'count_total' => (clone $baseStats)->count(),
            'count_lunas' => (clone $baseStats)->where('status', 'lunas')->count(),
            'count_belum_bayar' => (clone $baseStats)->where('status', 'belum_bayar')->count(),
            'count_terlambat' => (clone $baseStats)->where('status', 'belum_bayar')->where('tanggal_jatuh_tempo', '<', $today)->count(),
            'count_bulan_ini' => (clone $baseStats)
                ->where('status', 'belum_bayar')
                ->whereMonth('tanggal_jatuh_tempo', $now->month)
                ->whereYear('tanggal_jatuh_tempo', $now->year)
                ->count(),
            'tagihan_bulan_ini' => (clone $baseStats)
                ->whereMonth('tanggal_jatuh_tempo', $now->month)
                ->whereYear('tanggal_jatuh_tempo', $now->year)
                ->sum('jumlah_angsuran'),
        ];

        // Filter status / periode cepat
        if ($request->filled('status')) {
            if ($request->status === 'terlambat') {
                $query->where('status', 'belum_bayar')->where('tanggal_jatuh_tempo', '<', $today);
            } elseif ($request->status === 'bulan_ini') {
                $query->where('status', 'belum_bayar')
                      ->whereMonth('tanggal_jatuh_tempo', $now->month)
                      ->whereYear('tanggal_jatuh_tempo', $now->year);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $query->whereHas('penghuni', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                  ->orWhere('blok_kavling', 'like', '%' . $request->search . '%')
                  ->orWhere('nik', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting: DEFAULTKAN urutkan berdasarkan data record terbaru (id desc)
        $sort = $request->get('sort', 'terbaru');
        if ($sort === 'terbaru' || $sort === 'created_desc') {
            $query->orderBy('id', 'desc');
        } elseif ($sort === 'terlama' || $sort === 'created_asc') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'tempo_asc') {
            $query->orderBy('tanggal_jatuh_tempo', 'asc');
        } elseif ($sort === 'tempo_desc') {
            $query->orderBy('tanggal_jatuh_tempo', 'desc');
        } elseif ($sort === 'ke_asc') {
            $query->orderBy('angsuran_ke', 'asc');
        } elseif ($sort === 'ke_desc') {
            $query->orderBy('angsuran_ke', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $angsuran = $query->paginate(20)->withQueryString();

        return view('pembayaran.index', compact(
            'angsuran',
            'stats',
            'availableMonths',
            'selectedBulan',
            'tanggalDari',
            'tanggalSampai',
            'isAll',
            'sort'
        ));
    }

    public function create(Request $request)
    {
        $angsuran = null;
        if ($request->filled('angsuran_id')) {
            $angsuran = Angsuran::with('penghuni')->findOrFail($request->angsuran_id);
        }

        $penghuniList = Penghuni::where('status', 'aktif')
            ->with(['angsuran' => function ($q) {
                $q->whereIn('status', ['belum_bayar', 'terlambat', 'sebagian'])->orderBy('angsuran_ke');
            }])
            ->get();

        return view('pembayaran.create', compact('penghuniList', 'angsuran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'angsuran_id' => 'required|exists:angsuran,id',
            'jumlah_bayar' => 'required|numeric|min:0',
            'tanggal_bayar' => 'required|date',
            'metode_bayar' => 'nullable|string|max:255',
            'no_referensi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiPath = $request->file('bukti_bayar')->store('bukti-angsuran', 'public');
        }

        $angsuran = Angsuran::with('penghuni')->findOrFail($validated['angsuran_id']);

        // Update angsuran
        $totalBayar = $angsuran->jumlah_bayar + $validated['jumlah_bayar'];
        $status = 'sebagian';
        if ($totalBayar >= $angsuran->jumlah_angsuran) {
            $status = 'lunas';
        }

        // Check if overdue
        if ($angsuran->tanggal_jatuh_tempo->isPast() && $status !== 'lunas') {
            $status = 'terlambat';
        }

        $angsuranData = [
            'jumlah_bayar' => $totalBayar,
            'tanggal_bayar' => $validated['tanggal_bayar'],
            'status' => $status,
            'keterangan' => $validated['keterangan'],
        ];

        if ($buktiPath) {
            $angsuranData['bukti_bayar'] = $buktiPath;
        }

        $angsuran->update($angsuranData);

        // Create kas masuk record
        $kategoriAngsuran = KategoriKas::firstOrCreate(
            ['nama_kategori' => 'Angsuran Rumah', 'jenis' => 'masuk'],
            ['keterangan' => 'Pembayaran angsuran rumah dari penghuni']
        );

        KasMasuk::create([
            'kategori_kas_id' => $kategoriAngsuran->id,
            'penghuni_id' => $angsuran->penghuni_id,
            'angsuran_id' => $angsuran->id,
            'tanggal' => $validated['tanggal_bayar'],
            'jumlah' => $validated['jumlah_bayar'],
            'metode_bayar' => $validated['metode_bayar'],
            'no_referensi' => $validated['no_referensi'],
            'keterangan' => 'Pembayaran angsuran ke-' . $angsuran->angsuran_ke . ' - ' . $angsuran->penghuni->nama_lengkap,
            'bukti_file' => $buktiPath,
            'created_by' => auth()->id(),
        ]);

        // Update sisa angsuran penghuni
        $penghuni = $angsuran->penghuni;
        $penghuni->sisa_angsuran = $penghuni->sisa_angsuran - $validated['jumlah_bayar'];
        if ($penghuni->sisa_angsuran <= 0) {
            $penghuni->sisa_angsuran = 0;
            $penghuni->status = 'lunas';
        }
        $penghuni->save();

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran angsuran berhasil dicatat.');
    }

    public function show(Angsuran $angsuran)
    {
        $user = auth()->user();
        if ($user && $user->isWarga() && $angsuran->penghuni_id !== $user->penghuni?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat rincian pembayaran ini.');
        }

        $angsuran->load(['penghuni.perumahan', 'kasMasuk']);
        return view('pembayaran.show', compact('angsuran'));
    }
}
