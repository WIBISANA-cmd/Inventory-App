<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data demo Coffee Shop Inventory App yang lengkap & saling terhubung:
     * kategori -> bahan baku -> bahan proses (racikan) -> menu -> inventaris -> user per
     * role -> 14 hari riwayat operasional (penjualan, pemakaian bahan, stok opname, restock).
     *
     * Aman dijalankan berulang kali (`php artisan db:seed`) — data lama dari seeder ini
     * dibersihkan lebih dulu, urutan hapus disusun child->parent supaya tidak perlu
     * menonaktifkan foreign key check (portable ke MySQL/PostgreSQL/SQLite).
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->wipe();

            $this->call([
                KategoriSeeder::class,
                RoleSeeder::class,
                BahanSeeder::class,
                BahanProcessSeeder::class,
                MenuSeeder::class,
                InventarisSeeder::class,
                OperationalHistorySeeder::class,
            ]);
        });
    }

    private function wipe(): void
    {
        DB::table('bahan_keluar')->delete();
        DB::table('bahan_akhir')->delete();
        DB::table('bahan_masuk')->delete();
        DB::table('menu_terjual')->delete();
        DB::table('menu')->delete();          // cascade: menu_bahan, bahan_process_menu
        DB::table('bahan_processes')->delete(); // cascade: bahan_process_bahan, komposisis
        DB::table('bahans')->delete();
        DB::table('inventaris')->delete();
        DB::table('kategoris')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('users')->delete();
    }
}
