<?php

namespace App\Http\Controllers;

use App\Models\Legalitas;
use App\Models\Penghuni;
use App\Models\TimelineLegalitas;
use Illuminate\Http\Request;

class LegalitasController extends Controller
{
    public function index(Request $request)
    {
        $query = Legalitas::with('penghuni.perumahan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('penghuni', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                  ->orWhere('blok_kavling', 'like', '%' . $request->search . '%');
            });
        }

        $legalitas = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();
        $legalitasAll = Legalitas::with('penghuni')->get();
        $recentTimelines = TimelineLegalitas::with('legalitas.penghuni')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('legalitas.index', compact('legalitas', 'legalitasAll', 'recentTimelines'));
    }

    public function create()
    {
        $penghuniList = Penghuni::doesntHave('legalitas')
            ->orderBy('nama_lengkap')
            ->get();
        return view('legalitas.create', compact('penghuniList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id|unique:legalitas,penghuni_id',
            'jenis_sertifikat' => 'nullable|string|max:255',
            'no_ajb' => 'nullable|string|max:255',
            'tanggal_ajb' => 'nullable|date',
            'notaris' => 'nullable|string|max:255',
            'no_imb' => 'nullable|string|max:255',
            'no_pbb' => 'nullable|string|max:255',
            'status' => 'required|in:belum_proses,pengajuan,verifikasi,proses_bpn,proses_notaris,selesai',
            'tanggal_mulai' => 'nullable|date',
            'estimasi_selesai' => 'nullable|date',
            'biaya_pengurusan' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['progress_persen'] = $this->calculateProgress($validated['status']);
        $legalitas = Legalitas::create($validated);

        // Create initial timeline entry
        TimelineLegalitas::create([
            'legalitas_id' => $legalitas->id,
            'tanggal' => now(),
            'judul' => 'Data Legalitas Dibuat',
            'deskripsi' => 'Proses pengurusan legalitas sertifikat dimulai.',
            'status' => 'selesai',
            'icon' => 'bi-file-earmark-plus',
            'warna' => '#0d6efd',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('legalitas.index')->with('success', 'Data legalitas berhasil ditambahkan.');
    }

    public function show(Legalitas $legalita)
    {
        $user = auth()->user();
        if ($user && $user->isWarga() && $legalita->penghuni_id !== $user->penghuni?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat rincian legalitas ini.');
        }

        $legalita->load(['penghuni.perumahan', 'timeline']);
        return view('legalitas.show', compact('legalita'));
    }

    public function edit(Legalitas $legalita)
    {
        $legalita->load('penghuni');
        return view('legalitas.edit', compact('legalita'));
    }

    public function update(Request $request, Legalitas $legalita)
    {
        $validated = $request->validate([
            'jenis_sertifikat' => 'nullable|string|max:255',
            'no_sertifikat' => 'nullable|string|max:255',
            'no_ajb' => 'nullable|string|max:255',
            'tanggal_ajb' => 'nullable|date',
            'notaris' => 'nullable|string|max:255',
            'no_imb' => 'nullable|string|max:255',
            'no_pbb' => 'nullable|string|max:255',
            'status' => 'required|in:belum_proses,pengajuan,verifikasi,proses_bpn,proses_notaris,selesai',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'estimasi_selesai' => 'nullable|date',
            'biaya_pengurusan' => 'nullable|numeric|min:0',
            'biaya_terbayar' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['progress_persen'] = $this->calculateProgress($validated['status']);

        // If status changed, create timeline entry
        if ($legalita->status !== $validated['status']) {
            TimelineLegalitas::create([
                'legalitas_id' => $legalita->id,
                'tanggal' => now(),
                'judul' => 'Status Diperbarui: ' . $this->getStatusLabel($validated['status']),
                'deskripsi' => 'Status legalitas diperbarui dari "' . $legalita->status_label . '" menjadi "' . $this->getStatusLabel($validated['status']) . '".',
                'status' => 'selesai',
                'icon' => 'bi-arrow-right-circle',
                'warna' => '#198754',
                'created_by' => auth()->id(),
            ]);
        }

        $legalita->update($validated);
        return redirect()->route('legalitas.show', $legalita)->with('success', 'Data legalitas berhasil diperbarui.');
    }

    public function addTimeline(Request $request, Legalitas $legalita)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:pending,proses,selesai,ditolak',
            'dokumen_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if ($request->hasFile('dokumen_file')) {
            $validated['dokumen_file'] = $request->file('dokumen_file')->store('dokumen-legalitas', 'public');
        }

        $validated['legalitas_id'] = $legalita->id;
        $validated['created_by'] = auth()->id();
        $validated['icon'] = $this->getTimelineIcon($validated['status']);
        $validated['warna'] = $this->getTimelineColor($validated['status']);

        TimelineLegalitas::create($validated);

        return redirect()->route('legalitas.show', $legalita)->with('success', 'Timeline berhasil ditambahkan.');
    }

    public function storeTimelineDirect(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'judul' => 'required|string|max:255',
            'kavling_terkait' => 'nullable|string|max:255',
            'legalitas_id' => 'nullable|exists:legalitas,id',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:pending,proses,selesai,ditolak',
            'dokumen_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if ($request->hasFile('dokumen_file')) {
            $validated['dokumen_file'] = $request->file('dokumen_file')->store('dokumen-legalitas', 'public');
        }

        if (!empty($validated['legalitas_id']) && empty($validated['kavling_terkait'])) {
            $leg = Legalitas::with('penghuni')->find($validated['legalitas_id']);
            if ($leg && $leg->penghuni) {
                $validated['kavling_terkait'] = 'Kavling ' . $leg->penghuni->blok_kavling;
            }
        }

        $validated['created_by'] = auth()->id();
        $validated['icon'] = $this->getTimelineIcon($validated['status']);
        $validated['warna'] = $this->getTimelineColor($validated['status']);

        TimelineLegalitas::create($validated);

        return redirect()->back()->with('success', 'Update tahapan berkas "' . $validated['judul'] . '" berhasil dicatat ke timeline publik.');
    }

    public function destroy(Legalitas $legalita)
    {
        $legalita->delete();
        return redirect()->route('legalitas.index')->with('success', 'Data legalitas berhasil dihapus.');
    }

    private function calculateProgress($status)
    {
        return match($status) {
            'belum_proses' => 0,
            'pengajuan' => 20,
            'verifikasi' => 40,
            'proses_bpn' => 60,
            'proses_notaris' => 80,
            'selesai' => 100,
            default => 0,
        };
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'belum_proses' => 'Belum Diproses',
            'pengajuan' => 'Pengajuan',
            'verifikasi' => 'Verifikasi',
            'proses_bpn' => 'Proses BPN',
            'proses_notaris' => 'Proses Notaris',
            'selesai' => 'Selesai',
            default => $status,
        };
    }

    private function getTimelineIcon($status)
    {
        return match($status) {
            'pending' => 'bi-clock',
            'proses' => 'bi-gear',
            'selesai' => 'bi-check-circle',
            'ditolak' => 'bi-x-circle',
            default => 'bi-circle',
        };
    }

    private function getTimelineColor($status)
    {
        return match($status) {
            'pending' => '#ffc107',
            'proses' => '#0d6efd',
            'selesai' => '#198754',
            'ditolak' => '#dc3545',
            default => '#6c757d',
        };
    }
}
