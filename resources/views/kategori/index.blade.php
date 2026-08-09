@extends('layouts.master')
@section('title', 'Kategori')
@section('kategori')
<div class="container">
    <div class="page-inner py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1">Daftar Kategori</h3>
            <p class="text-muted mb-0">Pengelompokan bahan baku dan inventaris.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKategoriModal">
            <i class="fa fa-plus me-1"></i> Tambah Kategori
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <x-filter-bar :action="route('kategori.index')">
        <div class="col-12 col-md-4">
            <label class="form-label">Kelompok</label>
            <select class="form-select" name="kelompok">
                <option value="">Semua Kelompok</option>
                <option value="bahan" {{ request('kelompok') == 'bahan' ? 'selected' : '' }}>Bahan Baku (BB*)</option>
                <option value="inventaris" {{ request('kelompok') == 'inventaris' ? 'selected' : '' }}>Inventaris (INV*)</option>
            </select>
        </div>
        <div class="col-12 col-md-5">
            <label class="form-label">Cari Kategori</label>
            <input type="text" class="form-control" name="search" placeholder="Nama atau kode kategori..."
                value="{{ request('search') }}">
        </div>
    </x-filter-bar>

    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="addKategoriModal" tabindex="-1" aria-labelledby="addKategoriModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addKategoriModalLabel">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label">Kode Kategori</label>
                            <select class="form-select" id="kode_kategori" name="kode_kategori" required>
                                <option value="BBAR">BBAR</option>
                                <option value="BBKTC">BBKTC</option>
                                <option value="INVB">INVB</option>
                                <option value="INVK">INVK</option>
                                <option value="INVO">INVO</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive px-3" style="overflow-x: auto;">
    <table class="table table-bordered table-striped text-center">
        <thead style="align-content: center;">
            <tr>
                <th>Kode Kategori</th>
                <th>Nama Kategori</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kategoris as $kategori)
            <tr>
                <td>{{ $kategori->kode_kategori }}</td>
                <td>{{ $kategori->nama_kategori }}</td>
                <td>{{ $kategori->keterangan }}</td>
                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editKategoriModal{{ $kategori->id }}">Edit</button>
                    <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-delete">Hapus</button>
                    </form>

                    <!-- Modal Edit Kategori -->
                    <div class="modal fade" id="editKategoriModal{{ $kategori->id }}" tabindex="-1" aria-labelledby="editKategoriModalLabel{{ $kategori->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editKategoriModalLabel{{ $kategori->id }}">Edit Kategori</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('kategori.update', $kategori->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="kode_kategori{{ $kategori->id }}" class="form-label">Kode Kategori</label>
                                            <input type="text" class="form-control" id="kode_kategori{{ $kategori->id }}" name="kode_kategori" value="{{ $kategori->kode_kategori }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nama_kategori{{ $kategori->id }}" class="form-label">Nama Kategori</label>
                                            <input type="text" class="form-control" id="nama_kategori{{ $kategori->id }}" name="nama_kategori" value="{{ $kategori->nama_kategori }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="keterangan{{ $kategori->id }}" class="form-label">Keterangan</label>
                                            <textarea class="form-control" id="keterangan{{ $kategori->id }}" name="keterangan">{{ $kategori->keterangan }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <x-table-pagination :paginator="$kategoris" label="kategori" />
    </div>
</div>
{{-- SweetAlert sudah dimuat global di master.blade.php --}}
<script>
    // Tampilkan SweetAlert saat data berhasil ditambahkan
    @if(session('success'))
        swal("Good job!", "{{ session('success') }}", {
            icon: "success",
            buttons: {
                confirm: {
                    className: "btn btn-success",
                },
            },
        });
    @endif
    
    document.getElementById('kode_kategori').addEventListener('change', function() {
        let kategoriMap = {
            'BBAR': 'Bahan Baku Bar',
            'BBKTC': 'Bahan Baku Kitchen',
            'INVB': 'Inventaris Bar',
            'INVK': 'Inventaris Kitchen',
            'INVO': 'Inventaris Operasional'
        };
        document.getElementById('nama_kategori').value = kategoriMap[this.value] || '';
    });
    
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