<?php

namespace Database\Seeders;

use App\Models\Bahan;
use Illuminate\Database\Seeder;

class BahanSeeder extends Seeder
{
    /**
     * Data bahan baku non-proses untuk Bar (prefix kode BBAR, jenis_bahan & satuan
     * mengikuti persis pilihan dropdown di resources/views/bahan/daftarbahan.blade.php)
     * dan Kitchen (prefix BBKTC).
     *
     * sisa_stok & batas_minimum sengaja divariasikan (mayoritas AMAN, beberapa MENIPIS,
     * satu-dua HABIS) supaya status stok, dashboard, dan notifikasi stok menipis punya
     * data yang realistis untuk didemokan.
     */
    private array $bar = [
        ['nama' => 'Kopi Arabika Gayo', 'jenis' => 'kopi', 'satuan' => 'gram', 'stok' => 8000, 'min' => 2000],
        ['nama' => 'Kopi Robusta Lampung', 'jenis' => 'kopi', 'satuan' => 'gram', 'stok' => 6000, 'min' => 2000],
        ['nama' => 'Espresso Bean Blend', 'jenis' => 'kopi', 'satuan' => 'gram', 'stok' => 1500, 'min' => 2000], // MENIPIS
        ['nama' => 'Susu Full Cream UHT', 'jenis' => 'milk', 'satuan' => 'pack', 'stok' => 60, 'min' => 15],
        ['nama' => 'Susu Oat', 'jenis' => 'milk', 'satuan' => 'pack', 'stok' => 20, 'min' => 10],
        ['nama' => 'Susu Kental Manis', 'jenis' => 'milk', 'satuan' => 'pack', 'stok' => 25, 'min' => 8],
        ['nama' => 'Sirup Vanilla', 'jenis' => 'syrup', 'satuan' => 'pcs', 'stok' => 12, 'min' => 3],
        ['nama' => 'Sirup Caramel', 'jenis' => 'syrup', 'satuan' => 'pcs', 'stok' => 10, 'min' => 3],
        ['nama' => 'Sirup Hazelnut', 'jenis' => 'syrup', 'satuan' => 'pcs', 'stok' => 2, 'min' => 3], // MENIPIS
        ['nama' => 'Sirup Pandan', 'jenis' => 'syrup', 'satuan' => 'pcs', 'stok' => 9, 'min' => 3],
        ['nama' => 'Bubuk Coklat', 'jenis' => 'powder', 'satuan' => 'gram', 'stok' => 4000, 'min' => 1000],
        ['nama' => 'Bubuk Matcha', 'jenis' => 'powder', 'satuan' => 'gram', 'stok' => 1200, 'min' => 500],
        ['nama' => 'Bubuk Taro', 'jenis' => 'powder', 'satuan' => 'gram', 'stok' => 900, 'min' => 500],
        ['nama' => 'Gula Pasir Bar', 'jenis' => 'powder', 'satuan' => 'gram', 'stok' => 10000, 'min' => 2000],
        ['nama' => 'Teh Hijau Celup', 'jenis' => 'tehsprite', 'satuan' => 'pack', 'stok' => 15, 'min' => 5],
        ['nama' => 'Teh Melati Celup', 'jenis' => 'tehsprite', 'satuan' => 'pack', 'stok' => 14, 'min' => 5],
        ['nama' => 'Sprite Bottle 390ml', 'jenis' => 'tehsprite', 'satuan' => 'pcs', 'stok' => 30, 'min' => 12],
        ['nama' => 'Whipped Cream Bubuk', 'jenis' => 'topping', 'satuan' => 'gram', 'stok' => 2000, 'min' => 800],
        ['nama' => 'Choco Chips Topping', 'jenis' => 'topping', 'satuan' => 'gram', 'stok' => 1500, 'min' => 500],
        ['nama' => 'Boba Pearl', 'jenis' => 'topping', 'satuan' => 'gram', 'stok' => 0, 'min' => 1000], // HABIS
        ['nama' => 'Jelly Nata de Coco', 'jenis' => 'topping', 'satuan' => 'gram', 'stok' => 1800, 'min' => 500],
        ['nama' => 'Buah Lemon', 'jenis' => 'buah', 'satuan' => 'buah', 'stok' => 40, 'min' => 15],
        ['nama' => 'Buah Strawberry Segar', 'jenis' => 'buah', 'satuan' => 'gram', 'stok' => 1200, 'min' => 500],
        ['nama' => 'Cup Plastik 16oz', 'jenis' => 'equip', 'satuan' => 'pcs', 'stok' => 500, 'min' => 100],
        ['nama' => 'Sedotan', 'jenis' => 'equip', 'satuan' => 'pack', 'stok' => 40, 'min' => 10],
        ['nama' => 'Tissue', 'jenis' => 'equip', 'satuan' => 'pack', 'stok' => 25, 'min' => 10],
    ];

