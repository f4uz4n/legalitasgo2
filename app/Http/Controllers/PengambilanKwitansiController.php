<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\PengambilanKwitansi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengambilanKwitansiController extends Controller
{
    public function index(Request $request)
    {
        $riwayat = $this->queryRiwayat($request)
            ->orderByDesc('tanggal_ambil')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $siapDiambil = Angsuran::with('penghuni')
            ->where('jumlah_bayar', '>', 0)
            ->whereDoesntHave('pengambilanKwitansi')
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();

        $dipilih = $request->integer('angsuran');

        return view('kwitansi.index', compact('riwayat', 'siapDiambil', 'dipilih'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'angsuran_id' => ['required', 'exists:angsuran,id', Rule::unique('pengambilan_kwitansi', 'angsuran_id')],
            'tanggal_ambil' => ['required', 'date'],
            'nama_pengambil' => ['required', 'string', 'max:255'],
            'hubungan' => ['required', Rule::in(array_keys(PengambilanKwitansi::HUBUNGAN))],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'angsuran_id.unique' => 'Kwitansi cicilan ini sudah dicatat pengambilannya.',
            'angsuran_id.required' => 'Pilih kwitansi pembayaran yang diambil.',
        ]);

        $angsuran = Angsuran::findOrFail($data['angsuran_id']);
        if ((float) $angsuran->jumlah_bayar <= 0) {
            return back()->withInput()->withErrors([
                'angsuran_id' => 'Kwitansi hanya bisa dicatat untuk cicilan yang sudah ada pembayarannya.',
            ]);
        }

        PengambilanKwitansi::create([
            'angsuran_id' => $angsuran->id,
            'penghuni_id' => $angsuran->penghuni_id,
            'tanggal_ambil' => $data['tanggal_ambil'],
            'nama_pengambil' => $data['nama_pengambil'],
            'hubungan' => $data['hubungan'],
            'no_hp' => $data['no_hp'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'dicatat_oleh' => auth()->id(),
        ]);

        return redirect()->route('kwitansi.index')->with('success', 'Pengambilan kwitansi berhasil dicatat.');
    }

    public function destroy(PengambilanKwitansi $pengambilan)
    {
        $pengambilan->delete();

        return redirect()->route('kwitansi.index')->with('success', 'Catatan pengambilan kwitansi dihapus.');
    }

    private function queryRiwayat(Request $request)
    {
        $query = PengambilanKwitansi::with(['angsuran', 'penghuni', 'petugas']);

        if ($request->filled('nama')) {
            $nama = $request->nama;
            $query->where(function ($q) use ($nama) {
                $q->where('nama_pengambil', 'like', '%' . $nama . '%')
                    ->orWhereHas('penghuni', function ($p) use ($nama) {
                        $p->where('nama_lengkap', 'like', '%' . $nama . '%');
                    });
            });
        }

        if ($request->filled('rumah')) {
            $rumah = $request->rumah;
            $query->whereHas('penghuni', function ($p) use ($rumah) {
                $p->where('blok_kavling', 'like', '%' . $rumah . '%');
            });
        }

        return $query;
    }
}
