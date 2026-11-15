@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(@json($formId));
    const hasil = document.getElementById(@json($hasilId));
    if (!form || !hasil) return;

    let timer = null;
    let kontrol = null;
    const sinkron = @json($sinkron ?? []);

    function urlDariForm(pageUrl) {
        if (pageUrl) return pageUrl;
        const params = new URLSearchParams();
        new FormData(form).forEach(function (nilai, kunci) {
            if (String(nilai).trim() !== '') params.append(kunci, nilai);
        });
        if (params.get('bulan') || params.get('tanggal_dari') || params.get('tanggal_sampai')) {
            params.delete('periode');
        }
        params.delete('page');
        const query = params.toString();
        return form.action + (query ? '?' + query : '');
    }

    function muat(url) {
        if (kontrol) kontrol.abort();
        kontrol = new AbortController();
        hasil.style.opacity = '0.45';
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: kontrol.signal
        }).then(function (res) {
            return res.text();
        }).then(function (html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const baru = doc.getElementById(hasil.id);
            if (baru) hasil.innerHTML = baru.innerHTML;
            sinkron.forEach(function (id) {
                const sumber = doc.getElementById(id);
                const tujuan = document.getElementById(id);
                if (sumber && tujuan && tujuan !== hasil) tujuan.innerHTML = sumber.innerHTML;
            });
            history.replaceState(null, '', url);
            hasil.style.opacity = '1';
        }).catch(function (err) {
            if (err.name !== 'AbortError') hasil.style.opacity = '1';
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        muat(urlDariForm());
    });

    form.querySelectorAll('input[type="text"], input[type="search"]').forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { muat(urlDariForm()); }, 280);
        });
    });

    form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
        el.addEventListener('change', function () { muat(urlDariForm()); });
    });

    hasil.addEventListener('click', function (e) {
        const tautan = e.target.closest('.pagination a');
        if (!tautan) return;
        e.preventDefault();
        muat(tautan.href);
    });
});
</script>
@endpush
