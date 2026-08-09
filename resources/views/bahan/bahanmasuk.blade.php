@extends('layouts.master')
@section('title', 'Daftar Bahan Masuk')
@section('BahanMasuk')
<div class="container py-4">
    <h1 class="mb-4 text-center text-md-start">Daftar Bahan Masuk</h1>

    {{-- Add Bahan Masuk Buttons --}}
    <div class="row mb-3 justify-content-end gx-2">
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Bar'))
            <div class="col-auto">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addBahanMasukBarModal">
                    <i class="fa fa-plus"></i> Tambah Bahan Masuk Bar
                </button>
            </div>
        @endif
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headkitchen') || auth()->user()->hasRole('Kitchen'))
            <div class="col-auto">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addBahanMasukKitchenModal">
                    <i class="fa fa-plus"></i> Tambah Bahan Masuk Kitchen
                </button>
            </div>
        @endif
    </div>

    {{-- Success Session Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <hr>

    {{-- Filter --}}
    <x-filter-bar :action="route('bahan.bahanmasuk')">
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
            <label class="form-label">Tipe Bahan</label>
            <select class="form-select" name="tipe">
                <option value="">Semua Tipe</option>
                <option value="non-proses" {{ request('tipe') == 'non-proses' ? 'selected' : '' }}>Non-Proses</option>
                <option value="proses" {{ request('tipe') == 'proses' ? 'selected' : '' }}>Proses (Batch)</option>
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
            <input type="text" class="form-control" name="search" placeholder="Masukkan nama bahan..."
                value="{{ request('search') }}">
        </div>
    </x-filter-bar>

    {{-- Bahan Masuk Table --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle" id="table_bahan_masuk">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal Masuk</th>
                    <th>Kode Bahan</th>
                    <th>Nama Bahan</th>
                    <th>Jumlah Masuk</th>
                    <th>Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bahanMasuk->sortByDesc('created_at') as $item)
                    <tr class="data-row" data-kode="{{ $item->kode_bahan }}">
                        <td>{{ $item->tanggal_masuk }}</td>
                        <td>{{ $item->kode_bahan }}</td>
                        <td>{{ $item->nama_bahan }}</td>
                        <td>{{ $item->jumlah_masuk }}</td>
                        <td>{{ $item->bahan->satuan ?? ($item->bahanProcess->satuan ?? '-') }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm my-1" data-bs-toggle="modal" data-bs-target="#editBahanMasukModal{{ $item->id }}">Edit</button>
                            <form action="{{ route('bahan.bahanmasuk.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm my-1">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Tidak ada data bahan masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-table-pagination :paginator="$bahanMasuk" label="catatan bahan masuk" />
</div>

    <!-- Modal Tambah Bahan Masuk Bar-->
    <div class="modal fade" id="addBahanMasukBarModal" tabindex="-1" aria-labelledby="addBahanMasukBarLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBahanMasukLabel">Tambah Bahan Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bahan.bahanmasuk.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control w-30" name="tanggal_masuk" id="tanggal_masuk"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Bahan</label>
                            <select class="form-select" id="tipe_bahan_select_bar">
                                <option value="non-proses" selected>Non-Proses</option>
                                <option value="proses">Proses</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Bahan dan Jumlah Masuk</label>
                            {{-- Non-Proses Section --}}
                            <div id="bahan-nonproses-bar" class="list-group bahan-list">
                                @foreach ($bahans as $bahan)
                                    @if (strpos($bahan->kode_bahan, 'BBAR') === 0)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-3">{{ $bahan->nama_bahan }} <span>({{$bahan->satuan}})</span></span>
                                            <input type="hidden" name="bahan_id[]" value="{{ $bahan->id }}">
                                            <input type="hidden" name="tipe_bahan[]" value="non-proses">
                                            <input type="number" class="form-control w-50 ms-auto" name="jumlah_masuk[]"
                                                min="0" placeholder="Jumlah Masuk">
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Proses Section --}}
                            <div id="bahan-proses-bar" class="list-group bahan-list d-none">
                                @foreach ($bahan_processes as $process)
                                    @if (strpos($process->kode_bahan, 'BBAR') === 0)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-3">{{ $process->nama_bahan }}</span>
                                            <input type="hidden" name="bahan_id[]" value="{{ $process->id }}">
                                            <input type="hidden" name="tipe_bahan[]" value="proses">
                                            <input type="number" class="form-control w-50 ms-auto" name="jumlah_masuk[]"
                                                min="0" placeholder="Jumlah Masuk">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Tambah Bahan Masuk Kitchen-->
    <div class="modal fade" id="addBahanMasukKitchenModal" tabindex="-1" aria-labelledby="addBahanMasukKitchenLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBahanMasukLabel">Tambah Bahan Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('bahan.bahanmasuk.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control w-30" name="tanggal_masuk" id="tanggal_masuk"><br>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Bahan</label>
                            <select class="form-select" id="tipe_bahan_select_kitchen">
                                <option value="non-proses" selected>Non-Proses</option>
                                <option value="proses">Proses</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Bahan dan Jumlah Masuk</label>
                            {{-- Non-Proses Section --}}
                            <div id="bahan-nonproses-kitchen" class="list-group bahan-list">
                                @foreach ($bahans as $bahan)
                                    @if (strpos($bahan->kode_bahan, 'BBKTC') === 0)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-3">{{ $bahan->nama_bahan }} <span>({{$bahan->satuan}})</span></span>
                                            <input type="hidden" name="bahan_id[]" value="{{ $bahan->id }}">
                                            <input type="hidden" name="tipe_bahan[]" value="non-proses">
                                            <input type="number" class="form-control w-50 ms-auto" name="jumlah_masuk[]"
                                                min="0" placeholder="Jumlah Masuk">
                                            <input type="hidden" class="form-control w-50 ms-auto" name="jumlah_hasil[]" placeholder="Jumlah hasilkan">
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Proses Section --}}
                            <div id="bahan-proses-kitchen" class="list-group bahan-list d-none">
                                @foreach ($bahan_processes as $process)
                                    @if (strpos($process->kode_bahan, 'BBKTC') === 0)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-3">{{ $process->nama_bahan }}</span>
                                            <input type="hidden" name="bahan_id[]" value="{{ $process->id }}">
                                            <input type="hidden" name="tipe_bahan[]" value="proses">
                                            <input type="number" class="form-control w-50 ms-auto" name="jumlah_masuk[]" placeholder="Jumlah Masuk">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- JavaScript untuk mengisi satuan otomatis -->
    <script>
        document.getElementById('kode_bahan').addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            let satuan = selectedOption.getAttribute('data-satuan') || '-';
            document.getElementById('satuan_bahan').value = satuan;
        });
    </script>

    <!-- Modal Edit Bahan Masuk -->
    @foreach ($bahanMasuk as $item)
        <div class="modal fade" id="editBahanMasukModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="editBahanMasukLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBahanMasukLabel{{ $item->id }}">Edit Bahan Masuk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('bahan.bahanmasuk.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="text" class="form-control" name="tanggal_masuk"
                                    value="{{ $item->tanggal_masuk }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kode Bahan</label>
                                <input type="text" class="form-control" name="kode_bahan"
                                    value="{{ $item->kode_bahan }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah Masuk</label>
                                <input type="number" class="form-control" name="jumlah_masuk"
                                    value="{{ $item->jumlah_masuk }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Satuan</label>
                                <input type="text" class="form-control" value="{{ $item->bahan->satuan ?? '-' }}"
                                    readonly>
                            </div>
                            <button type="submit" class="btn btn-success">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    {{-- SweetAlert sudah dimuat global di master.blade.php. Script sinkronisasi #sortBahan
         lama dihapus: elemennya sudah diganti filter bar (selalu error null), dan ia
         menimpa <tbody> halaman ini dengan <tbody> dari halaman Daftar Bahan. --}}
    <script>
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', function () {
                if (window.swal) swal("Berhasil!", "{{ session('success') }}", "success");
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            function setupTipeBahanToggle(selectId, nonProsesId, prosesId) {
                const tipeSelect = document.getElementById(selectId);
                const nonProsesList = document.getElementById(nonProsesId);
                const prosesList = document.getElementById(prosesId);

                if (!tipeSelect || !nonProsesList || !prosesList) return;

                tipeSelect.addEventListener('change', function() {
                    if (this.value === 'proses') {
                        prosesList.classList.remove('d-none');
                        nonProsesList.classList.add('d-none');
                    } else {
                        nonProsesList.classList.remove('d-none');
                        prosesList.classList.add('d-none');
                    }
                });
            }

            setupTipeBahanToggle('tipe_bahan_select_bar', 'bahan-nonproses-bar', 'bahan-proses-bar');
            setupTipeBahanToggle('tipe_bahan_select_kitchen', 'bahan-nonproses-kitchen', 'bahan-proses-kitchen');
        });
    </script>

@endsection
