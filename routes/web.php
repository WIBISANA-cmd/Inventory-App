<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\BahanMasukController;
use App\Http\Controllers\BahanAkhirController;
use App\Http\Controllers\BahanKeluarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\LaporanBahanBakuController;
use App\Http\Controllers\MenuTerjualController;
use App\Http\Controllers\PemantauanController;
use App\Http\Controllers\BahanProcessController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\DataBahanakhirController;


Route::get('/kirim-notifikasi-stok', [StokController::class, 'kirimNotifikasiStok']);

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('web')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Redirect setelah login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    // Route::get('/', [DashboardController::class, 'index']); // Redirect ke dashboard setelah login
});// route kategori
Route::middleware(['role:Admin'])->group(function () {
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
});

// route daftarbahan
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(function () {
    Route::get('/bahan/daftarbahan', [BahanController::class, 'index'])->name('bahan.index');
    Route::get('/cek-stok-menipis', [BahanController::class, 'cekStokMenipis']);
    Route::post('/bahan', [BahanController::class, 'store'])->name('bahan.store');
    Route::put('/bahan/{id}', [BahanController::class, 'update'])->name('bahan.update');
    Route::delete('/bahan/{id}', [BahanController::class, 'destroy'])->name('bahan.destroy');
});
    //laporan index
    Route::middleware(['role:Admin|Headbar|Headkitchen'])->group(function () {
        Route::get('/laporan/index',[DashboardController::class,'indexlaporan'])->name('laporan.index');
});
// laporan bahan
Route::middleware('role:Admin|Headbar|Headkitchen')->group(function () {
    Route::get('/laporan/bahan', [BahanController::class, 'laporan'])->name('laporan.bahan');
    Route::get('/laporan/bahan/pdf', [BahanController::class, 'downloadPdf'])->name('laporan.bahan.pdf');
});
// route bahanmasuk
Route::middleware('role:Admin|Headbar|Headkitchen|Bar|Kitchen')->group(function () {
    Route::get('/bahan/bahanmasuk', [BahanMasukController::class, 'index'])->name('bahan.bahanmasuk');
    Route::post('/bahan/bahanmasuk', [BahanMasukController::class, 'store'])->name('bahan.bahanmasuk.store');
    Route::get('/bahan/bahanmasuk/{id}/edit', [BahanMasukController::class, 'edit'])->name('bahan.bahanmasuk.edit');
    Route::put('/bahan/bahanmasuk/{id}', [BahanMasukController::class, 'update'])->name('bahan.bahanmasuk.update');
    Route::delete('/bahan/bahanmasuk/{id}', [BahanMasukController::class, 'destroy'])->name('bahan.bahanmasuk.destroy');
});
    // laporan bahanmasuk
Route::middleware('role:Admin|Headbar|Headkitchen')->group(function () {
    Route::get('/laporan/bahanmasuk', [BahanMasukController::class, 'laporanmasuk'])->name('laporan.bahanmasuk');
    Route::get('/laporan/bahanmasuk/pdf', [BahanMasukController::class, 'downloadPdfmasuk'])->name('laporan.bahanmasuk.pdf');
});
// bahan akhir
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(function () {
    Route::get('/bahan/akhir/tanggal', [BahanAkhirController::class, 'showTanggalInput'])->name('bahan.bahanakhir');
    Route::post('/bahan/akhir/tanggal', [BahanAkhirController::class, 'prosesTanggal'])->name('bahan.akhir.tanggal');
    Route::get('/bahan/akhir', [BahanAkhirController::class, 'index'])->name('bahan.akhir.index');
    Route::post('/bahan/akhir/update', [BahanAkhirController::class, 'update'])->name('bahan.akhir.update');
    Route::get('/bahan/akhir/tampilkan', [BahanAkhirController::class, 'tampilkan'])->name('bahan.akhir.tampilkan');
});
// route edit bahan akhir
Route::put('/bahanakhir/{id}', [DataBahanakhirController::class, 'update'])->name('bahanakhir.update');
// route update single bahan akhir
Route::put('/bahanakhir/update/{id}', [DataBahanakhirController::class, 'updateSingle'])->name('bahanakhir.update');

