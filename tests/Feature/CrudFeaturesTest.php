<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Bahan;
use App\Models\Menu;
use App\Models\Inventaris;
use App\Models\BahanProcess;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CrudFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Headbar']);
        Role::create(['name' => 'Headkitchen']);
        Role::create(['name' => 'Bar']);
        Role::create(['name' => 'Kitchen']);

        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole($roleAdmin);
    }

    public function test_kategori_crud()
    {
        // CREATE
        $response = $this->actingAs($this->admin)->post('/kategori', [
            'kode_kategori' => 'BBAR',
            'keterangan' => 'Bar category'
        ]);
        $response->assertRedirect(route('kategori.index'));
        $kategori = Kategori::where('kode_kategori', 'BBAR')->first();
        $this->assertNotNull($kategori);
        $this->assertEquals('Bahan Baku Bar', $kategori->nama_kategori);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/kategori/{$kategori->id}", [
            'kode_kategori' => 'BBAR',
            'nama_kategori' => 'Bahan Baku Bar Updated',
            'keterangan' => 'Updated desc'
        ]);
        $updateResponse->assertRedirect(route('kategori.index'));
        $this->assertEquals('Bahan Baku Bar Updated', $kategori->fresh()->nama_kategori);

        // DELETE
        $deleteResponse = $this->actingAs($this->admin)->delete("/kategori/{$kategori->id}");
        $deleteResponse->assertRedirect(route('kategori.index'));
        $this->assertNull(Kategori::find($kategori->id));
    }

    public function test_bahan_crud()
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'BBAR',
            'nama_kategori' => 'Bahan Baku Bar',
        ]);

        // CREATE
        $response = $this->actingAs($this->admin)->post('/bahan', [
            'kode_bahan' => 'BBAR0001',
            'nama_bahan' => 'Susu Segar',
            'tipe' => 'non-process',
            'jenis_bahan' => 'baku',
            'kategori_bahan' => 'Bahan Baku Bar',
            'sisa_stok' => 10,
            'satuan' => 'liter',
        ]);
        $response->assertRedirect(route('bahan.index'));
        $bahan = Bahan::where('nama_bahan', 'Susu Segar')->first();
        $this->assertNotNull($bahan);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/bahan/{$bahan->id}", [
            'nama_bahan' => 'Susu Segar Full Cream',
            'jenis_bahan' => 'baku',
            'sisa_stok' => 12,
            'satuan' => 'liter',
            'batas_minimum' => 5,
        ]);
        $updateResponse->assertRedirect(route('bahan.index'));
        $this->assertEquals('Susu Segar Full Cream', $bahan->fresh()->nama_bahan);

        // DELETE
        $deleteResponse = $this->actingAs($this->admin)->delete("/bahan/{$bahan->id}");
        $deleteResponse->assertRedirect(route('bahan.index'));
        $this->assertNull(Bahan::find($bahan->id));
    }

    public function test_menu_crud()
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'BBAR',
            'nama_kategori' => 'Bahan Baku Bar',
        ]);

        $bahan = Bahan::create([
            'kode_bahan' => 'BBAR001',
            'nama_bahan' => 'Espresso Beans',
            'tipe' => 'non-process',
            'jenis_bahan' => 'baku',
            'kategori_bahan' => 'bar',
            'sisa_stok' => 10,
            'satuan' => 'kg',
        ]);

        // CREATE
        $response = $this->actingAs($this->admin)->post('/menu', [
            'nama_menu' => 'Kopi Latte',
            'kategori_id' => $kategori->id,
            'bahan_biasa' => [$bahan->id],
            'gramasi_biasa' => [$bahan->id => 18],
        ]);
        $response->assertRedirect(route('menu.index'));
        $menu = Menu::where('nama_menu', 'Kopi Latte')->first();
        $this->assertNotNull($menu);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/menu/{$menu->id}", [
            'nama_menu' => 'Kopi Latte Vanilla',
            'bahan_biasa' => [$bahan->id],
            'gramasi_biasa' => [$bahan->id => 20],
        ]);
        $updateResponse->assertRedirect(route('menu.index'));
        $this->assertEquals('Kopi Latte Vanilla', $menu->fresh()->nama_menu);

        // DELETE
        $deleteResponse = $this->actingAs($this->admin)->delete("/menu/{$menu->id}");
        $deleteResponse->assertRedirect(route('menu.index'));
        $this->assertNull(Menu::find($menu->id));
    }

    public function test_inventaris_crud()
    {
        // CREATE
        $response = $this->actingAs($this->admin)->post('/inventaris', [
            'kode_inventaris' => 'INVB001',
            'nama_inventaris' => 'Blender Bar',
            'jumlah_inventaris' => 2,
            'satuan' => 'unit',
            'kondisi' => 'Baik',
        ]);
        $response->assertRedirect(route('inventaris.index'));
        $inv = Inventaris::where('kode_inventaris', 'INVB001')->first();
        $this->assertNotNull($inv);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/inventaris/{$inv->id}", [
            'nama_inventaris' => 'Blender Bar Pro',
            'jumlah_inventaris' => 3,
            'satuan' => 'unit',
            'kondisi' => 'Baik',
        ]);
        $updateResponse->assertRedirect(route('inventaris.index'));
        $this->assertEquals('Blender Bar Pro', $inv->fresh()->nama_inventaris);

        // DELETE
        $deleteResponse = $this->actingAs($this->admin)->delete("/inventaris/{$inv->id}");
        $deleteResponse->assertRedirect(route('inventaris.index'));
        $this->assertNull(Inventaris::find($inv->id));
    }

    public function test_users_crud()
    {
        // CREATE
        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Barista Baru',
            'email' => 'barista@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Bar',
        ]);
        $response->assertRedirect(route('users.index'));
        $user = User::where('email', 'barista@test.com')->first();
        $this->assertNotNull($user);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'Barista Senior',
            'email' => 'barista@test.com',
            'role' => 'Bar',
        ]);
        $updateResponse->assertRedirect(route('users.index'));
        $this->assertEquals('Barista Senior', $user->fresh()->name);

        // DELETE
        $deleteResponse = $this->actingAs($this->admin)->delete("/users/{$user->id}");
        $deleteResponse->assertRedirect(route('users.index'));
        $this->assertNull(User::find($user->id));
    }

    public function test_bahan_masuk_crud()
    {
        $bahan = Bahan::create([
            'kode_bahan' => 'BBAR0002',
            'nama_bahan' => 'Biji Kopi Arabika',
            'tipe' => 'non-process',
            'jenis_bahan' => 'kopi',
            'kategori_bahan' => 'Bahan Baku Bar',
            'sisa_stok' => 5,
            'satuan' => 'kg',
        ]);

        // STORE
        $response = $this->actingAs($this->admin)->post('/bahan/bahanmasuk', [
            'tanggal_masuk' => now()->toDateString(),
            'bahan_id' => [$bahan->id],
            'tipe_bahan' => ['non-proses'],
            'jumlah_masuk' => [10],
        ]);
        $response->assertRedirect(route('bahan.bahanmasuk'));
        $this->assertEquals(15, $bahan->fresh()->sisa_stok);

        $bahanMasuk = \App\Models\BahanMasuk::where('kode_bahan', 'BBAR0002')->first();
        $this->assertNotNull($bahanMasuk);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/bahan/bahanmasuk/{$bahanMasuk->id}", [
            'jumlah_masuk' => 12,
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals(17, $bahan->fresh()->sisa_stok);

        // DESTROY
        $deleteResponse = $this->actingAs($this->admin)->delete("/bahan/bahanmasuk/{$bahanMasuk->id}");
        $deleteResponse->assertRedirect(route('bahan.bahanmasuk'));
        $this->assertNull(\App\Models\BahanMasuk::find($bahanMasuk->id));
    }

    public function test_bahan_process_crud()
    {
        $bahan = Bahan::create([
            'kode_bahan' => 'BBAR0003',
            'nama_bahan' => 'Gula Pasir',
            'tipe' => 'non-process',
            'jenis_bahan' => 'syrup',
            'kategori_bahan' => 'Bahan Baku Bar',
            'sisa_stok' => 5000,
            'satuan' => 'gram',
        ]);

        // STORE
        $response = $this->actingAs($this->admin)->post('/bahan/process', [
            'kode_bahan' => 'P_SIMPLESYRUP',
            'kategori_bahan' => 'Bahan Baku Bar',
            'nama_bahan' => 'Simple Syrup',
            'satuan' => 'ml',
            'batas_minimum' => 500,
            'bahan_biasa' => [$bahan->id],
            'gramasi_biasa' => [$bahan->id => 1000],
        ]);
        $response->assertRedirect(route('bahan.process'));
        $process = BahanProcess::where('kode_bahan', 'P_SIMPLESYRUP')->first();
        $this->assertNotNull($process);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/bahan/process/{$process->id}", [
            'nama_bahan' => 'Simple Syrup Premium',
            'satuan' => 'ml',
            'batas_minimum' => 600,
            'bahan_proses' => [$bahan->id],
            'gramasi' => [$bahan->id => 1200],
        ]);
        $updateResponse->assertRedirect(route('bahan.process'));
        $this->assertEquals('Simple Syrup Premium', $process->fresh()->nama_bahan);

        // DESTROY
        $deleteResponse = $this->actingAs($this->admin)->delete("/bahan/process/{$process->id}");
        $deleteResponse->assertRedirect(route('bahan.process'));
        $this->assertNull(BahanProcess::find($process->id));
    }

    public function test_menu_terjual_crud()
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'BBAR',
            'nama_kategori' => 'Bahan Baku Bar',
        ]);

        $menu = Menu::create([
            'kode_menu' => 'BBAR005',
            'nama_menu' => 'Cappuccino',
            'kategori_id' => $kategori->id,
            'status_menu' => 'Tersedia',
        ]);

        // STORE
        $response = $this->actingAs($this->admin)->post('/menu-terjual', [
            'menu_id' => [$menu->id],
            'jumlah_terjual' => [5],
            'tanggal' => now()->toDateString(),
        ]);
        $response->assertRedirect(route('menu.terjual.index'));
        $terjual = \App\Models\MenuTerjual::where('menu_id', $menu->id)->first();
        $this->assertNotNull($terjual);
        $this->assertEquals(5, $terjual->jumlah_terjual);

        // UPDATE
        $updateResponse = $this->actingAs($this->admin)->put("/menu-terjual/{$terjual->id}", [
            'jumlah_terjual' => 8,
        ]);
        $updateResponse->assertRedirect(route('menu.terjual.index'));
        $this->assertEquals(8, $terjual->fresh()->jumlah_terjual);

        // DESTROY
        $deleteResponse = $this->actingAs($this->admin)->delete("/menu-terjual/{$terjual->id}");
        $deleteResponse->assertRedirect(route('menu.terjual.index'));
        $this->assertNull(\App\Models\MenuTerjual::find($terjual->id));
    }
}
