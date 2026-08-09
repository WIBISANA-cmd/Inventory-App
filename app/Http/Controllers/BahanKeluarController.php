<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bahan;
use App\Models\BahanAkhir;
use App\Models\BahanKeluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;



class BahanKeluarController extends Controller
{
    // Menampilkan data bahan keluar
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil data bahan sesuai role
        $query = Bahan::query();

        if ($user->hasRole(['Admin', 'Headbar', 'Bar'])) {
            $query->where('kode_bahan', 'LIKE', 'BBAR%');
        }

        if ($user->hasRole(['Admin', 'Headkitchen', 'Kitchen'])) {
            $query->orWhere('kode_bahan', 'LIKE', 'BBKTC%');
        }

        $bahan = $query->get();

        // Riwayat bahan keluar mengikuti cakupan role yang sama seperti daftar bahan.
        $queryBahanKeluar = BahanKeluar::query()->where(function ($q) use ($user) {
            if ($user->hasRole(['Admin', 'Headbar', 'Bar'])) {
                $q->where('kode_bahan', 'LIKE', 'BBAR%');
            }
            if ($user->hasRole(['Admin', 'Headkitchen', 'Kitchen'])) {
                $q->orWhere('kode_bahan', 'LIKE', 'BBKTC%');
            }
        });

        if ($request->filled('search')) {
            $queryBahanKeluar->where('nama_bahan', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kategori_bahan')) {
            $queryBahanKeluar->where('kode_bahan', 'LIKE', $request->kategori_bahan . '%');
        }

        if ($request->filled('dari_tanggal')) {
            $queryBahanKeluar->whereDate('tanggal_keluar', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $queryBahanKeluar->whereDate('tanggal_keluar', '<=', $request->sampai_tanggal);
        }

        // 'produksi' = keluar otomatis dari pembuatan batch bahan proses (punya bahan_masuk_id),
        // 'opname'   = keluar hasil input stok akhir harian (bahan_masuk_id null).
        match ($request->query('sumber')) {
            'produksi' => $queryBahanKeluar->whereNotNull('bahan_masuk_id'),
            'opname' => $queryBahanKeluar->whereNull('bahan_masuk_id'),
            default => null,
        };

        $dataKeluar = $queryBahanKeluar
            ->orderByDesc('tanggal_keluar')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('bahan.bahankeluar', compact('dataKeluar', 'bahan'));
    }
    public function laporankeluar(Request $request)
    {
        $dataKeluar = collect(); // Default kosong jika belum ada input tanggal
        $message = null;

        if ($request->has(['dari_tanggal', 'sampai_tanggal'])) {
            $dari_tanggal = $request->dari_tanggal . ' 00:00:00';
            $sampai_tanggal = $request->sampai_tanggal . ' 23:59:59';

            $dataKeluar = BahanKeluar::whereBetween('created_at', [$dari_tanggal, $sampai_tanggal])->get();

            if ($dataKeluar->isEmpty()) {
                $message = "Maaf, tidak ada data di tanggal ini";
            }
        }

        return view('laporan.bahankeluar', compact('dataKeluar', 'message'));
    }

    public function downloadPdfkeluar()
    {
        $dataKeluar = BahanKeluar::where('kode_bahan', 'LIKE', 'BB%')->get();
        $pdf = Pdf::loadView('laporan.bahankeluar_pdf', compact('dataKeluar'));
        return $pdf->download('laporan_bahan_keluar.pdf');
    }
}