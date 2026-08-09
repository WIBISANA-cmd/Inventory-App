@extends('layouts.master')
@section('title', 'Pemantauan Bahan Baku')
@section('pemantauan')
    <div class="container">
        <div class="page-inner py-4">

            <div class="mb-4">
                <h3 class="fw-bold mb-1">Pemantauan Bahan Baku</h3>
                <p class="text-muted mb-0">
                    Membandingkan pemakaian bahan yang <strong>seharusnya</strong> (resep menu &times; jumlah terjual)
                    dengan pemakaian <strong>aktual</strong> yang tercatat.
                </p>
            </div>

            @if (session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            {{-- ---------- Filter ---------- --}}
            <x-filter-bar :action="route('laporan.pemantauan')">
                <div class="col-12 col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" name="dari_tanggal" value="{{ request('dari_tanggal') }}" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="Waste" {{ request('status') === 'Waste' ? 'selected' : '' }}>Waste</option>
                        <option value="Balance" {{ request('status') === 'Balance' ? 'selected' : '' }}>Balance</option>
                        <option value="Plus" {{ request('status') === 'Plus' ? 'selected' : '' }}>Plus</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Area</label>
                    <select class="form-select" name="kategori_bahan">
                        <option value="">Semua Area</option>
                        <option value="BBAR" {{ request('kategori_bahan') === 'BBAR' ? 'selected' : '' }}>Bar</option>
                        <option value="BBKTC" {{ request('kategori_bahan') === 'BBKTC' ? 'selected' : '' }}>Kitchen</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Cari Bahan</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nama bahan...">
                </div>
            </x-filter-bar>

            @if (!request('dari_tanggal') || !request('sampai_tanggal'))
                <div class="card border-0">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-days fa-3x mb-3" style="color: var(--brand-primary); opacity: .35;"></i>
                        <h5 class="fw-semibold">Pilih rentang tanggal</h5>
                        <p class="text-muted mb-0">Tentukan tanggal awal dan akhir untuk melihat hasil pemantauan.</p>
                    </div>
                </div>
            @elseif ($laporan->isEmpty())
                <div class="card border-0">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-semibold">Tidak ada data</h5>
                        <p class="text-muted mb-0">Belum ada penjualan menu pada rentang tanggal ini, atau tidak ada yang cocok dengan filter.</p>
                    </div>
                </div>
            @else
                {{-- ---------- Ringkasan ---------- --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card stat-primary border-0 h-100">
                            <div class="card-body">
                                <div class="stat-value">{{ $ringkasan['total'] }}</div>
                                <div class="stat-label">Bahan Dipantau</div>
                                <i class="fas fa-boxes-stacked stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card stat-success border-0 h-100">
                            <div class="card-body">
                                <div class="stat-value">{{ $ringkasan['balance'] }}</div>
                                <div class="stat-label">Balance (sesuai resep)</div>
                                <i class="fas fa-scale-balanced stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card stat-danger border-0 h-100">
                            <div class="card-body">
                                <div class="stat-value">{{ $ringkasan['waste'] }}</div>
                                <div class="stat-label">Waste (pemakaian berlebih)</div>
                                <i class="fas fa-trash-can stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card stat-card stat-warning border-0 h-100">
                            <div class="card-body">
                                <div class="stat-value">{{ $ringkasan['plus'] }}</div>
                                <div class="stat-label">Plus (pemakaian kurang)</div>
                                <i class="fas fa-arrow-trend-up stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ---------- Legenda: cara membaca ---------- --}}
                <div class="card border-0 mb-4">
                    <div class="card-body py-3 small">
                        <span class="me-3"><span class="badge-solid badge-solid-success">BALANCE</span> pemakaian aktual sama dengan resep — ideal.</span>
                        <span class="me-3"><span class="badge-solid badge-solid-danger">WASTE</span> aktual lebih besar dari resep — ada bahan terbuang / takaran berlebih.</span>
                        <span><span class="badge-solid badge-solid-warning">PLUS</span> aktual lebih kecil dari resep — kemungkinan ada pencatatan yang terlewat.</span>
                    </div>
                </div>

                {{-- ---------- Kartu per bahan (pengganti tabel) ---------- --}}
                <form method="POST" action="{{ route('laporan.pemantauan.pdf') }}" target="_blank">
                    @csrf
                    <input type="hidden" name="dari_tanggal" value="{{ request('dari_tanggal') }}">
                    <input type="hidden" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="kategori_bahan" value="{{ request('kategori_bahan') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <div class="row g-3">
                        @foreach ($laporan as $data)
                            @php
                                $statusClass = match ($data['status']) {
                                    'Balance' => ['is-balance', 'success', 'fa-scale-balanced'],
                                    'Waste' => ['is-waste', 'danger', 'fa-trash-can'],
                                    default => ['is-plus', 'warning', 'fa-arrow-trend-up'],
                                };
                                $selisih = $data['selisih'];
                            @endphp

                            <div class="col-12 col-xl-6">
                                <div class="card monitor-card {{ $statusClass[0] }} border-0 h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $data['nama_bahan'] }}</h6>
                                                <div class="text-muted" style="font-size: .78rem;">
                                                    {{ $data['kode_bahan'] }}
                                                    <span class="mx-1">&middot;</span>
                                                    {{ $data['tipe'] }}
                                                    <span class="mx-1">&middot;</span>
                                                    {{ str_starts_with($data['kode_bahan'], 'BBAR') ? 'Bar' : 'Kitchen' }}
                                                </div>
                                            </div>
                                            <span class="badge-solid badge-solid-{{ $statusClass[1] }}">
                                                <i class="fas {{ $statusClass[2] }} me-1"></i>{{ strtoupper($data['status']) }}
                                            </span>
                                        </div>

                                        {{-- Perbandingan visual: aktual terhadap seharusnya --}}
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Aktual vs Seharusnya</span>
                                            <strong>{{ $data['rasio'] }}%</strong>
                                        </div>
                                        <div class="monitor-bar mb-3">
                                            <span style="width: {{ min(100, $data['rasio']) }}%; background-color: var(--brand-{{ $statusClass[1] }});"></span>
                                        </div>

                                        <div class="row g-2 text-center small">
                                            <div class="col-4">
                                                <div class="text-muted" style="font-size: .72rem;">SEHARUSNYA</div>
                                                <div class="fw-bold">{{ round($data['pemakaian_seharusnya'], 2) }}</div>
                                                <div class="text-muted" style="font-size: .7rem;">{{ $data['satuan'] }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted" style="font-size: .72rem;">AKTUAL</div>
                                                <div class="fw-bold">{{ round($data['total_keluar'], 2) }}</div>
                                                <div class="text-muted" style="font-size: .7rem;">{{ $data['satuan'] }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted" style="font-size: .72rem;">SELISIH</div>
                                                <div class="fw-bold text-{{ $statusClass[1] }}">
                                                    {{ $selisih > 0 ? '+' : '' }}{{ round($selisih, 2) }}
                                                </div>
                                                <div class="text-muted" style="font-size: .7rem;">{{ $data['satuan'] }}</div>
                                            </div>
                                        </div>

                                        <input type="text" name="keterangan[{{ $data['kode_bahan'] }}]"
                                            class="form-control form-control-sm mt-3"
                                            placeholder="Catatan untuk bahan ini (opsional, ikut tercetak di PDF)">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-arrow-down me-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
@endsection
