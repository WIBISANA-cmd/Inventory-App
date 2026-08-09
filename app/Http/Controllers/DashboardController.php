<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\BahanKeluar;
use App\Models\BahanMasuk;
use App\Models\BahanProcess;
use App\Models\Inventaris;
use App\Models\MenuTerjual;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {

        $user = Auth::user();

        // Peran yang diizinkan untuk mengakses dashboard
        $roles = [ 'Admin', 'Bar', 'Kitchen'];

        if ($user->hasAnyRole($roles)) {
            return redirect()->route('dashboard');
        }

        return abort(403, 'Unauthorized access');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $prefix = $this->kodePrefixUntuk($user);

        // ---------- Kartu ringkasan & data status stok ----------
        $bahanQuery = fn () => $this->terapkanPrefix(Bahan::query(), $prefix);

        $totalBahan = $bahanQuery()->count();
        $bahanHabis = $bahanQuery()->where('sisa_stok', '<=', 0)->count();
        $bahanMenipis = $bahanQuery()->where('sisa_stok', '>', 0)
            ->whereColumn('sisa_stok', '<=', 'batas_minimum')->count();
        $bahanAman = max(0, $totalBahan - $bahanHabis - $bahanMenipis);

        $bahan_low_stock = $bahanQuery()
            ->whereColumn('sisa_stok', '<=', 'batas_minimum')
            ->orderBy('sisa_stok')
            ->get();

        // ---------- Tren 14 hari: bahan masuk vs bahan keluar ----------
        $mulai = Carbon::today()->subDays(13);

        $masukPerHari = $this->terapkanPrefix(BahanMasuk::query(), $prefix)
            ->whereDate('tanggal_masuk', '>=', $mulai)
            ->selectRaw('tanggal_masuk as tanggal, SUM(jumlah_masuk) as total')
            ->groupBy('tanggal_masuk')
            ->pluck('total', 'tanggal');

        $keluarPerHari = $this->terapkanPrefix(BahanKeluar::query(), $prefix)
            ->whereDate('tanggal_keluar', '>=', $mulai)
            ->selectRaw('tanggal_keluar as tanggal, SUM(jumlah_keluar) as total')
            ->groupBy('tanggal_keluar')
            ->pluck('total', 'tanggal');

        $trenLabels = [];
        $trenMasuk = [];
        $trenKeluar = [];
        for ($i = 0; $i < 14; $i++) {
            $hari = $mulai->copy()->addDays($i);
            $kunci = $hari->toDateString();
            $trenLabels[] = $hari->translatedFormat('d M');
            $trenMasuk[] = (float) ($masukPerHari[$kunci] ?? 0);
            $trenKeluar[] = (float) ($keluarPerHari[$kunci] ?? 0);
        }

        // ---------- Top 7 menu terlaris (30 hari terakhir) ----------
        $topMenu = MenuTerjual::query()
            ->whereDate('tanggal', '>=', Carbon::today()->subDays(29))
            ->selectRaw('menu_id, SUM(jumlah_terjual) as total')
            ->groupBy('menu_id')
            ->orderByDesc('total')
            ->limit(7)
            ->with('menu:id,nama_menu')
            ->get();

        $topMenuLabels = $topMenu->map(fn ($r) => $r->menu->nama_menu ?? 'Menu dihapus')->all();
        $topMenuData = $topMenu->map(fn ($r) => (float) $r->total)->all();

        // ---------- Komposisi bahan per jenis ----------
        $perJenis = $bahanQuery()
            ->selectRaw('jenis_bahan, COUNT(*) as total')
            ->groupBy('jenis_bahan')
            ->orderByDesc('total')
            ->get();

        $jenisLabels = $perJenis->pluck('jenis_bahan')->all();
        $jenisData = $perJenis->pluck('total')->map(fn ($v) => (int) $v)->all();

        // ---------- Angka pendukung lain ----------
        $totalMenu = Menu::count();
        $menuTerdampak = Menu::whereHas('bahans', fn ($q) => $q->where('sisa_stok', '<=', 0))->count();
        $totalInventaris = Inventaris::count();
        $inventarisRusak = Inventaris::where('kondisi', '!=', 'Baik')->count();
        $totalBahanProses = $this->terapkanPrefix(BahanProcess::query(), $prefix)->count();
        $terjualHariIni = (int) MenuTerjual::whereDate('tanggal', Carbon::today())->sum('jumlah_terjual');

        // Menu yang tidak bisa dijual karena ada bahan habis (untuk tabel peringatan)
        $menu = Menu::with('bahans')
            ->whereHas('bahans', fn ($q) => $q->where('sisa_stok', '<=', 0))
            ->orderBy('nama_menu')
            ->paginate(8, ['*'], 'menu_page');

        return view('dashboard', compact(
            'menu',
            'bahan_low_stock',
            'totalBahan',
            'bahanHabis',
            'bahanMenipis',
            'bahanAman',
            'trenLabels',
            'trenMasuk',
            'trenKeluar',
            'topMenuLabels',
            'topMenuData',
            'jenisLabels',
            'jenisData',
            'totalMenu',
            'menuTerdampak',
            'totalInventaris',
            'inventarisRusak',
            'totalBahanProses',
            'terjualHariIni'
        ));
    }

    /**
     * Prefix kode bahan yang boleh dilihat role ini (null = semua, seperti Admin).
     */
    private function kodePrefixUntuk($user): ?string
    {
        if ($user->hasRole('Admin')) {
            return null;
        }
        if ($user->hasAnyRole(['Headbar', 'Bar'])) {
            return 'BBAR';
        }
        if ($user->hasAnyRole(['Headkitchen', 'Kitchen'])) {
            return 'BBKTC';
        }

        return null;
    }

    private function terapkanPrefix($query, ?string $prefix)
    {
        return $prefix ? $query->where('kode_bahan', 'LIKE', $prefix . '%') : $query;
    }

    public function indexlaporan()
    {
        return view('laporan.index');
    }
}
