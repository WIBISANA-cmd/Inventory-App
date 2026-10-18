@extends('layouts.master')

@section('title', 'Daftar Menu Terjual')

@section('MenuTerjual')
    <div class="container">
        <div class="page-inner py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">Daftar Menu Terjual</h3>
                <p class="text-muted mb-0">
                    Total terjual sesuai filter aktif:
                    <strong>{{ number_format($totalTerjual) }}</strong> porsi
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuTerjualModal">
                <i class="fa fa-plus me-1"></i> Tambah Menu Terjual
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter --}}
        <x-filter-bar :action="route('menu.terjual.index')">
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Tanggal Tepat</label>
                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="dari_tanggal" class="form-control" value="{{ request('dari_tanggal') }}">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai_tanggal" class="form-control" value="{{ request('sampai_tanggal') }}">
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <label class="form-label">Menu</label>
                <select class="form-select" name="menu_id">
                    <option value="">Semua Menu</option>
                    @foreach ($menus as $m)
                        <option value="{{ $m->id }}" {{ request('menu_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_menu }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <label class="form-label">Cari Menu</label>
                <input type="text" class="form-control" name="search" placeholder="Nama menu..."
                    value="{{ request('search') }}">
            </div>
        </x-filter-bar>

        <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Menu</th>
                        <th>Jumlah Terjual</th>
                        <th>Komposisi Menu</th>
                        <th>Total Komposisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($menuTerjual as $item)
                        <tr>
                            <td>{{ $item->tanggal }}</td>
                            <td>{{ $item->menu->nama_menu ?? 'Tidak Diketahui' }}</td>
                            <td>{{ $item->jumlah_terjual }}</td>
                            <td>
                                <ul>
                                    @foreach ($item->menu->bahans ?? [] as $bahan)
                                        <li>{{ $bahan->nama_bahan ?? 'Tidak Diketahui' }} -
                                            {{ $bahan->pivot->gramasi ?? 0 }} {{ $bahan->satuan }}</li>
                                    @endforeach

                                    @foreach ($item->menu->bahanProcesses ?? [] as $bahanProcess)
                                        <li>{{ $bahanProcess->nama_bahan ?? 'Tidak Diketahui' }} -
                                            {{ $bahanProcess->pivot->gramasi ?? 0 }} {{ $bahanProcess->satuan }}</li>
                                    @endforeach
                                </ul>
                            </td>

                            <td>
                                <ul>
                                    @foreach ($item->menu->bahans ?? [] as $bahan)
                                        @php
                                            $hasil_seharusnya = $item->jumlah_terjual * ($bahan->pivot->gramasi ?? 0);
                                        @endphp
                                        <li>{{ $bahan->nama_bahan }}: {{ $hasil_seharusnya }} {{ $bahan->satuan }}</li>
                                    @endforeach

                                    @foreach ($item->menu->bahanProcesses ?? [] as $bahanProcess)
                                        @php
                                            $hasil_seharusnya =
                                                $item->jumlah_terjual * ($bahanProcess->pivot->gramasi ?? 0);
                                        @endphp
                                        <li>{{ $bahanProcess->nama_bahan }}: {{ $hasil_seharusnya }}
                                            {{ $bahanProcess->satuan }}</li>
                                    @endforeach
                                </ul>
                            </td>

                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"style="color:white;"
                                    data-bs-target="#editMenuTerjualModal{{ $item->id }}">
                                    Edit
                                </button>
                                <form action="{{ route('menu.terjual.destroy', $item->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-delete">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$menuTerjual" label="catatan penjualan" />
        </div>
        </div>
    </div>

    <!-- Modal Edit Menu Terjual -->
    @foreach ($menuTerjual as $item)
        <div class="modal fade" id="editMenuTerjualModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="editMenuTerjualLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMenuTerjualLabel{{ $item->id }}">Edit Jumlah
                            Terjual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('menu.terjual.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Jumlah Terjual</label>
                                <input type="number" class="form-control" name="jumlah_terjual"
                                    value="{{ $item->jumlah_terjual }}" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-success">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Tambah Menu Terjual -->
    <div class="modal fade" id="addMenuTerjualModal" tabindex="-1" aria-labelledby="addMenuTerjualLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMenuTerjualLabel">Tambah Menu Terjual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('menu.terjual.store') }}" method="POST">
                        @csrf

                        <!-- Input Tanggal -->
                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal Terjual</label>
                            <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                        </div>
                        @foreach ($menus->sortBy('nama_menu') as $menu)
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-3">{{ $menu->nama_menu }}</span>
                                <input type="hidden" name="menu_id[]" value="{{ $menu->id }}">
                                <input type="number" class="form-control w-50 ms-auto" name="jumlah_terjual[]"
                                    placeholder="Jumlah Terjual" required>
                            </div>
                        @endforeach

                        <!-- Input Menu Terjual -->
                        <div class="mb-3">
                            <label class="form-label">Nama Menu dan Jumlah Terjual</label>
                            <div class="list-group">
                                @foreach ($menus as $menu)
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-3">{{ $menu->nama_menu }}</span>
                                        <input type="hidden" name="menu_id[]" value="{{ $menu->id }}">
                                        <input type="number" class="form-control w-50 ms-auto" name="jumlah_terjual[]"
                                            placeholder="Jumlah Terjual" required>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                swal("Berhasil!", "{{ session('success') }}", "success");
            @endif
        });
    </script>
@endsection
