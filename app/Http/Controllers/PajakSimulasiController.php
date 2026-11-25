<?php

namespace App\Http\Controllers;

use App\Models\SimulasiBphtb;
use Illuminate\Http\Request;

class PajakSimulasiController extends Controller
{
    public function index()
    {
        return $this->tampil();
    }

    public function store(Request $request)
    {
        SimulasiBphtb::create($this->dataHitung($request) + [
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('pajak.simulasi')->with('success', 'Hasil perhitungan disimpan ke tabel.');
    }

    public function edit(SimulasiBphtb $simulasi)
    {
        return $this->tampil($simulasi);
    }

    public function update(Request $request, SimulasiBphtb $simulasi)
    {
        $simulasi->update($this->dataHitung($request));

        return redirect()->route('pajak.simulasi')->with('success', 'Hasil perhitungan diperbarui.');
    }

    public function destroy(SimulasiBphtb $simulasi)
    {
        $simulasi->delete();

        return redirect()->route('pajak.simulasi')->with('success', 'Hasil perhitungan dihapus.');
    }

    private function tampil(?SimulasiBphtb $ubah = null)
    {
        $daftar = SimulasiBphtb::query()->orderBy('id')->get();
        $totalPajak = (float) $daftar->sum('jumlah_pajak');

        return view('pajak.simulasi', compact('daftar', 'totalPajak', 'ubah'));
    }

    private function dataHitung(Request $request): array
    {
        foreach (['nilai_jual', 'njop', 'npoptkp'] as $kolom) {
            $mentah = preg_replace('/\D/', '', (string) $request->input($kolom, ''));
            $request->merge([$kolom => $mentah === '' ? null : $mentah]);
        }

        $data = $request->validate([
            'nilai_jual' => ['required', 'numeric', 'min:0'],
            'njop' => ['nullable', 'numeric', 'min:0'],
            'npoptkp' => ['nullable', 'numeric', 'min:0'],
            'persentase' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $jual = round((float) $data['nilai_jual']);
        $njop = round((float) ($data['njop'] ?? 0));
        $bebas = round((float) ($data['npoptkp'] ?? 0));
        $persen = round((float) $data['persentase'], 2);
        $npop = max($jual, $njop);
        $dasar = max(0, $npop - $bebas);

        return [
            'nilai_jual' => $jual,
            'njop' => $njop,
            'npoptkp' => $bebas,
            'persentase' => $persen,
            'npop' => $npop,
            'dasar_pengenaan' => $dasar,
            'jumlah_pajak' => round($dasar * $persen / 100),
        ];
    }
}
