<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use App\Models\Perumahan;
use App\Models\Angsuran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
        $query = Penghuni::with(['perumahan', 'user']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                  ->orWhere('blok_kavling', 'like', '%' . $request->search . '%')
                  ->orWhere('nik', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('perumahan_id')) {
            $query->where('perumahan_id', $request->perumahan_id);
        }

        $penghuni = $query->orderBy('nama_lengkap')->paginate(15)->withQueryString();
        $perumahanList = Perumahan::pluck('nama_perumahan', 'id');

        return view('penghuni.index', compact('penghuni', 'perumahanList'));
    }

    public function create()
    {
        $perumahan = Perumahan::first();
        $perumahanList = Perumahan::pluck('nama_perumahan', 'id');
        return view('penghuni.create', compact('perumahanList', 'perumahan'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('perumahan_id')) {
            $defaultP = Perumahan::first();
            if ($defaultP) {
                $request->merge(['perumahan_id' => $defaultP->id]);
            }
        }

        $validated = $request->validate([
            'perumahan_id' => 'required|exists:perumahan,id',
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'blok_kavling' => 'required|string|max:255',
            'tipe_rumah' => 'nullable|string|max:255',
            'luas_tanah' => 'nullable|numeric|min:0',
            'luas_bangunan' => 'nullable|numeric|min:0',
            'harga_rumah' => 'required|numeric|min:0',
            'uang_muka' => 'nullable|numeric|min:0',
            'tenor_bulan' => 'required|integer|min:1',
            'tanggal_akad' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'create_user_account' => 'nullable',
            'login_email' => 'nullable|email',
            'login_password' => 'nullable|string|min:6',
        ]);

        $validated['sisa_angsuran'] = $validated['harga_rumah'] - ($validated['uang_muka'] ?? 0);

        // Buat akun login jika dicentang atau diaktifkan (default aktif)
        $userId = null;
        if ($request->has('create_user_account') && $request->input('create_user_account') != '0') {
            $loginEmail = $request->filled('login_email') 
                ? $request->input('login_email') 
                : ($request->filled('email') ? $request->input('email') : 'warga.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->blok_kavling)) . '@warga.local');
            
            $loginPassword = $request->filled('login_password') 
                ? $request->input('login_password') 
                : ($request->filled('nik') ? $request->input('nik') : 'warga123');

            $existingUser = User::where('email', $loginEmail)->first();
            if ($existingUser) {
                $existingUser->update([
                    'role' => 'warga',
                    'name' => $validated['nama_lengkap'],
                ]);
                $userId = $existingUser->id;
            } else {
                $newUser = User::create([
                    'name' => $validated['nama_lengkap'],
                    'email' => $loginEmail,
                    'password' => Hash::make($loginPassword),
                    'role' => 'warga',
                ]);
                $userId = $newUser->id;
            }
        }

        $validated['user_id'] = $userId;
        $penghuni = Penghuni::create($validated);

        // Generate jadwal angsuran
        if ($penghuni->tenor_bulan > 0) {
            $angsuranPerBulan = $penghuni->sisa_angsuran / $penghuni->tenor_bulan;
            $startDate = $penghuni->tanggal_akad ? Carbon::parse($penghuni->tanggal_akad)->addMonth() : Carbon::now()->addMonth();

            for ($i = 1; $i <= $penghuni->tenor_bulan; $i++) {
                Angsuran::create([
                    'penghuni_id' => $penghuni->id,
                    'angsuran_ke' => $i,
                    'jumlah_angsuran' => round($angsuranPerBulan, 2),
                    'tanggal_jatuh_tempo' => $startDate->copy()->addMonths($i - 1),
                    'status' => 'belum_bayar',
                ]);
            }
        }

        $pesan = 'Data penghuni dan jadwal angsuran berhasil dibuat.';
        if ($userId) {
            $pesan .= ' Akun login warga aktif (' . $loginEmail . ').';
        }

        return redirect()->route('penghuni.index')->with('success', $pesan);
    }

    public function show(Penghuni $penghuni)
    {
        $user = auth()->user();
        if ($user && $user->isWarga() && $penghuni->id !== $user->penghuni?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data penghuni lain.');
        }

        $penghuni->load(['user', 'perumahan', 'angsuran' => function ($q) {
            $q->orderBy('angsuran_ke');
        }, 'legalitas.timeline', 'kasMasuk.kategori']);
        return view('penghuni.show', compact('penghuni'));
    }

    public function edit(Penghuni $penghuni)
    {
        $penghuni->load('user');
        $perumahanList = Perumahan::pluck('nama_perumahan', 'id');
        return view('penghuni.edit', compact('penghuni', 'perumahanList'));
    }

    public function update(Request $request, Penghuni $penghuni)
    {
        $validated = $request->validate([
            'perumahan_id' => 'required|exists:perumahan,id',
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'blok_kavling' => 'required|string|max:255',
            'tipe_rumah' => 'nullable|string|max:255',
            'luas_tanah' => 'nullable|numeric|min:0',
            'luas_bangunan' => 'nullable|numeric|min:0',
            'harga_rumah' => 'required|numeric|min:0',
            'uang_muka' => 'nullable|numeric|min:0',
            'tenor_bulan' => 'required|integer|min:1',
            'tanggal_akad' => 'nullable|date',
            'status' => 'required|in:aktif,lunas,bermasalah',
            'keterangan' => 'nullable|string',
            'create_user_account' => 'nullable',
            'login_email' => 'nullable|email',
            'login_password' => 'nullable|string|min:6',
        ]);

        // Tangani update atau pembuatan akun login warga
        if ($request->has('create_user_account') && $request->input('create_user_account') != '0') {
            $loginEmail = $request->filled('login_email') 
                ? $request->input('login_email') 
                : ($request->filled('email') ? $request->input('email') : 'warga.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->blok_kavling)) . '@warga.local');

            if ($penghuni->user) {
                $userUpdates = ['name' => $validated['nama_lengkap']];
                if ($request->filled('login_email')) {
                    $userUpdates['email'] = $request->login_email;
                }
                if ($request->filled('login_password')) {
                    $userUpdates['password'] = Hash::make($request->login_password);
                }
                $userUpdates['is_active'] = $request->boolean('login_is_active');
                $penghuni->user->update($userUpdates);
            } else {
                $loginPassword = $request->filled('login_password') ? $request->login_password : 'warga123';
                $existingUser = User::where('email', $loginEmail)->first();
                if ($existingUser) {
                    $existingUser->update([
                        'role' => 'warga',
                        'name' => $validated['nama_lengkap'],
                        'is_active' => $request->boolean('login_is_active'),
                    ]);
                    $validated['user_id'] = $existingUser->id;
                } else {
                    $newUser = User::create([
                        'name' => $validated['nama_lengkap'],
                        'email' => $loginEmail,
                        'password' => Hash::make($loginPassword),
                        'role' => 'warga',
                        'is_active' => $request->boolean('login_is_active'),
                    ]);
                    $validated['user_id'] = $newUser->id;
                }
            }
        }

        $penghuni->update($validated);
        return redirect()->route('penghuni.show', $penghuni)->with('success', 'Data penghuni dan akun login berhasil diperbarui.');
    }

    public function destroy(Penghuni $penghuni)
    {
        $penghuni->delete();
        return redirect()->route('penghuni.index')->with('success', 'Data penghuni berhasil dihapus.');
    }
}
