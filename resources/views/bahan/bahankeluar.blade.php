@extends('layouts.master')

@section('title', 'Daftar Bahan Keluar')

@section('BahanKeluar')
    <div class="container ">
        <div class="row">
            <div class="col">
                <h1 class="mb-4 my-5 mx-5 text-center text-md-start">Daftar Bahan Keluar</h1>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                {{-- Filter --}}
                <x-filter-bar :action="route('bahan.bahankeluar')">
                    @if (auth()->user()->hasRole('Admin'))
                        <div class="col-12 col-md-3 col-lg-2">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="kategori_bahan">
                                <option value="">Semua Area</option>
                                <option value="BBAR" {{ request('kategori_bahan') == 'BBAR' ? 'selected' : '' }}>Bahan Baku Bar</option>
                                <option value="BBKTC" {{ request('kategori_bahan') == 'BBKTC' ? 'selected' : '' }}>Bahan Baku Kitchen</option>
                            </select>
                        </div>
                    @endif

                    <div class="col-12 col-md-3 col-lg-2">
                        <label class="form-label">Sumber Keluar</label>
                        <select class="form-select" name="sumber">
                            <option value="">Semua Sumber</option>
                            <option value="produksi" {{ request('sumber') == 'produksi' ? 'selected' : '' }}>Produksi Batch</option>
                            <option value="opname" {{ request('sumber') == 'opname' ? 'selected' : '' }}>Stok Opname</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" name="dari_tanggal" value="{{ request('dari_tanggal') }}">
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}">
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label">Cari Nama Bahan</label>
                        <input type="text" class="form-control" name="search"
                            placeholder="Masukkan nama bahan..." value="{{ request('search') }}">
                    </div>
                </x-filter-bar>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal Keluar</th>
                                <th>Kode Bahan</th>
                                <th>Nama Bahan</th>
                                <th>Jumlah Keluar</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataKeluar as $item)
                                <tr>
                                    <td>{{ $item['tanggal_keluar'] }}</td>
                                    <td>{{ $item['kode_bahan'] }}</td>
                                    <td>{{ $item['nama_bahan'] }}</td>
                                    <td>{{ $item['jumlah_keluar'] }}</td>
                                    <td>{{ $item['satuan'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center fw-bold text-danger">Tidak ada data bahan keluar untuk
                                        hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-table-pagination :paginator="$dataKeluar" label="catatan bahan keluar" />
            </div>
        </div>
    </div>

    {{-- SweetAlert sudah dimuat global di master.blade.php --}}
    <script>
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', function () {
                if (window.swal) swal("Berhasil!", "{{ session('success') }}", "success");
            });
        @endif
    </script>

    <style>
        @media (max-width: 768px) {
            .table {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .table {
                font-size: 0.75rem;
            }
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endsection