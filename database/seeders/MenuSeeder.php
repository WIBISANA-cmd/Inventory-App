<?php

namespace Database\Seeders;

use App\Models\Bahan;
use App\Models\BahanProcess;
use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Resep menu: 'bahan' = nama Bahan non-proses + gramasi, 'proses' = nama BahanProcess + gramasi.
     * Mengikuti kondisi asli seperti yang di-generate MenuController::store() (kode_menu =
     * kode_kategori + urutan, status_menu dihitung dari ketersediaan stok bahan non-proses).
     */
    private array $bar = [
        ['nama' => 'Cappuccino', 'bahan' => ['Kopi Arabika Gayo' => 18], 'proses' => ['Susu Steamed Vanilla' => 150]],
        ['nama' => 'Caffe Latte', 'bahan' => ['Kopi Arabika Gayo' => 18], 'proses' => ['Susu Steamed Vanilla' => 200]],
        ['nama' => 'Espresso Single Shot', 'bahan' => ['Espresso Bean Blend' => 9], 'proses' => []],
        ['nama' => 'Americano', 'bahan' => ['Espresso Bean Blend' => 18], 'proses' => []],
        ['nama' => 'Caramel Macchiato', 'bahan' => ['Espresso Bean Blend' => 18], 'proses' => ['Susu Steamed Vanilla' => 180, 'Saus Karamel Racikan' => 20]],
        ['nama' => 'Mocha Latte', 'bahan' => ['Espresso Bean Blend' => 18], 'proses' => ['Susu Steamed Vanilla' => 180, 'Saus Coklat Racikan' => 25]],
        ['nama' => 'Matcha Latte', 'bahan' => ['Bubuk Matcha' => 15], 'proses' => ['Susu Steamed Vanilla' => 180]],
        ['nama' => 'Taro Latte', 'bahan' => ['Bubuk Taro' => 20], 'proses' => ['Susu Steamed Vanilla' => 180]],
        ['nama' => 'Cold Brew Signature', 'bahan' => ['Sirup Vanilla' => 1], 'proses' => ['Cold Brew Concentrate' => 200]],
        ['nama' => 'Es Teh Manis', 'bahan' => ['Teh Hijau Celup' => 1, 'Gula Pasir Bar' => 20], 'proses' => []],
        ['nama' => 'Thai Tea', 'bahan' => ['Teh Melati Celup' => 2, 'Susu Kental Manis' => 1, 'Gula Pasir Bar' => 20], 'proses' => []],
        ['nama' => 'Choco Boba Fresh Milk', 'bahan' => ['Bubuk Coklat' => 20, 'Susu Full Cream UHT' => 1, 'Boba Pearl' => 40], 'proses' => []],
        ['nama' => 'Lemon Tea Squash', 'bahan' => ['Buah Lemon' => 1, 'Sprite Bottle 390ml' => 1, 'Sirup Pandan' => 1], 'proses' => []],
    ];

    private array $kitchen = [
        ['nama' => 'Nasi Goreng Ayam', 'bahan' => ['Beras Premium' => 300, 'Daging Ayam Fillet' => 100, 'Telur Ayam' => 1], 'proses' => ['Saus Sambal Racikan' => 30]],
        ['nama' => 'Ayam Crispy Original', 'bahan' => ['Daging Ayam Fillet' => 200, 'Minyak Goreng' => 100], 'proses' => ['Adonan Tepung Crispy' => 100, 'Bumbu Marinasi Ayam' => 50]],
        ['nama' => 'Kentang Goreng', 'bahan' => ['Kentang Beku' => 250, 'Minyak Goreng' => 80], 'proses' => []],
        ['nama' => 'Sosis Bakar', 'bahan' => ['Sosis Sapi Beku' => 2], 'proses' => []],
        ['nama' => 'Nugget Crispy', 'bahan' => ['Nugget Ayam Beku' => 200, 'Minyak Goreng' => 80], 'proses' => []],
        ['nama' => 'Sop Ayam', 'bahan' => ['Daging Ayam Fillet' => 200, 'Kentang Beku' => 100], 'proses' => ['Kaldu Ayam Rebusan' => 300]],
        ['nama' => 'Beef Steak Saus Lada', 'bahan' => ['Daging Sapi Has Dalam' => 250, 'Bawang Putih' => 20], 'proses' => []],
    ];

    public function run(): void
    {
        $kategoriBar = Kategori::where('kode_kategori', 'BBAR')->first();
        $kategoriKitchen = Kategori::where('kode_kategori', 'BBKTC')->first();

        $this->seedGroup($kategoriBar, $this->bar);
        $this->seedGroup($kategoriKitchen, $this->kitchen);
    }

    private function seedGroup(?Kategori $kategori, array $items): void
    {
        if (!$kategori) {
            return;
        }

        foreach ($items as $index => $item) {
            $kodeMenu = $kategori->kode_kategori . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            $bahanIds = [];
            $bahanPivot = [];
            foreach ($item['bahan'] as $namaBahan => $gramasi) {
                $bahan = Bahan::where('nama_bahan', $namaBahan)->first();
                if ($bahan) {
                    $bahanIds[] = $bahan->id;
                    $bahanPivot[$bahan->id] = ['gramasi' => $gramasi];
                }
            }

            $prosesPivot = [];
            foreach ($item['proses'] as $namaProses => $gramasi) {
                $proses = BahanProcess::where('nama_bahan', $namaProses)->first();
                if ($proses) {
                    $prosesPivot[$proses->id] = ['gramasi' => $gramasi];
                }
            }

            // Sama seperti MenuController::cekStatusMenu(): tersedia hanya jika seluruh
            // bahan non-proses penyusunnya stoknya masih ada.
            $tersedia = Bahan::whereIn('id', $bahanIds)->where('sisa_stok', '>', 0)->count() === count($bahanIds);

            $menu = Menu::updateOrCreate(
                ['kode_menu' => $kodeMenu],
                [
                    'nama_menu' => $item['nama'],
                    'kategori_id' => $kategori->id,
                    'status_menu' => $tersedia ? 'Tersedia' : 'Tidak Tersedia',
                ]
            );

            if ($bahanPivot) {
                $menu->bahans()->sync($bahanPivot);
            }
            if ($prosesPivot) {
                $menu->bahanProcesses()->sync($prosesPivot);
            }
        }
    }
}
