<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penghuni extends Model
{
    use HasFactory;

    protected $table = 'penghuni';

    protected $fillable = [
        'user_id',
        'perumahan_id',
        'nama_lengkap',
        'nik',
        'no_hp',
        'email',
        'blok_kavling',
        'tahun_pembelian',
        'jenis_pembayaran',
        'tipe_rumah',
        'luas_tanah',
        'luas_bangunan',
        'harga_rumah',
        'uang_muka',
        'nominal_angsuran',
        'sisa_angsuran',
        'tenor_bulan',
        'sisa_tempo',
        'tunggakan',
        'tanggal_akad',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_akad' => 'date',
        'harga_rumah' => 'decimal:2',
        'uang_muka' => 'decimal:2',
        'nominal_angsuran' => 'decimal:2',
        'sisa_angsuran' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perumahan()
    {
        return $this->belongsTo(Perumahan::class);
    }

    public function angsuran()
    {
        return $this->hasMany(Angsuran::class);
    }

    /**
     * Nominal tiap sisa tempo. Jumlah seluruh tempo sama dengan sisa pembayaran rekap.
     * Tempo biasa memakai angsuran yang diisi, dan selisih diletakkan di tempo terakhir.
     * Jika angsuran kali sisa tempo melebihi sisa pembayaran, saldo dibagi rata.
     */
    public function nominalJadwalSisa(): array
    {
        $tempo = (int) $this->sisa_tempo;
        $target = (int) round((float) $this->sisa_angsuran);
        $nominal = (int) round((float) $this->nominal_angsuran);

        if ($tempo < 1 || $target < 1 || $nominal < 1) {
            return [];
        }

        $terakhir = $target - $nominal * ($tempo - 1);
        if ($terakhir > 0) {
            return array_merge(array_fill(0, $tempo - 1, $nominal), [$terakhir]);
        }

        $dasar = intdiv($target, $tempo);
        $sisaRupiah = $target - $dasar * $tempo;
        $jumlah = array_fill(0, $tempo, $dasar);
        $jumlah[$tempo - 1] += $sisaRupiah;

        return $jumlah;
    }

    /**
     * Buat jadwal untuk sisa tempo yang masih berjalan.
     * Pembayaran lama tidak dicatat ulang sebagai kas masuk.
     * Mengembalikan false bila jadwal lama sudah memiliki pembayaran.
     */
    public function buatJadwalSisa(): bool
    {
        if ($this->angsuran()->where('jumlah_bayar', '>', 0)->exists()) {
            return false;
        }

        $this->angsuran()->delete();

        $jumlah = $this->nominalJadwalSisa();
        $sisa = count($jumlah);
        if ($sisa < 1) {
            return true;
        }

        $telat = min($sisa, (int) ($this->tunggakan ?? 0));
        $sudahBayar = max(0, (int) $this->tenor_bulan - $sisa);
        if ($sisa > (int) $this->tenor_bulan) {
            $sudahBayar = 0;
        }

        $awalTerlambat = Carbon::now()->startOfMonth()->subMonths($telat);
        $awalBerjalan = Carbon::now()->startOfMonth()->addMonth();

        for ($i = 1; $i <= $sisa; $i++) {
            $terlambat = $i <= $telat;
            $jatuhTempo = $terlambat
                ? $awalTerlambat->copy()->addMonths($i - 1)
                : $awalBerjalan->copy()->addMonths($i - $telat - 1);

            Angsuran::create([
                'penghuni_id' => $this->id,
                'angsuran_ke' => $sudahBayar + $i,
                'jumlah_angsuran' => $jumlah[$i - 1],
                'tanggal_jatuh_tempo' => $jatuhTempo,
                'status' => $terlambat ? 'terlambat' : 'belum_bayar',
            ]);
        }

        return true;
    }

    public function kasMasuk()
    {
        return $this->hasMany(KasMasuk::class);
    }

    public function legalitas()
    {
        return $this->hasOne(Legalitas::class);
    }

    public function getTotalBayarAttribute()
    {
        return $this->angsuran()->where('status', 'lunas')->sum('jumlah_bayar');
    }

    public function getAngsuranBulananAttribute()
    {
        if ((float) $this->nominal_angsuran > 0) {
            return (float) $this->nominal_angsuran;
        }
        if ($this->tenor_bulan > 0) {
            return ((float) $this->harga_rumah - (float) $this->uang_muka) / $this->tenor_bulan;
        }
        return 0;
    }

    public function getProgressBayarAttribute()
    {
        if ((float) $this->harga_rumah > 0) {
            $terbayar = (float) $this->harga_rumah - (float) $this->sisa_angsuran;
            return min(100, max(0, round(($terbayar / (float) $this->harga_rumah) * 100, 1)));
        }
        return 0;
    }

    public function getJenisPembayaranLabelAttribute(): string
    {
        return $this->jenis_pembayaran === 'cash_lunak' ? 'CASH LUNAK' : 'INHOUSE';
    }

    public function getTempoLabelAttribute(): string
    {
        return (int) $this->tenor_bulan . 'X';
    }

    public function getSisaTempoLabelAttribute(): string
    {
        if ($this->sisa_tempo === null) {
            return '-';
        }

        return (int) $this->sisa_tempo . 'X';
    }

    public function getTunggakanLabelAttribute(): string
    {
        return (int) $this->tunggakan > 0 ? (int) $this->tunggakan . 'X' : '-';
    }
}
