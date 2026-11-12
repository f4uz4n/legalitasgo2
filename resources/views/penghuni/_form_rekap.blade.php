@php
    $p = $penghuni ?? null;
    $angka = function ($value) {
        if ($value === null || $value === '') {
            return '';
        }
        $formatted = number_format((float) $value, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    };
    $perumahanIdFallback = isset($perumahan) ? $perumahan?->id : null;
    $singlePerumahanId = $p?->perumahan_id
        ?? $perumahanIdFallback
        ?? ((isset($perumahanList) && count($perumahanList)) ? array_key_first($perumahanList->toArray()) : ($sharedPerumahan->id ?? 1));
    $namaDariList = isset($perumahanList) ? ($perumahanList[$singlePerumahanId] ?? null) : null;
    $singlePerumahanNama = $p?->perumahan?->nama_perumahan
        ?? (isset($perumahan) ? $perumahan?->nama_perumahan : null)
        ?? $namaDariList
        ?? ($sharedPerumahan->nama_perumahan ?? 'Perumahan');
@endphp

<input type="hidden" name="perumahan_id" value="{{ $singlePerumahanId }}">

<div class="rekap-sheet mb-4">
    <div class="rekap-sheet-head">
        <div class="rekap-sheet-title">REKAP PEMBAYARAN PEMBELIAN UNIT RUMAH</div>
        <div class="rekap-sheet-sub">{{ $singlePerumahanNama }} · isi satu baris pengangsur seperti kolom rekap</div>
    </div>

    <div class="p-3 p-lg-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" id="rekap_nama" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $p->nama_lengkap ?? '') }}" required placeholder="Nama pembeli">
            </div>
            <div class="col-md-3">
                <label class="form-label">Blok <span class="text-danger">*</span></label>
                <input type="text" name="blok_kavling" id="rekap_blok" class="form-control text-uppercase @error('blok_kavling') is-invalid @enderror" value="{{ old('blok_kavling', $p->blok_kavling ?? '') }}" required placeholder="G-04">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun Pembelian <span class="text-danger">*</span></label>
                <input type="number" name="tahun_pembelian" id="rekap_tahun" class="form-control @error('tahun_pembelian') is-invalid @enderror" min="1990" max="{{ date('Y') + 1 }}" value="{{ old('tahun_pembelian', $p->tahun_pembelian ?? date('Y')) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pembayaran <span class="text-danger">*</span></label>
                <select name="jenis_pembayaran" id="rekap_jenis" class="form-select @error('jenis_pembayaran') is-invalid @enderror" required>
                    <option value="inhouse" {{ old('jenis_pembayaran', $p->jenis_pembayaran ?? 'inhouse') === 'inhouse' ? 'selected' : '' }}>INHOUSE</option>
                    <option value="cash_lunak" {{ old('jenis_pembayaran', $p->jenis_pembayaran ?? '') === 'cash_lunak' ? 'selected' : '' }}>CASH LUNAK</option>
                </select>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label class="form-label">Harga <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="harga_rumah" id="rekap_harga" class="form-control @error('harga_rumah') is-invalid @enderror" min="0" step="1" value="{{ $angka(old('harga_rumah', $p->harga_rumah ?? '')) }}" required>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Uang Muka</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="uang_muka" id="rekap_um" class="form-control @error('uang_muka') is-invalid @enderror" min="0" step="1" value="{{ $angka(old('uang_muka', $p->uang_muka ?? 0)) }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Angsuran</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="nominal_angsuran" id="rekap_angsuran" class="form-control @error('nominal_angsuran') is-invalid @enderror" min="0" step="1" value="{{ $angka(old('nominal_angsuran', $p->nominal_angsuran ?? '')) }}" placeholder="Per tempo">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tempo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="tenor_bulan" id="rekap_tempo" class="form-control @error('tenor_bulan') is-invalid @enderror" min="1" max="600" value="{{ old('tenor_bulan', $p->tenor_bulan ?? 60) }}" required>
                    <span class="input-group-text fw-bold">X</span>
                </div>
                <div class="form-text">60X = 60 kali angsuran</div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="form-label">Sisa Tempo</label>
                <div class="input-group">
                    <input type="number" name="sisa_tempo" id="rekap_sisa_tempo" class="form-control @error('sisa_tempo') is-invalid @enderror" min="0" max="600" value="{{ old('sisa_tempo', $p->sisa_tempo ?? '') }}" placeholder="Kosong = sama dengan tempo">
                    <span class="input-group-text fw-bold">X</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tunggakan</label>
                <div class="input-group">
                    <input type="number" name="tunggakan" id="rekap_tunggakan" class="form-control @error('tunggakan') is-invalid @enderror" min="0" max="600" value="{{ old('tunggakan', ($p && (int) $p->tunggakan > 0) ? $p->tunggakan : '') }}" placeholder="-">
                    <span class="input-group-text fw-bold">X</span>
                </div>
                <div class="form-text">Kosongkan bila tidak ada tunggakan (tampil “-”)</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sisa Pembayaran</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="sisa_pembayaran" id="rekap_sisa_bayar" class="form-control @error('sisa_pembayaran') is-invalid @enderror" min="0" step="1" value="{{ $angka(old('sisa_pembayaran', $p->sisa_angsuran ?? '')) }}">
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnHitungAngsuran">
                <i class="bi bi-calculator me-1"></i> Hitung angsuran
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnHitungSisa">
                <i class="bi bi-calculator me-1"></i> Hitung sisa pembayaran
            </button>
        </div>
        <div id="selisihInfo" class="alert alert-warning py-2 px-3 mt-3 mb-0 small d-none"></div>
        <p class="text-muted small mt-3 mb-0">
            Angsuran otomatis = (harga − uang muka) ÷ tempo. Sisa pembayaran otomatis = sisa tempo × angsuran.
            Kedua angka tetap bisa diisi manual bila rekap memakai pembulatan atau skema cash lunak.
            Jadwal yang dibuat hanya sisa tempo yang masih berjalan; tunggakan ditandai terlambat. Pembayaran lama tidak dicatat ulang sebagai kas masuk.
        </p>
    </div>
