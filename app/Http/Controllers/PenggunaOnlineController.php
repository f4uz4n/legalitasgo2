<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenggunaOnlineController extends Controller
{
    private const MENIT_ONLINE = 5;

    public function index()
    {
        $batas = now()->subMinutes(self::MENIT_ONLINE)->timestamp;
        $sesi = DB::table('sessions')
            ->where('last_activity', '>=', $batas)
            ->orderByDesc('last_activity')
            ->get();

        $pengguna = User::with('penghuni')
            ->whereIn('id', $sesi->pluck('user_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $akunOnline = $sesi
            ->filter(fn ($baris) => $baris->user_id && $pengguna->has($baris->user_id))
            ->groupBy('user_id')
            ->map(function ($kelompok) use ($pengguna) {
                $terbaru = $kelompok->sortByDesc('last_activity')->first();
                $user = $pengguna->get($terbaru->user_id);

                return [
                    'user' => $user,
                    'peran' => $this->peran($user),
                    'ip' => $terbaru->ip_address ?: '-',
                    'perangkat' => $this->perangkat($terbaru->user_agent),
                    'terakhir' => Carbon::createFromTimestamp($terbaru->last_activity),
                    'sesi' => $kelompok->count(),
                    'ini_saya' => (int) $terbaru->user_id === (int) auth()->id(),
                ];
            })
            ->sortByDesc(fn ($baris) => $baris['terakhir']->timestamp)
            ->values();

        $ipOnline = $sesi
            ->groupBy(fn ($baris) => $baris->ip_address ?: 'tidak-diketahui')
            ->map(function ($kelompok, $ip) use ($pengguna) {
                $terbaru = $kelompok->sortByDesc('last_activity')->first();
                $akun = $kelompok
                    ->pluck('user_id')
                    ->filter()
                    ->unique()
                    ->map(fn ($id) => $pengguna->get($id))
                    ->filter();

                return [
                    'ip' => $ip === 'tidak-diketahui' ? '-' : $ip,
                    'akun' => $akun,
                    'tamu' => $kelompok->contains(fn ($baris) => empty($baris->user_id)),
                    'perangkat' => $this->perangkat($terbaru->user_agent),
                    'terakhir' => Carbon::createFromTimestamp($terbaru->last_activity),
                    'sesi' => $kelompok->count(),
                ];
            })
            ->sortByDesc(fn ($baris) => $baris['terakhir']->timestamp)
            ->values();

        $jumlahPengangsur = $akunOnline->filter(fn ($baris) => $baris['peran'] === 'Pengangsur')->count();

        return view('pengguna_online.index', [
            'akunOnline' => $akunOnline,
            'ipOnline' => $ipOnline,
            'jumlahPengangsur' => $jumlahPengangsur,
            'menitOnline' => self::MENIT_ONLINE,
        ]);
    }

    private function peran(User $user): string
    {
        if ($user->isWarga() || $user->penghuni) {
            return 'Pengangsur';
        }

        return $user->peranLabel();
    }

    private function perangkat(?string $agen): string
    {
        $agen = (string) $agen;
        $alat = str_contains($agen, 'Mobile') || str_contains($agen, 'Android') || str_contains($agen, 'iPhone')
            ? 'Ponsel'
            : 'Komputer';

        $browser = 'Peramban lain';
        foreach (['Edg' => 'Edge', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'] as $jejak => $nama) {
            if (str_contains($agen, $jejak)) {
                $browser = $nama;
                break;
            }
        }

        return $alat . ' · ' . $browser;
    }
}
