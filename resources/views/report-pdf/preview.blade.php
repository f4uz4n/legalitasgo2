<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan {{ $perumahan->nama_perumahan ?? 'Perumahan' }} — {{ $labelPeriode }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4;
            margin: 1.5cm 1.8cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: #fff;
            line-height: 1.5;
        }

        /* ─── Print Button Bar (only on screen, hidden in print) ─── */
        .print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1d4ed8;
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 9999;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .print-bar .info { font-size: 0.9rem; font-weight: 600; }
        .print-bar .actions { display: flex; gap: 10px; }
        .btn-print {
            background: #fff;
            color: #1d4ed8;
            border: none;
            padding: 8px 22px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
        }
        .btn-close-tab {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
        }

        /* ─── Main Document ──────────────────────────────────────────────── */
        .document-wrapper {
            padding-top: 60px; /* offset for print-bar on screen */
            max-width: 800px;
            margin: 0 auto;
        }

        /* ─── Kop Surat / Header ─────────────────────────────────────────── */
        .kop-surat {
            border-bottom: 3px double #1d4ed8;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .kop-logo {
            width: 64px;
            height: 64px;
            background: #1d4ed8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 28px;
            font-weight: 900;
            flex-shrink: 0;
        }
        .kop-text { flex: 1; }
        .kop-text h1 {
            font-size: 15pt;
            font-weight: 900;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .kop-text p {
            font-size: 9.5pt;
            color: #475569;
            margin: 0;
        }

        /* ─── Judul Laporan ──────────────────────────────────────────────── */
        .judul-laporan {
            text-align: center;
            margin: 18px 0 6px;
        }
        .judul-laporan h2 {
            font-size: 13pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
            color: #0f172a;
        }
        .judul-laporan p {
            font-size: 10.5pt;
            color: #475569;
            margin-top: 4px;
        }

        /* ─── Info Bar ───────────────────────────────────────────────────── */
        .info-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 18px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            font-size: 9.5pt;
        }
        .info-bar span { color: #64748b; }
        .info-bar strong { color: #1e293b; }

        /* ─── Section Header ─────────────────────────────────────────────── */
        .section-header {
            background: #1d4ed8;
            color: #fff;
            padding: 5px 12px;
            font-weight: 700;
            font-size: 10.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 18px;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .section-header-light {
            background: #eff6ff;
            color: #1d4ed8;
            border-left: 4px solid #1d4ed8;
            padding: 5px 12px;
            font-weight: 700;
            font-size: 10.5pt;
            margin-top: 14px;
            margin-bottom: 8px;
        }

        /* ─── KPI Summary Cards ──────────────────────────────────────────── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }
        .kpi-card .kpi-label {
            font-size: 8.5pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .kpi-card .kpi-value {
            font-size: 13pt;
            font-weight: 900;
            color: #1d4ed8;
            line-height: 1.2;
        }
        .kpi-card .kpi-sub {
            font-size: 8pt;
            color: #94a3b8;
            margin-top: 2px;
        }
        .kpi-card.green .kpi-value { color: #16a34a; }
        .kpi-card.red   .kpi-value { color: #dc2626; }
        .kpi-card.slate .kpi-value { color: #334155; }

        /* ─── Tables ─────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 12px;
        }
        thead th {
            background: #1e293b;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 9pt;
        }
        thead th.text-right { text-align: right; }
        thead th.text-center { text-align: center; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 5px 8px; vertical-align: top; }
        tbody td.text-right { text-align: right; }
        tbody td.text-center { text-align: center; }
        tfoot td {
            padding: 7px 8px;
            font-weight: 700;
            border-top: 2px solid #1e293b;
            font-size: 10pt;
        }
        tfoot td.text-right { text-align: right; }

        .badge-status {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 700;
        }
        .badge-lunas   { background: #dcfce7; color: #16a34a; }
        .badge-aktif   { background: #dbeafe; color: #1d4ed8; }
        .badge-default { background: #f1f5f9; color: #475569; }

        /* ─── Tanda Tangan ───────────────────────────────────────────────── */
        .ttd-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .ttd-kota {
            font-size: 10.5pt;
            margin-bottom: 20px;
        }
        .ttd-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }
        .ttd-box {
            text-align: center;
        }
        .ttd-box .jabatan {
            font-size: 10pt;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 60px;
        }
        .ttd-box .ttd-line {
            border-bottom: 1.5px solid #1e293b;
            margin-bottom: 4px;
        }
        .ttd-box .nama {
            font-size: 10pt;
            font-weight: 700;
        }
        .ttd-anggota-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        /* ─── Page Break ─────────────────────────────────────────────────── */
        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }

        /* ─── Footer Page ────────────────────────────────────────────────── */
        .doc-footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8.5pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* ─── Print Media: Hide toolbar ──────────────────────────────────── */
        @media print {
            .print-bar { display: none !important; }
            .document-wrapper { padding-top: 0 !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- ─── Print Toolbar (layar saja) ─────────────────────────────────────── -->
    <div class="print-bar">
        <div class="info">
            📄 Laporan {{ $perumahan->nama_perumahan ?? '' }} — {{ $labelPeriode }}
        </div>
        <div class="actions">
            <button class="btn-close-tab" onclick="window.close()">✕ Tutup</button>
            <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        </div>
    </div>

    <!-- ─── Dokumen Utama ───────────────────────────────────────────────────── -->
    <div class="document-wrapper">

        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="kop-logo">⌂</div>
            <div class="kop-text">
                <h1>{{ $perumahan->nama_perumahan ?? 'PERUMAHAN' }}</h1>
                <p>{{ $perumahan->alamat ?? '' }}</p>
                <p>Sistem Pengelolaan Administrasi & Keuangan Perumahan — LegalitasGo</p>
            </div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="judul-laporan">
            <h2>
                @if($jenis === 'keuangan') Laporan Keuangan
                @elseif($jenis === 'cicilan') Laporan Cicilan / Angsuran
                @elseif($jenis === 'penghuni') Data Daftar Penghuni
                @else Laporan Keuangan & Administrasi Lengkap
                @endif
            </h2>
            <p>Periode: <strong>{{ $labelPeriode }}</strong></p>
        </div>

        <!-- INFO BAR -->
        <div class="info-bar">
            <div><span>Perumahan: </span><strong>{{ $perumahan->nama_perumahan ?? '-' }}</strong></div>
            <div><span>Total Unit: </span><strong>{{ number_format($perumahan->total_unit ?? 0) }} Unit</strong></div>
            <div><span>Dicetak: </span><strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong></div>
            <div><span>Periode: </span><strong>{{ $labelPeriode }}</strong></div>
        </div>

        @if($jenis === 'lengkap' || $jenis === 'keuangan')

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- SEKSI 1: RINGKASAN KEUANGAN                                   -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <div class="section-header">I. Ringkasan Keuangan</div>

        <div class="kpi-grid">
            <div class="kpi-card green">
                <div class="kpi-label">Total Kas Masuk</div>
                <div class="kpi-value">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</div>
                <div class="kpi-sub">Penerimaan periode ini</div>
            </div>
            <div class="kpi-card red">
                <div class="kpi-label">Total Kas Keluar</div>
                <div class="kpi-value">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</div>
                <div class="kpi-sub">Pengeluaran periode ini</div>
            </div>
            <div class="kpi-card {{ $saldoKas >= 0 ? 'green' : 'red' }}">
                <div class="kpi-label">Saldo Kas Bersih</div>
                <div class="kpi-value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                <div class="kpi-sub">{{ $saldoKas >= 0 ? 'Surplus' : 'Defisit' }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Angsuran Terbayar</div>
                <div class="kpi-value">Rp {{ number_format($totalAngsuranTerbayar, 0, ',', '.') }}</div>
                <div class="kpi-sub">Cicilan terbayar periode ini</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Terkumpul</div>
                <div class="kpi-value">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</div>
                <div class="kpi-sub">DP + Angsuran ({{ $progressCollecting }}% dari target)</div>
            </div>
            <div class="kpi-card slate">
                <div class="kpi-label">Target Nilai Rumah</div>
                <div class="kpi-value">Rp {{ number_format($totalNilaiRumah, 0, ',', '.') }}</div>
                <div class="kpi-sub">Total nilai seluruh unit</div>
            </div>
        </div>

        <!-- Uang Masuk By Bulan -->
        @if($uangMasukByBulan->count() > 0)
        <div class="section-header-light">Rekapitulasi Kas Masuk Per Bulan</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Periode Bulan</th>
                    <th class="text-center">Frekuensi Transaksi</th>
                    <th class="text-right">Total Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($uangMasukByBulan as $i => $bln)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $bln['bulan_nama'] }}</strong></td>
                    <td class="text-center">{{ number_format($bln['frekuensi']) }} transaksi</td>
                    <td class="text-right">Rp {{ number_format($bln['total_nominal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL KAS MASUK</td>
                    <td class="text-right">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        <!-- Pengeluaran Kas Per Kategori -->
        @if($kasKeluarPerKategori->count() > 0)
        <div class="section-header-light">Rekapitulasi Pengeluaran Kas Per Kategori</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Kategori Pengeluaran</th>
                    <th class="text-right">Total (Rp)</th>
                    <th class="text-right" style="width:120px;">% dari Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kasKeluarPerKategori as $i => $kat)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $kat['kategori'] }}</td>
                    <td class="text-right">Rp {{ number_format($kat['total'], 0, ',', '.') }}</td>
                    <td class="text-right">
                        {{ $totalKasKeluar > 0 ? number_format(($kat['total']/$totalKasKeluar)*100, 1) : '0' }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL KAS KELUAR</td>
                    <td class="text-right">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tfoot>
        </table>
        @endif

        <!-- Rincian Kas Keluar -->
        @if($kasKeluarDetail->count() > 0)
        <div class="no-break">
        <div class="section-header-light">Rincian Detail Pengeluaran Kas</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 90px;">Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th class="text-right">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kasKeluarDetail as $i => $kel)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $kel->tanggal ? \Carbon\Carbon::parse($kel->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $kel->keterangan ?? '-' }}</td>
                    <td>{{ $kel->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($kel->jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">JUMLAH PENGELUARAN</td>
                    <td class="text-right">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif

        @endif {{-- end jenis keuangan --}}

        @if($jenis === 'lengkap' || $jenis === 'cicilan')

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- SEKSI 2: LAPORAN CICILAN / ANGSURAN                           -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        @if($jenis !== 'lengkap') @else <div class="page-break"></div> @endif
        <div class="section-header">{{ $jenis === 'lengkap' ? 'II.' : 'I.' }} Laporan Cicilan / Angsuran</div>

        <div class="kpi-grid">
            <div class="kpi-card green">
                <div class="kpi-label">Angsuran Terbayar</div>
                <div class="kpi-value">Rp {{ number_format($totalAngsuranTerbayar, 0, ',', '.') }}</div>
                <div class="kpi-sub">Periode: {{ $labelPeriode }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Transaksi Cicilan</div>
                <div class="kpi-value">{{ number_format($angsuranByPenghuni->count()) }}</div>
                <div class="kpi-sub">Record pembayaran</div>
            </div>
            <div class="kpi-card slate">
                <div class="kpi-label">Total Uang Muka</div>
                <div class="kpi-value">Rp {{ number_format($totalUangMuka, 0, ',', '.') }}</div>
                <div class="kpi-sub">DP seluruh penghuni</div>
            </div>
        </div>

        @if($angsuranByPenghuni->count() > 0)
        <div class="section-header-light">Detail Pembayaran Angsuran</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Nama Penghuni</th>
                    <th class="text-center">Kavling</th>
                    <th class="text-center">Angsuran Ke</th>
                    <th style="width: 90px;">Tgl Bayar</th>
                    <th class="text-right">Jumlah Bayar (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($angsuranByPenghuni as $i => $ang)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $ang->penghuni->nama_lengkap ?? '-' }}</td>
                    <td class="text-center">{{ $ang->penghuni->blok_kavling ?? '-' }}</td>
                    <td class="text-center">Ke-{{ $ang->angsuran_ke }}</td>
                    <td>{{ $ang->tanggal_bayar ? \Carbon\Carbon::parse($ang->tanggal_bayar)->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($ang->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">TOTAL ANGSURAN TERBAYAR</td>
                    <td class="text-right">Rp {{ number_format($totalAngsuranTerbayar, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @else
        <p style="text-align:center; color:#94a3b8; padding: 16px 0;">Tidak ada data cicilan untuk periode yang dipilih.</p>
        @endif

        @endif {{-- end jenis cicilan --}}

        @if($jenis === 'lengkap' || $jenis === 'penghuni')

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- SEKSI 3: DATA PENGHUNI                                        -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        @if($jenis !== 'penghuni') <div class="page-break"></div> @endif
        <div class="section-header">
            @if($jenis === 'lengkap') III. @else I. @endif Data Penghuni Perumahan
        </div>

        <div class="kpi-grid" style="grid-template-columns: repeat(3,1fr);">
            <div class="kpi-card">
                <div class="kpi-label">Total Penghuni</div>
                <div class="kpi-value">{{ number_format($totalPenghuni) }}</div>
                <div class="kpi-sub">Terdaftar di sistem</div>
            </div>
            <div class="kpi-card green">
                <div class="kpi-label">Lunas</div>
                <div class="kpi-value">{{ number_format($penghuniLunas) }}</div>
                <div class="kpi-sub">Penghuni</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Aktif Mencicil</div>
                <div class="kpi-value">{{ number_format($penghuniAktif) }}</div>
                <div class="kpi-sub">Penghuni</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 35px;">#</th>
                    <th>Nama Penghuni</th>
                    <th class="text-center" style="width: 60px;">Kavling</th>
                    <th class="text-right">Harga Unit (Rp)</th>
                    <th class="text-right">Uang Muka (Rp)</th>
                    <th class="text-right">Sisa (Rp)</th>
                    <th class="text-center" style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftarPenghuni as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $p->nama_lengkap }}</strong></td>
                    <td class="text-center">{{ $p->blok_kavling ?? '-' }}</td>
                    <td class="text-right">{{ number_format($p->harga_rumah, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($p->uang_muka, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($p->sisa_angsuran ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge-status {{ $p->status === 'lunas' ? 'badge-lunas' : ($p->status === 'aktif' ? 'badge-aktif' : 'badge-default') }}">
                            {{ strtoupper($p->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td class="text-right">Rp {{ number_format($totalNilaiRumah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalUangMuka, 0, ',', '.') }}</td>
                    <td class="text-right">—</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        @endif {{-- end jenis penghuni --}}

        {{-- Legalitas Summary only on lengkap --}}
        @if($jenis === 'lengkap')
        <div class="no-break">
        <div class="section-header">IV. Ringkasan Status Legalitas Sertifikat</div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Berkas</div>
                <div class="kpi-value">{{ $legalitasStats['total'] }}</div>
            </div>
            <div class="kpi-card green">
                <div class="kpi-label">Selesai</div>
                <div class="kpi-value">{{ $legalitasStats['selesai'] }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Proses BPN</div>
                <div class="kpi-value">{{ $legalitasStats['proses_bpn'] }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Proses Notaris</div>
                <div class="kpi-value">{{ $legalitasStats['proses_notaris'] }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Pengajuan</div>
                <div class="kpi-value">{{ $legalitasStats['pengajuan'] }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Belum Proses</div>
                <div class="kpi-value">{{ $legalitasStats['belum_proses'] }}</div>
            </div>
        </div>
        </div>
        @endif

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- BLOK TANDA TANGAN                                             -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <div class="ttd-section no-break">
            <div class="ttd-kota">
                {{ $kotaTtd }}, {{ $tanggalTtd }}
            </div>

            @if($ketua || $bendahara1 || $bendahara2)
            <div class="ttd-grid">
                @if($ketua)
                <div class="ttd-box">
                    <div class="jabatan">Ketua Pengelola</div>
                    <div class="ttd-line"></div>
                    <div class="nama">{{ $ketua }}</div>
                </div>
                @endif
                @if($bendahara1)
                <div class="ttd-box">
                    <div class="jabatan">Bendahara I</div>
                    <div class="ttd-line"></div>
                    <div class="nama">{{ $bendahara1 }}</div>
                </div>
                @endif
                @if($bendahara2)
                <div class="ttd-box">
                    <div class="jabatan">Bendahara II</div>
                    <div class="ttd-line"></div>
                    <div class="nama">{{ $bendahara2 }}</div>
                </div>
                @endif
            </div>
            @endif

            @if(!empty($anggotaList))
            <div style="margin-top: 8px;">
                <p style="font-size: 9.5pt; font-weight: 700; margin-bottom: 4px; color: #475569;">ANGGOTA / PENGURUS LAINNYA:</p>
                <div class="ttd-anggota-grid">
                    @foreach($anggotaList as $anggota)
                    <div class="ttd-box">
                        <div style="font-size: 9pt; color: #475569; margin-bottom: 48px;">Anggota</div>
                        <div class="ttd-line"></div>
                        <div class="nama" style="font-size: 9.5pt;">{{ $anggota }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- FOOTER DOKUMEN -->
        <div class="doc-footer">
            <span>Laporan ini digenerate otomatis oleh Sistem LegalitasGo pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
            <span>{{ $perumahan->nama_perumahan ?? '' }}</span>
        </div>

    </div>

    <script>
        // Auto-print on load (opsional, biarkan user control)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
