@extends('layouts.master')

@section('title', 'Data Bahan Akhir')

@section('databahanakhir')
<div class="container mt-5">
    <div class="card shadow-sm px-5 py-5">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h4 class="card-title mb-2">Data Bahan Akhir</h4>
        </div>
        
        <div class="card-body">
            <div class="table-responsive mt-4">
                @if ($data->isEmpty())
                    <div class="alert alert-warning text-center">Maaf, tidak ada data di tanggal ini</div>
                @else
                    @csrf
                    <table class="table table-striped table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Bahan</th>
                                <th>Nama Bahan</th>
                                <th>Last</th>
                                @if(auth()->user()->hasRole('Admin'))
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td>{{ $item->tanggal_input }}</td>
                                    <td>{{ $item->kode_bahan }}</td>
                                    <td>{{ $item->nama_bahan }}</td>
                                    <td>{{ $item->stok_terakhir }}</td>
                                    @if(auth()->user()->hasRole('Admin'))
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editBahanModal{{ $item->id }}">Edit</button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if(auth()->user()->hasRole('Admin'))
                        @foreach ($bahanAkhir as $item)
                            <!-- Modal Edit -->
                            <div class="modal fade" id="editBahanModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="editBahanModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('bahanakhir.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editBahanModalLabel{{ $item->id }}">Edit Bahan Akhir</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal</label>
                                                    <input type="date" name="tanggal_input" class="form-control"
                                                        value="{{ $item->tanggal_input }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kode Bahan</label>
                                                    <input type="text" name="kode_bahan" class="form-control"
                                                        value="{{ $item->kode_bahan }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Bahan</label>
                                                    <input type="text" name="nama_bahan" class="form-control"
                                                        value="{{ $item->nama_bahan }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Stok Terakhir</label>
                                                    <input type="number" name="stok_terakhir" class="form-control"
                                                        value="{{ $item->stok_terakhir }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                                                <button type="button" class="btn-secondary btn" data-bs-dismiss="modal">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal -->
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
