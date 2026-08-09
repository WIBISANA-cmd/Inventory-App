@extends('layouts.master')
@section('title', 'Daftar Menu')

@section('menu')
    <div class="container">
        <div class="page-inner py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">Daftar Menu</h3>
                <p class="text-muted mb-0">Menu jual beserta komposisi bahan penyusunnya.</p>
            </div>
            @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addmenuModal">
                    <i class="fa fa-plus me-1"></i> Tambah Menu
                </button>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filter --}}
        <x-filter-bar :action="route('menu.index')">
            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="kategori_id">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label">Status Menu</label>
                <select class="form-select" name="status_menu">
                    <option value="">Semua Status</option>
                    <option value="Tersedia" {{ request('status_menu') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="Tidak Tersedia" {{ request('status_menu') == 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                </select>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label">Mengandung Bahan</label>
                <select class="form-select" name="bahan_id">
                    <option value="">Semua Bahan</option>
                    @foreach ($bahans as $b)
                        <option value="{{ $b->id }}" {{ request('bahan_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->nama_bahan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-lg-3">
                <label class="form-label">Cari Menu</label>
                <input type="text" class="form-control" name="search" placeholder="Nama atau kode menu..."
                    value="{{ request('search') }}">
            </div>
        </x-filter-bar>

        <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead style="align-content: center;">
                    <tr>
                        <th>Kode Menu</th>
                        <th>Nama Menu</th>
                        <th>Komposisi Menu</th>
                        <th>Status</th>
                        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($menu as $item)
                        <tr>
                            <td>{{ $item->kode_menu }}</td>
                            <td>{{ $item->nama_menu }}</td>
                            <td>
                                @if ($item->bahans->isEmpty() && $item->bahanProcesses->isEmpty())
                                    <span>Tidak ada bahan</span>
                                @else
                                    <ul>
                                        @foreach ($item->bahans as $bahan)
                                            <li>{{ $bahan->nama_bahan }} - {{ $bahan->pivot->gramasi }} </li>
                                        @endforeach
                                        @foreach ($item->bahanProcesses as $bahanProcess)
                                            <li>{{ $bahanProcess->nama_bahan }} (Proses) -
                                                {{ $bahanProcess->pivot->gramasi }} </li>
                                        @endforeach
                                        
                                    </ul>
                                @endif
                            </td>
                            <td>
                                @php
                                    $bahanHabis = $item->bahans->where('sisa_stok', '<=', 0);
                                    $warna = $bahanHabis->isNotEmpty() ? 'red' : '#3db641';
                                    $status = $bahanHabis->isNotEmpty()
                                        ? 'Bahan Habis: ' . $bahanHabis->pluck('nama_bahan')->implode(', ')
                                        : 'Menu Tersedia';
                                @endphp
                                <span class="badge"
                                    style="background-color: {{ $warna }}; color: white; font-weight: bold; padding: 7px 15px; border-radius: 10px;">
                                    {{ $status }}
                                </span>
                            </td>
                            @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                                <td>
                                    <button class="btn btn-warning btn-sm mb-1" data-bs-toggle="modal"
                                        data-bs-target="#editmenuModal{{ $item->id }}">Edit</button>
                                    <form action="{{ route('menu.destroy', $item->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-delete">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>

                        <!-- Modal Edit Menu -->
                        <div class="modal fade" id="editmenuModal{{ $item->id }}" tabindex="-1"
                            aria-labelledby="editmenuModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Menu</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('menu.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="mb-3">
                                                <label class="form-label">Nama Menu</label>
                                                <input type="text" class="form-control" name="nama_menu"
                                                    value="{{ $item->nama_menu }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Bahan Biasa</label>
                                                <select class="form-control multi-select bahan-biasa" name="bahan_biasa[]"
                                                    multiple>
                                                    @foreach ($bahans as $bahan)
                                                        <option value="{{ $bahan->id }}"
                                                            {{ in_array($bahan->id, $item->bahans->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                            {{ $bahan->nama_bahan }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Gramasi Bahan Biasa</label>
                                                @foreach ($bahans as $bahan)
                                                    @php
                                                        $selectedBahan = $item->bahans
                                                            ->where('id', $bahan->id)
                                                            ->first();
                                                    @endphp
                                                    <div class="mb-2 {{ in_array($bahan->id, $item->bahans->pluck('id')->toArray()) ? '' : 'd-none' }} gramasi-biasa-group"
                                                        data-bahan-id="{{ $bahan->id }}">
                                                        <label class="form-label">Gramasi untuk
                                                            {{ $bahan->nama_bahan }}</label>
                                                        <input type="number" class="form-control"
                                                            name="gramasi_biasa[{{ $bahan->id }}]"
                                                            value="{{ $selectedBahan ? $selectedBahan->pivot->gramasi : '' }}">
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Bahan Process</label>
                                                <select class="form-control multi-select bahan-process"
                                                    name="bahan_process[]" multiple>
                                                    @foreach ($bahanProcesses as $bp)
                                                        <option value="{{ $bp->id }}"
                                                            {{ in_array($bp->id, $item->bahanProcesses->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                            {{ $bp->nama_bahan }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Gramasi Bahan Process</label>
                                                @foreach ($bahanProcesses as $bp)
                                                    @php
                                                        $selectedProcess = $item->bahanProcesses
                                                            ->where('id', $bp->id)
                                                            ->first();
                                                    @endphp
                                                    <div class="mb-2 {{ in_array($bp->id, $item->bahanProcesses->pluck('id')->toArray()) ? '' : 'd-none' }} gramasi-process-group"
                                                        data-bahan-id="{{ $bp->id }}">
                                                        <label class="form-label">Gramasi untuk
                                                            {{ $bp->nama_bahan }}</label>
                                                        <input type="number" class="form-control"
                                                            name="gramasi_process[{{ $bp->id }}]"
                                                            value="{{ $selectedProcess ? $selectedProcess->pivot->gramasi : '' }}">
                                                    </div>
                                                @endforeach
                                            </div>

                                            <button type="submit" class="btn btn-primary w-100">Update Menu</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$menu" label="menu" />
        </div>
        </div>

        <!-- Modal Tambah Menu -->
        <div class="modal fade" id="addmenuModal" tabindex="-1" aria-labelledby="addmenuModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('menu.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="kategori_id">Kategori</label>
                                <select class="form-select" name="kategori_id" id="kategori_id" required>
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    @foreach ($kategoris as $kat)
                                        <option value="{{ $kat->id }}" data-kode="{{ $kat->kode_kategori }}">
                                            {{ $kat->kode_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="nama_menu">Nama Menu</label>
                                <input type="text" class="form-control" name="nama_menu" id="nama_menu"
                                    placeholder="Masukkan nama menu" required>
                            </div>
                            {{-- komposisi bahan non proses --}}
                            <div class="mb-3">
                                <label class="form-label" for="bahan_biasa">Komposisi Bahan Non-Proses</label>
                                <select class="form-control bahan-biasa multi-select" name="bahan_biasa[]"
                                    id="bahan_biasa" multiple disabled>
                                    @foreach ($bahans as $bahan)
                                        <option value="{{ $bahan->id }}" data-nama="{{ $bahan->nama_bahan }}"
                                            data-kode_bahan="{{ $bahan->kode_bahan }}">
                                            {{ $bahan->nama_bahan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- komposisi bahan proses --}}
                            <div class="mb-3">
                                <label class="form-label" for="bahan_process">Komposisi Bahan Process</label>
                                <select class="form-control bahan-process multi-select" name="bahan_process[]"
                                    id="bahan_process" multiple disabled>
                                    @foreach ($bahanProcesses as $bp)
                                        <option value="{{ $bp->id }}" data-nama="{{ $bp->nama_bahan }}"
                                            data-kode_bahan="{{ $bp->kode_bahan }}">
                                            {{ $bp->nama_bahan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 gramasi-container"></div>
                            <button type="submit" class="btn btn-primary w-100">Tambah Menu</button>
                        </form>
                    </div>
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

            $(document).ready(function() {
                // Initialize dropdown if needed (Ensure dropdown.js is loaded)
                $('.multi-select').dropdown();

                // Event listener hanya untuk modal yang aktif
                $('.bahan-biasa, .bahan-process').on('change', function() {
                    let $modal = $(this).closest('.modal');
                    let bahanBiasa = ($modal.find('.bahan-biasa').val() || []).filter(item =>
                        item !== "");
                    let bahanProcess = ($modal.find('.bahan-process').val() || []).filter(item =>
                        item !== "");
                    
                    let gramasiContainer = $modal.find('.gramasi-container');

                    gramasiContainer.html(
                    ''); // Kosongkan gramasi container sebelum menambah input baru

                    // Generate gramasi input untuk bahan biasa
                    bahanBiasa.forEach(bahanId => {
                        let bahanName = $modal.find('.bahan-biasa option[value="' +
                            bahanId + '"]').data('nama');
                        gramasiContainer.append(`
                            <div class="mb-3">
                                <label class="form-label">Gramasi untuk ${bahanName} (Biasa)</label>
                                <input type="number" class="form-control" name="gramasi_biasa[${bahanId}]" required>
                            </div>
                        `);
                    });

                    // Generate gramasi input untuk bahan process
                    bahanProcess.forEach(bahanId => {
                        let bahanName = $modal.find('.bahan-process option[value="' +
                            bahanId + '"]').data('nama');
                        gramasiContainer.append(`
                            <div class="mb-3">
                                <label class="form-label">Gramasi untuk ${bahanName} (Process)</label>
                                <input type="number" class="form-control" name="gramasi_process[${bahanId}]" required>
                            </div>
                        `);
                    });

                    
                });

                // Filtering berdasarkan kode kategori saat pilih kategori menu
                $('select[name="kategori_id"]').on('change', function() {
                    const selectedKode = $(this).find(':selected').data(
                        'kode'); // Ambil kode_kategori (ex: BBAR/BBKTC)

                    // Jika tidak ada kategori yang dipilih, nonaktifkan bahan dan sembunyikan komposisi
                    if (!selectedKode) {
                        $('#bahan_biasa, #bahan_process').prop('disabled',
                            true); // Nonaktifkan dropdown bahan
                        $('#bahan_biasa option, #bahan_process option')
                            .hide(); // Sembunyikan semua opsi bahan
                        $('.gramasi-container').html(''); // Kosongkan komposisi gramasi
                    } else {
                        // Reset dan sembunyikan semua opsi bahan terlebih dahulu
                        $('#bahan_biasa option, #bahan_process option')
                            .show();

                        // Filter bahan biasa berdasarkan kategori
                        $('.bahan-biasa option').each(function() {
                            const kodeBahan = $(this).data('kode_bahan');
                            if (!kodeBahan || !kodeBahan.startsWith(selectedKode)) {
                                $(this).hide();
                            }
                        });

                        // Filter bahan proses berdasarkan kategori
                        $('.bahan-process option').each(function() {
                            const kodeBahan = $(this).data('kode_bahan');
                            if (!kodeBahan || !kodeBahan.startsWith(selectedKode)) {
                                $(this).hide();
                            }
                        });

                        // Aktifkan dropdown bahan setelah kategori dipilih
                        $('#bahan_biasa, #bahan_process').prop('disabled',
                        false);
                    }

                    // Kosongkan nilai yang mungkin masih terisi setelah kategori dipilih
                    $('.bahan-biasa').val([]).trigger('change');
                    $('.bahan-process').val([]).trigger('change');
                });

                // Modal shown event
                $('#addmenuModal').on('shown.bs.modal', function() {
                    // Sembunyikan semua option bahan saat modal dibuka
                    $('#bahan_biasa option, #bahan_process option')
                .hide();

                    // Nonaktifkan select-nya
                    $('#bahan_biasa, #bahan_process').prop('disabled', true);

                    // Kosongkan nilai yang mungkin masih terisi
                    $('#bahan_biasa, #bahan_process').val(null).trigger(
                    'change');
                    $('.gramasi-container').html(''); // Kosongkan komposisi gramasi
                });
            });
        });
    </script>
@endsection