</div>

<div class="table-responsive mb-4">
    <table class="table table-rekap mb-0">
        <thead>
            <tr>
                <th>NAMA</th>
                <th>BLOK</th>
                <th>TAHUN</th>
                <th>PEMBAYARAN</th>
                <th>HARGA</th>
                <th>UANG MUKA</th>
                <th>ANGSURAN</th>
                <th>TEMPO</th>
                <th>SISA TEMPO</th>
                <th>TUNGGAKAN</th>
                <th>SISA PEMBAYARAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td id="prev_nama" class="fw-semibold">-</td>
                <td id="prev_blok">-</td>
                <td id="prev_tahun">-</td>
                <td id="prev_jenis">INHOUSE</td>
                <td id="prev_harga" class="text-end">-</td>
                <td id="prev_um" class="text-end">-</td>
                <td id="prev_angsuran" class="text-end">-</td>
                <td id="prev_tempo" class="text-center">-</td>
                <td id="prev_sisa_tempo" class="text-center">-</td>
                <td id="prev_tunggakan" class="text-center">-</td>
                <td id="prev_sisa_bayar" class="text-end fw-bold">-</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="mb-4">
    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#dataTambahanPengangsur">
        <i class="bi bi-person-vcard me-1"></i> Data kontak & unit (opsional)
    </button>
    <div class="collapse mt-3 {{ ($errors->has('nik') || $errors->has('email') || old('nik') || old('no_hp') || old('email') || ($p && ($p->nik || $p->no_hp || $p->email || $p->tipe_rumah))) ? 'show' : '' }}" id="dataTambahanPengangsur">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">NIK</label>
                <input type="text" name="nik" class="form-control" maxlength="16" value="{{ old('nik', $p->nik ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $p->no_hp ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $p->email ?? '') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Tipe Rumah</label>
                <input type="text" name="tipe_rumah" class="form-control" value="{{ old('tipe_rumah', $p->tipe_rumah ?? '') }}" placeholder="36/72">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Luas Tanah (m²)</label>
                <input type="number" name="luas_tanah" class="form-control" step="0.01" min="0" value="{{ old('luas_tanah', $p->luas_tanah ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Luas Bangunan (m²)</label>
                <input type="number" name="luas_bangunan" class="form-control" step="0.01" min="0" value="{{ old('luas_bangunan', $p->luas_bangunan ?? '') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Akad</label>
                <input type="date" name="tanggal_akad" class="form-control" value="{{ old('tanggal_akad', isset($p->tanggal_akad) ? $p->tanggal_akad?->format('Y-m-d') : '') }}">
                <div class="form-text">Kosongkan untuk memakai 1 Januari tahun pembelian.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $p->keterangan ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<style>
    .rekap-sheet { border: 1px solid #e7c96a; border-radius: 12px; overflow: hidden; background: #fffdf6; }
    .rekap-sheet-head { background: #ffe08a; padding: 0.9rem 1.25rem; border-bottom: 1px solid #e7c96a; }
    .rekap-sheet-title { font-weight: 800; letter-spacing: 0.04em; color: #3f3f46; font-size: 0.92rem; }
    .rekap-sheet-sub { color: #57534e; font-size: 0.8rem; margin-top: 2px; }
    .table-rekap { font-size: 0.75rem; border: 1px solid #d6d3d1; margin-bottom: 0; }
    .table-rekap thead th {
        background: #ffe08a;
        color: #292524;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        white-space: nowrap;
        border-color: #e7c96a;
        text-align: center;
        vertical-align: middle;
    }
    .table-rekap td { white-space: nowrap; vertical-align: middle; background: #fff; }
</style>

<script>
(function () {
    const isEdit = @json((bool) $p);
    const fields = {
        nama: document.getElementById('rekap_nama'),
        blok: document.getElementById('rekap_blok'),
        tahun: document.getElementById('rekap_tahun'),
        jenis: document.getElementById('rekap_jenis'),
        harga: document.getElementById('rekap_harga'),
        um: document.getElementById('rekap_um'),
        angsuran: document.getElementById('rekap_angsuran'),
        tempo: document.getElementById('rekap_tempo'),
        sisaTempo: document.getElementById('rekap_sisa_tempo'),
        tunggakan: document.getElementById('rekap_tunggakan'),
        sisaBayar: document.getElementById('rekap_sisa_bayar'),
    };

    let angsuranManual = isEdit && fields.angsuran.value !== '';
    let sisaTempoManual = isEdit && fields.sisaTempo.value !== '';
    let sisaBayarManual = isEdit && fields.sisaBayar.value !== '';
    let tempoSebelumnya = num(fields.tempo.value) || 60;

    function num(v) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : 0;
    }

    function rupiah(n) {
        return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    }

    function kali(n) {
        if (n === null || n === '' || Number.isNaN(n)) return '-';
        return Math.round(n) + 'X';
    }

    function hitungAngsuran() {
        const tempo = num(fields.tempo.value);
        if (tempo <= 0) return 0;
        return Math.round((num(fields.harga.value) - num(fields.um.value)) / tempo);
    }

    function terapkanAngsuran() {
        const nilai = hitungAngsuran();
        if (nilai >= 0) fields.angsuran.value = nilai;
        angsuranManual = false;
    }

    function terapkanSisa() {
        const sisaTempo = fields.sisaTempo.value === '' ? num(fields.tempo.value) : num(fields.sisaTempo.value);
        fields.sisaBayar.value = Math.round(sisaTempo * num(fields.angsuran.value));
        sisaBayarManual = false;
    }

    function segarkan() {
        const tempo = num(fields.tempo.value);
        if (!sisaTempoManual) {
            if (fields.sisaTempo.value === '' || num(fields.sisaTempo.value) === tempoSebelumnya) {
                fields.sisaTempo.value = tempo > 0 ? tempo : '';
            }
        }
        tempoSebelumnya = tempo;

        if (!angsuranManual) {
            const nilai = hitungAngsuran();
            if (fields.harga.value !== '' && tempo > 0) {
                fields.angsuran.value = nilai;
            }
        }

        const sisaTempo = fields.sisaTempo.value === '' ? tempo : num(fields.sisaTempo.value);
        if (!sisaBayarManual && fields.angsuran.value !== '') {
            fields.sisaBayar.value = Math.round(sisaTempo * num(fields.angsuran.value));
        }

        const jenis = fields.jenis.value === 'cash_lunak' ? 'CASH LUNAK' : 'INHOUSE';
        const tunggakanKosong = fields.tunggakan.value === '' || num(fields.tunggakan.value) === 0;
        document.getElementById('prev_nama').textContent = fields.nama.value || '-';
        document.getElementById('prev_blok').textContent = (fields.blok.value || '-').toUpperCase();
        document.getElementById('prev_tahun').textContent = fields.tahun.value || '-';
        document.getElementById('prev_jenis').textContent = jenis;
        document.getElementById('prev_harga').textContent = rupiah(num(fields.harga.value));
        document.getElementById('prev_um').textContent = rupiah(num(fields.um.value));
        document.getElementById('prev_angsuran').textContent = rupiah(num(fields.angsuran.value));
        document.getElementById('prev_tempo').textContent = tempo > 0 ? kali(tempo) : '-';
        document.getElementById('prev_sisa_tempo').textContent = fields.sisaTempo.value === '' ? (tempo > 0 ? kali(tempo) : '-') : kali(sisaTempo);
        document.getElementById('prev_tunggakan').textContent = tunggakanKosong ? '-' : kali(num(fields.tunggakan.value));
        document.getElementById('prev_sisa_bayar').textContent = rupiah(num(fields.sisaBayar.value));

        const hitung = Math.round(sisaTempo * num(fields.angsuran.value));
        const isi = Math.round(num(fields.sisaBayar.value));
        const info = document.getElementById('selisihInfo');
        if (fields.sisaBayar.value !== '' && fields.angsuran.value !== '' && hitung !== isi) {
            info.classList.remove('d-none');
            info.textContent = 'Sisa pembayaran (' + rupiah(isi) + ') berbeda dari sisa tempo × angsuran (' + rupiah(hitung) + '). Kedua angka tetap disimpan sesuai isian.';
        } else {
            info.classList.add('d-none');
        }
    }

    ['nama', 'blok', 'tahun', 'jenis', 'harga', 'um', 'tempo', 'tunggakan'].forEach(function (key) {
        fields[key].addEventListener('input', segarkan);
        fields[key].addEventListener('change', segarkan);
    });
    fields.angsuran.addEventListener('input', function () {
        angsuranManual = true;
        if (!sisaBayarManual) terapkanSisa();
        segarkan();
    });
    fields.sisaTempo.addEventListener('input', function () {
        sisaTempoManual = fields.sisaTempo.value !== '';
        if (!sisaBayarManual) terapkanSisa();
        segarkan();
    });
    fields.sisaBayar.addEventListener('input', function () {
        sisaBayarManual = true;
        segarkan();
    });

    document.getElementById('btnHitungAngsuran').addEventListener('click', function () {
        terapkanAngsuran();
        if (!sisaBayarManual) terapkanSisa();
        segarkan();
    });
    document.getElementById('btnHitungSisa').addEventListener('click', function () {
        terapkanSisa();
        segarkan();
    });

    const form = fields.nama.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            if (fields.angsuran.value === '' && num(fields.tempo.value) > 0) {
                terapkanAngsuran();
            }
            if (fields.sisaBayar.value === '') {
                terapkanSisa();
            }
        });
    }

    segarkan();
})();
</script>
