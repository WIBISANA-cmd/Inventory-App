<?php

namespace Database\Seeders;

use App\Models\Bahan;
use App\Models\BahanProcess;
use App\Models\Komposisis;
use Illuminate\Database\Seeder;

class BahanProcessSeeder extends Seeder
{
    /**
     * Racikan/batch (bahan proses). kode_bahan tetap diberi prefix BBAR/BBKTC karena
     * filter role di BahanProcessController, DashboardController, dan BahanAkhirController
     * semuanya bergantung pada prefix ini (LIKE 'BBAR%' / 'BBKTC%'), bukan cuma kolom
     * kategori_bahan.
     *
     * "komposisi" = nama Bahan non-proses + gramasi per 1 batch.
     * "komposisi_proses" (opsional) = nama BahanProcess lain yang dipakai sebagai bahan
     * (proses berlapis) + gramasi per 1 batch — mendemokan relasi Komposisis.
     */
    private array $bar = [
        [
            'nama' => 'Simple Syrup Racikan', 'satuan' => 'ml', 'batch' => 4, 'min' => 500,
            'komposisi' => ['Gula Pasir Bar' => 600],
        ],
        [
            'nama' => 'Susu Steamed Vanilla', 'satuan' => 'ml', 'batch' => 6, 'min' => 800,
            'komposisi' => ['Susu Full Cream UHT' => 2, 'Sirup Vanilla' => 1],
        ],
        [
            'nama' => 'Cold Brew Concentrate', 'satuan' => 'ml', 'batch' => 3, 'min' => 500,
            'komposisi' => ['Kopi Robusta Lampung' => 800],
        ],
        [
            'nama' => 'Whipped Cream Siap Pakai', 'satuan' => 'gram', 'batch' => 5, 'min' => 400,
            'komposisi' => ['Whipped Cream Bubuk' => 400, 'Susu Full Cream UHT' => 1],
        ],
        [
            'nama' => 'Saus Karamel Racikan', 'satuan' => 'ml', 'batch' => 3, 'min' => 300,
            'komposisi' => ['Gula Pasir Bar' => 400, 'Sirup Caramel' => 1],
        ],
        [
            // Proses berlapis: memakai "Simple Syrup Racikan" (bahan proses lain) sebagai komposisi.
            'nama' => 'Saus Coklat Racikan', 'satuan' => 'gram', 'batch' => 4, 'min' => 400,
            'komposisi' => ['Bubuk Coklat' => 500, 'Susu Kental Manis' => 1],
            'komposisi_proses' => ['Simple Syrup Racikan' => 150],
        ],
    ];

    private array $kitchen = [
        [
            'nama' => 'Bumbu Marinasi Ayam', 'satuan' => 'gram', 'batch' => 5, 'min' => 500,
            'komposisi' => ['Bawang Putih' => 1, 'Bawang Merah' => 1, 'Merica Bubuk' => 100, 'Garam' => 1],
        ],
        [
            'nama' => 'Saus Sambal Racikan', 'satuan' => 'gram', 'batch' => 4, 'min' => 600,
            'komposisi' => ['Cabai Merah Keriting' => 2, 'Bawang Putih' => 1, 'Garam' => 1, 'Gula Pasir' => 1],
        ],
        [
            'nama' => 'Kaldu Ayam Rebusan', 'satuan' => 'ml', 'batch' => 3, 'min' => 1000,
            'komposisi' => ['Daging Ayam Fillet' => 1, 'Bawang Putih' => 1],
        ],
        [
            'nama' => 'Adonan Tepung Crispy', 'satuan' => 'gram', 'batch' => 4, 'min' => 800,
            'komposisi' => ['Tepung Terigu' => 2, 'Tepung Panir' => 1, 'Garam' => 1],
        ],
    ];

    public function run(): void
    {
        $this->seedGroup('BBAR', 'Bahan Baku Bar', $this->bar);
        $this->seedGroup('BBKTC', 'Bahan Baku Kitchen', $this->kitchen);

        // Pass kedua: sambungkan komposisi proses-dalam-proses (butuh semua BahanProcess sudah ada).
        foreach (array_merge($this->bar, $this->kitchen) as $process) {
            if (empty($process['komposisi_proses'])) {
                continue;
            }

            $induk = BahanProcess::where('nama_bahan', $process['nama'])->first();
            if (!$induk) {
                continue;
            }

            foreach ($process['komposisi_proses'] as $namaProsesLain => $gramasi) {
                $anak = BahanProcess::where('nama_bahan', $namaProsesLain)->first();
                if ($anak) {
                    Komposisis::updateOrCreate(
                        ['bahan_process_id' => $induk->id, 'bahan_id' => $anak->id],
                        ['gramasi' => $gramasi]
                    );
                }
            }
        }
    }

    private function seedGroup(string $prefix, string $kategoriBahan, array $items): void
    {
        foreach ($items as $index => $item) {
            $kodeBahan = $prefix . '9' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            $totalGramasi = array_sum($item['komposisi']);
            $sisaStok = $item['batch'] * $totalGramasi;

            $bahanProcess = BahanProcess::updateOrCreate(
                ['kode_bahan' => $kodeBahan],
                [
                    'nama_bahan' => $item['nama'],
                    'tipe' => 'proses',
                    'kategori_bahan' => $kategoriBahan,
                    'jumlah_batch' => $item['batch'],
                    'sisa_stok' => $sisaStok,
                    'batas_minimum' => $item['min'],
                    'satuan' => $item['satuan'],
                    'status' => null,
                ]
            );

            $pivotData = [];
            foreach ($item['komposisi'] as $namaBahan => $gramasi) {
                $bahan = Bahan::where('nama_bahan', $namaBahan)
                    ->where('kode_bahan', 'LIKE', "{$prefix}%")
                    ->first();

                if ($bahan) {
                    $pivotData[$bahan->id] = ['gramasi' => $gramasi];
                }
            }

            if ($pivotData) {
                $bahanProcess->bahans()->sync($pivotData);
            }
        }
    }
}