    private array $kitchen = [
        ['nama' => 'Daging Ayam Fillet', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 25, 'min' => 8],
        ['nama' => 'Daging Sapi Has Dalam', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 10, 'min' => 5],
        ['nama' => 'Telur Ayam', 'jenis' => 'pasar', 'satuan' => 'pack', 'stok' => 20, 'min' => 5],
        ['nama' => 'Beras Premium', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 50, 'min' => 15],
        ['nama' => 'Tepung Terigu', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 30, 'min' => 10],
        ['nama' => 'Tepung Panir', 'jenis' => 'luar', 'satuan' => 'kg', 'stok' => 3, 'min' => 5], // MENIPIS
        ['nama' => 'Minyak Goreng', 'jenis' => 'luar', 'satuan' => 'liter', 'stok' => 40, 'min' => 10],
        ['nama' => 'Bawang Putih', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 8, 'min' => 3],
        ['nama' => 'Bawang Merah', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 8, 'min' => 3],
        ['nama' => 'Cabai Merah Keriting', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 6, 'min' => 3],
        ['nama' => 'Tomat', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 7, 'min' => 3],
        ['nama' => 'Kentang Beku', 'jenis' => 'frozen', 'satuan' => 'kg', 'stok' => 20, 'min' => 8],
        ['nama' => 'Nugget Ayam Beku', 'jenis' => 'frozen', 'satuan' => 'kg', 'stok' => 15, 'min' => 5],
        ['nama' => 'Sosis Sapi Beku', 'jenis' => 'frozen', 'satuan' => 'pack', 'stok' => 12, 'min' => 5],
        ['nama' => 'Keju Cheddar Slice', 'jenis' => 'luar', 'satuan' => 'pack', 'stok' => 18, 'min' => 6],
        ['nama' => 'Saus Tomat Botol', 'jenis' => 'luar', 'satuan' => 'pcs', 'stok' => 10, 'min' => 4],
        ['nama' => 'Saus Sambal Botol', 'jenis' => 'luar', 'satuan' => 'pcs', 'stok' => 0, 'min' => 4], // HABIS
        ['nama' => 'Garam', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 12, 'min' => 3],
        ['nama' => 'Gula Pasir', 'jenis' => 'pasar', 'satuan' => 'kg', 'stok' => 25, 'min' => 8],
        ['nama' => 'Merica Bubuk', 'jenis' => 'luar', 'satuan' => 'gram', 'stok' => 800, 'min' => 300],
    ];

    public function run(): void
    {
        $this->seedGroup('BBAR', 'Bahan Baku Bar', $this->bar);
        $this->seedGroup('BBKTC', 'Bahan Baku Kitchen', $this->kitchen);
    }

    private function seedGroup(string $prefix, string $kategoriBahan, array $items): void
    {
        foreach ($items as $index => $item) {
            $kodeBahan = $prefix . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            Bahan::updateOrCreate(
                ['kode_bahan' => $kodeBahan],
                [
                    'nama_bahan' => $item['nama'],
                    'tipe' => Bahan::TIPE_NON_PROCESS,
                    'jenis_bahan' => $item['jenis'],
                    'kategori_bahan' => $kategoriBahan,
                    'sisa_stok' => $item['stok'],
                    'batas_minimum' => $item['min'],
                    'satuan' => $item['satuan'],
                    'status' => null,
                ]
            );
        }
    }
}
