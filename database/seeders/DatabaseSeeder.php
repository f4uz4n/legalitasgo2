<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Perumahan;
use App\Models\Penghuni;
use App\Models\Angsuran;
use App\Models\KategoriKas;
use App\Models\KasMasuk;
use App\Models\KasKeluar;
use App\Models\Legalitas;
use App\Models\TimelineLegalitas;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin user
        User::create([
            'name' => 'Pengelola Perumahan',
            'email' => 'admin@legalitasgo.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Kategori Kas Masuk & Keluar
        $kategoriMasuk = [
            KategoriKas::create(['nama_kategori' => 'Angsuran Cicilan Rumah', 'jenis' => 'masuk', 'keterangan' => 'Penerimaan pembayaran cicilan bulanan warga']),
            KategoriKas::create(['nama_kategori' => 'Uang Muka (DP)', 'jenis' => 'masuk', 'keterangan' => 'Penerimaan pembayaran DP rumah']),
            KategoriKas::create(['nama_kategori' => 'Iuran & Dana Masuk Lainnya', 'jenis' => 'masuk', 'keterangan' => 'Penerimaan kas penunjang']),
        ];

        $kategoriKeluar = [
            KategoriKas::create(['nama_kategori' => 'Pengurusan Notaris & AJB', 'jenis' => 'keluar', 'keterangan' => 'Biaya pembuatan akta jual beli dan jasa PPAT']),
            KategoriKas::create(['nama_kategori' => 'Pendaftaran & Balik Nama BPN', 'jenis' => 'keluar', 'keterangan' => 'Biaya resmi pendaftaran sertifikat di kantor pertanahan']),
            KategoriKas::create(['nama_kategori' => 'Pajak BPHTB & PPh Final', 'jenis' => 'keluar', 'keterangan' => 'Penyetoran bea perolehan hak atas tanah dan bangunan']),
            KategoriKas::create(['nama_kategori' => 'Pengukuran & Pemecahan Sertifikat', 'jenis' => 'keluar', 'keterangan' => 'Biaya juru ukur dan plotting kadastral']),
            KategoriKas::create(['nama_kategori' => 'Operasional Pengelolaan & Transport', 'jenis' => 'keluar', 'keterangan' => 'Biaya operasional pengurusan berkas legalitas']),
        ];

        // 3. SATU PERUMAHAN SAJA (Single Housing Project)
        $perumahan = Perumahan::create([
            'nama_perumahan' => 'Griya Harmoni Residence',
            'alamat' => 'Jl. Raya Harmoni No. 88, Cibinong',
            'kecamatan' => 'Cibinong',
            'kelurahan' => 'Pakansari',
            'kota' => 'Kabupaten Bogor',
            'provinsi' => 'Jawa Barat',
            'total_unit' => 40,
        ]);

        // 4. Data Warga / Penghuni pada Perumahan ini
        $dataWarga = [
            ['nama' => 'Ahmad Fauzi', 'blok' => 'A-01', 'tipe' => '36/72', 'harga' => 285000000, 'lunas' => true],
            ['nama' => 'Budi Santoso', 'blok' => 'A-02', 'tipe' => '45/90', 'harga' => 385000000, 'lunas' => false],
            ['nama' => 'Citra Dewi', 'blok' => 'A-03', 'tipe' => '36/72', 'harga' => 285000000, 'lunas' => false],
            ['nama' => 'Dian Permata', 'blok' => 'B-01', 'tipe' => '54/105', 'harga' => 485000000, 'lunas' => false],
            ['nama' => 'Eko Prasetyo', 'blok' => 'B-02', 'tipe' => '45/90', 'harga' => 385000000, 'lunas' => false],
            ['nama' => 'Fitri Handayani', 'blok' => 'B-03', 'tipe' => '36/72', 'harga' => 285000000, 'lunas' => false],
            ['nama' => 'Gunawan Wibowo', 'blok' => 'C-01', 'tipe' => '54/105', 'harga' => 485000000, 'lunas' => true],
            ['nama' => 'Heni Susanti', 'blok' => 'C-02', 'tipe' => '45/90', 'harga' => 385000000, 'lunas' => false],
            ['nama' => 'Irfan Maulana', 'blok' => 'C-03', 'tipe' => '36/72', 'harga' => 285000000, 'lunas' => false],
            ['nama' => 'Joko Widodo', 'blok' => 'D-01', 'tipe' => '54/105', 'harga' => 485000000, 'lunas' => false],
            ['nama' => 'Kartini Sari', 'blok' => 'D-02', 'tipe' => '36/72', 'harga' => 285000000, 'lunas' => false],
            ['nama' => 'Lukman Hakim', 'blok' => 'D-03', 'tipe' => '45/90', 'harga' => 385000000, 'lunas' => false],
            ['nama' => 'Maya Anggraeni', 'blok' => 'E-01', 'tipe' => '36/72', 'harga' => 285000000, 'lunas' => false],
            ['nama' => 'Nurul Hidayah', 'blok' => 'E-02', 'tipe' => '45/90', 'harga' => 385000000, 'lunas' => false],
            ['nama' => 'Oscar Tanujaya', 'blok' => 'E-03', 'tipe' => '54/105', 'harga' => 485000000, 'lunas' => false],
        ];

        $legalitasStages = ['selesai', 'proses_bpn', 'proses_notaris', 'verifikasi', 'pengajuan', 'belum_proses'];

        foreach ($dataWarga as $idx => $dw) {
            $uangMuka = $dw['harga'] * 0.15;
            $sisaAngsuran = $dw['harga'] - $uangMuka;
            $tenor = 60; // 5 tahun
            $tanggalAkad = Carbon::now()->subMonths(rand(6, 18));

            $penghuni = Penghuni::create([
                'perumahan_id' => $perumahan->id,
                'nama_lengkap' => $dw['nama'],
                'nik' => '327501' . str_pad($idx + 10, 10, '0', STR_PAD_LEFT),
                'no_hp' => '0812' . rand(10000000, 99999999),
                'email' => strtolower(str_replace(' ', '.', $dw['nama'])) . '@warga.id',
                'blok_kavling' => $dw['blok'],
                'tipe_rumah' => $dw['tipe'],
                'luas_tanah' => (int) explode('/', $dw['tipe'])[1],
                'luas_bangunan' => (int) explode('/', $dw['tipe'])[0],
                'harga_rumah' => $dw['harga'],
                'uang_muka' => $uangMuka,
                'sisa_angsuran' => $dw['lunas'] ? 0 : ($sisaAngsuran * 0.65),
                'tenor_bulan' => $tenor,
                'tanggal_akad' => $tanggalAkad,
                'status' => $dw['lunas'] ? 'lunas' : 'aktif',
            ]);

            // Catat Kas Masuk untuk DP Rumah Warga
            KasMasuk::create([
                'kategori_kas_id' => $kategoriMasuk[1]->id,
                'penghuni_id' => $penghuni->id,
                'tanggal' => $tanggalAkad,
                'jumlah' => $uangMuka,
                'metode_bayar' => 'Transfer Bank',
                'no_referensi' => 'DP-' . $penghuni->blok_kavling . '-' . date('Ymd', strtotime($tanggalAkad)),
                'keterangan' => 'Pembayaran Uang Muka (DP) Kavling ' . $penghuni->blok_kavling . ' a/n ' . $penghuni->nama_lengkap,
                'created_by' => 1,
            ]);

            // Buat Cicilan Bulanan
            $angsuranPerBulan = $sisaAngsuran / $tenor;
            $paidCount = $dw['lunas'] ? 24 : rand(3, 14);

            for ($i = 1; $i <= min($tenor, 24); $i++) {
                $jatuhTempo = $tanggalAkad->copy()->addMonths($i);
                $isPaid = $i <= $paidCount;
                $isOverdue = !$isPaid && $jatuhTempo->isPast();

                $angsuran = Angsuran::create([
                    'penghuni_id' => $penghuni->id,
                    'angsuran_ke' => $i,
                    'jumlah_angsuran' => round($angsuranPerBulan, 0),
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'tanggal_bayar' => $isPaid ? $jatuhTempo->copy()->subDays(rand(1, 4)) : null,
                    'jumlah_bayar' => $isPaid ? round($angsuranPerBulan, 0) : 0,
                    'status' => $isPaid ? 'lunas' : ($isOverdue ? 'terlambat' : 'belum_bayar'),
                    'keterangan' => $isPaid ? 'Lunas via Transfer' : null,
                ]);

                // Kas Masuk dari cicilan yang sudah dibayar
                if ($isPaid) {
                    KasMasuk::create([
                        'kategori_kas_id' => $kategoriMasuk[0]->id,
                        'penghuni_id' => $penghuni->id,
                        'angsuran_id' => $angsuran->id,
                        'tanggal' => $angsuran->tanggal_bayar,
                        'jumlah' => $angsuran->jumlah_bayar,
                        'metode_bayar' => collect(['Transfer Bank BCA', 'Transfer Mandiri', 'Tunai Kasir'])->random(),
                        'no_referensi' => 'TRX-' . $penghuni->blok_kavling . '-BLN' . $i,
                        'keterangan' => 'Cicilan Angsuran Ke-' . $i . ' - Kavling ' . $penghuni->blok_kavling . ' (' . $penghuni->nama_lengkap . ')',
                        'created_by' => 1,
                    ]);
                }
            }

            // Legalitas Sertifikat Rumah untuk masing-masing warga
            $stage = $legalitasStages[$idx % count($legalitasStages)];
            $progressPercent = match($stage) {
                'belum_proses' => 0,
                'pengajuan' => 20,
                'verifikasi' => 40,
                'proses_notaris' => 60,
                'proses_bpn' => 80,
                'selesai' => 100,
            };

            $legalitas = Legalitas::create([
                'penghuni_id' => $penghuni->id,
                'jenis_sertifikat' => collect(['SHM (Sertifikat Hak Milik)', 'SHGB', 'AJB PPAT'])->random(),
                'no_ajb' => 'AJB/' . date('Y') . '/' . $penghuni->blok_kavling,
                'tanggal_ajb' => $tanggalAkad->copy()->addMonths(1),
                'notaris' => 'H. Bambang Irawan, SH., M.Kn (PPAT Kab. Bogor)',
                'status' => $stage,
                'progress_persen' => $progressPercent,
                'tanggal_mulai' => $tanggalAkad->copy()->addMonths(1),
                'estimasi_selesai' => $tanggalAkad->copy()->addMonths(8),
                'tanggal_selesai' => $stage === 'selesai' ? $tanggalAkad->copy()->addMonths(6) : null,
                'biaya_pengurusan' => 12500000,
                'biaya_terbayar' => $stage === 'selesai' ? 12500000 : 7500000,
                'keterangan' => 'Pengurusan sertifikat kavling ' . $penghuni->blok_kavling,
            ]);

            // Timeline tahapan legalitas
            $timelineTemplates = [
                ['judul' => 'Pengumpulan Berkas & KTP', 'deskripsi' => 'Kelengkapan fotocopy KTP, KK, NPWP & SPPT PBB telah diverifikasi pengelola.', 'status' => 'selesai', 'icon' => 'bi-file-earmark-check', 'warna' => '#2563eb'],
                ['judul' => 'Pengecekan Sertifikat Induk di BPN', 'deskripsi' => 'Plotting dan validasi keabsahan buku tanah induk di BPN Cibinong.', 'status' => $progressPercent >= 40 ? 'selesai' : 'proses', 'icon' => 'bi-search', 'warna' => '#0284c7'],
                ['judul' => 'Penandatanganan AJB di Notaris', 'deskripsi' => 'Penandatanganan Akta Jual Beli resmi dihadapan Pejabat Pembuat Akta Tanah.', 'status' => $progressPercent >= 60 ? 'selesai' : ($progressPercent >= 40 ? 'proses' : 'pending'), 'icon' => 'bi-pen', 'warna' => '#4f46e5'],
                ['judul' => 'Validasi BPHTB & Balik Nama BPN', 'deskripsi' => 'Penyetoran pajak daerah dan pendaftaran pendaftaran balik nama buku tanah perorangan.', 'status' => $progressPercent >= 80 ? 'selesai' : ($progressPercent >= 60 ? 'proses' : 'pending'), 'icon' => 'bi-bank', 'warna' => '#059669'],
                ['judul' => 'Sertifikat Pecahan Terbit & Diserahkan', 'deskripsi' => 'Sertifikat pecahan atas nama pemilik kavling resmi terbit dari BPN.', 'status' => $progressPercent == 100 ? 'selesai' : 'pending', 'icon' => 'bi-award', 'warna' => '#16a34a'],
            ];

            foreach ($timelineTemplates as $tIdx => $tl) {
                if ($progressPercent >= ($tIdx * 20)) {
                    TimelineLegalitas::create([
                        'legalitas_id' => $legalitas->id,
                        'kavling_terkait' => 'Kavling ' . $penghuni->blok_kavling,
                        'tanggal' => $tanggalAkad->copy()->addMonths($tIdx + 1),
                        'judul' => $tl['judul'] . ' (' . $penghuni->blok_kavling . ')',
                        'deskripsi' => $tl['deskripsi'],
                        'status' => $tl['status'],
                        'icon' => $tl['icon'],
                        'warna' => $tl['warna'],
                        'created_by' => 1,
                    ]);
                }
            }
        }

        // Project-wide legalitas milestones
        $projectMilestones = [
            [
                'judul' => 'Pengajuan Berkas Validasi Pajak ke Bapenda',
                'kavling' => 'Tahap 1 (Blok A & B)',
                'tanggal' => Carbon::now()->subMonths(3),
                'status' => 'selesai',
                'deskripsi' => 'Pengajuan berkas SSPD BPHTB dan validasi data PPh final ke Bapenda Kab. Bogor telah diverifikasi dan disetujui.',
                'icon' => 'bi-receipt-cutoff',
                'warna' => '#1d4ed8'
            ],
            [
                'judul' => 'Pengecekan Keabsahan Sertifikat Induk di BPN',
                'kavling' => 'Seluruh Proyek Perumahan',
                'tanggal' => Carbon::now()->subMonths(4),
                'status' => 'selesai',
                'deskripsi' => 'Plotting peta bidang tanah dan pengecekan warkah sertifikat induk di BPN Cibinong dinyatakan bersih dan bebas sengketa.',
                'icon' => 'bi-shield-check',
                'warna' => '#059669'
            ],
            [
                'judul' => 'Penandatanganan Akta Jual Beli (AJB) di Notaris',
                'kavling' => 'Blok A (Unit A-01 s/d A-03)',
                'tanggal' => Carbon::now()->subMonths(2),
                'status' => 'selesai',
                'deskripsi' => 'Proses penandatanganan akta resmi PPAT dihadiri oleh para pihak pembeli dan pengembang di Kantor Notaris Bambang Irawan.',
                'icon' => 'bi-pen',
                'warna' => '#7c3aed'
            ],
            [
                'judul' => 'Pengajuan Balik Nama Sertifikat Pecahan ke Kantor Pertanahan',
                'kavling' => 'Kavling Blok B & C',
                'tanggal' => Carbon::now()->subWeeks(3),
                'status' => 'proses',
                'deskripsi' => 'Berkas pendaftaran hak milik perorangan telah diserahkan ke loket pendaftaran BPN. Estimasi terbit buku tanah dalam 60 hari kerja.',
                'icon' => 'bi-hourglass-split',
                'warna' => '#d97706'
            ],
            [
                'judul' => 'Pengajuan Berkas Pajak Lanjutan Tahap 2 ke Bapenda',
                'kavling' => 'Tahap 2 (Blok D & E)',
                'tanggal' => Carbon::now()->subDays(5),
                'status' => 'proses',
                'deskripsi' => 'Admin telah menginput dan menyerahkan berkas PBB dan BPHTB tahap 2 ke kantor Bapenda Pajak daerah.',
                'icon' => 'bi-building-up',
                'warna' => '#2563eb'
            ],
        ];

        foreach ($projectMilestones as $pm) {
            TimelineLegalitas::create([
                'legalitas_id' => null,
                'kavling_terkait' => $pm['kavling'],
                'tanggal' => $pm['tanggal'],
                'judul' => $pm['judul'],
                'deskripsi' => $pm['deskripsi'],
                'status' => $pm['status'],
                'icon' => $pm['icon'],
                'warna' => $pm['warna'],
                'created_by' => 1,
            ]);
        }

        // 5. Pencatatan Segala Pengeluaran Kas (Penggunaan Dana Transparan)
        $daftarPengeluaran = [
            ['kat' => 0, 'jml' => 18500000, 'penerima' => 'Kantor Notaris Bambang Irawan, SH', 'ket' => 'Honorarium pembuatan 5 Akta Jual Beli (AJB) Blok A & B'],
            ['kat' => 1, 'jml' => 14200000, 'penerima' => 'Kantor Pertanahan BPN Kab. Bogor', 'ket' => 'Biaya resmi PNBP pendaftaran dan balik nama sertifikat 4 kavling'],
            ['kat' => 2, 'jml' => 22800000, 'penerima' => 'Bapenda Kab. Bogor (Kas Daerah)', 'ket' => 'Pembayaran BPHTB dan validasi pajak pembeli kavling tahap 1'],
            ['kat' => 3, 'jml' => 9500000,  'penerima' => 'Tim Ukur Kadastral BPN', 'ket' => 'Biaya pengukuran lapangan dan pemecahan batas patok kavling'],
            ['kat' => 0, 'jml' => 12000000, 'penerima' => 'Kantor Notaris Bambang Irawan, SH', 'ket' => 'Biaya legalisasi dan roya hak tanggungan induk'],
            ['kat' => 1, 'jml' => 8750000,  'penerima' => 'Kantor Pertanahan BPN Kab. Bogor', 'ket' => 'Penerbitan blanko sertifikat hak milik (SHM) Blok C'],
            ['kat' => 4, 'jml' => 3200000,  'penerima' => 'Staff Lapangan Pengelola', 'ket' => 'Operasional koordinasi berkas warga ke BPN dan Pemda'],
            ['kat' => 2, 'jml' => 16500000, 'penerima' => 'Bapenda Kab. Bogor (Kas Daerah)', 'ket' => 'Pelunasan BPHTB lanjutan untuk kavling D & E'],
            ['kat' => 4, 'jml' => 2100000,  'penerima' => 'Percetakan & ATK Harmoni', 'ket' => 'Penggandaan warkah dokumen perumahan dan map sertifikat'],
        ];

        foreach ($daftarPengeluaran as $pIdx => $dp) {
            KasKeluar::create([
                'kategori_kas_id' => $kategoriKeluar[$dp['kat']]->id,
                'tanggal' => Carbon::now()->subDays(rand(5, 120)),
                'jumlah' => $dp['jml'],
                'penerima' => $dp['penerima'],
                'keterangan' => $dp['ket'],
                'created_by' => 1,
            ]);
        }

        // 6. Pengaturan Profil Perumahan & Sistem
        Pengaturan::setValue('nama_perumahan', 'Griya Harmoni Residence', 'umum', 'Nama perumahan');
        Pengaturan::setValue('nama_pengelola', 'Pengelola Griya Harmoni', 'umum', 'Badan / Tim Pengelola');
        Pengaturan::setValue('alamat_perumahan', 'Jl. Raya Harmoni No. 88, Pakansari, Cibinong, Kab. Bogor', 'umum', 'Alamat lengkap');
        Pengaturan::setValue('telepon', '0812-3456-7890', 'umum', 'Kontak WhatsApp Pengelola');
        Pengaturan::setValue('email', 'harmoni.residence@gmail.com', 'umum', 'Email resmi pengelola');
    }
}
