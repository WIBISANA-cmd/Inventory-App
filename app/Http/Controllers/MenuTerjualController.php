<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuTerjual;
use App\Models\Menu;

class MenuTerjualController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuTerjual::with(['menu.bahans.bahanKeluar']);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal', '<=', $request->sampai_tanggal);
        }

        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('menu', fn ($q) => $q->where('nama_menu', 'like', '%' . $search . '%'));
        }

        $menuTerjual = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Ringkasan mengikuti filter yang sedang aktif (bukan hanya halaman yang tampil).
        $totalTerjual = (clone $query)->sum('jumlah_terjual');

        $menus = Menu::orderBy('nama_menu')->get();

        return view('menu_terjual.index', compact('menuTerjual', 'menus', 'totalTerjual'));
    }



    public function store(Request $request)
{
    $request->validate([
        'tanggal' => 'required|date',
        'menu_id' => 'required|array',
        'menu_id.*' => 'exists:menu,id',
        'jumlah_terjual' => 'required|array',
        'jumlah_terjual.*' => 'integer',
    ]);

    foreach ($request->menu_id as $index => $menu_id) {
        if ($request->jumlah_terjual[$index] > 0) {
            MenuTerjual::create([
                'menu_id' => $menu_id,
                'jumlah_terjual' => $request->jumlah_terjual[$index],
                'tanggal' => $request->tanggal,
            ]);
        }
    }

    return redirect()->route('menu.terjual.index')->with('success','data berhasil disimpan');
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'jumlah_terjual' => 'required|integer',
        ]);

        $menuTerjual = MenuTerjual::findOrFail($id);
        $menuTerjual->update(['jumlah_terjual' => $request->jumlah_terjual]);

        return redirect()->route('menu.terjual.index')->with('success');
    }

    public function destroy($id)
    {
        $menuTerjual = MenuTerjual::findOrFail($id);
        $menuTerjual->delete();

        return redirect()->route('menu.terjual.index')->with('success');
    }
}