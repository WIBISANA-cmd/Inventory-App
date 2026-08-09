<?php

namespace Database\Seeders;

use App\Models\Bahan;
use App\Models\BahanAkhir;
use App\Models\BahanKeluar;
use App\Models\BahanMasuk;
use App\Models\BahanProcess;
use App\Models\Menu;
use App\Models\MenuTerjual;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OperationalHistorySeeder extends Seeder
{
    private const DAYS = 14;

    /**
     * Simulasi 14 hari operasional: penjualan menu harian -> pemakaian bahan (dengan sedikit
     * varians/waste seperti dunia nyata) -> stok opname & bahan keluar harian -> restock
     * berkala untuk bahan yang mulai menipis. Semua langkah meniru persis apa yang dilakukan
     * BahanAkhirController::update() dan BahanMasukController::store() supaya data yang
     * dihasilkan konsisten dengan logic aplikasi, bukan cuma angka acak.
     */
    public function run(): void
    {
        $menus = Menu::with(['bahans', 'bahanProcesses'])->get();
        if ($menus->isEmpty()) {
            return;
        }

        $start = Carbon::today()->subDays(self::DAYS - 1);

        for ($day = 0; $day < self::DAYS; $day++) {
            $tanggal = $start->copy()->addDays($day);
            $isWeekend = $tanggal->isWeekend();

            $usage = $this->recordDailySales($menus, $tanggal, $isWeekend);
            $this->applyUsageAndOpname($usage, $tanggal);

            // Restock tiap 4 hari untuk bahan yang sudah mendekati/menyentuh batas minimum.
            if ($day > 0 && $day % 4 === 0) {
                $this->restockLowStock($tanggal);
            }
        }
    }

    /**
     * Catat penjualan menu hari ini & kembalikan agregat pemakaian bahan/bahan-proses
     * "seharusnya" (resep x jumlah terjual), sudah dengan varians waste ringan.
     *
     * @return array{bahan: array<int,int>, proses: array<int,int>}
     */
    private function recordDailySales($menus, Carbon $tanggal, bool $isWeekend): array
    {
        $bahanUsage = [];
        $prosesUsage = [];

        foreach ($menus as $menu) {
            $base = random_int(5, 25) + ($isWeekend ? random_int(5, 15) : 0);
            $jumlahTerjual = max(0, $base - random_int(0, 4));

            if ($jumlahTerjual <= 0) {
                continue;
            }

            MenuTerjual::create([
                'menu_id' => $menu->id,
                'jumlah_terjual' => $jumlahTerjual,
                'tanggal' => $tanggal->toDateString(),
            ]);

            foreach ($menu->bahans as $bahan) {
                $pakai = $jumlahTerjual * ($bahan->pivot->gramasi ?? 0);
                $bahanUsage[$bahan->id] = ($bahanUsage[$bahan->id] ?? 0) + $pakai;
            }

            foreach ($menu->bahanProcesses as $proses) {
                $pakai = $jumlahTerjual * ($proses->pivot->gramasi ?? 0);
                $prosesUsage[$proses->id] = ($prosesUsage[$proses->id] ?? 0) + $pakai;
            }
        }

        // Varians realistis: kadang lebih boros (waste), kadang persis pas.
        $withVariance = fn (int $qty) => (int) round($qty * (random_int(96, 109) / 100));

        return [
            'bahan' => array_map($withVariance, $bahanUsage),
            'proses' => array_map($withVariance, $prosesUsage),
        ];
    }

    /**
     * Terapkan pemakaian ke sisa_stok, lalu catat BahanKeluar + snapshot BahanAkhir —
     * persis alur BahanAkhirController::update() untuk stok opname harian.
     */
    private function applyUsageAndOpname(array $usage, Carbon $tanggal): void
    {
        foreach ($usage['bahan'] as $bahanId => $jumlahKeluar) {
            $bahan = Bahan::find($bahanId);
            if (!$bahan || $jumlahKeluar <= 0) {
                continue;
            }
            $this->applyOpname($bahan, 'kode_bahan', $jumlahKeluar, $tanggal);
        }

        foreach ($usage['proses'] as $prosesId => $jumlahKeluar) {
            $proses = BahanProcess::find($prosesId);
            if (!$proses || $jumlahKeluar <= 0) {
                continue;
            }
            $this->applyOpname($proses, 'kode_bahan', $jumlahKeluar, $tanggal);
        }
    }

    private function applyOpname($item, string $kodeField, int $jumlahKeluar, Carbon $tanggal): void
    {
        $jumlahKeluar = min($jumlahKeluar, $item->sisa_stok); // tidak mungkin keluar lebih dari stok yang ada
        if ($jumlahKeluar <= 0) {
            return;
        }

        $stokAkhir = $item->sisa_stok - $jumlahKeluar;
        $item->update(['sisa_stok' => $stokAkhir]);

        BahanKeluar::create([
            'tanggal_keluar' => $tanggal->toDateString(),
            'kode_bahan' => $item->{$kodeField},
            'nama_bahan' => $item->nama_bahan,
            'jumlah_keluar' => $jumlahKeluar,
            'satuan' => $item->satuan,
            'bahan_masuk_id' => null,
        ]);

        BahanAkhir::updateOrCreate(
            ['tanggal_input' => $tanggal->toDateString(), 'kode_bahan' => $item->{$kodeField}],
            ['kategori_bahan' => $item->kategori_bahan, 'nama_bahan' => $item->nama_bahan, 'stok_terakhir' => $stokAkhir]
        );
    }

    /**
     * Restock bahan non-proses & buat batch baru bahan proses yang stoknya sudah
     * mendekati/di bawah batas minimum — meniru persis efek samping
     * BahanMasukController::store() (termasuk konsumsi komposisi utk bahan proses).
     */
    private function restockLowStock(Carbon $tanggal): void
    {
        Bahan::whereColumn('sisa_stok', '<=', 'batas_minimum')
            ->orWhereRaw('sisa_stok <= batas_minimum * 1.5')
            ->get()
            ->each(function (Bahan $bahan) use ($tanggal) {
                $jumlahMasuk = max($bahan->batas_minimum * 3, 20);

                BahanMasuk::create([
                    'tanggal_masuk' => $tanggal->toDateString(),
                    'kode_bahan' => $bahan->kode_bahan,
                    'nama_bahan' => $bahan->nama_bahan,
                    'jumlah_masuk' => $jumlahMasuk,
                    'tipe' => 'non-proses',
                ]);

                $bahan->increment('sisa_stok', $jumlahMasuk);
            });

        BahanProcess::whereColumn('sisa_stok', '<=', 'batas_minimum')
            ->orWhereRaw('sisa_stok <= batas_minimum * 1.5')
            ->with(['komposisis.bahan', 'komposisiBahanProses.bahan'])
            ->get()
            ->each(function (BahanProcess $proses) use ($tanggal) {
                $jumlahBatchBaru = 3;

                $bahanMasuk = BahanMasuk::create([
                    'tanggal_masuk' => $tanggal->toDateString(),
                    'kode_bahan' => $proses->kode_bahan,
                    'nama_bahan' => $proses->nama_bahan,
                    'jumlah_masuk' => $jumlahBatchBaru,
                    'tipe' => 'proses',
                ]);

                $totalGramasi = $proses->komposisis->sum('gramasi');
                $proses->update([
                    'jumlah_batch' => $proses->jumlah_batch + $jumlahBatchBaru,
                    'sisa_stok' => $proses->sisa_stok + ($jumlahBatchBaru * $totalGramasi),
                ]);

                foreach ($proses->komposisis as $komposisi) {
                    $bahanBaku = $komposisi->bahan;
                    $jumlahKeluar = $jumlahBatchBaru * $komposisi->gramasi;
                    if (!$bahanBaku || $jumlahKeluar <= 0) {
                        continue;
                    }

                    $bahanBaku->decrement('sisa_stok', $jumlahKeluar);
                    BahanKeluar::create([
                        'tanggal_keluar' => $tanggal->toDateString(),
                        'kode_bahan' => $bahanBaku->kode_bahan,
                        'nama_bahan' => $bahanBaku->nama_bahan,
                        'jumlah_keluar' => $jumlahKeluar,
                        'satuan' => $bahanBaku->satuan,
                        'bahan_masuk_id' => $bahanMasuk->id,
                    ]);
                }

                foreach ($proses->komposisiBahanProses as $komposisiProses) {
                    $prosesLain = $komposisiProses->bahan;
                    $jumlahKeluar = $jumlahBatchBaru * $komposisiProses->gramasi;
                    if (!$prosesLain || $jumlahKeluar <= 0) {
                        continue;
                    }

                    $prosesLain->decrement('sisa_stok', $jumlahKeluar);
                    BahanKeluar::create([
                        'tanggal_keluar' => $tanggal->toDateString(),
                        'kode_bahan' => $prosesLain->kode_bahan,
                        'nama_bahan' => $prosesLain->nama_bahan,
                        'jumlah_keluar' => $jumlahKeluar,
                        'satuan' => $prosesLain->satuan,
                        'bahan_masuk_id' => $bahanMasuk->id,
                    ]);
                }
            });
    }
}