// Routes untuk Bahan Keluar
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(function () {
    Route::get('/bahan/bahankeluar', [BahanKeluarController::class, 'index'])->name('bahan.bahankeluar');
});
// route laporan bahan keluar
Route::middleware('role:Admin|Headbar|Headkitchen')->group(function () {
    Route::get('/laporan/bahankeluar', [BahanKeluarController::class, 'laporankeluar'])->name('laporan.bahankeluar');
    Route::get('/laporan/bahankeluar/pdf', [BahanKeluarController::class, 'downloadPdfkeluar'])->name('laporan.bahankeluar.pdf');
});

// LAPORAN KESELURUHAN BAHAN BAKU
Route::middleware('role:Admin|Headbar|Headkitchen')->group(function () {
    Route::get('/laporan/keseluruhan-bahan-baku', [LaporanBahanBakuController::class, 'keseluruhanBahanBaku'])->name('laporan.keseluruhanbahanbaku');
    Route::get('/laporan/keseluruhanbb/pdf', [LaporanBahanBakuController::class, 'downloadPdf'])->name('laporan.keseluruhanbb.pdf');
});
// LAPORAN BAHAN AKHIR
Route::middleware('role:Admin|Headbar|Headkitchen')->group(function () {
    Route::get('/laporan/bahanakhir', [BahanAkhirController::class, 'laporan'])->name('laporan.bahanakhir');
    Route::get('/laporan/bahanakhir/pdf', [BahanAkhirController::class, 'downloadPdf'])->name('laporan.bahanakhir.pdf');
});

// route manajemen pengguna
Route::middleware(['role:Admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});
// route Menu
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(function () {
    Route::resource('menu', MenuController::class);
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}', [MenuController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');
});
// route inventaris
Route::middleware(['role:Admin|Headbar|Headkitchen'])->group(function () {
    Route::resource('inventaris', InventarisController::class);
    Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris.index');
    Route::post('/inventaris', [InventarisController::class, 'store'])->name('inventaris.store');
    Route::put('/inventaris/{id}', [InventarisController::class, 'update'])->name('inventaris.update');
    Route::delete('/inventaris/{id}', [InventarisController::class, 'destroy'])->name('inventaris.destroy');
    Route::get('/inventaris/cetak-barcode/{id}', [InventarisController::class, 'cetakBarcode'])->name('inventaris.cetakBarcode');
});
// routes inventaris
Route::get('/scan-inventaris/{id}', [InventarisController::class, 'showAfterLogin'])
    ->middleware('auth')
    ->name('inventaris.scan');

// profile
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
// Menu Terjual
Route::middleware(['auth', 'role:Admin|Headbar|Headkitchen'])->group(function () {  
    Route::prefix('menu-terjual')->group(function () {
        Route::get('/', [MenuTerjualController::class, 'index'])->name('menu.terjual.index');
        Route::post('/', [MenuTerjualController::class, 'store'])->name('menu.terjual.store');
        Route::get('/{menuTerjual}', [MenuTerjualController::class, 'show'])->name('menu.terjual.show');
        Route::delete('/{menuTerjual}', [MenuTerjualController::class, 'destroy'])->name('menu.terjual.destroy');
        Route::put('/{menuTerjual}', [MenuTerjualController::class, 'update'])->name('menu.terjual.update');
    });
});
// route pemantauan bahan
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(function () {
Route::get('/pemantauan-bahan', [PemantauanController::class, 'index'])->name('laporan.pemantauan');
Route::get('/laporan/pemantauan/pdf', [PemantauanController::class, 'downloadPDF'])->name('laporan.pemantauan.pdf');
Route::post('/laporan/pemantauan/pdf', [PemantauanController::class, 'downloadPDF'])->name('laporan.pemantauan.pdf');

});
// Route bahan process
Route::middleware(['auth'])->group(function () {
    Route::get('/bahan/process', [BahanProcessController::class, 'index'])->name('bahan.process');
    Route::post('/bahan/process', [BahanProcessController::class, 'store'])->name('bahan.process.store');
    Route::put('/bahan/process/{id}', [BahanProcessController::class, 'update'])->name('bahan.process.update');
    Route::delete('/bahan/process/{id}', [BahanProcessController::class, 'destroy'])->name('bahan.process.destroy');
});
Route::get('/check-session', function () {
    return response()->json(['status' => auth()->check()]);
})->name('check.session');

require __DIR__ . '/auth.php';
