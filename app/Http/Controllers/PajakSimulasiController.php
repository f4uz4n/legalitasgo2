<?php

namespace App\Http\Controllers;

use App\Models\SimulasiBphtb;
use Illuminate\Http\Request;

class PajakSimulasiController extends Controller
{
    public function index()
    {
        $this->hanyaPengelola();

        return $this->tampil();
    }

    public function store(Request $request)
    {
        $this->hanyaPengelola();

        SimulasiBphtb::create($this->dataHitung($request) + [
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('pajak.simulasi')->with('success', 'Hasil perhitungan disimpan.');
    }

    public function edit(SimulasiBphtb $simulasi)
    {
        $this->hanyaPengelola();

        return $this->tampil($simulasi);
    }

    public function update(Request $request, SimulasiBphtb $simulasi)
    {
        $this->hanyaPengelola();

        $simulasi->update($this->dataHitung($request));

        return redirect()->route('pajak.simulasi')->with('success', 'Hasil perhitungan diperbarui.');
    }

    public function destroy(SimulasiBphtb $simulasi)
    {
        $this->hanyaPengelola();

        $simulasi->delete();

        return redirect()->route('pajak.simulasi')->with('success', 'Hasil perhitungan dihapus.');
    }

    private function hanyaPengelola(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Simulasi BPHTB hanya untuk pengelola.');
    }

    private function tampil(?SimulasiBphtb $ubah = null)
    {
        $daftar = SimulasiBphtb::query()->orderBy('id')->get();
        $totalPenjual = (float) $daftar->sum('pajak_penjual');
        $totalPembeli = (float) $daftar->sum('pajak_pembeli');

        return view('pajak.simulasi', compact('daftar', 'totalPenjual', 'totalPembeli', 'ubah'));
    }

    private function dataHitung(Request $request): array
    {
        $mentah = preg_replace('/\D/', '', (string) $request->input('nilai_jual', ''));
        $request->merge([
            'nilai_jual' => $mentah === '' ? null : $mentah,
            'nama_penjual' => trim((string) $request->input('nama_penjual', '')),
        ]);

        $data = $request->validate([
            'nama_penjual' => ['required', 'string', 'max:255'],
            'nilai_jual' => ['required', 'numeric', 'min:0'],
            'persentase' => ['required', 'numeric', 'min:0', 'max:100'],
            'persentase_pembeli' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $jual = round((float) $data['nilai_jual']);
        $persenPenjual = round((float) $data['persentase'], 2);
        $persenPembeli = round((float) $data['persentase_pembeli'], 2);
        $dasarPembeli = max(0, $jual - SimulasiBphtb::POTONGAN_PEMBELI);
        $pajakPenjual = round($jual * $persenPenjual / 100);
        $pajakPembeli = round($dasarPembeli * $persenPembeli / 100);

        return [
            'nama_penjual' => $data['nama_penjual'],
            'nilai_jual' => $jual,
            'njop' => 0,
            'npoptkp' => SimulasiBphtb::POTONGAN_PEMBELI,
            'persentase' => $persenPenjual,
            'persentase_pembeli' => $persenPembeli,
            'npop' => $jual,
            'dasar_pengenaan' => $dasarPembeli,
            'jumlah_pajak' => $pajakPenjual + $pajakPembeli,
            'pajak_penjual' => $pajakPenjual,
            'pajak_pembeli' => $pajakPembeli,
        ];
    }
}
