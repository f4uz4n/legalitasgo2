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

        $piutang = Penghuni::query();
        if ($user && $user->isWarga()) {
            $piutang->where('id', $user->penghuni?->id ?? 0);
        }
        $totalSisa = (float) $piutang->sum('sisa_angsuran');
        $totalTerbayar = (float) (clone $baseStats)->sum('jumlah_bayar');

        $stats = [
            'total_tagihan' => $totalSisa + $totalTerbayar,
            'total_terbayar' => $totalTerbayar,
            'total_sisa' => $totalSisa,
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

        if ($request->filled('nama')) {
            $nama = $request->nama;
            $query->whereHas('penghuni', function ($q) use ($nama) {
                $q->where('nama_lengkap', 'like', '%' . $nama . '%')
                  ->orWhere('nik', 'like', '%' . $nama . '%');
            });
        }

        if ($request->filled('rumah')) {
            $rumah = $request->rumah;
            $query->whereHas('penghuni', function ($q) use ($rumah) {
                $q->where('blok_kavling', 'like', '%' . $rumah . '%');
            });
        }

        if ($request->filled('search')) {
            $kata = $request->search;
            $query->whereHas('penghuni', function ($q) use ($kata) {
                $q->where('nama_lengkap', 'like', '%' . $kata . '%')
                  ->orWhere('blok_kavling', 'like', '%' . $kata . '%')
                  ->orWhere('nik', 'like', '%' . $kata . '%');
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

        $riwayat = Angsuran::with(['penghuni']);
        if ($user && $user->isWarga()) {
            $riwayat->where('penghuni_id', $user->penghuni?->id ?? 0);
        }
        $riwayat->where('jumlah_bayar', '>', 0)->whereNotNull('tanggal_bayar');

        if (!empty($tanggalDari) && !empty($tanggalSampai)) {
            $riwayat->whereBetween('tanggal_bayar', [$tanggalDari, $tanggalSampai]);
        } elseif (!empty($tanggalDari)) {
            $riwayat->whereDate('tanggal_bayar', '>=', $tanggalDari);
        } elseif (!empty($tanggalSampai)) {
            $riwayat->whereDate('tanggal_bayar', '<=', $tanggalSampai);
        } elseif (!$isAll && !empty($selectedBulan)) {
            $bagianBulan = explode('-', $selectedBulan);
            if (count($bagianBulan) === 2) {
                $riwayat->whereYear('tanggal_bayar', $bagianBulan[0])
                    ->whereMonth('tanggal_bayar', $bagianBulan[1]);
            }
        }

        if ($request->filled('nama')) {
            $nama = $request->nama;
            $riwayat->whereHas('penghuni', function ($q) use ($nama) {
                $q->where('nama_lengkap', 'like', '%' . $nama . '%')
                    ->orWhere('nik', 'like', '%' . $nama . '%');
            });
        }
        if ($request->filled('rumah')) {
            $rumah = $request->rumah;
            $riwayat->whereHas('penghuni', function ($q) use ($rumah) {
                $q->where('blok_kavling', 'like', '%' . $rumah . '%');
            });
        }
        if ($request->filled('search')) {
            $kata = $request->search;
            $riwayat->whereHas('penghuni', function ($q) use ($kata) {
                $q->where('nama_lengkap', 'like', '%' . $kata . '%')
                    ->orWhere('blok_kavling', 'like', '%' . $kata . '%')
                    ->orWhere('nik', 'like', '%' . $kata . '%');
            });
        }

        $totalNominalRiwayat = (float) (clone $riwayat)->sum('jumlah_bayar');
        $riwayatBayar = $riwayat->orderByDesc('tanggal_bayar')->orderByDesc('id')->paginate(10, ['*'], 'riwayat')->withQueryString();

        return view('pembayaran.index', compact(
            'angsuran',
            'stats',
            'availableMonths',
            'selectedBulan',
            'tanggalDari',
            'tanggalSampai',
            'isAll',
            'sort',
            'riwayatBayar',
            'totalNominalRiwayat'
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
        $statusAwal = $angsuran->status;
        $jatuhTempoLewat = $angsuran->tanggal_jatuh_tempo && $angsuran->tanggal_jatuh_tempo->isPast();

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

        // Update sisa pembayaran, sisa tempo, dan tunggakan pada rekap
        $penghuni = $angsuran->penghuni;
        $penghuni->sisa_angsuran = $penghuni->sisa_angsuran - $validated['jumlah_bayar'];
        if ($penghuni->sisa_angsuran <= 0) {
            $penghuni->sisa_angsuran = 0;
            $penghuni->sisa_tempo = 0;
            $penghuni->tunggakan = null;
            $penghuni->status = 'lunas';
        } elseif ($status === 'lunas' && $statusAwal !== 'lunas') {
            if ((int) $penghuni->sisa_tempo > 0) {
                $penghuni->sisa_tempo = (int) $penghuni->sisa_tempo - 1;
            }
            if (($statusAwal === 'terlambat' || $jatuhTempoLewat) && (int) $penghuni->tunggakan > 0) {
                $sisaTunggakan = (int) $penghuni->tunggakan - 1;
                $penghuni->tunggakan = $sisaTunggakan > 0 ? $sisaTunggakan : null;
            }
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

        $angsuran->load(['penghuni.perumahan', 'kasMasuk', 'pengambilanKwitansi']);
        return view('pembayaran.show', compact('angsuran'));
    }
}
