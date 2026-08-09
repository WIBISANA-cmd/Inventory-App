<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\BahanProcess;
use App\Models\Kategori;
use App\Models\Komposisis;
use Illuminate\Http\Request;
use App\Events\StokMenipisEvent;
use App\Models\BahanProcessKomposisi;

class BahanProcessController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $kategoriFilter = request('kategori_bahan');

        $query = BahanProcess::with(['bahans']);
        $bahanQuery = Bahan::where('tipe', 'non-process');

        if ($user->hasRole('Admin')) {
            // Admin bisa lihat semua, tapi tetap bisa difilter berdasarkan kategori_bahan
            if ($kategoriFilter) {
                $query->whereHas('bahans', function ($q) use ($kategoriFilter) {
                    $q->where('kode_bahan', 'LIKE', $kategoriFilter . '%');
                });
                $bahanQuery->where('kode_bahan', 'LIKE', $kategoriFilter . '%');
            }
        } elseif ($user->hasAnyRole(['Headbar', 'Bar'])) {
            $query->whereHas('bahans', function ($q) {
                $q->where('kode_bahan', 'LIKE', 'BBAR%');
            });
            $bahanQuery->where('kode_bahan', 'LIKE', 'BBAR%');
        } elseif ($user->hasAnyRole(['Headkitchen', 'Kitchen'])) {
            $query->whereHas('bahans', function ($q) {
                $q->where('kode_bahan', 'LIKE', 'BBKTC%');
            });
            $bahanQuery->where('kode_bahan', 'LIKE', 'BBKTC%');
        } else {
            // Role tidak dikenal: kosongkan hasil lewat query (bukan early-return collect())
            // supaya view selalu menerima paginator, bukan dua tipe berbeda.
            $query->whereRaw('1 = 0');
            $bahanQuery->whereRaw('1 = 0');
        }

        if (request()->filled('search')) {
            $query->where('nama_bahan', 'like', '%' . request('search') . '%');
        }

        match (request('status_stok')) {
            'habis' => $query->where('sisa_stok', '<=', 0),
            'menipis' => $query->where('sisa_stok', '>', 0)->whereColumn('sisa_stok', '<=', 'batas_minimum'),
            'aman' => $query->whereColumn('sisa_stok', '>', 'batas_minimum'),
            default => null,
        };

        $bahanProcesses = $query
            ->orderByRaw('CASE WHEN sisa_stok <= 0 THEN 0 WHEN sisa_stok <= batas_minimum THEN 1 ELSE 2 END')
            ->orderBy('nama_bahan')
            ->paginate(10)
            ->withQueryString();

        $bahans = $bahanQuery->get();
        $kategoris = Kategori::all();

        return view('bahan.process', compact('bahanProcesses', 'bahans', 'kategoris'));
    }



    public function cekStokMenipis()
    {
        $bahanMenipis = BahanProcess::whereColumn('sisa_stok', '<=', 'batas_minimum')->get();

        foreach ($bahanMenipis as $bahan) {
            event(new StokMenipisEvent($bahan));
        }

        return response()->json(['message' => 'Notifikasi stok menipis dikirim!']);
    }
    public function store(Request $request)
    {
        $request->validate([
            'kode_bahan' => 'required|unique:bahan_processes,kode_bahan',
            'kategori_bahan' => 'required',
            'nama_bahan' => 'required|string',
            'bahan_process' => 'nullable|array',
            'bahan_process.*' => 'exists:bahan_processes,id',
            'gramasi_process' => 'nullable|array',
            'gramasi_process.*' => 'numeric|min:0',
            'satuan' => 'required',
            'bahan_biasa' => 'nullable|array',
            'bahan_biasa.*' => 'exists:bahans,id',
            'gramasi_biasa' => 'nullable|array',
            'gramasi_biasa.*' => 'numeric|min:0',
            'batas_minimum' => 'required|integer|min:0',
        ]);

        // Buat record bahan proses utama
        $bahanProses = BahanProcess::create([
            'kode_bahan' => $request->kode_bahan,
            'kategori_bahan' => $request->kategori_bahan,
            'nama_bahan' => $request->nama_bahan,
            'satuan' => $request->satuan,
            'batas_minimum' => $request->batas_minimum,
        ]);

        // Simpan bahan biasa ke tabel pivot
        if ($request->bahan_biasa && $request->gramasi_biasa) {
            $bahanBiasaData = [];
            foreach ($request->bahan_biasa as $bahanId) {
                if (isset($request->gramasi_biasa[$bahanId])) {
                    $bahanBiasaData[$bahanId] = ['gramasi' => $request->gramasi_biasa[$bahanId]];
                }
            }
            // Menggunakan sync untuk bahan biasa
            $bahanProses->bahans()->sync($bahanBiasaData);
        }

        // Simpan bahan proses ke tabel pivot `bahan_process_komposisi`
        if ($request->bahan_process && $request->gramasi_process) {
            foreach ($request->bahan_process as $bahanProcessId) {
                if (isset($request->gramasi_process[$bahanProcessId])) {
                    Komposisis::create([
                        'bahan_process_id' => $bahanProses->id,
                        'bahan_id' => $bahanProcessId,
                        'gramasi' => $request->gramasi_process[$bahanProcessId],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success');
    }


    public function update(Request $request, $id)
{
    // Validasi input
    $request->validate([
        'nama_bahan' => 'required|string|max:255',
        'satuan' => 'required|string|in:gram,ml',
        'batas_minimum' => 'required|integer|min:0',
        'bahan_proses' => 'required|array',
        'bahan_proses.*' => 'exists:bahans,id',
        'gramasi' => 'required|array',
        'gramasi.*' => 'nullable|numeric|min:0'
    ]);

    // Ambil model
    $bahanProses = BahanProcess::findOrFail($id);

    // Hitung total gramasi hanya dari bahan yang dipilih
    $totalGramasi = 0;
    foreach ($request->bahan_proses as $bahanId) {
        $gramasi = $request->gramasi[$bahanId] ?? null;
        if (!is_null($gramasi)) {
            $totalGramasi += (float) $gramasi;
        }
    }

    // Update field utama di model
    $bahanProses->update([
        'nama_bahan' => $request->nama_bahan,
        'satuan' => $request->satuan,
        'batas_minimum' => $request->batas_minimum,
        'sisa_stok' => $totalGramasi, // Sesuaikan jika perlu dikosongkan
    ]);

    // Siapkan data pivot untuk sync (hanya bahan yang dipilih dan memiliki gramasi)
    $pivotData = [];
    foreach ($request->bahan_proses as $bahanId) {
        $gramasi = $request->gramasi[$bahanId] ?? null;
        if (!is_null($gramasi)) {
            $pivotData[$bahanId] = ['gramasi' => (float) $gramasi];
        }
    }

    // Sinkronisasi relasi many-to-many (bahan_proses <-> bahan)
    $bahanProses->bahans()->sync($pivotData);

    return redirect()->back()->with('success');
}


    public function destroy($id)
    {
        $bahanProses = BahanProcess::findOrFail($id);
        $bahanProses->bahans()->detach();
        $bahanProses->delete();

        return redirect()->route('bahan.process')->with('success');
    }
}
