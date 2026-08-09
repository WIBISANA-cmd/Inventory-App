@extends('layouts.master')
@section('title', 'Daftar Bahan')
@section('daftarbahan')
    <div class="container">
        <div class="page-inner py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">Data Bahan Baku</h3>
                <p class="text-muted mb-0">Stok bahan non-proses beserta status ketersediaannya.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar'))
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBahanBarModal">
                        <i class="fa fa-plus me-1"></i> Tambah Bahan Bar
                    </button>
                @endif
                @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headkitchen'))
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBahanKitchenModal">
                        <i class="fa fa-plus me-1"></i> Tambah Bahan Kitchen
                    </button>
                @endif
            </div>
        </div>

        {{-- Notifikasi Sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter --}}
        <x-filter-bar :action="route('bahan.index')">
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
                <label class="form-label">Status Stok</label>
                <select class="form-select" name="status_stok">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status_stok') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
            </div>

            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label">Jenis Bahan</label>
                <select class="form-select" name="jenis_bahan">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisOptions as $jenis)
                        <option value="{{ $jenis }}" {{ request('jenis_bahan') == $jenis ? 'selected' : '' }}>
                            {{ ucfirst($jenis) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label">Satuan</label>
                <select class="form-select" name="satuan">
                    <option value="">Semua Satuan</option>
                    @foreach ($satuanOptions as $satuan)
                        <option value="{{ $satuan }}" {{ request('satuan') == $satuan ? 'selected' : '' }}>
                            {{ ucfirst($satuan) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Cari Nama Bahan</label>
                <input type="text" class="form-control" name="search" placeholder="Masukkan nama bahan..."
                    value="{{ request('search') }}">
            </div>
        </x-filter-bar>

        {{-- tabel --}}
        <div class="card border-0">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-bordered table-striped text-center">
                <thead style="align-content: center;">
                    <tr>
                        <th>Kode Bahan</th>
                        <th>Nama Bahan</th>
                        <th>Jenis</th>
                        {{-- <th>Kategori</th> --}}
                        <th>Sisa Stok</th>
                        <th>Batas Minimum</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    {{-- Urutan status & pembatasan per role sudah ditangani di query
                         BahanController::index(); jangan diulang di sini karena akan
                         merusak jumlah baris per halaman saat dipaginasi. --}}
                    @foreach ($bahan as $item)
                        <tr>
                            <td>{{ $item->kode_bahan }}</td>
                            <td>{{ $item->nama_bahan }}</td>
                            <td>{{ $item->jenis_bahan }}</td>
                            <td>{{ $item->sisa_stok }}</td>
                            <td>{{ $item->batas_minimum }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td>
                                <x-status-badge :sisa-stok="$item->sisa_stok" :batas-minimum="$item->batas_minimum" />
                            </td>
                            @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editBahanModal{{ $item->id }}">Edit</button>
                                    <form action="{{ route('bahan.destroy', $item->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus bahan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-delete">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$bahan" label="bahan baku" />
    </div>
    </div>
    <!-- Modal Edit Bahan -->
    @foreach ($bahan as $item)
        <div class="modal fade" id="editBahanModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="editBahanModalLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBahanModalLabel{{ $item->id }}">Edit Bahan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('bahan.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="nama_bahan{{ $item->id }}" class="form-label">Nama
                                    Bahan</label>
                                <input type="text" class="form-control w-100" id="nama_bahan{{ $item->id }}"
                                    name="nama_bahan" value="{{ $item->nama_bahan }}" required>
                            </div>
                            @if (auth()->user()->hasRole('Headkitchen'))
                                <div class="mb-3">
                                    <label for="jenis_bahan{{ $item->id }}" class="form-label">Jenis Bahan</label>
                                    <select name="jenis_bahan" class="form-select w-100"
                                        id="jenis_bahan{{ $item->id }}" required>
                                        <option value="pasar" {{ $item->jenis_bahan == 'pasar' ? 'selected' : '' }}>Pasar
                                        </option>
                                        <option value="luar" {{ $item->jenis_bahan == 'luar' ? 'selected' : '' }}>
                                            Luar
                                        </option>
                                        <option value="frozen" {{ $item->jenis_bahan == 'frozen' ? 'selected' : '' }}>
                                            Frozen
                                        </option>
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label for="batas_minimum{{ $item->id }}" class="form-label">Batas
                                    Minimum</label>
                                <input type="number" class="form-control w-100" id="batas_minimum{{ $item->id }}"
                                    name="batas_minimum" value="{{ $item->batas_minimum }}">
                            </div>
                            @if (auth()->user()->hasRole('Headkitchen') || auth()->user()->hasRole('Headbar'))
                            <div class="mb-3">
                                <label for="sisa_stok{{ $item->id }}" class="form-label">Sisa Stok</label>
                                <input type="number" class="form-control w-100" id="sisa_stok{{ $item->id }}"
                                    name="sisa_stok" value="{{ $item->sisa_stok }}">
                            </div>
                            @endif
                            <div class="mb-3">
                                <label for="satuan{{ $item->id }}" class="form-label">Satuan
                                    Bahan</label>
                                <select class="form-select w-100" id="satuan{{ $item->id }}" name="satuan"
                                    required>
                                    <option value="pack" {{ $item->satuan == 'pack' ? 'selected' : '' }}>
                                        Pack</option>
                                    <option value="pcs" {{ $item->satuan == 'pcs' ? 'selected' : '' }}>
                                        Pcs</option>
                                    <option value="gram" {{ $item->satuan == 'gram' ? 'selected' : '' }}>
                                        Gram/ml</option>
                                    <option value="buah" {{ $item->satuan == 'buah' ? 'selected' : '' }}>
                                        Buah</option>
                                    <option value="kg" {{ $item->satuan == 'kg' ? 'selected' : '' }}>
                                        Kg</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- end --}}
    <!-- Modal Tambah Bahan Bar -->
    <div class="modal fade" id="addBahanBarModal" tabindex="-1" aria-labelledby="addBahanBarModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBahanBarModalLabel">Tambah Bahan Bar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bahan.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kode_bahan" class="form-label">Kode Bahan</label>
                            <select class="form-control" id="kode_bahan" name="kode_bahan" required>
                                @foreach ($kategoris as $kategori)
                                    @if (str_starts_with($kategori->kode_kategori, 'BB'))
                                        @php
                                            $lastBahan = App\Models\Bahan::where(
                                                'kode_bahan',
                                                'like',
                                                "$kategori->kode_kategori%",
                                            )
                                                ->orderBy('kode_bahan', 'desc')
                                                ->first();
                                            $nextNumber = $lastBahan
                                                ? ((int) substr($lastBahan->kode_bahan, -4)) + 1
                                                : 1;
                                            $kodeBahan =
                                                $kategori->kode_kategori . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                                        @endphp
                                        <option value="{{ $kodeBahan }}">{{ $kodeBahan }} -
                                            {{ $kategori->nama_kategori }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nama_bahan" class="form-label">Nama Bahan</label>
                            <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe Bahan</label>
                            <select name="tipe" class="form-control" required disabled data-locked>
                                {{-- <option value="process">Process</option> --}}
                                <option value="non-process">Non-Process</option>
                            </select>
                            <input type="hidden" name="tipe" value="non-process">
                        </div>

                        <div class="mb-3">
                            <label for="jenis_bahan" class="form-label">Jenis Bahan</label>
                            <select class="form-control" id="jenis_bahan" name="jenis_bahan" required>
                                <option value="" disabled selected hidden>Pilih Jenis Bahan</option>
                                <option value="kopi">Kopi</option>
                                <option value="milk">Milk</option>
                                <option value="syrup">Syrup</option>
                                <option value="poultry">Poultry</option>
                                <option value="powder">Powder</option>
                                <option value="tehsprite">Teh & Sprite</option>
                                <option value="topping">Topping</option>
                                <option value="buah">Buah</option>
                                <option value="equip">Equipment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="kategori_bahan" class="form-label">Kategori bahan</label>
                            <select class="form-control" id="kategori_bahan" name="kategori_bahan" required disabled data-locked>
                                @foreach ($kategoris as $kategori)
                                    @if (str_starts_with($kategori->kode_kategori, 'BBAR'))
                                        <option value="{{ $kategori->nama_kategori }}">{{ $kategori->nama_kategori }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" name="kategori_bahan"
                                value="{{ $kategoris->first(fn($k) => str_starts_with($k->kode_kategori, 'BBAR'))?->nama_kategori }}">
                        </div>

                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan Bahan</label>
                            <select class="form-control" id="satuan" name="satuan" required>
                                <option value="pack">Pack</option>
                                <option value="pcs">Pcs</option>
                                <option value="gram">Gram/ml</option>
                                <option value="buah">Buah</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- end --}}
    {{-- Modal Tambah Bahan Kitchen --}}
    <div class="modal fade" id="addBahanKitchenModal" tabindex="-1" aria-labelledby="addBahanKitchenModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBahanKitchenModalLabel">Tambah Bahan Kitchen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bahan.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kode_bahan" class="form-label">Kode Bahan</label>
                            <select class="form-control" id="kode_bahan" name="kode_bahan" required>
                                @foreach ($kategoris as $kategori)
                                    @if (str_starts_with($kategori->kode_kategori, 'BBKTC'))
                                        @php
                                            $lastBahan = App\Models\Bahan::where(
                                                'kode_bahan',
                                                'like',
                                                "$kategori->kode_kategori%",
                                            )
                                                ->orderBy('kode_bahan', 'desc')
                                                ->first();
                                            $nextNumber = $lastBahan
                                                ? ((int) substr($lastBahan->kode_bahan, -4)) + 1
                                                : 1;
                                            $kodeBahan =
                                                $kategori->kode_kategori . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                                        @endphp
                                        <option value="{{ $kodeBahan }}">{{ $kodeBahan }} -
                                            {{ $kategori->nama_kategori }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nama_bahan" class="form-label">Nama Bahan</label>
                            <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe Bahan</label>
                            <select name="tipe" class="form-control" required disabled data-locked>
                                <option value="non-process">Non-Process</option>
                            </select>
                            <input type="hidden" name="tipe" value="non-process">
                        </div>
                        <div class="mb-3">
                            <label for="jenis_bahan" class="form-label">Jenis Bahan</label>
                            <select class="form-control" id="jenis_bahan" name="jenis_bahan" required>
                                <option value="" disabled selected hidden>Pilih Jenis Bahan</option>
                                <option value="pasar">Pasar</option>
                                <option value="luar">Luar</option>
                                <option value="frozen">Frozen</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="kategori_bahan" class="form-label">Kategori bahan</label>
                            <select class="form-control" id="kategori_bahan" name="kategori_bahan" required disabled data-locked>
                                @foreach ($kategoris as $kategori)
                                    @if (str_starts_with($kategori->kode_kategori, 'BBKTC'))
                                        <option value="{{ $kategori->nama_kategori }}">{{ $kategori->nama_kategori }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" name="kategori_bahan"
                                value="{{ $kategoris->first(fn($k) => str_starts_with($k->kode_kategori, 'BBKTC'))?->nama_kategori }}">
                        </div>

                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan Bahan</label>
                            <select class="form-control" id="satuan" name="satuan" required>
                                <option value="kg">Kg</option>
                                <option value="gram">Gram</option>
                                <option value="liter">Liter</option>
                                <option value="ml">Ml</option>
                                <option value="pack">Pack</option>
                                <option value="pcs">Pcs</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- end --}}
    </div>{{-- /.container --}}

    {{-- SweetAlert sudah dimuat global di master.blade.php, tidak perlu CDN lagi di sini.
         Script sinkronisasi #sortBahan lama dihapus: elemennya sudah diganti filter bar,
         sehingga addEventListener-nya selalu error null, dan penggantian <tbody> lewat
         AJAX tidak lagi relevan setelah filter memakai submit form biasa. --}}
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

            .btn {
                font-size: 0.75rem;
                padding: 0.375rem 0.5rem;
            }
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endsection
