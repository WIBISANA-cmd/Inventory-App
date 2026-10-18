<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_kategori', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_kategori', 'like', '%' . $request->search . '%');
            });
        }

        // Kelompok: bahan baku (BB*) vs inventaris (INV*)
        match ($request->query('kelompok')) {
            'bahan' => $query->where('kode_kategori', 'LIKE', 'BB%'),
            'inventaris' => $query->where('kode_kategori', 'LIKE', 'INV%'),
            default => null,
        };

        $kategoris = $query->orderBy('kode_kategori')->paginate(15)->withQueryString();

        return view('kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:50|unique:kategoris,kode_kategori',
            'nama_kategori' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Mapping kode kategori ke nama kategori jika tidak diinput
        $namaKategoriMap = [
            'BBAR' => 'Bahan Baku Bar',
            'BBKTC' => 'Bahan Baku Kitchen',
            'INVB' => 'Inventaris Bar',
            'INVK' => 'Inventaris Kitchen',
            'INVO' => 'Inventaris Operasional',
        ];

        $kode = strtoupper(trim($request->kode_kategori));
        $nama = $request->filled('nama_kategori')
            ? trim($request->nama_kategori)
            : ($namaKategoriMap[$kode] ?? $kode);

        Kategori::create([
            'kode_kategori' => $kode,
            'nama_kategori' => $nama,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function edit(Kategori $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:50|unique:kategoris,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $kategori->update([
            'kode_kategori' => strtoupper(trim($request->kode_kategori)),
            'nama_kategori' => trim($request->nama_kategori),
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}