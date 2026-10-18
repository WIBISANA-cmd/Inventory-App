<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Bahan;
use App\Models\BahanProcess;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $kategoris = Kategori::where('kode_kategori', 'LIKE', 'BB%')->get();

        $query = Menu::with('bahans', 'bahanProcesses');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_menu', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_menu', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('status_menu')) {
            $query->where('status_menu', $request->status_menu);
        }

        // Filter berdasarkan bahan tertentu: berguna untuk melihat menu apa saja yang
        // terdampak bila satu bahan habis.
        if ($request->filled('bahan_id')) {
            $bahanId = $request->bahan_id;
            $query->whereHas('bahans', fn ($q) => $q->where('bahans.id', $bahanId));
        }

        $menu = $query->orderBy('nama_menu')->paginate(12)->withQueryString();

        $bahans = Bahan::orderBy('nama_bahan')->get();
        $bahanProcesses = BahanProcess::orderBy('nama_bahan')->get();

        return view('menu.index', compact('menu', 'kategoris', 'bahans', 'bahanProcesses'));
    }

    public function store(Request $request)
    { 
        // Validasi input
        $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'nama_menu' => 'required|string|max:255',
            'bahan_biasa' => 'nullable|array',
            'bahan_biasa.*' => 'exists:bahans,id',
            'gramasi_biasa' => 'nullable|array',
            'gramasi_biasa.*' => 'numeric|min:1',
            'bahan_process' => 'nullable|array',
            'bahan_process.*' => 'exists:bahan_processes,id',
            'gramasi_process' => 'nullable|array',
            'gramasi_process.*' => 'numeric|min:1',
        ]);

        // Mendapatkan data kategori dan membuat kode_menu unik
        $kategori = Kategori::findOrFail($request->kategori_id);
        do {
            $kode_menu = $kategori->kode_kategori . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Menu::where('kode_menu', $kode_menu)->exists());

        // Membuat menu baru
        $menu = Menu::create([
            'kode_menu' => $kode_menu,
            'nama_menu' => $request->nama_menu,
            'kategori_id' => $request->kategori_id,
            'status_menu' => $this->cekStatusMenu(array_merge($request->bahan_biasa ?? [], $request->bahan_process ?? [])),
        ]);

        // Menyimpan bahan biasa jika ada
        if ($request->bahan_biasa) {
            $bahanBiasaData = [];
            foreach ($request->bahan_biasa as $bahanId) {
                if (isset($request->gramasi_biasa[$bahanId])) {
                    $bahanBiasaData[$bahanId] = ['gramasi' => $request->gramasi_biasa[$bahanId]];
                }
            }            
            if (!empty($bahanBiasaData)) {
                $menu->bahans()->sync($bahanBiasaData);
            }
        }

        // Menyimpan bahan process jika ada
        if ($request->bahan_process) {
            $bahanProcessData = [];
            foreach ($request->bahan_process as $bahanId) {
                if (isset($request->gramasi_process[$bahanId])) {
                    $bahanProcessData[$bahanId] = ['gramasi' => $request->gramasi_process[$bahanId]];
                }
            }            
            if (!empty($bahanProcessData)) {
                $menu->bahanProcesses()->sync($bahanProcessData);
            }
        }

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('menu.index')->with('success', 'Menu berhasil ditambahkan!');
    }

    public function update(Request $request, $id = null)
    {
        $id = $id ?? $request->route('menu') ?? $request->route('id');
        $menu = Menu::findOrFail($id);

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'bahan_biasa' => 'nullable|array',
            'bahan_biasa.*' => 'exists:bahans,id',
            'gramasi_biasa' => 'nullable|array',
            'gramasi_biasa.*' => 'nullable|numeric',
            'bahan_process' => 'nullable|array',
            'bahan_process.*' => 'exists:bahan_processes,id',
            'gramasi_process' => 'nullable|array',
            'gramasi_process.*' => 'nullable|numeric',
        ]);

        $menu->update([
            'nama_menu' => $request->nama_menu,
        ]);

        // Update bahan biasa
        $bahanBiasaData = [];
        if ($request->has('bahan_biasa') && is_array($request->bahan_biasa)) {
            foreach ($request->bahan_biasa as $bahanId) {
                $gramasi = $request->gramasi_biasa[$bahanId] ?? null;
                $bahanBiasaData[$bahanId] = ['gramasi' => ($gramasi !== null && $gramasi !== '') ? (float) $gramasi : 0];
            }
        }
        $menu->bahans()->sync($bahanBiasaData);

        // Update bahan process
        $bahanProcessData = [];
        if ($request->has('bahan_process') && is_array($request->bahan_process)) {
            foreach ($request->bahan_process as $bahanId) {
                $gramasi = $request->gramasi_process[$bahanId] ?? null;
                $bahanProcessData[$bahanId] = ['gramasi' => ($gramasi !== null && $gramasi !== '') ? (float) $gramasi : 0];
            }
        }
        $menu->bahanProcesses()->sync($bahanProcessData);

        $menu->update([
            'status_menu' => $this->cekStatusMenu(array_keys($bahanBiasaData))
        ]);

        return redirect()->route('menu.index')->with('success', 'Menu berhasil diperbarui!');
    }

    public function destroy($id = null)
    {
        $id = $id ?? request()->route('menu') ?? request()->route('id');
        $menu = Menu::findOrFail($id);
        $menu->bahans()->detach();
        $menu->bahanProcesses()->detach();
        $menu->delete();

        return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus!');
    }

    private function cekStatusMenu($bahanIds)
    {
        $bahanTersedia = Bahan::whereIn('id', $bahanIds)->where('sisa_stok', '>', 0)->count();
        return $bahanTersedia == count($bahanIds) ? 'Tersedia' : 'Tidak Tersedia';
    }
}
