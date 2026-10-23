<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogAdminActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya mencatat aktivitas admin yang telah terautentikasi
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $response;
        }

        // Jangan catat jika request menghasilkan error HTTP 4xx atau 5xx (kecuali validasi yang gagal tidak perlu dicatat sebagai aksi berhasil)
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            return $response;
        }

        // Abaikan polling API live atau asset rute
        $path = $request->path();
        if (str_starts_with($path, 'api/public/report-data') || str_starts_with($path, '_debugbar')) {
            return $response;
        }

        try {
            $this->recordActivity($request, $user);
        } catch (Throwable $e) {
            // Jangan biarkan error pencatatan log menghentikan response utama aplikasi
            report($e);
        }

        return $response;
    }

    /**
     * Catat log aktivitas ke database
     */
    protected function recordActivity(Request $request, $user): void
    {
        $method = strtoupper($request->method());
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $path = $request->path();

        [$action, $module, $description] = $this->resolveActionDetails($request, $method, $routeName, $path);

        ActivityLog::create([
            'user_id'     => $user->id,
            'admin_name'  => $user->name ?? 'Administrator',
            'admin_email' => $user->email,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'method'      => $method,
            'url'         => $request->fullUrl(),
            'ip_address'  => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 500),
            'created_at'  => now(),
        ]);
    }

    /**
     * Menganalisis aksi, modul, dan deskripsi deskriptif dari request
     */
    protected function resolveActionDetails(Request $request, string $method, ?string $routeName, string $path): array
    {
        $action = match ($method) {
            'POST'          => 'INSERT',
            'PUT', 'PATCH'  => 'UPDATE',
            'DELETE'        => 'DELETE',
            default         => 'VIEW',
        };

        $module = 'Umum';
        $desc = 'Mengakses halaman ' . $path;

        // 1. Dashboard
        if (str_starts_with($routeName ?? '', 'dashboard') || $path === 'dashboard') {
            $module = 'Dashboard Finansial';
            $desc = 'Membuka halaman Dashboard Finansial Pengelola';
            return [$action, $module, $desc];
        }

        // 2. Data Warga / Penghuni
        if (str_starts_with($routeName ?? '', 'penghuni') || str_starts_with($path, 'penghuni')) {
            $module = 'Data Warga / Kavling';
            if ($method === 'POST') {
                $nama = $request->input('nama_penghuni', 'Warga');
                $blok = $request->input('blok_kavling', '-');
                $desc = "Menambahkan warga / kavling baru: {$nama} (Blok {$blok})";
            } elseif (in_array($method, ['PUT', 'PATCH'])) {
                $nama = $request->input('nama_penghuni', 'Warga');
                $blok = $request->input('blok_kavling', '-');
                $desc = "Memperbarui data warga kavling: {$nama} (Blok {$blok})";
            } elseif ($method === 'DELETE') {
                $id = $request->route('penghuni') ?? $request->route('id') ?? '-';
                if (is_object($id)) $id = $id->id ?? '-';
                $desc = "Menghapus data warga / kavling (ID #{$id})";
            } elseif (str_contains($path, 'create')) {
                $desc = "Membuka formulir pendaftaran warga kavling baru";
            } elseif (str_contains($path, 'edit')) {
                $desc = "Membuka formulir edit data warga kavling";
            } elseif ($request->route('penghuni')) {
                $desc = "Melihat rincian data unit warga kavling";
            } else {
                $desc = "Membuka daftar Warga dan Kavling Perumahan";
            }
            return [$action, $module, $desc];
        }

        // 3. Pembayaran Cicilan & Angsuran
        if (str_starts_with($routeName ?? '', 'pembayaran') || str_starts_with($path, 'pembayaran')) {
            $module = 'Cicilan Angsuran';
            if ($method === 'POST') {
                $nominal = number_format((float) $request->input('jumlah_bayar', 0), 0, ',', '.');
                $ke = $request->input('angsuran_ke', '-');
                $desc = "Mencatat penerimaan pembayaran angsuran ke-{$ke} sebesar Rp {$nominal}";
            } elseif (str_contains($path, 'create')) {
                $desc = "Membuka formulir input pembayaran angsuran cicilan";
            } elseif ($request->route('angsuran')) {
                $desc = "Melihat detail / kuitansi pembayaran angsuran warga";
            } else {
                $desc = "Membuka riwayat pembayaran cicilan & angsuran warga";
            }
            return [$action, $module, $desc];
        }

        // 4. Buku Kas (Masuk & Keluar)
        if (str_starts_with($routeName ?? '', 'kas') || str_starts_with($path, 'kas')) {
            $module = 'Buku Kas';
            if ($method === 'POST') {
                if (str_contains($path, 'masuk')) {
                    $nominal = number_format((float) $request->input('jumlah', 0), 0, ',', '.');
                    $ket = $request->input('keterangan', 'Penerimaan Kas');
                    $desc = "Mencatat kas masuk sebesar Rp {$nominal} (Ket: {$ket})";
                } elseif (str_contains($path, 'keluar')) {
                    $nominal = number_format((float) $request->input('jumlah', 0), 0, ',', '.');
                    $ket = $request->input('keterangan', 'Pengeluaran Kas');
                    $desc = "Mencatat kas keluar sebesar Rp {$nominal} (Ket: {$ket})";
                } elseif (str_contains($path, 'kategori')) {
                    $namaKat = $request->input('nama_kategori', '-');
                    $jenisKat = $request->input('jenis', '-');
                    $desc = "Menambahkan kategori kas baru: {$namaKat} ({$jenisKat})";
                } else {
                    $desc = "Menambahkan transaksi kas";
                }
            } elseif ($method === 'DELETE') {
                if (str_contains($path, 'masuk')) {
                    $id = $request->route('kasMasuk') ?? $request->route('id') ?? '-';
                    if (is_object($id)) $id = $id->id ?? '-';
                    $desc = "Menghapus pencatatan transaksi kas masuk (ID #{$id})";
                } elseif (str_contains($path, 'keluar')) {
                    $id = $request->route('kasKeluar') ?? $request->route('id') ?? '-';
                    if (is_object($id)) $id = $id->id ?? '-';
                    $desc = "Menghapus pencatatan transaksi kas keluar (ID #{$id})";
                } else {
                    $desc = "Menghapus data transaksi kas";
                }
            } else {
                $desc = "Membuka buku Kas Masuk & Kas Keluar perumahan";
            }
            return [$action, $module, $desc];
        }

        // 5. Legalitas & Sertifikat
        if (str_starts_with($routeName ?? '', 'legalitas') || str_starts_with($path, 'legalitas') || str_starts_with($routeName ?? '', 'timeline')) {
            $module = 'Legalitas Sertifikat';
            if ($method === 'POST') {
                if (str_contains($path, 'timeline') || str_contains($routeName ?? '', 'timeline')) {
                    $kegiatan = $request->input('deskripsi_kegiatan', 'Progres Legalitas');
                    $desc = "Menambahkan catatan progres timeline sertifikat: {$kegiatan}";
                } else {
                    $jenis = $request->input('jenis_legalitas', 'Sertifikat');
                    $desc = "Mendaftarkan berkas kepengurusan legalitas baru ({$jenis})";
                }
            } elseif (in_array($method, ['PUT', 'PATCH'])) {
                $status = $request->input('status', '-');
                $desc = "Memperbarui status berkas legalitas menjadi '{$status}'";
            } elseif ($method === 'DELETE') {
                $id = $request->route('legalita') ?? $request->route('id') ?? '-';
                if (is_object($id)) $id = $id->id ?? '-';
                $desc = "Menghapus berkas legalitas sertifikat (ID #{$id})";
            } elseif (str_contains($path, 'create')) {
                $desc = "Membuka formulir pendaftaran berkas legalitas";
            } elseif (str_contains($path, 'edit')) {
                $desc = "Membuka formulir edit status legalitas";
            } elseif ($request->route('legalita')) {
                $desc = "Melihat detail riwayat pemberkasan legalitas warga";
            } else {
                $desc = "Membuka daftar pengelolaan berkas legalitas & sertifikat";
            }
            return [$action, $module, $desc];
        }

        // 6. Profil Perumahan
        if (str_starts_with($routeName ?? '', 'perumahan') || str_starts_with($path, 'perumahan')) {
            $module = 'Profil Perumahan';
            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $nama = $request->input('nama_perumahan', 'Perumahan');
                $desc = "Memperbarui konfigurasi profil perumahan ({$nama})";
            } else {
                $desc = "Membuka pengaturan profil data perumahan";
            }
            return [$action, $module, $desc];
        }

        // 7. Profil Akun Pengelola
        if (str_starts_with($routeName ?? '', 'profile') || str_starts_with($path, 'profile')) {
            $module = 'Akun Pengelola';
            if (in_array($method, ['PUT', 'PATCH', 'POST'])) {
                $desc = "Memperbarui profil atau password akun pengelola";
            } elseif ($method === 'DELETE') {
                $desc = "Menghapus akun pengelola";
            } else {
                $desc = "Membuka pengaturan profil & keamanan akun pengelola";
            }
            return [$action, $module, $desc];
        }

        // 8. Log Aktivitas Admin
        if (str_contains($path, 'log-aktivitas') || str_contains($routeName ?? '', 'activity-log')) {
            $module = 'Audit Trail';
            $desc = "Membuka halaman catatan Audit Trail & Log Aktivitas Admin";
            return [$action, $module, $desc];
        }

        // 9. Laporan Publik Warga (diakses oleh admin saat sedang login)
        if (str_starts_with($path, 'laporan/publik')) {
            $module = 'Laporan Publik';
            $desc = "Melihat halaman Laporan Transparansi Publik Warga";
            return [$action, $module, $desc];
        }
        if (str_starts_with($path, 'laporan/timeline')) {
            $module = 'Laporan Publik';
            $desc = "Melihat halaman Timeline Legalitas Publik Warga";
            return [$action, $module, $desc];
        }

        return [$action, $module, $desc];
    }
}
