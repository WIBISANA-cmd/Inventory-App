<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bahan;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Events\StokMenipisEvent;

class BahanController extends Controller
{
    public function index(Request $request)
    {
        $kategoris = Kategori::all();
        $user = Auth::user();
        $kategoriBahan = $request->query('kategori_bahan', 'all');

        $query = Bahan::query();

        if ($request->filled('search')) {
            $query->where('nama_bahan', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan kategori bahan (BBAR / BBKTC)
        if ($kategoriBahan === 'BBAR') {
            $query->where('kode_bahan', 'like', 'BBAR%');
        } elseif ($kategoriBahan === 'BBKTC') {
            $query->where('kode_bahan', 'like', 'BBKTC%');
        } else {
            // Filter berdasarkan role user
            $query->where(function ($q) use ($user) {
                if ($user->hasRole(['Admin', 'Headbar', 'Bar'])) {
                    $q->where('kode_bahan', 'LIKE', 'BBAR%');
                }

                if ($user->hasRole(['Admin', 'Headkitchen', 'Kitchen'])) {
                    $q->orWhere('kode_bahan', 'LIKE', 'BBKTC%');
                }
            });
        }

        if ($request->filled('jenis_bahan')) {
            $query->where('jenis_bahan', $request->jenis_bahan);
        }

        if ($request->filled('satuan')) {
            $query->where('satuan', $request->satuan);
        }

        // Filter status stok — dihitung dari sisa_stok vs batas_minimum, sama seperti
        // yang ditampilkan komponen <x-status-badge>.
        match ($request->query('status_stok')) {
            'habis' => $query->where('sisa_stok', '<=', 0),
            'menipis' => $query->where('sisa_stok', '>', 0)->whereColumn('sisa_stok', '<=', 'batas_minimum'),
            'aman' => $query->whereColumn('sisa_stok', '>', 'batas_minimum'),
            default => null,
        };

        // Urutan HABIS -> MENIPIS -> AMAN. Dulu di-sort di Blade; dipindah ke query karena
        // sort di view hanya mengurutkan isi satu halaman setelah pagination.
        $bahan = $query
            ->orderByRaw('CASE WHEN sisa_stok <= 0 THEN 0 WHEN sisa_stok <= batas_minimum THEN 1 ELSE 2 END')
            ->orderBy('nama_bahan')
            ->paginate(15)
            ->withQueryString();

        // Opsi dropdown filter diambil dari data yang ada, bukan hardcode.
        $jenisOptions = Bahan::query()->select('jenis_bahan')->distinct()->orderBy('jenis_bahan')->pluck('jenis_bahan');
        $satuanOptions = Bahan::query()->select('satuan')->distinct()->orderBy('satuan')->pluck('satuan');

        return view('bahan.daftarbahan', compact('bahan', 'kategoris', 'jenisOptions', 'satuanOptions'));
    }



    public function laporan(Request $request)
    {
        $bahans = collect();
        $message = null;

        if ($request->has(['dari_tanggal', 'sampai_tanggal'])) {
            $dari_tanggal = $request->dari_tanggal . ' 00:00:00';
            $sampai_tanggal = $request->sampai_tanggal . ' 23:59:59';

            $bahans = Bahan::whereBetween('created_at', [$dari_tanggal, $sampai_tanggal])->get();

            if ($bahans->isEmpty()) {
                $message = "Maaf, tidak ada data di tanggal ini";
            }
        }

        return view('laporan.bahan', compact('bahans', 'message'));
    }

    public function downloadPdf()
    {
        $bahans = Bahan::where('kode_bahan', 'LIKE', 'BB%')->get();
        $pdf = Pdf::loadView('laporan.bahan_pdf', compact('bahans'));
        return $pdf->download('laporan_daftar_bahan.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_bahan' => 'required|unique:bahans,kode_bahan',
            'nama_bahan' => 'required|string|max:255',
            'tipe' => 'required|string',
            'jenis_bahan' => 'required|string',
            'kategori_bahan' => 'required|string',
            'sisa_stok' => 'nullable|integer|min:0',
            'satuan' => 'required|string',
            'batas_minimum' => 'nullable|integer|min:0',
        ]);

        $sisaStok = $request->input('sisa_stok', 0) ?? 0;
        $status = $sisaStok > 2 ? 'AMAN' : ($sisaStok > 0 ? 'MENIPIS' : 'HABIS');

        Bahan::create([
            'kode_bahan' => $request->kode_bahan,
            'nama_bahan' => $request->nama_bahan,
            'tipe' => $request->tipe,
            'jenis_bahan' => $request->jenis_bahan,
            'kategori_bahan' => $request->kategori_bahan,
            'sisa_stok' => $sisaStok,
            'batas_minimum' => $request->input('batas_minimum', 5) ?? 5,
            'satuan' => $request->satuan,
            'status' => $status
        ]);

        return redirect()->route('bahan.index')->with('success', 'Bahan baku berhasil ditambahkan!');
    }

    public function cekStokMenipis()
    {
        $batasMenipis = 5;
        $bahanMenipis = Bahan::where('sisa_stok', '<=', $batasMenipis)->get();

        foreach ($bahanMenipis as $bahan) {
            event(new StokMenipisEvent($bahan));
        }

        return response()->json(['message' => 'Notifikasi stok menipis dikirim!']);
    }

    public function update(Request $request, $id)
    {
        $bahan = Bahan::findOrFail($id);

        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'jenis_bahan' => 'nullable|string',
            'sisa_stok' => 'nullable|integer|min:0',
            'satuan' => 'required|string',
            'batas_minimum' => 'nullable|integer|min:0'
        ]);

        $dataToUpdate = [
            'nama_bahan' => $request->nama_bahan,
            'satuan' => $request->satuan,
        ];

        if ($request->filled('jenis_bahan')) {
            $dataToUpdate['jenis_bahan'] = $request->jenis_bahan;
        }

        if ($request->filled('sisa_stok')) {
            $dataToUpdate['sisa_stok'] = (int) $request->sisa_stok;
        }

        if ($request->filled('batas_minimum')) {
            $dataToUpdate['batas_minimum'] = (int) $request->batas_minimum;
        }

        $bahan->update($dataToUpdate);

        return redirect()->route('bahan.index')->with('success', 'Bahan baku berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $bahan = Bahan::findOrFail($id);
        // Hapus semua data bahan_keluar dan bahan_masuk yang terkait
        $bahan->bahanKeluar()->delete();
        $bahan->bahanMasuk()->delete();
        $bahan->delete();

        return redirect()->route('bahan.index')->with('success', 'Bahan baku berhasil dihapus!');
    }
}