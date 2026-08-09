<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Kode & pemetaan nama persis mengikuti hardcoded map di KategoriController::store(),
     * supaya kategori yang dibuat lewat UI nanti tetap konsisten dengan data seeder ini.
     */
    public function run(): void
    {
        $kategoris = [
            ['kode_kategori' => 'BBAR', 'nama_kategori' => 'Bahan Baku Bar', 'keterangan' => 'Bahan baku & racikan untuk operasional Bar (minuman).'],
            ['kode_kategori' => 'BBKTC', 'nama_kategori' => 'Bahan Baku Kitchen', 'keterangan' => 'Bahan baku & racikan untuk operasional Kitchen (makanan).'],
            ['kode_kategori' => 'INVB', 'nama_kategori' => 'Inventaris Bar', 'keterangan' => 'Peralatan & aset tetap area Bar.'],
            ['kode_kategori' => 'INVK', 'nama_kategori' => 'Inventaris Kitchen', 'keterangan' => 'Peralatan & aset tetap area Kitchen.'],
            ['kode_kategori' => 'INVO', 'nama_kategori' => 'Inventaris Operasional', 'keterangan' => 'Peralatan & aset tetap operasional umum (kasir, ruang tunggu, dll).'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::updateOrCreate(['kode_kategori' => $kategori['kode_kategori']], $kategori);
        }
    }
}
