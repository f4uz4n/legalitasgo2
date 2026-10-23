<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Angsuran;
use App\Models\KasKeluar;
use App\Models\KasMasuk;
use App\Models\KategoriKas;
use App\Models\Legalitas;
use App\Models\Penghuni;
use App\Models\Perumahan;
use App\Models\TimelineLegalitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SystemController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // Halaman panel Sistem — Backup & Reset Data
    // ══════════════════════════════════════════════════════════════════════════
    public function index()
    {
        $stats = [
            'penghuni'  => Penghuni::count(),
            'angsuran'  => Angsuran::count(),
            'kas_masuk' => KasMasuk::count(),
            'kas_keluar'=> KasKeluar::count(),
            'legalitas' => Legalitas::count(),
            'timeline'  => TimelineLegalitas::count(),
            'kategori'  => KategoriKas::count(),
        ];

        $backupFiles = collect(glob(storage_path('app/backups/*.sql')))->map(function ($f) {
            return [
                'name'    => basename($f),
                'size'    => round(filesize($f) / 1024, 1) . ' KB',
                'created' => Carbon::createFromTimestamp(filemtime($f))->translatedFormat('d M Y H:i'),
            ];
        })->sortByDesc('created')->values();

        return view('system.index', compact('stats', 'backupFiles'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Backup database ke file SQL
    // ══════════════════════════════════════════════════════════════════════════
    public function backup(Request $request)
    {
        set_time_limit(120);

        $request->validate([
            'konfirmasi_backup' => 'required|in:BACKUP',
        ], [
            'konfirmasi_backup.required' => 'Ketik kata konfirmasi BACKUP untuk melanjutkan.',
            'konfirmasi_backup.in'       => 'Kata konfirmasi salah. Ketik BACKUP (kapital).',
        ]);

        try {
            $dir = storage_path('app/backups');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename  = 'backup_legalitasgo_' . now()->format('Ymd_His') . '.sql';
            $filepath  = $dir . '/' . $filename;

            $dbHost = config('database.connections.mysql.host', '127.0.0.1');
            $dbPort = config('database.connections.mysql.port', '3306');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Buat SQL dump manual melalui PDO (tanpa depend mysqldump executable)
            $pdo = DB::connection()->getPdo();
            $sql  = "-- LegalitasGo Database Backup\n";
            $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sql .= "-- Database: {$dbName}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Struktur tabel
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $create     = end($createStmt);
                $sql .= "-- Table: {$table}\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $create . ";\n\n";

                // Data
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $cols    = implode('`, `', array_keys($rows[0]));
                    $inserts = [];
                    foreach ($rows as $row) {
                        $vals = array_map(function ($v) use ($pdo) {
                            return $v === null ? 'NULL' : $pdo->quote((string) $v);
                        }, array_values($row));
                        $inserts[] = '(' . implode(', ', $vals) . ')';
                    }
                    // Split besar menjadi chunk 100 rows per INSERT
                    foreach (array_chunk($inserts, 100) as $chunk) {
                        $sql .= "INSERT INTO `{$table}` (`{$cols}`) VALUES\n" . implode(",\n", $chunk) . ";\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($filepath, $sql);

            return redirect()->route('system.index')
                ->with('success', "Backup berhasil disimpan: {$filename} (" . round(filesize($filepath) / 1024, 1) . " KB)");
        } catch (\Throwable $e) {
            return redirect()->route('system.index')
                ->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Download file backup
    // ══════════════════════════════════════════════════════════════════════════
    public function downloadBackup(string $filename)
    {
        $path = storage_path('app/backups/' . basename($filename));
        abort_unless(file_exists($path), 404, 'File backup tidak ditemukan.');
        return Response::download($path);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Reset data transaksi (Angsuran, Kas, Timeline) — bukan warga / perumahan
    // ══════════════════════════════════════════════════════════════════════════
    public function reset(Request $request)
    {
        $request->validate([
            'konfirmasi_reset' => 'required|in:HAPUS SEMUA DATA',
            'target_reset'     => 'required|array|min:1',
            'target_reset.*'   => 'in:angsuran,kas_masuk,kas_keluar,timeline,penghuni,legalitas',
        ], [
            'konfirmasi_reset.required' => 'Ketik kalimat konfirmasi untuk melanjutkan.',
            'konfirmasi_reset.in'       => 'Konfirmasi salah. Ketik persis: HAPUS SEMUA DATA',
            'target_reset.required'     => 'Pilih minimal satu data yang ingin direset.',
            'target_reset.min'          => 'Pilih minimal satu data yang ingin direset.',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            foreach ($request->target_reset as $target) {
                match ($target) {
                    'angsuran'  => Angsuran::query()->update([
                        'jumlah_bayar'  => 0,
                        'status'        => 'belum_bayar',
                        'tanggal_bayar' => null,
                        'keterangan'    => null,
                        'bukti_bayar'   => null,
                    ]),
                    'kas_masuk'  => KasMasuk::query()->delete(),
                    'kas_keluar' => KasKeluar::query()->delete(),
                    'timeline'   => TimelineLegalitas::query()->delete(),
                    'penghuni'   => (function () {
                        Angsuran::query()->delete();
                        KasMasuk::query()->delete();
                        Legalitas::query()->delete();
                        TimelineLegalitas::query()->delete();
                        Penghuni::query()->delete();
                    })(),
                    'legalitas'  => (function () {
                        TimelineLegalitas::query()->delete();
                        Legalitas::query()->delete();
                    })(),
                    default => null,
                };
            }

            // Reset ulang sisa_angsuran pada penghuni yang masih ada
            if (in_array('angsuran', $request->target_reset) || in_array('kas_masuk', $request->target_reset)) {
                Penghuni::all()->each(function ($p) {
                    $totalBayar = Angsuran::where('penghuni_id', $p->id)->where('status', 'lunas')->sum('jumlah_bayar');
                    $p->sisa_angsuran = max(0, $p->harga_rumah - $p->uang_muka - $totalBayar);
                    if ($p->sisa_angsuran <= 0) {
                        $p->status = 'lunas';
                    } else {
                        $p->status = 'aktif';
                    }
                    $p->save();
                });
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return redirect()->route('system.index')
            ->with('success', 'Reset data berhasil dilakukan sesuai pilihan yang dipilih.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PWA: Web App Manifest
    // ══════════════════════════════════════════════════════════════════════════
    public function manifest()
    {
        $manifest = [
            'name'             => config('app.name', 'LegalitasGo'),
            'short_name'       => 'LegalitasGo',
            'description'      => 'Sistem Manajemen Perumahan, Cicilan & Transparansi Legalitas Warga',
            'start_url'        => '/',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait-primary',
            'background_color' => '#ffffff',
            'theme_color'      => '#1d4ed8',
            'lang'             => 'id',
            'categories'       => ['finance', 'productivity'],
            'icons'            => [
                [
                    'src'     => '/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src'     => '/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
            'shortcuts' => [
                [
                    'name'       => 'Dashboard Finansial',
                    'short_name' => 'Dashboard',
                    'url'        => '/dashboard',
                    'icons'      => [['src' => '/icon-192.png', 'sizes' => '192x192']],
                ],
                [
                    'name'       => 'Laporan Publik Warga',
                    'short_name' => 'Laporan',
                    'url'        => '/laporan/publik',
                    'icons'      => [['src' => '/icon-192.png', 'sizes' => '192x192']],
                ],
                [
                    'name'       => 'Catat Cicilan',
                    'short_name' => 'Cicilan',
                    'url'        => '/pembayaran/create',
                    'icons'      => [['src' => '/icon-192.png', 'sizes' => '192x192']],
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PWA: Service Worker JS
    // ══════════════════════════════════════════════════════════════════════════
    public function serviceWorker()
    {
        $js = <<<'JS'
const CACHE_NAME = 'legalitasgo-v1';
const OFFLINE_URL = '/offline';

const PRECACHE_URLS = [
    '/',
    '/offline',
    '/laporan/publik',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
];

// Install: pre-cache shell resources
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(PRECACHE_URLS).catch(err => {
                console.warn('[SW] Pre-cache partial failure (CDN offline ok):', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate: remove old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Fetch: Network-first dengan fallback ke cache, lalu /offline
self.addEventListener('fetch', event => {
    // Hanya intercept GET & same-origin / CDN penting
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    const isSameOrigin = url.origin === location.origin;
    const isCDN = url.hostname.includes('jsdelivr.net') || url.hostname.includes('googleapis.com') || url.hostname.includes('gstatic.com');

    if (!isSameOrigin && !isCDN) return;

    // Untuk API polling — selalu network, jangan cache
    if (url.pathname.startsWith('/api/')) return;

    event.respondWith(
        fetch(event.request)
            .then(resp => {
                // Cache response baru
                if (resp && resp.status === 200 && (isSameOrigin || isCDN)) {
                    const clone = resp.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                }
                return resp;
            })
            .catch(async () => {
                const cached = await caches.match(event.request);
                if (cached) return cached;
                // Fallback halaman offline untuk navigasi
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
            })
    );
});
JS;

        return response($js)->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PWA: Halaman Offline Fallback
    // ══════════════════════════════════════════════════════════════════════════
    public function offline()
    {
        return view('pwa.offline');
    }
}
