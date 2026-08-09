@extends('layouts.master')
@section('title', 'Dashboard | Merra Inventory')
@section('content')
    <div class="container">
        <div class="page-inner py-4">

            {{-- ---------- Judul ---------- --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h3 class="fw-bold mb-1">Dashboard</h3>
                    <p class="text-muted mb-0">Ringkasan kondisi stok &amp; operasional {{ now()->translatedFormat('d F Y') }}</p>
                </div>
                <a href="{{ route('laporan.pemantauan') }}" class="btn btn-primary">
                    <i class="fas fa-chart-line me-1"></i> Pemantauan Bahan
                </a>
            </div>

            {{-- ---------- Kartu ringkasan ---------- --}}
            <div class="row g-3 mb-4">
                @php
                    $kartu = [
                        ['label' => 'Total Bahan Baku', 'value' => $totalBahan, 'icon' => 'fa-boxes-stacked', 'class' => 'stat-primary', 'url' => route('bahan.index')],
                        ['label' => 'Stok Aman', 'value' => $bahanAman, 'icon' => 'fa-circle-check', 'class' => 'stat-success', 'url' => route('bahan.index') . '?status_stok=aman'],
                        ['label' => 'Stok Menipis', 'value' => $bahanMenipis, 'icon' => 'fa-triangle-exclamation', 'class' => 'stat-warning', 'url' => route('bahan.index') . '?status_stok=menipis'],
                        ['label' => 'Stok Habis', 'value' => $bahanHabis, 'icon' => 'fa-circle-xmark', 'class' => 'stat-danger', 'url' => route('bahan.index') . '?status_stok=habis'],
                    ];
                @endphp

                @foreach ($kartu as $k)
                    <div class="col-6 col-lg-3">
                        <a href="{{ $k['url'] }}" class="text-decoration-none">
                            <div class="card stat-card {{ $k['class'] }} h-100 border-0">
                                <div class="card-body">
                                    <div class="stat-value">{{ number_format($k['value']) }}</div>
                                    <div class="stat-label">{{ $k['label'] }}</div>
                                    <i class="fas {{ $k['icon'] }} stat-icon"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- ---------- Grafik utama ---------- --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="card chart-card h-100 border-0">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span><i class="fas fa-arrow-right-arrow-left me-2 text-primary"></i>Pergerakan Stok 14 Hari</span>
                            <small class="text-muted">Bahan masuk vs bahan keluar</small>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap"><canvas id="chartTren"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card chart-card h-100 border-0">
                        <div class="card-header">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>Komposisi Status Stok
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="chart-wrap chart-wrap-sm"><canvas id="chartStatus"></canvas></div>
                            <div class="mt-3 small">
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span><span class="badge-solid badge-solid-success">AMAN</span></span>
                                    <strong>{{ $bahanAman }} bahan</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span><span class="badge-solid badge-solid-warning">MENIPIS</span></span>
                                    <strong>{{ $bahanMenipis }} bahan</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span><span class="badge-solid badge-solid-danger">HABIS</span></span>
                                    <strong>{{ $bahanHabis }} bahan</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="card chart-card h-100 border-0">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span><i class="fas fa-ranking-star me-2 text-primary"></i>Menu Terlaris</span>
                            <small class="text-muted">30 hari terakhir</small>
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap"><canvas id="chartMenu"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card chart-card h-100 border-0">
                        <div class="card-header">
                            <i class="fas fa-layer-group me-2 text-primary"></i>Bahan per Jenis
                        </div>
                        <div class="card-body">
                            <div class="chart-wrap"><canvas id="chartJenis"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ---------- Angka pendukung ---------- --}}
            <div class="row g-3 mb-4">
                @php
                    $mini = [
                        ['label' => 'Menu Aktif', 'value' => $totalMenu, 'icon' => 'fa-utensils', 'color' => 'var(--brand-primary)'],
                        ['label' => 'Menu Terdampak Bahan Habis', 'value' => $menuTerdampak, 'icon' => 'fa-ban', 'color' => 'var(--brand-danger)'],
                        ['label' => 'Bahan Proses (Racikan)', 'value' => $totalBahanProses, 'icon' => 'fa-flask', 'color' => 'var(--brand-info)'],
                        ['label' => 'Menu Terjual Hari Ini', 'value' => $terjualHariIni, 'icon' => 'fa-cash-register', 'color' => 'var(--brand-success)'],
                        ['label' => 'Total Inventaris', 'value' => $totalInventaris, 'icon' => 'fa-box-open', 'color' => 'var(--brand-primary)'],
                        ['label' => 'Inventaris Perlu Perbaikan', 'value' => $inventarisRusak, 'icon' => 'fa-screwdriver-wrench', 'color' => 'var(--brand-warning)'],
                    ];
                @endphp

                @foreach ($mini as $m)
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card h-100 border-0 text-center">
                            <div class="card-body py-3">
                                <i class="fas {{ $m['icon'] }} fa-lg mb-2" style="color: {{ $m['color'] }}"></i>
                                <div class="fw-bold fs-4">{{ number_format($m['value']) }}</div>
                                <div class="small text-muted">{{ $m['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ---------- Bahan perlu restock ---------- --}}
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="card h-100 border-0">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-triangle-exclamation me-2" style="color: var(--brand-warning)"></i>
                            Bahan Perlu Restock
                        </div>
                        <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                            @forelse ($bahan_low_stock as $b)
                                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                                    <div class="me-2">
                                        <div class="fw-semibold small">{{ $b->nama_bahan }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ $b->kode_bahan }} &middot; min {{ $b->batas_minimum }} {{ $b->satuan }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ $b->sisa_stok }} <small class="text-muted">{{ $b->satuan }}</small></div>
                                        <x-status-badge :sisa-stok="$b->sisa_stok" :batas-minimum="$b->batas_minimum" />
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-circle-check fa-2x d-block mb-2" style="color: var(--brand-success)"></i>
                                    Semua stok dalam kondisi aman.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ---------- Menu tidak tersedia ---------- --}}
                <div class="col-lg-7">
                    <div class="card h-100 border-0">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-ban me-2" style="color: var(--brand-danger)"></i>
                            Menu Tidak Tersedia (Bahan Habis)
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Menu</th>
                                            <th>Bahan yang Habis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($menu as $item)
                                            @php $bahanHabisList = $item->bahans->where('sisa_stok', '<=', 0); @endphp
                                            <tr>
                                                <td class="small">{{ $item->kode_menu }}</td>
                                                <td class="small fw-semibold">{{ $item->nama_menu }}</td>
                                                <td>
                                                    @foreach ($bahanHabisList as $bh)
                                                        <span class="badge-solid badge-solid-danger mb-1">{{ $bh->nama_bahan }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">
                                                    <i class="fas fa-circle-check fa-2x d-block mb-2" style="color: var(--brand-success)"></i>
                                                    Semua menu bisa dijual.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <x-table-pagination :paginator="$menu" label="menu terdampak" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        {{-- Chart.js 4 (di-vendor lokal, bukan CDN) dan hanya dimuat di halaman ini.
             Chart.js bawaan template adalah v2.7.2 dari 2018 yang API-nya sudah tidak
             kompatibel (legend/scales/indexAxis), sehingga tidak dipakai di sini. --}}
        <script src="{{ asset('admin/assets/js/plugin/chartjs4/chart.umd.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Chart === 'undefined') return;

                const css = getComputedStyle(document.documentElement);
                const c = (name, fallback) => (css.getPropertyValue(name).trim() || fallback);

                const primary = c('--brand-primary', '#2563eb');
                const success = c('--brand-success', '#16a34a');
                const warning = c('--brand-warning', '#d97706');
                const danger  = c('--brand-danger', '#dc2626');
                const info    = c('--brand-info', '#0891b2');

                Chart.defaults.font.family = "'Public Sans', system-ui, sans-serif";
                Chart.defaults.color = '#64748b';

                // --- Tren masuk vs keluar ---
                new Chart(document.getElementById('chartTren'), {
                    type: 'line',
                    data: {
                        labels: @json($trenLabels),
                        datasets: [
                            {
                                label: 'Bahan Masuk',
                                data: @json($trenMasuk),
                                borderColor: success,
                                backgroundColor: 'rgba(22, 163, 74, 0.12)',
                                fill: true, tension: 0.35, borderWidth: 2,
                                pointRadius: 3, pointBackgroundColor: success,
                            },
                            {
                                label: 'Bahan Keluar',
                                data: @json($trenKeluar),
                                borderColor: danger,
                                backgroundColor: 'rgba(220, 38, 38, 0.12)',
                                fill: true, tension: 0.35, borderWidth: 2,
                                pointRadius: 3, pointBackgroundColor: danger,
                            }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.06)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // --- Donut status stok ---
                new Chart(document.getElementById('chartStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Aman', 'Menipis', 'Habis'],
                        datasets: [{
                            data: [{{ $bahanAman }}, {{ $bahanMenipis }}, {{ $bahanHabis }}],
                            backgroundColor: [success, warning, danger],
                            borderWidth: 0, hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } }
                    }
                });

                // --- Menu terlaris ---
                new Chart(document.getElementById('chartMenu'), {
                    type: 'bar',
                    data: {
                        labels: @json($topMenuLabels),
                        datasets: [{
                            label: 'Porsi terjual',
                            data: @json($topMenuData),
                            backgroundColor: primary, borderRadius: 6, maxBarThickness: 26,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.06)' } },
                            y: { grid: { display: false } }
                        }
                    }
                });

                // --- Bahan per jenis ---
                new Chart(document.getElementById('chartJenis'), {
                    type: 'polarArea',
                    data: {
                        labels: @json($jenisLabels),
                        datasets: [{
                            data: @json($jenisData),
                            backgroundColor: [primary, success, warning, info, danger, '#7c3aed', '#db2777', '#0d9488', '#ca8a04'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } } },
                        scales: { r: { ticks: { display: false }, grid: { color: 'rgba(15,23,42,0.08)' } } }
                    }
                });
            });
        </script>
    @endpush
@endsection
