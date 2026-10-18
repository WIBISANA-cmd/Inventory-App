<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Inventaris;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'all');
        $kategoris = Kategori::all();

        $query = Inventaris::query();

        if (in_array($sort, ['INVB', 'INVK', 'INVO'], true)) {
            $query->where('kode_inventaris', 'like', $sort . '%');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_inventaris', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_inventaris', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->filled('satuan')) {
            $query->where('satuan', $request->satuan);
        }

        $inventaris = $query
            ->orderBy('kode_inventaris')
            ->paginate(15)
            ->withQueryString();

        $satuanOptions = Inventaris::query()->select('satuan')->distinct()->orderBy('satuan')->pluck('satuan');

        return view('inventaris.index', compact('inventaris', 'kategoris', 'satuanOptions'));
    }

public function showAfterLogin($id)
{
    $inventaris = Inventaris::findOrFail($id);
    return view('inventaris.scan_result', compact('inventaris'));
}

public function cetakBarcode($id)
{
    $inventaris = Inventaris::findOrFail($id);

    // Generate QR Code menggunakan format SVG agar tidak butuh Imagick
    $qrCodeImage = base64_encode(QrCode::format('svg')->size(150)->generate(url('inventaris/' . $inventaris->id)));

    // Load ke PDF
    $pdf = Pdf::loadView('inventaris.barcode_pdf', compact('inventaris', 'qrCodeImage'));

    return $pdf->download('qrcode-inventaris-' . $inventaris->kode_inventaris . '.pdf');
}


    
    public function store(Request $request)
    {
        $request->validate([
            'kode_inventaris' => 'required|unique:inventaris,kode_inventaris',
            'nama_inventaris' => 'required|string|max:255',
            'jumlah_inventaris' => 'nullable|integer|min:0',
            'satuan' => 'required|string',
            'kondisi' => 'required|string',
        ]);

        Inventaris::create([
            'kode_inventaris' => $request->kode_inventaris,
            'nama_inventaris' => $request->nama_inventaris,
            'jumlah_inventaris' => $request->input('jumlah_inventaris', 1) ?? 1,
            'satuan' => $request->satuan,
            'kondisi' => $request->kondisi,
        ]);
        
        return redirect()->route('inventaris.index')->with('success', 'Inventaris baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id = null)
    {
        $id = $id ?? $request->route('inventaris') ?? $request->route('id');
        $inventaris = Inventaris::findOrFail($id);

        $request->validate([
            'nama_inventaris' => 'required|string|max:255',
            'jumlah_inventaris' => 'nullable|integer|min:0',
            'satuan' => 'required|string',
            'kondisi' => 'required|string',
        ]);

        $inventaris->update([
            'nama_inventaris' => $request->nama_inventaris,
            'jumlah_inventaris' => $request->input('jumlah_inventaris', $inventaris->jumlah_inventaris ?? 1) ?? 1,
            'satuan' => $request->satuan,
            'kondisi' => $request->kondisi,
        ]);

        return redirect()->route('inventaris.index')->with('success', 'Data inventaris berhasil diperbarui!');
    }

    public function destroy($id = null)
    {
        $id = $id ?? request()->route('inventaris') ?? request()->route('id');
        $inventaris = Inventaris::findOrFail($id);
        $inventaris->delete();

        return redirect()->route('inventaris.index')->with('success', 'Data inventaris berhasil dihapus!');
    }
}
