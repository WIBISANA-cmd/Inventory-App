<?php

namespace Database\Seeders;

use App\Models\Inventaris;
use Illuminate\Database\Seeder;

class InventarisSeeder extends Seeder
{
    /**
     * kondisi hanya boleh salah satu dari 3 nilai yang dipakai di
     * resources/views/inventaris/index.blade.php: Baik, Rusak Ringan, Rusak Berat.
     */
    private array $bar = [
        ['nama' => 'Mesin Espresso 2 Group', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Mesin Grinder Kopi', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Blender Bar', 'jumlah' => 3, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Kulkas Display Bar', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Rusak Ringan'],
        ['nama' => 'Ice Maker', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Milk Frother Pitcher', 'jumlah' => 5, 'satuan' => 'pcs', 'kondisi' => 'Baik'],
        ['nama' => 'Timbangan Digital Bar', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Baik'],
    ];

    private array $kitchen = [
        ['nama' => 'Kompor Gas 2 Tungku', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Deep Fryer', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Rusak Ringan'],
        ['nama' => 'Kulkas Chiller Kitchen', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Freezer Box', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Talenan Set', 'jumlah' => 8, 'satuan' => 'pcs', 'kondisi' => 'Baik'],
        ['nama' => 'Rice Cooker Besar', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Rusak Berat'],
        ['nama' => 'Exhaust Fan Dapur', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Baik'],
    ];

    private array $operasional = [
        ['nama' => 'Mesin Kasir POS', 'jumlah' => 1, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Printer Struk', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'AC Ruang Tunggu', 'jumlah' => 3, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'Meja Pelanggan', 'jumlah' => 12, 'satuan' => 'pcs', 'kondisi' => 'Baik'],
        ['nama' => 'Kursi Pelanggan', 'jumlah' => 40, 'satuan' => 'pcs', 'kondisi' => 'Rusak Ringan'],
        ['nama' => 'CCTV', 'jumlah' => 4, 'satuan' => 'unit', 'kondisi' => 'Baik'],
        ['nama' => 'WiFi Router', 'jumlah' => 2, 'satuan' => 'unit', 'kondisi' => 'Baik'],
    ];

    public function run(): void
    {
        $this->seedGroup('INVB', $this->bar);
        $this->seedGroup('INVK', $this->kitchen);
        $this->seedGroup('INVO', $this->operasional);
    }

    private function seedGroup(string $prefix, array $items): void
    {
        foreach ($items as $index => $item) {
            Inventaris::updateOrCreate(
                ['kode_inventaris' => $prefix . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'nama_inventaris' => $item['nama'],
                    'jumlah_inventaris' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                    'kondisi' => $item['kondisi'],
                ]
            );
        }
    }
}
