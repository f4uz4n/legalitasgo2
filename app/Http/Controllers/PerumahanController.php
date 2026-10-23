<?php

namespace App\Http\Controllers;

use App\Models\Perumahan;
use Illuminate\Http\Request;

class PerumahanController extends Controller
{
    public function index()
    {
        $perumahan = Perumahan::first();
        if (!$perumahan) {
            $perumahan = Perumahan::create([
                'nama_perumahan' => 'Griya Harmoni Residence',
                'alamat' => 'Jl. Raya Harmoni No. 88, Cibinong',
                'kecamatan' => 'Cibinong',
                'kelurahan' => 'Pakansari',
                'kota' => 'Kabupaten Bogor',
                'provinsi' => 'Jawa Barat',
                'total_unit' => 40,
            ]);
        }
        $perumahan->load(['penghuni' => function ($q) {
            $q->with('legalitas');
        }]);
        return view('perumahan.show', compact('perumahan'));
    }

    public function create()
    {
        return view('perumahan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perumahan' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kecamatan' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'provinsi' => 'nullable|string|max:255',
            'total_unit' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        Perumahan::create($validated);
        return redirect()->route('perumahan.index')->with('success', 'Data perumahan berhasil ditambahkan.');
    }

    public function show(Perumahan $perumahan)
    {
        $perumahan->load(['penghuni' => function ($q) {
            $q->with('legalitas');
        }]);
        return view('perumahan.show', compact('perumahan'));
    }

    public function edit(Perumahan $perumahan)
    {
        return view('perumahan.edit', compact('perumahan'));
    }

    public function update(Request $request, Perumahan $perumahan)
    {
        $validated = $request->validate([
            'nama_perumahan' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kecamatan' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'provinsi' => 'nullable|string|max:255',
            'total_unit' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $perumahan->update($validated);
        return redirect()->route('perumahan.index')->with('success', 'Data perumahan berhasil diperbarui.');
    }

    public function destroy(Perumahan $perumahan)
    {
        $perumahan->delete();
        return redirect()->route('perumahan.index')->with('success', 'Data perumahan berhasil dihapus.');
    }
}
