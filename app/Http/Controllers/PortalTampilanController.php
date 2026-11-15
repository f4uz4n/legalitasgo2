<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Support\PortalWarga;
use Illuminate\Http\Request;

class PortalTampilanController extends Controller
{
    public function edit(PortalWarga $portal)
    {
        return view('portal.pengaturan', [
            'persen' => $portal->persen(),
            'bagian' => $portal->semuanya(),
            'labelBagian' => PortalWarga::BAGIAN,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'persen_dana_masuk' => 'required|integer|min:0|max:100',
        ]);

        Pengaturan::setValue(
            'portal_persen_dana_masuk',
            (string) $data['persen_dana_masuk'],
            'portal',
            'Persentase dana masuk yang ditampilkan di portal warga'
        );

        foreach (array_keys(PortalWarga::BAGIAN) as $kunci) {
            Pengaturan::setValue(
                'portal_bagian_' . $kunci,
                $request->boolean('bagian_' . $kunci) ? '1' : '0',
                'portal',
                'Tampilkan bagian ' . $kunci . ' di portal warga'
            );
        }

        return redirect()->route('portal.pengaturan')->with('success', 'Pengaturan tampilan portal warga disimpan.');
    }
}
