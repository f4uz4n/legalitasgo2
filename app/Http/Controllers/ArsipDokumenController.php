<?php

namespace App\Http\Controllers;

use App\Models\ArsipDokumen;
use App\Models\Perumahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArsipDokumenController extends Controller
{
    public const KATEGORI_LIST = [
        'Perizinan & Legalitas Induk',
        'Sertifikat & Pertanahan',
        'Masterplan & Teknis',
        'Notaris & Kerjasama',
        'Lingkungan & PSU',
        'Administrasi & Perpajakan',
        'Lain-lain',
    ];

    /**
     * Menampilkan daftar kearsipan dokumen perumahan.
     */
    public function index(Request $request)
    {
        $query = ArsipDokumen::with(['uploader', 'perumahan']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('judul_dokumen', 'like', "%{$keyword}%")
                  ->orWhere('nomor_dokumen', 'like', "%{$keyword}%")
                  ->orWhere('instansi_penerbit', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi', 'like', "%{$keyword}%")
                  ->orWhere('file_name', 'like', "%{$keyword}%");
            });
        }

        // Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter Akses (Publik Warga / Khusus Internal)
        if ($request->filled('akses')) {
            if ($request->akses === 'publik') {
                $query->where('is_public', true);
            } elseif ($request->akses === 'internal') {
                $query->where('is_public', false);
            }
        }

        // Filter Tahun Dokumen
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_dokumen', $request->tahun);
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'tgl_dokumen_desc') {
            $query->orderBy('tanggal_dokumen', 'desc');
        } elseif ($sort === 'tgl_dokumen_asc') {
            $query->orderBy('tanggal_dokumen', 'asc');
        } elseif ($sort === 'judul_asc') {
            $query->orderBy('judul_dokumen', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $dokumenList = $query->paginate(15)->withQueryString();

        // Statistik Ringkas
        $totalDokumen    = ArsipDokumen::count();
        $totalPerizinan  = ArsipDokumen::where('kategori', 'Perizinan & Legalitas Induk')->count();
        $totalSertifikat = ArsipDokumen::where('kategori', 'Sertifikat & Pertanahan')->count();
        $totalPublik     = ArsipDokumen::where('is_public', true)->count();
        $totalBytes      = ArsipDokumen::sum('file_size');

        $totalSizeFormatted = $totalBytes >= 1048576 
            ? round($totalBytes / 1048576, 2) . ' MB' 
            : round($totalBytes / 1024, 1) . ' KB';

        $kategoriList = self::KATEGORI_LIST;

        // Daftar tahun yang ada dokumennya
        $tahunList = ArsipDokumen::whereNotNull('tanggal_dokumen')
            ->selectRaw('YEAR(tanggal_dokumen) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('arsip.index', compact(
            'dokumenList',
            'totalDokumen',
            'totalPerizinan',
            'totalSertifikat',
            'totalPublik',
            'totalSizeFormatted',
            'kategoriList',
            'tahunList',
            'sort'
        ));
    }

    /**
     * Menampilkan form upload dokumen baru.
     */
    public function create()
    {
        $kategoriList = self::KATEGORI_LIST;
        $perumahan    = Perumahan::first();

        return view('arsip.create', compact('kategoriList', 'perumahan'));
    }

    /**
     * Menyimpan dokumen baru ke arsip storage & database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_dokumen'     => ['required', 'string', 'max:255'],
            'kategori'          => ['required', 'string', 'max:255'],
            'nomor_dokumen'     => ['nullable', 'string', 'max:255'],
            'tanggal_dokumen'   => ['nullable', 'date'],
            'instansi_penerbit' => ['nullable', 'string', 'max:255'],
            'deskripsi'         => ['nullable', 'string'],
            'is_public'         => ['nullable', 'boolean'],
            'file_dokumen'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,zip,rar', 'max:25600'], // maks 25 MB
        ], [
            'judul_dokumen.required' => 'Judul dokumen wajib diisi.',
            'kategori.required'      => 'Pilih kategori dokumen arsip.',
            'file_dokumen.required'  => 'Pilih berkas dokumen yang ingin diarsipkan.',
            'file_dokumen.max'       => 'Ukuran file dokumen maksimal 25 MB.',
            'file_dokumen.mimes'     => 'Format file yang diperbolehkan: PDF, JPG, PNG, WEBP, DOCX, XLSX, ZIP.',
        ]);

        $file     = $request->file('file_dokumen');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileType = strtolower($file->getClientOriginalExtension());

        // Simpan ke storage/app/public/arsip-dokumen
        $filePath = $file->store('arsip-dokumen', 'public');

        $perumahan = Perumahan::first();

        ArsipDokumen::create([
            'perumahan_id'      => $perumahan?->id,
            'judul_dokumen'     => $validated['judul_dokumen'],
            'kategori'          => $validated['kategori'],
            'nomor_dokumen'     => $validated['nomor_dokumen'] ?? null,
            'tanggal_dokumen'   => $validated['tanggal_dokumen'] ?? null,
            'instansi_penerbit' => $validated['instansi_penerbit'] ?? null,
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'file_path'         => $filePath,
            'file_name'         => $fileName,
            'file_size'         => $fileSize,
            'file_type'         => $fileType,
            'is_public'         => $request->boolean('is_public'),
            'uploaded_by'       => auth()->id(),
        ]);

        return redirect()->route('arsip.index')
            ->with('success', "Dokumen '{$validated['judul_dokumen']}' berhasil diarsipkan dengan aman.");
    }

    /**
     * Menampilkan detail & pratinjau dokumen arsip.
     */
    public function show(ArsipDokumen $arsip)
    {
        $arsip->load(['uploader', 'perumahan']);
        return view('arsip.show', compact('arsip'));
    }

    /**
     * Menampilkan form edit informasi dokumen.
     */
    public function edit(ArsipDokumen $arsip)
    {
        $kategoriList = self::KATEGORI_LIST;
        return view('arsip.edit', compact('arsip', 'kategoriList'));
    }

    /**
     * Memperbarui metadata atau mengganti file dokumen.
     */
    public function update(Request $request, ArsipDokumen $arsip)
    {
        $validated = $request->validate([
            'judul_dokumen'     => ['required', 'string', 'max:255'],
            'kategori'          => ['required', 'string', 'max:255'],
            'nomor_dokumen'     => ['nullable', 'string', 'max:255'],
            'tanggal_dokumen'   => ['nullable', 'date'],
            'instansi_penerbit' => ['nullable', 'string', 'max:255'],
            'deskripsi'         => ['nullable', 'string'],
            'is_public'         => ['nullable', 'boolean'],
            'file_dokumen'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,zip,rar', 'max:25600'],
        ], [
            'judul_dokumen.required' => 'Judul dokumen wajib diisi.',
            'kategori.required'      => 'Pilih kategori dokumen arsip.',
            'file_dokumen.max'       => 'Ukuran file dokumen maksimal 25 MB.',
        ]);

        $data = [
            'judul_dokumen'     => $validated['judul_dokumen'],
            'kategori'          => $validated['kategori'],
            'nomor_dokumen'     => $validated['nomor_dokumen'] ?? null,
            'tanggal_dokumen'   => $validated['tanggal_dokumen'] ?? null,
            'instansi_penerbit' => $validated['instansi_penerbit'] ?? null,
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'is_public'         => $request->boolean('is_public'),
        ];

        // Jika ada unggahan file baru pengganti
        if ($request->hasFile('file_dokumen')) {
            // Hapus file lama jika ada
            if ($arsip->file_path && Storage::disk('public')->exists($arsip->file_path)) {
                Storage::disk('public')->delete($arsip->file_path);
            }

            $newFile            = $request->file('file_dokumen');
            $data['file_path']  = $newFile->store('arsip-dokumen', 'public');
            $data['file_name']  = $newFile->getClientOriginalName();
            $data['file_size']  = $newFile->getSize();
            $data['file_type']  = strtolower($newFile->getClientOriginalExtension());
        }

        $arsip->update($data);

        return redirect()->route('arsip.index')
            ->with('success', "Informasi arsip dokumen '{$arsip->judul_dokumen}' berhasil diperbarui.");
    }

    /**
     * Menghapus dokumen arsip dari sistem & storage.
     */
    public function destroy(ArsipDokumen $arsip)
    {
        $judul = $arsip->judul_dokumen;

        // Hapus fisik berkas dari disk
        if ($arsip->file_path && Storage::disk('public')->exists($arsip->file_path)) {
            Storage::disk('public')->delete($arsip->file_path);
        }

        $arsip->delete();

        return redirect()->route('arsip.index')
            ->with('success', "Dokumen arsip '{$judul}' berhasil dihapus dari sistem.");
    }

    /**
     * Unduh file dokumen.
     */
    public function download(ArsipDokumen $arsip)
    {
        if (!$arsip->file_path || !Storage::disk('public')->exists($arsip->file_path)) {
            abort(404, 'Berkas fisik dokumen tidak ditemukan di server penyimpanan.');
        }

        return Storage::disk('public')->download($arsip->file_path, $arsip->file_name);
    }
}
