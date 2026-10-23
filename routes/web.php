<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerumahanController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\LegalitasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\ArsipDokumenController;
use App\Http\Controllers\ReportPdfController;
use Illuminate\Support\Facades\Route;

// ─── Public routes (tanpa login) ───────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('public.report');
});

Route::get('/laporan/publik', [PublicReportController::class, 'index'])->name('public.report');
Route::get('/laporan/timeline', [PublicReportController::class, 'timelinePage'])->name('public.timeline');
Route::get('/api/public/report-data', [PublicReportController::class, 'apiData'])->name('api.public.report');

// PWA: manifest & service worker (publik)
Route::get('/manifest.webmanifest', [SystemController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [SystemController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('/offline', [SystemController::class, 'offline'])->name('pwa.offline');

// ─── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/deactivate', [ProfileController::class, 'deactivate'])->name('profile.deactivate');

    // Pembayaran — PENTING: rute statis (create) HARUS sebelum rute parameter ({angsuran})
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');

    // ─── Khusus Administrator ────────────────────────────────────────────────────
    Route::middleware('admin')->group(function () {

        // Pembayaran Create & Store (HARUS terdaftar SEBELUM /pembayaran/{angsuran})
        Route::get('/pembayaran/create', [PembayaranController::class, 'create'])->name('pembayaran.create');
        Route::post('/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');

        // Perumahan
        Route::resource('perumahan', PerumahanController::class);

        // Penghuni Management
        Route::get('/penghuni', [PenghuniController::class, 'index'])->name('penghuni.index');
        Route::get('/penghuni/create', [PenghuniController::class, 'create'])->name('penghuni.create');
        Route::post('/penghuni', [PenghuniController::class, 'store'])->name('penghuni.store');
        Route::get('/penghuni/{penghuni}/edit', [PenghuniController::class, 'edit'])->name('penghuni.edit');
        Route::put('/penghuni/{penghuni}', [PenghuniController::class, 'update'])->name('penghuni.update');
        Route::delete('/penghuni/{penghuni}', [PenghuniController::class, 'destroy'])->name('penghuni.destroy');

        // Kas Management
        Route::get('/kas', [KasController::class, 'index'])->name('kas.index');
        Route::post('/kas/masuk', [KasController::class, 'storeMasuk'])->name('kas.masuk.store');
        Route::post('/kas/keluar', [KasController::class, 'storeKeluar'])->name('kas.keluar.store');
        Route::post('/kas/kategori', [KasController::class, 'storeKategori'])->name('kas.kategori.store');
        Route::delete('/kas/masuk/{kasMasuk}', [KasController::class, 'destroyMasuk'])->name('kas.masuk.destroy');
        Route::delete('/kas/keluar/{kasKeluar}', [KasController::class, 'destroyKeluar'])->name('kas.keluar.destroy');

        // Legalitas Management
        Route::get('/legalitas', [LegalitasController::class, 'index'])->name('legalitas.index');
        Route::get('/legalitas/create', [LegalitasController::class, 'create'])->name('legalitas.create');
        Route::post('/legalitas', [LegalitasController::class, 'store'])->name('legalitas.store');
        Route::get('/legalitas/{legalita}/edit', [LegalitasController::class, 'edit'])->name('legalitas.edit');
        Route::put('/legalitas/{legalita}', [LegalitasController::class, 'update'])->name('legalitas.update');
        Route::delete('/legalitas/{legalita}', [LegalitasController::class, 'destroy'])->name('legalitas.destroy');
        Route::post('/legalitas/{legalita}/timeline', [LegalitasController::class, 'addTimeline'])->name('legalitas.timeline.store');
        Route::post('/timeline/store', [LegalitasController::class, 'storeTimelineDirect'])->name('timeline.store.direct');

        // Kearsipan Dokumen Perumahan
        Route::get('/arsip', [ArsipDokumenController::class, 'index'])->name('arsip.index');
        Route::get('/arsip/create', [ArsipDokumenController::class, 'create'])->name('arsip.create');
        Route::post('/arsip', [ArsipDokumenController::class, 'store'])->name('arsip.store');
        Route::get('/arsip/{arsip}', [ArsipDokumenController::class, 'show'])->name('arsip.show');
        Route::get('/arsip/{arsip}/edit', [ArsipDokumenController::class, 'edit'])->name('arsip.edit');
        Route::put('/arsip/{arsip}', [ArsipDokumenController::class, 'update'])->name('arsip.update');
        Route::delete('/arsip/{arsip}', [ArsipDokumenController::class, 'destroy'])->name('arsip.destroy');
        Route::get('/arsip/{arsip}/download', [ArsipDokumenController::class, 'download'])->name('arsip.download');

        // Log Aktivitas Admin (Audit Trail — Read Only & Immutable)
        Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/log-aktivitas/data', [ActivityLogController::class, 'data'])->name('admin.activity-logs.data');

        // System: Backup & Reset Data
        Route::get('/sistem', [SystemController::class, 'index'])->name('system.index');
        Route::post('/sistem/backup', [SystemController::class, 'backup'])->name('system.backup');
        Route::post('/sistem/reset', [SystemController::class, 'reset'])->name('system.reset');
        Route::get('/sistem/backup/{filename}', [SystemController::class, 'downloadBackup'])->name('system.backup.download');

        // Laporan PDF — Download/Print
        Route::get('/laporan/pdf', [ReportPdfController::class, 'form'])->name('report-pdf.form');
        Route::post('/laporan/pdf/preview', [ReportPdfController::class, 'preview'])->name('report-pdf.preview');
    });

    // Legalitas Show (warga bisa lihat)
    Route::get('/legalitas/{legalita}', [LegalitasController::class, 'show'])->name('legalitas.show');

    // Penghuni Show (warga bisa lihat unit sendiri)
    Route::get('/penghuni/{penghuni}', [PenghuniController::class, 'show'])->name('penghuni.show');

    // Pembayaran Show — HARUS setelah /pembayaran/create
    Route::get('/pembayaran/{angsuran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
});

require __DIR__.'/auth.php';
