@extends('layouts.master')
@section('title', 'Daftar Inventaris')
@section('inventaris')
    <div class="container">
        <div class="page-inner py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">Daftar Inventaris</h3>
                <p class="text-muted mb-0">Aset &amp; peralatan Bar, Kitchen, dan Operasional.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventarisModal">
                <i class="fa fa-plus me-1"></i> Tambah Inventaris
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter (server-side, supaya tetap benar lintas halaman pagination) --}}
        <x-filter-bar :action="route('inventaris.index')">
            <div class="col-12 col-md-3">
                <label class="form-label">Kelompok</label>
                <select class="form-select" name="sort">
                    <option value="all">Semua Kelompok</option>
                    <option value="INVB" {{ request('sort') == 'INVB' ? 'selected' : '' }}>Inventaris Bar</option>
                    <option value="INVK" {{ request('sort') == 'INVK' ? 'selected' : '' }}>Inventaris Kitchen</option>
                    <option value="INVO" {{ request('sort') == 'INVO' ? 'selected' : '' }}>Inventaris Operasional</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Kondisi</label>
                <select class="form-select" name="kondisi">
                    <option value="">Semua Kondisi</option>
                    <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                    <option value="Rusak Ringan" {{ request('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak Berat" {{ request('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label">Satuan</label>
                <select class="form-select" name="satuan">
                    <option value="">Semua Satuan</option>
                    @foreach ($satuanOptions as $satuan)
                        <option value="{{ $satuan }}" {{ request('satuan') == $satuan ? 'selected' : '' }}>{{ $satuan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Cari Inventaris</label>
                <input type="text" class="form-control" name="search" placeholder="Nama atau kode inventaris..."
                    value="{{ request('search') }}">
            </div>
        </x-filter-bar>

        <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle"> {{-- Added align-middle for vertical alignment --}}
                <thead class="table-dark"> {{-- Darker header for better contrast --}}
                    <tr>
                        <th>Kode Inventaris</th>
                        <th>Nama Inventaris</th>
                        <th>Satuan</th>
                        <th>Kondisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventaris as $item)
                        <tr>
                            <td>
                                {{ $item->kode_inventaris }} <br>
                                @php
                                    $qrCode = DNS2D::getBarcodePNG(route('inventaris.scan', $item->id), 'QRCODE');
                                @endphp
                                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" width="100">
                                <br>
                                <a href="{{ route('inventaris.cetakBarcode', $item->id) }}"
                                    class="btn btn-success btn-sm mt-2">Cetak QR Code</a>
                            </td>
                            <td>{{ $item->nama_inventaris }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td>
                                @php
                                    $warna = match ($item->kondisi) {
                                        'Baik' => 'bg-success',
                                        'Rusak Ringan' => 'bg-warning text-dark',
                                        'Rusak Berat' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $warna }} p-2"> {{-- Using Bootstrap badge class --}}
                                    {{ $item->kondisi }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#editInventarisModal{{ $item->id }}">Edit</button>
                                <form action="{{ route('inventaris.destroy', $item->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus inventaris ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <div class="modal fade" id="editInventarisModal{{ $item->id }}" tabindex="-1"
                            aria-labelledby="editInventarisModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editInventarisModalLabel{{ $item->id }}">Edit
                                            Inventaris</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('inventaris.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="nama_inventaris{{ $item->id }}" class="form-label">Nama
                                                    Inventaris</label>
                                                <input type="text" class="form-control"
                                                    id="nama_inventaris{{ $item->id }}" name="nama_inventaris"
                                                    value="{{ $item->nama_inventaris }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="jumlah_inventaris{{ $item->id }}" class="form-label">Jumlah
                                                    Inventaris</label>
                                                <input type="number" class="form-control"
                                                    id="jumlah_inventaris{{ $item->id }}" name="jumlah_inventaris"
                                                    value="{{ $item->jumlah_inventaris }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="satuan{{ $item->id }}" class="form-label">Satuan
                                                    Inventaris</label>
                                                <input type="text" class="form-control" id="satuan{{ $item->id }}"
                                                    name="satuan" value="{{ $item->satuan }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="kondisi{{ $item->id }}" class="form-label">Kondisi
                                                    Inventaris</label>
                                                <select class="form-select" id="kondisi{{ $item->id }}" name="kondisi"
                                                    required>
                                                    <option value="Baik"
                                                        {{ $item->kondisi == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                    <option value="Rusak Ringan"
                                                        {{ $item->kondisi == 'Rusak Ringan' ? 'selected' : '' }}>Rusak
                                                        Ringan</option>
                                                    <option value="Rusak Berat"
                                                        {{ $item->kondisi == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat
                                                    </option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-warning">Update Inventaris</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$inventaris" label="inventaris" />
        </div>
        </div>
    </div>

    <div class="modal fade" id="addInventarisModal" tabindex="-1" aria-labelledby="addInventarisModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInventarisModalLabel">Tambah Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('inventaris.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kode_inventaris" class="form-label">Kode Inventaris</label>
                            <select class="form-select" id="kode_inventaris" name="kode_inventaris" required>
                                @foreach ($kategoris as $kategori)
                                    @if (str_starts_with($kategori->kode_kategori, 'INV'))
                                        @php
                                            $lastInventaris = App\Models\Inventaris::where(
                                                'kode_inventaris',
                                                'like',
                                                "$kategori->kode_kategori%",
                                            )
                                                ->orderBy('kode_inventaris', 'desc')
                                                ->first();
                                            $nextNumber = $lastInventaris
                                                ? ((int) substr($lastInventaris->kode_inventaris, -4)) + 1
                                                : 1;
                                            $kodeInventaris =
                                                $kategori->kode_kategori . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                                        @endphp
                                        <option value="{{ $kodeInventaris }}">{{ $kodeInventaris }} -
                                            {{ $kategori->nama_kategori }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nama_inventaris" class="form-label">Nama Inventaris</label>
                            <input type="text" class="form-control" id="nama_inventaris" name="nama_inventaris"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="satuan" class="form-label">Satuan</label>
                            <select class="form-select" id="satuan" name="satuan" required>
                                <option value="pcs">Pcs</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="kondisi" class="form-label">Kondisi</label>
                            <select class="form-select" id="kondisi" name="kondisi" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Inventaris</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- Sort AJAX & search keyup lama dihapus: keduanya hanya bekerja pada baris yang
         sedang tampil, sehingga memberi hasil menyesatkan begitu data dipaginasi.
         Keduanya digantikan filter server-side di atas. --}}
    <script>
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                if (window.swal) swal("Berhasil!", "{{ session('success') }}", "success");
            });
        @endif
    </script>
@endsection