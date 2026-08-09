@extends('layouts.master')
@section('title', 'Proses Bahan')

@section('bahanProcess')
    <div class="container">
        <h1 class="mb-4 mx-5 my-5">Komposisi Bahan Proses</h1>
        
        {{-- END BAR --}}
        {{-- TAMBAH BAHAN PROSES BAR --}}
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Bar'))
            <button class="btn btn-primary mb-3 float-end me-3" data-bs-toggle="modal" data-bs-target="#addProcessModalBAR">
                <i class="fa fa-plus"></i> Tambah Bahan Proses BAR
            </button>
        @endif
        {{-- END BAR --}}
        {{-- TAMBAH BAHAN PROSES KITCHEN --}}
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headkitchen') || auth()->user()->hasRole('Kitchen'))
            <button class="btn btn-primary mb-3 float-end me-3" data-bs-toggle="modal" data-bs-target="#addProcessModalKTC">
                <i class="fa fa-plus"></i> Tambah Bahan Proses KITCHEN
            </button>
        @endif
        {{-- END BAR --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        {{-- Filter --}}
        <x-filter-bar :action="route('bahan.process')">
            <div class="col-12 col-md-3">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="kategori_bahan">
                    <option value="">Semua Area</option>
                    <option value="BBAR" {{ request('kategori_bahan') == 'BBAR' ? 'selected' : '' }}>Bahan Baku Bar</option>
                    <option value="BBKTC" {{ request('kategori_bahan') == 'BBKTC' ? 'selected' : '' }}>Bahan Baku Kitchen</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Status Stok</label>
                <select class="form-select" name="status_stok">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status_stok') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Cari Nama Bahan Proses</label>
                <input type="text" class="form-control" name="search" placeholder="Masukkan nama racikan..."
                    value="{{ request('search') }}">
            </div>
        </x-filter-bar>

        {{-- TABEL --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>Kode Bahan</th>
                        <th>Kategori Bahan</th>
                        <th>Nama Bahan Proses</th>
                        <th>Jumlah</th>
                        <th>Sisa Stok</th>
                        <th>Komposisi Bahan</th>
                        <th>Batas Minimum</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bahanProcesses as $item)
                        <tr>
                            <td>{{ $item->kode_bahan }}</td>
                            <td>{{ $item->kategori_bahan }}</td>
                            <td>{{ $item->nama_bahan }}</td>
                            <td>{{ $item->jumlah_batch}}</td>
                            <td>{{ $item->sisa_stok }}</td>
                            <td>
                                @if ($item->bahans->isEmpty())
                                    <span>Tidak ada bahan</span>
                                @else
                                    <ul>
                                        @foreach ($item->bahans as $bahan)
                                            <li>{{ $bahan->nama_bahan }} - {{ $bahan->pivot->gramasi }} gr</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>{{ $item->batas_minimum }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td>
                                <x-status-badge :sisa-stok="$item->sisa_stok" :batas-minimum="$item->batas_minimum" />
                            </td>
                            @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Headbar') || auth()->user()->hasRole('Headkitchen'))
                                <td>
                                    <button class="btn btn-warning btn-sm mb-1" data-bs-toggle="modal"
                                        data-bs-target="#editProcessModal{{ $item->id }}">Edit</button>

                                    <form action="{{ route('bahan.process.destroy', $item->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus proses bahan ini?')">
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

        <x-table-pagination :paginator="$bahanProcesses" label="bahan proses" />
        {{-- END TABEL --}}
        <!-- Modal Edit Proses -->
        @foreach ($bahanProcesses as $item)
            <div class="modal fade" id="editProcessModal{{ $item->id }}" tabindex="-1"
                aria-labelledby="editProcessModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('bahan.process.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Proses Bahan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nama_bahan" class="form-label">Nama Proses</label>
                                    <input type="text" class="form-control" name="nama_bahan"
                                        value="{{ $item->nama_bahan }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Komposisi Bahan</label>
                                    <select class="form-control multi-select" name="bahan_proses[]" multiple required
                                        onchange="toggleGramasiInputs({{ $item->id }})"
                                        id="select-bahan-{{ $item->id }}">
                                        @foreach ($bahans as $bahan)
                                            <option value="{{ $bahan->id }}"
                                                {{ in_array($bahan->id, $item->bahans->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                {{ $bahan->nama_bahan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @foreach ($bahans as $bahan)
                                    @php
                                        $pivot = $item->bahans->firstWhere('id', $bahan->id)?->pivot;
                                        $isSelected = in_array($bahan->id, $item->bahans->pluck('id')->toArray());
                                    @endphp
                                    <div class="mb-3">
                                        <label class="form-label">Gramasi untuk {{ $bahan->nama_bahan }}</label>
                                        <input type="number" class="form-control gramasi-input-{{ $item->id }}"
                                            name="gramasi[{{ $bahan->id }}]"
                                            value="{{ $pivot ? $pivot->gramasi : '' }}"
                                            id="gramasi-{{ $item->id }}-{{ $bahan->id }}"
                                            {{ $isSelected ? '' : 'disabled' }}>
                                    </div>
                                @endforeach

                                <div class="mb-3">
                                    <label for="batas_minimum" class="form-label">Batas Minimum</label>
                                    <input type="number" class="form-control" name="batas_minimum"
                                        value="{{ $item->batas_minimum }}">
                                </div>
                                <div class="mb-3">
                                    <label for="satuan" class="form-label">Satuan</label>
                                    <select class="form-select" name="satuan" required>
                                        <option value="gram" {{ $item->satuan == 'gram' ? 'selected' : '' }}>Gram
                                        </option>
                                        <option value="ml" {{ $item->satuan == 'ml' ? 'selected' : '' }}>ML</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary w-100" type="submit">Update Proses</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- END MODAL EDIT --}}
        
        <!-- Modal Tambah Proses BAR -->
        <div class="modal fade" id="addProcessModalBAR" tabindex="-1" aria-labelledby="addProcessModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('bahan.process.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Proses Bahan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="kode_bahan" class="form-label">Kode Bahan</label>
                                <select class="form-control" id="kode_bahan_bar" name="kode_bahan" required>
                                    @foreach ($kategoris as $kategori)
                                        @if (str_starts_with($kategori->kode_kategori, 'BBAR'))
                                            @php
                                                // Generate angka random 4 digit
                                                $randomNumber = random_int(1000, 9999);

                                                //  kode kategori dengan angka random
                                                $kodeBahan = $kategori->kode_kategori . $randomNumber;
                                            @endphp
                                            <option value="{{ $kodeBahan }}">{{ $kodeBahan }} -
                                                {{ $kategori->nama_kategori }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="kategori_bahan" class="form-label">Kategori bahan</label>
                                <select class="form-control" id="kategori_bahan" name="kategori_bahan" required>
                                    @foreach ($kategoris as $kategori)
                                        @if (str_starts_with($kategori->kode_kategori, 'BBAR'))
                                            <option value="{{ $kategori->nama_kategori }}">{{ $kategori->nama_kategori }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nama_bahan" class="form-label">Nama Proses</label>
                                <input type="text" class="form-control" name="nama_bahan" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="bahan_biasa">Komposisi Bahan</label>
                                <select class="form-control bahan-biasa multi-select" name="bahan_biasa[]" multiple>
                                    @foreach ($bahans as $bahan)
                                        @if (str_starts_with($bahan->kode_bahan, 'BBAR'))
                                            <option value="{{ $bahan->id }}" data-nama="{{ $bahan->nama_bahan }}"
                                                data-kode_bahan="{{ $bahan->kode_bahan }}">
                                                {{ $bahan->nama_bahan }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="mb-3">
                                <label class="form-label" for="bahan_process">Komposisi Bahan Process</label>
                                <select class="form-control bahan-process multi-select" name="bahan_process[]" multiple>
                                    @foreach ($bahanProcesses as $bp)
                                        @if (str_starts_with($bp->kode_bahan, 'BBAR'))
                                            <option value="{{ $bp->id }}" data-nama="{{ $bp->nama_bahan }}"
                                                data-kode_bahan="{{ $bp->kode_bahan }}">
                                                {{ $bp->nama_bahan }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="mb-3 gramasi-container" data-target="bar"></div>
                            <div class="mb-3">
                                <label for="batas_minimum" class="form-label">Batas Minimum</label>
                                <input type="number" class="form-control" id="batas_minimum" name="batas_minimum"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="satuan" class="form-label">Satuan</label>
                                <select class="form-select" name="satuan" required>
                                    <option value="gram">Gram</option>
                                    <option value="ml">ML</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary w-100" type="submit">Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal Tambah Proses KITCHEN -->
        <div class="modal fade" id="addProcessModalKTC" tabindex="-1" aria-labelledby="addProcessModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('bahan.process.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Proses Bahan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="kode_bahan" class="form-label">Kode Bahan</label>
                                <select class="form-control" id="kode_bahan_kitchen" name="kode_bahan" required>
                                    @foreach ($kategoris as $kategori)
                                        @if (str_starts_with($kategori->kode_kategori, 'BBKTC'))
                                            @php
                                                // Generate angka random 4 digit
                                                $randomNumber = random_int(1000, 9999);

                                                //  kode kategori dengan angka random
                                                $kodeBahan = $kategori->kode_kategori . $randomNumber;
                                            @endphp
                                            <option value="{{ $kodeBahan }}">{{ $kodeBahan }} -
                                                {{ $kategori->nama_kategori }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="kategori_bahan" class="form-label">Kategori bahan</label>
                                <select class="form-control" id="kategori_bahan" name="kategori_bahan" required>
                                    @foreach ($kategoris as $kategori)
                                        @if (str_starts_with($kategori->kode_kategori, 'BBKTC'))
                                            <option value="{{ $kategori->nama_kategori }}">{{ $kategori->nama_kategori }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nama_bahan" class="form-label">Nama Proses</label>
                                <input type="text" class="form-control" name="nama_bahan" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="bahan_biasa">Komposisi Bahan Non-Proses</label>
                                <select class="form-control bahan-biasa multi-select" name="bahan_biasa[]"multiple>
                                    @foreach ($bahans as $bahan)
                                        @if (str_starts_with($bahan->kode_bahan, 'BBKTC'))
                                            <option value="{{ $bahan->id }}" data-nama="{{ $bahan->nama_bahan }}"
                                                data-kode_bahan="{{ $bahan->kode_bahan }}">
                                                {{ $bahan->nama_bahan }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="mb-3">
                                <label class="form-label" for="bahan_process">Komposisi Bahan Process</label>
                                <select class="form-control bahan-process multi-select" name="bahan_process[]"multiple>
                                    @foreach ($bahanProcesses as $bp)
                                        @if (str_starts_with($bp->kode_bahan, 'BBKTC'))
                                            <option value="{{ $bp->id }}" data-nama="{{ $bp->nama_bahan }}"
                                                data-kode_bahan="{{ $bp->kode_bahan }}">
                                                {{ $bp->nama_bahan }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div> --}}

                            <div class="mb-3 gramasi-container" data-target="kitchen"></div>
                            <div class="mb-3">
                                <label for="batas_minimum" class="form-label">Batas Minimum</label>
                                <input type="number" class="form-control" id="batas_minimum" name="batas_minimum"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="satuan" class="form-label">Satuan</label>
                                <select class="form-select" name="satuan" required>
                                    <option value="gram">Gram</option>
                                    <option value="ml">ML</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary w-100" type="submit">Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        function toggleGramasiInputs(processId) {
            const selectedOptions = Array.from(document.getElementById(`select-bahan-${processId}`).selectedOptions).map(
                opt => opt.value);
            const allInputs = document.querySelectorAll(`.gramasi-input-${processId}`);

            allInputs.forEach(input => {
                const bahanId = input.name.match(/\[(\d+)\]/)[1];
                if (selectedOptions.includes(bahanId)) {
                    input.removeAttribute('disabled');
                } else {
                    input.setAttribute('disabled', 'disabled');
                    input.value = ''; // Optional: kosongkan jika tidak dipilih
                }
            });
        }

        // Inisialisasi saat modal dibuka agar sesuai dengan data awal
        document.addEventListener('DOMContentLoaded', () => {
            @foreach ($bahanProcesses as $item)
                toggleGramasiInputs({{ $item->id }});
            @endforeach
        });
        $(document).ready(function() {
            // Event perubahan bahan biasa / bahan process di kedua modal
            $(document).on('change', '.bahan-biasa, .bahan-process', function() {
                let $modal = $(this).closest('.modal');
                let bahanBiasa = $modal.find('.bahan-biasa').val() || [];
                let bahanProcess = $modal.find('.bahan-process').val() || [];
                let gramasiContainer = $modal.find('.gramasi-container');

                gramasiContainer.html(''); // Kosongkan container sebelum diisi ulang

                // Tambahkan input gramasi untuk bahan biasa
                bahanBiasa.forEach(function(bahanId) {
                    let bahanOption = $modal.find(`.bahan-biasa option[value="${bahanId}"]`);
                    let bahanName = bahanOption.data('nama') || bahanOption.text();
                    gramasiContainer.append(`
                        <div class="mb-3">
                            <label class="form-label">Gramasi untuk ${bahanName} (Biasa)</label>
                            <input type="number" class="form-control" name="gramasi_biasa[${bahanId}]" min="1" required>
                        </div>
                    `);
                });

                // Tambahkan input gramasi untuk bahan process
                bahanProcess.forEach(function(bahanId) {
                    let bahanOption = $modal.find(`.bahan-process option[value="${bahanId}"]`);
                    let bahanName = bahanOption.data('nama') || bahanOption.text();
                    gramasiContainer.append(`
                        <div class="mb-3">
                            <label class="form-label">Gramasi untuk ${bahanName} (Process)</label>
                            <input type="number" class="form-control" name="gramasi_process[${bahanId}]" min="1" required>
                        </div>
                    `);
                });
            });

            // Opsional: Reset form ketika modal ditutup agar tidak cache field sebelumnya
            $('.modal').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset(); // reset form
                $(this).find('.gramasi-container').html(''); // hapus input dinamis
                $(this).find('.multi-select').val(null).trigger('change'); // reset select2 jika dipakai
            });
        });
    </script>


@endsection
