<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use App\Models\Perumahan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->queryRekap($request);

        $totalPengangsur = (clone $query)->count();
        $totalSisaPembayaran = (clone $query)->sum('sisa_angsuran');

        $penghuni = $query->orderBy('blok_kavling')->orderBy('nama_lengkap')->paginate(25)->withQueryString();
        $perumahanList = Perumahan::pluck('nama_perumahan', 'id');
        $tahunList = Penghuni::query()
            ->whereNotNull('tahun_pembelian')
            ->distinct()
            ->orderByDesc('tahun_pembelian')
            ->pluck('tahun_pembelian');

        return view('penghuni.index', compact(
            'penghuni',
            'perumahanList',
            'tahunList',
            'totalPengangsur',
            'totalSisaPembayaran'
        ));
    }

    public function unduh(Request $request)
    {
        $baris = $this->queryRekap($request)
            ->orderBy('blok_kavling')
            ->orderBy('nama_lengkap')
            ->get();

        $perumahan = Perumahan::query()->value('nama_perumahan') ?: 'Perumahan';
        $namaFile = 'rekap-pengangsur-' . now()->format('Y-m-d') . '.xls';
        $xml = $this->rekapSebagaiExcel($baris, $perumahan);

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
        ]);
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

        $validated = $request->validate($this->aturanPengangsur());
        $data = $this->dataRekap($validated);

        $userId = null;
        $loginEmail = null;
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
                    'name' => $data['nama_lengkap'],
                ]);
                $userId = $existingUser->id;
            } else {
                $newUser = User::create([
                    'name' => $data['nama_lengkap'],
                    'email' => $loginEmail,
                    'password' => Hash::make($loginPassword),
                    'role' => 'warga',
                ]);
                $userId = $newUser->id;
            }
        }

        $data['user_id'] = $userId;
        $penghuni = Penghuni::create($data);
        $jadwalBaru = $penghuni->buatJadwalSisa();

        $pesan = 'Data pengangsur berhasil disimpan.';
        if ($jadwalBaru && (int) $penghuni->sisa_tempo > 0) {
            $pesan .= ' Jadwal sisa tempo ' . $penghuni->sisa_tempo . 'X sudah dibuat.';
        }
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
        $validated = $request->validate($this->aturanPengangsur(true));
        $data = $this->dataRekap($validated, $validated['status']);

        if ($request->has('create_user_account') && $request->input('create_user_account') != '0') {
            $loginEmail = $request->filled('login_email')
                ? $request->input('login_email')
                : ($request->filled('email') ? $request->input('email') : 'warga.' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->blok_kavling)) . '@warga.local');

            if ($penghuni->user) {
                $userUpdates = ['name' => $data['nama_lengkap']];
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
                        'name' => $data['nama_lengkap'],
                        'is_active' => $request->boolean('login_is_active'),
                    ]);
                    $data['user_id'] = $existingUser->id;
                } else {
                    $newUser = User::create([
                        'name' => $data['nama_lengkap'],
                        'email' => $loginEmail,
                        'password' => Hash::make($loginPassword),
                        'role' => 'warga',
                        'is_active' => $request->boolean('login_is_active'),
                    ]);
                    $data['user_id'] = $newUser->id;
                }
            }
        }

        $penghuni->update($data);
        $penghuni->refresh();
        $jadwalBaru = $penghuni->buatJadwalSisa();

        $pesan = 'Data pengangsur berhasil diperbarui.';
        if ($jadwalBaru) {
            $pesan .= (int) $penghuni->sisa_tempo > 0
                ? ' Jadwal sisa tempo disesuaikan.'
                : ' Jadwal angsuran dikosongkan karena sisa tempo 0.';
        } else {
            $pesan .= ' Jadwal yang sudah ada pembayaran tidak diubah.';
        }

        return redirect()->route('penghuni.show', $penghuni)->with('success', $pesan);
    }

    public function destroy(Penghuni $penghuni)
    {
        $penghuni->delete();
        return redirect()->route('penghuni.index')->with('success', 'Data pengangsur berhasil dihapus.');
    }

    private function aturanPengangsur(bool $wajibStatus = false): array
    {
        $rules = [
            'perumahan_id' => 'required|exists:perumahan,id',
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'blok_kavling' => 'required|string|max:50',
            'tipe_rumah' => 'nullable|string|max:255',
            'luas_tanah' => 'nullable|numeric|min:0',
            'luas_bangunan' => 'nullable|numeric|min:0',
            'tahun_pembelian' => 'required|integer|min:1990|max:' . ((int) date('Y') + 1),
            'jenis_pembayaran' => 'required|in:inhouse,cash_lunak',
            'harga_rumah' => 'required|numeric|min:0',
            'uang_muka' => 'nullable|numeric|min:0',
            'nominal_angsuran' => 'nullable|numeric|min:0',
            'tenor_bulan' => 'required|integer|min:1|max:600',
            'sisa_tempo' => 'nullable|integer|min:0|max:600',
            'tunggakan' => 'nullable|integer|min:0|max:600',
            'sisa_pembayaran' => 'nullable|numeric|min:0',
            'tanggal_akad' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'create_user_account' => 'nullable',
            'login_email' => 'nullable|email',
            'login_password' => 'nullable|string|min:6',
        ];

        if ($wajibStatus) {
            $rules['status'] = 'required|in:aktif,lunas,bermasalah';
        }

        return $rules;
    }

    private function dataRekap(array $validated, ?string $status = null): array
    {
        $uangMuka = round((float) ($validated['uang_muka'] ?? 0));
        $harga = round((float) $validated['harga_rumah']);
        $tempo = (int) $validated['tenor_bulan'];
        $sisaTempo = array_key_exists('sisa_tempo', $validated) && $validated['sisa_tempo'] !== null
            ? (int) $validated['sisa_tempo']
            : $tempo;

        $nominal = (isset($validated['nominal_angsuran']) && $validated['nominal_angsuran'] !== null)
            ? (float) $validated['nominal_angsuran']
            : ($tempo > 0 ? ($harga - $uangMuka) / $tempo : 0);
        $nominal = round($nominal);

        $tunggakan = $validated['tunggakan'] ?? null;
        $tunggakan = ($tunggakan === null || (int) $tunggakan === 0) ? null : (int) $tunggakan;

        $sisaBayar = array_key_exists('sisa_pembayaran', $validated) && $validated['sisa_pembayaran'] !== null
            ? round((float) $validated['sisa_pembayaran'])
            : round($sisaTempo * $nominal);

        $tanggalAkad = $validated['tanggal_akad'] ?? null;
        if (!$tanggalAkad && !empty($validated['tahun_pembelian'])) {
            $tanggalAkad = sprintf('%04d-01-01', (int) $validated['tahun_pembelian']);
        }

        if ($status === null) {
            $status = ($sisaBayar <= 0 && $sisaTempo <= 0) ? 'lunas' : 'aktif';
        }

        return [
            'perumahan_id' => $validated['perumahan_id'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'nik' => $validated['nik'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'email' => $validated['email'] ?? null,
            'blok_kavling' => $validated['blok_kavling'],
            'tipe_rumah' => $validated['tipe_rumah'] ?? null,
            'luas_tanah' => $validated['luas_tanah'] ?? null,
            'luas_bangunan' => $validated['luas_bangunan'] ?? null,
            'tahun_pembelian' => $validated['tahun_pembelian'],
            'jenis_pembayaran' => $validated['jenis_pembayaran'],
            'harga_rumah' => $harga,
            'uang_muka' => $uangMuka,
            'nominal_angsuran' => $nominal,
            'sisa_angsuran' => max(0, $sisaBayar),
            'tenor_bulan' => $tempo,
            'sisa_tempo' => $sisaTempo,
            'tunggakan' => $tunggakan,
            'tanggal_akad' => $tanggalAkad,
            'status' => $status,
            'keterangan' => $validated['keterangan'] ?? null,
        ];
    }

    private function queryRekap(Request $request)
    {
        $query = Penghuni::query();

        if ($request->filled('nama')) {
            $nama = $request->nama;
            $query->where(function ($q) use ($nama) {
                $q->where('nama_lengkap', 'like', '%' . $nama . '%')
                  ->orWhere('nik', 'like', '%' . $nama . '%');
            });
        }

        if ($request->filled('rumah')) {
            $query->where('blok_kavling', 'like', '%' . $request->rumah . '%');
        }

        if ($request->filled('search')) {
            $kata = $request->search;
            $query->where(function ($q) use ($kata) {
                $q->where('nama_lengkap', 'like', '%' . $kata . '%')
                  ->orWhere('blok_kavling', 'like', '%' . $kata . '%')
                  ->orWhere('nik', 'like', '%' . $kata . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_pembayaran')) {
            $query->where('jenis_pembayaran', $request->jenis_pembayaran);
        }

        if ($request->filled('tahun_pembelian')) {
            $query->where('tahun_pembelian', $request->tahun_pembelian);
        }

        if ($request->filled('perumahan_id')) {
            $query->where('perumahan_id', $request->perumahan_id);
        }

        return $query;
    }

    private function rekapSebagaiExcel($baris, string $perumahan): string
    {
        $judul = htmlspecialchars($perumahan, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $sel = function ($nilai, string $tipe = 'String') {
            $teks = htmlspecialchars((string) $nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            return '<Cell><Data ss:Type="' . $tipe . '">' . $teks . '</Data></Cell>';
        };
        $uang = function ($nilai) use ($sel) {
            return $sel((string) (int) round((float) $nilai), 'Number');
        };

        $isi = '<Row>'
            . $sel('NO')
            . $sel('NAMA')
            . $sel('BLOK')
            . $sel('TAHUN PEMBELIAN')
            . $sel('PEMBAYARAN')
            . $sel('HARGA')
            . $sel('UANG MUKA')
            . $sel('ANGSURAN')
            . $sel('TEMPO')
            . $sel('SISA TEMPO')
            . $sel('TUNGGAKAN')
            . $sel('SISA PEMBAYARAN')
            . '</Row>';

        $jumlah = 0;
        foreach ($baris as $i => $p) {
            $jumlah += (float) $p->sisa_angsuran;
            $isi .= '<Row>'
                . $sel((string) ($i + 1), 'Number')
                . $sel($p->nama_lengkap)
                . $sel($p->blok_kavling)
                . $sel((string) ($p->tahun_pembelian ?? ''), $p->tahun_pembelian ? 'Number' : 'String')
                . $sel($p->jenis_pembayaran_label)
                . $uang($p->harga_rumah)
                . $uang($p->uang_muka)
                . $uang($p->angsuran_bulanan)
                . $sel($p->tempo_label)
                . $sel($p->sisa_tempo_label)
                . $sel($p->tunggakan_label)
                . $uang($p->sisa_angsuran)
                . '</Row>';
        }

        $isi .= '<Row>'
            . str_repeat('<Cell></Cell>', 10)
            . $sel('JUMLAH')
            . $uang($jumlah)
            . '</Row>';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<?mso-application progid="Excel.Sheet"?>'
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            . '<Worksheet ss:Name="Rekap Pengangsur"><Table>'
            . '<Row><Cell ss:MergeAcross="11"><Data ss:Type="String">REKAP PENGANGSUR ' . $judul . '</Data></Cell></Row>'
            . $isi
            . '</Table></Worksheet></Workbook>';
    }
}
