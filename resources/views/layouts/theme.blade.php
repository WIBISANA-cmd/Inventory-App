{{-- Design tokens & polish terpusat — diinclude sekali di master.blade.php, berlaku ke seluruh halaman --}}
<style>
    :root {
        /* Warna solid terpusat — satu-satunya tempat untuk mengubah brand color aplikasi */
        --brand-primary: #2563eb;
        --brand-primary-dark: #1d4ed8;
        --brand-success: #16a34a;
        --brand-warning: #d97706;
        --brand-danger: #dc2626;
        --brand-info: #0891b2;
        --sidebar-bg: #1f2430;
        --sidebar-bg-active: #2a3040;
        --surface: #ffffff;
        --surface-muted: #f4f6fb;
        --text-muted: #64748b;
        --radius-md: 0.6rem;
        --shadow-soft: 0 2px 10px rgba(15, 23, 42, 0.06);
        --transition-fast: 150ms ease;
        --transition-base: 250ms ease;
    }

    body {
        background-color: var(--surface-muted);
    }

    /* ---------- Animasi masuk halaman & elemen ---------- */
    @keyframes merraFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Animasi diterapkan langsung ke container halaman — tanpa wrapper tambahan, supaya
       selector direct-child KaiAdmin (.main-panel > .container) tetap utuh. */
    .main-panel > .container,
    .main-panel > .container-fluid,
    .main-panel > .container-full,
    .fade-in-up {
        animation: merraFadeIn 0.35s ease both;
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* ---------- Kartu & tabel: solid, konsisten, sedikit interaktif ---------- */
    .card {
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-soft);
        transition: box-shadow var(--transition-base), transform var(--transition-base);
    }

    .card:hover {
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.09);
    }

    .table-responsive {
        border-radius: var(--radius-md);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table tbody tr {
        transition: background-color var(--transition-fast);
    }

    .btn {
        border-radius: var(--radius-md);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    }

    .btn:active {
        transform: translateY(1px);
    }

    .btn-primary {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: var(--brand-primary-dark);
        border-color: var(--brand-primary-dark);
    }

    /* ---------- Badge status solid (dipakai lewat komponen status-badge) ---------- */
    .badge-solid {
        display: inline-block;
        padding: 0.35em 0.75em;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #fff;
        border-radius: 999px;
        white-space: nowrap;
    }

    .badge-solid-success { background-color: var(--brand-success); }
    .badge-solid-warning { background-color: var(--brand-warning); }
    .badge-solid-danger  { background-color: var(--brand-danger); }
    .badge-solid-info    { background-color: var(--brand-info); }

    /* ---------- Sidebar: warna solid terpusat (sebelumnya inline hex per file) ---------- */
    .sidebar,
    .sidebar .sidebar-wrapper,
    .sidebar .logo-header {
        background-color: var(--sidebar-bg) !important;
    }

    .sidebar .nav-item.active > a,
    .sidebar .nav-item > a:hover {
        background-color: var(--sidebar-bg-active) !important;
        border-radius: var(--radius-md);
    }

    /* CATATAN: JANGAN menambahkan aturan posisi/transform untuk .sidebar di sini.
       KaiAdmin sudah menangani sendiri posisi sidebar (position:fixed) beserta perilaku
       responsifnya lewat `body.nav_open`. File ini dimuat PALING AKHIR di <head>, jadi
       aturan posisi apa pun di sini akan menimpa KaiAdmin — `position: static` sempat
       membuat sidebar masuk ke normal flow sehingga .main-panel (float:right) terdorong
       ke bawahnya dan seluruh konten halaman keluar dari viewport. */

    /* ---------- Select2 mengikuti tinggi & radius komponen Bootstrap form-control ---------- */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(1.5em + 0.75rem + 2px);
        border-radius: var(--radius-md) !important;
    }

    /* ---------- Filter bar ---------- */
    .filter-bar {
        background-color: var(--surface);
        border-left: 4px solid var(--brand-primary) !important;
    }

    .filter-bar .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    /* ---------- Pagination: warna solid, tanpa gradasi ---------- */
    .pagination { margin-bottom: 0; }

    .pagination .page-link {
        color: var(--brand-primary);
        border-radius: var(--radius-md);
        margin: 0 2px;
        border: 1px solid rgba(15, 23, 42, 0.08);
        transition: background-color var(--transition-fast), color var(--transition-fast);
    }

    .pagination .page-item.active .page-link {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        color: var(--text-muted);
        background-color: transparent;
    }

    /* ---------- Kartu statistik & grafik dashboard ---------- */
    .stat-card {
        border-radius: var(--radius-md);
        color: #fff;
        overflow: hidden;
        position: relative;
    }

    .stat-card .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1.1; }
    .stat-card .stat-label { font-size: 0.82rem; opacity: 0.9; }

    .stat-card .stat-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.6rem;
        opacity: 0.22;
    }

    .stat-primary { background-color: var(--brand-primary); }
    .stat-success { background-color: var(--brand-success); }
    .stat-warning { background-color: var(--brand-warning); }
    .stat-danger  { background-color: var(--brand-danger); }
    .stat-info    { background-color: var(--brand-info); }

    .chart-card .card-header {
        background-color: var(--surface);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        font-weight: 600;
    }

    /* Bungkus canvas: memberi tinggi pasti supaya Chart.js responsif tanpa melar tak terbatas */
    .chart-wrap { position: relative; width: 100%; height: 300px; }
    .chart-wrap-sm { height: 240px; }

    /* ---------- Kartu pemantauan (laporan visual) ---------- */
    .monitor-card { border-left: 5px solid var(--brand-info) !important; }
    .monitor-card.is-balance { border-left-color: var(--brand-success) !important; }
    .monitor-card.is-waste   { border-left-color: var(--brand-danger) !important; }
    .monitor-card.is-plus    { border-left-color: var(--brand-warning) !important; }

    .monitor-bar {
        height: 10px;
        border-radius: 999px;
        background-color: #e8ecf4;
        overflow: hidden;
    }

    .monitor-bar > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background-color: var(--brand-primary);
    }

    /* ---------- Responsif: perbesar target sentuh & rapikan spasi di layar kecil ---------- */
    @media (max-width: 576px) {
        .card-body { padding: 1rem; }
        .btn { min-height: 2.5rem; }
        h4 { font-size: 1.15rem; }
        .stat-card .stat-value { font-size: 1.5rem; }
        .chart-wrap { height: 260px; }
    }
</style>
