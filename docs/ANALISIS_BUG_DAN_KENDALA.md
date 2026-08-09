# Analisis Bug & Kendala — Coffee Shop Inventory App

Hasil audit statis kode (backend + frontend). Setiap temuan diverifikasi langsung ke baris kode sumber (bukan asumsi) sebelum dicatat di sini. Kolom **Status** menandai apakah sudah diperbaiki pada task ini.

## Ringkasan

| # | Area | Severity | Status |
|---|---|---|---|
| 1 | Route `/bahan/process` bentrok & memanggil method yang tidak ada | Critical | ✅ Fixed |
| 2 | Route `/bahan/process/{id}/edit` memanggil method yang tidak ada | High | ✅ Fixed |
| 3 | Foto profil rusak/tidak konsisten antar sumber upload | High | ✅ Fixed |
| 4 | Dead code: query dobel di `BahanKeluarController::index` | Low | ✅ Fixed |
| 5 | HTML badge status di-generate di Controller (bukan View) | Medium | ✅ Fixed |
| 6 | Atribut `readonly` pada elemen `<select>` tidak berlaku secara HTML | Low | ✅ Fixed |
| 7 | Bootstrap & jQuery dimuat berkali-kali (duplikat CDN) di setiap halaman | High | ✅ Fixed |
| 8 | Tailwind CSS & Alpine.js ter-install tapi 100% tidak terpakai | Medium | ✅ Fixed (dihapus) |
| 9 | Tidak ada dropdown searchable — native `<select>` tanpa pencarian | Medium | ✅ Fixed |
| 10 | Warna UI tersebar sebagai inline `style=""` / hex literal, bukan token terpusat | Medium | ✅ Fixed |
| 11 | Query list tanpa pagination (`::all()`/`::get()` pada tabel besar) | Low | ⚠️ Didokumentasikan, belum di-fix (lihat catatan) |
| 12 | `env('PUSHER_APP_KEY')` dipanggil langsung di Blade view, bukan lewat `config()` | Medium | ✅ Fixed |
| 13 | `bahan_masuk.satuan`/`tipe` di-set controller tapi tak pernah benar-benar tersimpan (mass-assignment guard) | Medium | ✅ Fixed |
| 14 | CSS sidebar mati di inline `<style>` layout — jadi aktif & mendorong seluruh konten keluar viewport | Critical | ✅ Fixed |
| 15 | Pemantauan: baris hari pertama selalu terbuang (DATE dibanding string datetime) | High | ✅ Fixed |
| 16 | Script `#sortBahan`/`#sortInventaris` menimpa `<tbody>` & error null | Medium | ✅ Fixed |
| 17 | Pencarian & sort client-side memberi hasil menyesatkan setelah pagination | Medium | ✅ Fixed |

---

## 15. [High] Laporan Pemantauan membuang data hari pertama

**Lokasi:** `PemantauanController` (perhitungan total keluar aktual).

Rentang tanggal dibangun sebagai `date('Y-m-d 00:00:00')` lalu dipakai pada `whereBetween('tanggal_keluar', ...)`. Kolom `tanggal_keluar` bertipe **DATE**, jadi perbandingannya menjadi perbandingan string:

```
'2026-07-27'  <  '2026-07-27 00:00:00'   → baris hari pertama TIDAK ikut
```

Terukur pada data seed 14 hari: **31 dari 300 baris** `bahan_keluar` hilang diam-diam (269 vs 300), yang membuat pemakaian aktual tampak lebih kecil dari kenyataan dan status jadi salah (setelah diperbaiki, Waste berubah 2 → 5 dan Plus 30 → 27).

**Fix:** bandingkan sebagai tanggal murni dengan `whereDate(... , '>=', $dari)` dan `whereDate(... , '<=', $sampai)`.

---

## 16. [Medium] Script sortir lama menimpa isi tabel halaman lain

**Lokasi:** `bahan/daftarbahan.blade.php`, `bahan/bahanmasuk.blade.php`, `inventaris/index.blade.php`.

Ketiganya punya script yang mem-`fetch` halaman lain lalu **menimpa `<tbody>` halaman saat ini** dengan `<tbody>` hasil fetch. Di `bahanmasuk.blade.php` URL yang di-fetch bahkan `route('bahan.index')` — halaman **Daftar Bahan**, bukan Bahan Masuk — sehingga tabel Bahan Masuk terisi baris milik menu lain. Setelah filter bar baru dipasang, elemen `#sortBahan`/`#sortInventaris` juga sudah tidak ada, sehingga `addEventListener`-nya melempar `Cannot read properties of null`.

**Fix:** seluruh script tersebut dihapus, digantikan submit form filter biasa (server-side).

---

## 17. [Medium] Pencarian/sortir client-side menyesatkan setelah pagination

**Lokasi:** `inventaris/index.blade.php` (search `keyup` menyembunyikan baris via `style.display`).

Filter semacam ini hanya bekerja pada baris yang sedang ter-render. Begitu data dipaginasi, hasil pencarian hanya mencakup halaman aktif — pengguna menyimpulkan "data tidak ada" padahal ada di halaman lain. Hal yang sama berlaku untuk pengurutan status stok yang sebelumnya dilakukan di Blade (`$bahan->sortBy(...)`) dan penyaringan role per baris (`$canSee`).

**Fix:** semua penyaringan, pencarian, dan pengurutan dipindahkan ke query controller sehingga konsisten lintas halaman; Blade hanya menampilkan.

### Catatan tambahan (ditemukan saat verifikasi yang sama)

- **Chart.js bawaan template adalah v2.7.2 (2018)** — API-nya (`plugins.legend`, `scales.y`, `indexAxis`) tidak kompatibel dengan konfigurasi modern, sehingga canvas dashboard tampil kosong tanpa error jelas. Chart.js **4.4.1** kini di-vendor lokal di `public/admin/assets/js/plugin/chartjs4/` dan hanya dimuat di halaman dashboard.
- **`BahanProcessController` & `BahanMasukController`** sebelumnya melakukan early-return `collect()` untuk role tak dikenal. Karena view kini memanggil `->links()`, kedua jalur dipaksa selalu mengembalikan paginator (`whereRaw('1 = 0')`) agar tipe datanya konsisten.

---

## 14. [Critical] `position: static` pada sidebar membuat seluruh isi halaman keluar dari viewport

**Gejala:** setiap halaman tampak **kosong** — sidebar tampil normal, tapi area kanan (header navbar + seluruh konten) blank.

**Root cause:** `master.blade.php` versi lama memuat inline `<style>` di `<head>` **sebelum** `kaiadmin.min.css`:

```css
@media (min-width: 992px) {
    .sidebar { transform: none !important; position: static; }
}
```

Aturan itu selama ini **tidak pernah berlaku** — `kaiadmin.min.css` dimuat setelahnya dan mendefinisikan `.sidebar{position:fixed}` dengan spesifisitas sama, sehingga yang terakhir menang. Praktis itu adalah **dead CSS**.

Saat styling dipindahkan ke `layouts/theme.blade.php` yang dimuat **paling akhir** di `<head>`, urutan cascade berbalik: `position: static` menang atas `position: fixed`. Sidebar pun masuk ke normal flow sebagai blok 265×757px, dan karena `.main-panel` memakai `float: right`, float itu terdorong ke **bawah** sidebar — keluar dari viewport.

Terukur di browser (Chrome headless, viewport 1440×900):

| Elemen | Sebelum fix | Sesudah fix |
|---|---|---|
| `.sidebar` | `position: static`, y=0 | `position: fixed`, y=0 |
| `.main-panel` | **y=757** (di bawah layar) | **y=0** |
| `.main-header` | y=757 | y=0 |
| `.main-panel > .container` | y=826, `opacity: 1` (ada, tapi jauh di bawah) | y=69 (tepat di bawah header) |

Perhatikan `opacity: 1` — konten **selalu ada dan terlihat**; murni masalah posisi, bukan visibility. Ini sebabnya pengecekan berbasis "apakah HTML ter-render" (panjang byte) lolos dan gagal menangkap bug ini; hanya pengukuran layout di browser sungguhan yang menemukannya.

**Fix:** hapus seluruh aturan posisi/transform `.sidebar` dari `theme.blade.php`. KaiAdmin sudah menangani posisi sidebar beserta perilaku responsifnya lewat `body.nav_open`. Di file itu kini ada komentar peringatan agar aturan posisi sidebar tidak ditambahkan lagi.

**Pelajaran:** memindahkan CSS ke urutan muat yang berbeda dapat **menghidupkan dead CSS** yang selama ini diam. Saat memusatkan style, periksa apakah aturan lama memang aktif — bukan sekadar menyalinnya.

### Catatan tambahan (ditemukan saat verifikasi yang sama)

- **Nama `@keyframes fadeInUp` bentrok** dengan definisi animate.css di `plugins.min.css`. Diganti jadi `merraFadeIn` agar tidak mungkin bertabrakan.
- **`.swal-wide { width: 600px !important }`** di `dashboard.blade.php` diganti `min(600px, calc(100vw - 2rem))` supaya popup tidak pernah melebihi lebar viewport pada layar sempit.

---

## 12. [Medium] `env()` dipanggil langsung di Blade view — rusak begitu `config:cache` dijalankan

**Lokasi:** `resources/views/layouts/master.blade.php` (skrip inisialisasi Pusher).

```php
var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", { cluster: "{{ env('PUSHER_APP_CLUSTER') }}", ... });
```

Laravel secara eksplisit mendokumentasikan bahwa `env()` **hanya boleh dipanggil di file `config/*.php`**. Begitu `php artisan config:cache` dijalankan (praktik standar production, dan otomatis dijalankan oleh `docker/entrypoint.sh` pada setup Dokploy), seluruh pemanggilan `env()` di luar file config — termasuk yang ini — akan **selalu mengembalikan `null`**, karena runtime tidak lagi membaca file `.env`. Efeknya: notifikasi stok menipis realtime **berhenti berfungsi total** di production tanpa error yang jelas (Pusher key kosong → koneksi gagal senyap).

**Fix:** ganti ke `config('broadcasting.connections.pusher.key')` dan `config('broadcasting.connections.pusher.options.cluster')`, yang sudah membaca `env()` dengan benar di `config/broadcasting.php` dan tetap valid setelah `config:cache`.

---

## 13. [Medium] `bahan_masuk.satuan` & `.tipe` di-set controller, tapi diam-diam tidak pernah tersimpan

**Ditemukan saat:** membangun & memvalidasi seeder data operasional — `BahanMasuk::create()` versi seeder (yang berjalan dengan `Model::unguard()` khas `db:seed`) langsung error SQL karena kolom `satuan` **tidak pernah ada** di tabel `bahan_masuk` (dicek ke migrasi aslinya). Ini menyingkap bug yang selama ini **senyap** di jalur HTTP biasa.

**Root cause:** `App\Models\BahanMasuk::$fillable` hanya berisi `tanggal_masuk, kode_bahan, nama_bahan, jumlah_masuk, stok_hasil` — sementara `stok_hasil` sendiri **bukan kolom yang pernah dibuat migrasi manapun** (fillable menunjuk field hantu), dan `tipe`/`satuan` yang benar-benar coba ditulis `BahanMasukController::store()` **tidak ada di `$fillable`**. Karena mass-assignment guard Eloquent aktif di request HTTP normal, `'satuan'` dan `'tipe'` yang dikirim controller **selalu di-drop diam-diam tanpa error** — kolom `tipe` di tabel `bahan_masuk` (yang memang ada secara skema) selalu tersangkut di nilai default migrasi `'non-proses'`, apa pun jenis batch yang sebenarnya diinput.

**Dampak:** riwayat `bahan_masuk` tidak pernah benar-benar mencatat apakah suatu baris adalah batch *proses* atau *non-proses* lewat kolom `tipe`-nya sendiri — untungnya tidak ada fitur lain di aplikasi yang membaca `bahan_masuk.tipe` atau `.satuan` secara langsung (semua view selalu join balik lewat relasi `bahan`/`bahanProcess`), jadi dampak ke UI = nihil, tapi data historis ini jadi tidak bisa dipercaya untuk audit/laporan di masa depan.

**Fix:**
- `BahanMasukController::store()`: hapus penulisan `'satuan'` ke `BahanMasuk::create()` (dead write ke kolom yang tidak ada).
- `App\Models\BahanMasuk::$fillable`: buang `stok_hasil` (field hantu), tambahkan `tipe` (kolom nyata yang sebelumnya diam-diam ter-guard) — sehingga niat controller untuk mencatat tipe batch benar-benar tersimpan mulai sekarang.

---

## 1. [Critical] Route `/bahan/process` terdaftar dua kali, salah satunya rusak

**Lokasi:** `routes/web.php` baris 60–61 vs baris 172.

```php
// Baris 60 — route pertama, WIN karena didaftarkan lebih dulu
Route::get('/bahan/process', [BahanController::class, 'listProcess'])->name('bahan.process');
Route::get('/bahan/non-process', [BahanController::class, 'listNonProcess'])->name('bahan.nonprocess');

// Baris 172 — implementasi asli & benar, TIDAK PERNAH TEREKSEKUSI
Route::get('/bahan/process', [BahanProcessController::class, 'index'])->name('bahan.process');
```

**Root cause:** Laravel mencocokkan route berdasarkan urutan pendaftaran — route pertama yang match URI+method akan dieksekusi. `BahanController` **tidak punya method `listProcess`/`listNonProcess`** (diverifikasi — hanya ada `index`, `laporan`, `downloadPdf`, `store`, `cekStokMenipis`, `update`, `destroy`). Karena route ini didaftarkan lebih dulu, setiap request ke `/bahan/process` (menu sidebar **Bahan Process**, dipakai oleh semua role) akan **selalu fatal error** (`Call to undefined method`), padahal implementasi yang benar (`BahanProcessController::index` + view `bahan/process.blade.php`) sudah ada tapi tidak pernah kepanggil.

**Dampak:** Modul **Bahan Proses** (racikan/batch) — salah satu fitur inti aplikasi — 100% tidak bisa diakses siapa pun.

**Fix:** Hapus route baris 60–61 yang rusak. `/bahan/non-process` tidak dipakai di UI manapun (tidak ada link ke `bahan.nonprocess`), jadi dihapus, bukan diperbaiki (tidak ada kebutuhan bisnis yang jelas untuknya).

---

## 2. [High] Route `bahan.process.edit` memanggil method yang tidak ada

**Lokasi:** `routes/web.php` baris 174 → `BahanProcessController::edit`.

`BahanProcessController` tidak punya method `edit()` (hanya `index`, `cekStokMenipis`, `store`, `update`, `destroy`). Edit bahan proses sebenarnya dilakukan lewat modal di halaman yang sama (submit langsung ke `bahan.process.update`), jadi route GET-edit ini adalah **dead route** yang akan 500 jika ada yang mengaksesnya (mis. link lama, bookmark, atau scraper).

**Fix:** Route dihapus.

---

## 3. [High] Foto profil: format penyimpanan tidak konsisten → gambar rusak

**Lokasi:** `app/Http/Controllers/UserController.php`, `app/Http/Controllers/ProfileController.php`, `resources/views/layouts/header.blade.php`, `resources/views/profile/index.blade.php`.

Dua controller yang sama-sama menulis kolom `users.photo` memakai **format path yang berbeda**:

- `UserController::store/update` → `$request->file('photo')->store('photos', 'public')` → hasil: `photos/xxxx.jpg` (path relatif, tanpa prefix).
- `ProfileController::update` → hasil di-hardcode: `'/storage/' . $path` → tersimpan sebagai `/storage/profile_photos/xxxx.jpg` (prefix absolut ikut disimpan di DB).

Lalu di sisi tampilan:
- `header.blade.php` merender **kedua kemungkinan** dengan cara yang sama: `asset(Auth::user()->photo)`. Untuk user hasil `UserController` ini menghasilkan URL **tanpa** `/storage/` (mis. `https://domain/photos/xxxx.jpg`) → **404, foto tidak muncul** di navbar untuk *setiap user yang dibuat lewat Manajemen Pengguna*.
- `profile/index.blade.php` bahkan tidak memanggil `asset()` sama sekali (`src="{{ Auth::user()->photo }}"`), jadi untuk user format `UserController` akan resolve sebagai URL relatif terhadap halaman saat ini — hampir pasti salah.
- `ProfileController::update` juga memanggil `Storage::delete($user->photo)` untuk menghapus foto lama — tapi `Storage::delete()` default memakai disk `local`, sedangkan file fisik ada di disk `public`, dan path yang disimpan sudah kepalang berisi prefix `/storage/` yang tidak valid sebagai path disk. **Foto lama tidak pernah benar-benar terhapus** (orphaned files menumpuk di storage, gagal senyap tanpa error).

**Root cause:** tidak ada satu sumber kebenaran (single source of truth) untuk format & resolusi URL foto — dua controller menulis dua format berbeda, dua view membaca dengan asumsi berbeda pula.

**Fix (root-cause, bukan tambal di tiap file):**
- Tambah accessor `photo_url` di `App\Models\User` sebagai satu-satunya cara membaca URL foto (fallback ke avatar default jika kosong).
- Normalisasi kedua controller agar **selalu menyimpan path relatif** (format `UserController`, tanpa prefix `/storage/`).
- `ProfileController` memakai `Storage::disk('public')->delete($path)` dengan path relatif yang benar saat mengganti foto.
- `header.blade.php` & `profile/index.blade.php` diubah memakai `$user->photo_url` — bukan `asset()` manual.

---

## 4. [Low] Dead code: query builder di-assign dua kali

**Lokasi:** `app/Http/Controllers/BahanKeluarController.php::index()`.

```php
$query = Bahan::query();
// ... beberapa baris lain memakai $dataKeluar ...
$query = Bahan::query();   // assignment pertama langsung dibuang, tidak dipakai sama sekali
```

Tidak menyebabkan bug fungsional, tapi mengaburkan alur baca kode. **Fix:** hapus baris duplikat.

---

## 5. [Medium] Markup HTML (badge status) digenerate di layer Controller

**Lokasi:** `app/Http/Controllers/BahanController.php::index()`.

```php
$item->status = '<span class="badge bg-success">AMAN</span>';
```

Controller menimpa kolom `status` milik model (di memori, untuk kebutuhan tampilan) dengan string HTML mentah. Ini melanggar separation of concerns (markup harusnya di View), membuat warna/style status **tidak bisa dikelola terpusat** (setiap kali warna ingin diubah, harus edit PHP di controller, bukan CSS/komponen), dan berisiko duplikasi logic yang sama di controller lain jika dibutuhkan di tempat lain.

**Fix:** hapus logic ini dari controller, buat Blade component `<x-status-badge>` yang menghitung status (AMAN/MENIPIS/HABIS) langsung dari `sisa_stok` & `batas_minimum` di View — dipakai di `daftarbahan.blade.php` dan bisa dipakai ulang di halaman lain (bahan proses, laporan) untuk mendapat styling **solid color** yang konsisten di seluruh aplikasi.

---

## 6. [Low] Atribut `readonly` pada `<select>` tidak berlaku secara spesifikasi HTML

**Lokasi:** `resources/views/bahan/daftarbahan.blade.php` baris 270, 293, 362, 377.

```html
<select name="tipe" class="form-control" required readonly>
```

`readonly` **hanya valid** untuk `<input>`/`<textarea>` yang bisa diketik; untuk elemen `<select>`, atribut ini **diam-diam diabaikan browser** — dropdown tetap sepenuhnya bisa diklik & diubah meski intensi developer jelas ingin menguncinya. Saat ini dampaknya kecil karena masing-masing dropdown ini kebetulan hanya berisi 1 opsi, tapi jadi bug nyata begitu ada opsi kedua ditambahkan di masa depan (mis. kategori baru).

**Fix:** ganti pola menjadi `disabled` (agar benar-benar terkunci & terlihat abu-abu) + `<input type="hidden">` bernama sama membawa nilai yang sama, karena field `disabled` tidak ikut ter-submit oleh browser.

---

## 7. [High] Bootstrap & jQuery dimuat berulang kali di setiap halaman

**Lokasi:** `resources/views/layouts/master.blade.php`.

Pada satu halaman yang sama, ditemukan:
- **jQuery** dimuat 2×: CDN `jquery-3.6.0.min.js` (head) + lokal `core/jquery-3.7.1.min.js` (sebelum `</body>`).
- **Bootstrap JS** dimuat **4×**: CDN `5.3.0-alpha1` (head) + CDN `5.3.3` (head) + lokal `core/bootstrap.min.js` (body) + CDN `5.3.3` lagi tepat sebelum `</body>`.
- Juga memuat **Semantic UI** (CSS+JS) dari CDN yang ternyata tidak dipakai komponen manapun di codebase (tidak ada kelas `ui dropdown`/`ui modal` dsb ditemukan).

**Dampak:** setiap page-load mengunduh script yang sama berkali-kali (memperlambat load, terutama di koneksi lambat/mobile), berpotensi memicu re-inisialisasi komponen Bootstrap dua kali (dropdown/modal event ganda), dan dua versi Bootstrap berbeda (`5.3.0-alpha1` vs `5.3.3`) dimuat bersamaan yang bisa menyebabkan perilaku tidak konsisten tergantung urutan eksekusi/cache browser.

**Fix:** satu sumber per library (pakai aset lokal `public/admin/assets` yang sudah ada, hapus semua duplikat CDN), Semantic UI dihapus karena tidak dipakai.

---

## 8. [Medium] Tailwind CSS & Alpine.js: ter-install tapi tidak pernah dipakai

**Lokasi:** `package.json`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`.

Sisa scaffolding `laravel/breeze` — `@tailwind base/components/utilities` di `app.css`, `Alpine.start()` di `app.js` — **tidak ada satupun file di `resources/views/` yang memanggil `@vite(...)`**. UI produksi 100% berjalan di atas template Bootstrap "KaiAdmin" (`public/admin/assets`). Ini menyesatkan siapa pun yang membaca `package.json` dan mengira stack UI-nya Tailwind.

**Fix:** hapus dependency Tailwind/Alpine + file `app.css`/`app.js`/`vite.config.js` yang tidak dipakai, supaya tidak ada jejak stack ganda yang membingungkan.

---

## 9. [Medium] Tidak ada dropdown searchable

Native `<select>` dipakai di 8 file (`daftarbahan`, `process`, `bahanmasuk`, `bahankeluar`, `users/index`, `menu/index`, `kategori/index`, `inventaris/index`) tanpa kemampuan pencarian. Menariknya, ada komentar sisa di `process.blade.php` baris 434 — `// reset select2 jika dipakai` — menandakan searchable-select memang sudah direncanakan sebelumnya tapi **tidak pernah benar-benar diimplementasikan**. Pada form dengan puluhan baris bahan (mis. Bahan Masuk), memilih bahan dari dropdown panjang tanpa search sangat tidak praktis.

**Fix:** integrasikan **Select2** (kompatibel jQuery + Bootstrap 5, cukup 1 tag CDN, tanpa build step) dan auto-init secara global di `master.blade.php` — otomatis berlaku ke **semua** `<select>` di seluruh 53 halaman tanpa perlu edit satu-satu.

---

## 10. [Medium] Warna tersebar sebagai inline style / hex literal

**Lokasi:** `resources/views/layouts/sidebar.blade.php` (`style="background-color: #2e2e2e;"`, `#3a3a3a`), badge warna hardcode di controller (lihat temuan #5), dsb.

Tidak ada satu tempat pun yang mendefinisikan palet warna aplikasi — setiap developer yang ingin mengubah warna brand harus grep manual ke banyak file. Ini bertentangan dengan requirement "warna solid di setiap komponen" karena warna solid butuh **konsistensi**, bukan cuma "tidak gradient".

**Fix:** definisikan token warna (CSS custom properties) terpusat di `master.blade.php`, pakai di seluruh komponen (sidebar, badge, tombol, card) via `var(--...)`.

---

## 11. [Low, belum di-fix] Query tanpa pagination

`BahanController::index`, `BahanKeluarController::index`, `MenuController::index`, `LaporanBahanBakuController::*` memuat seluruh baris tabel sekaligus (`::all()`/`::get()`). Untuk skala data coffee shop saat ini (puluhan–ratusan baris) ini tidak masalah, tapi begitu riwayat `bahan_masuk`/`bahan_keluar` terkumpul bertahun-tahun, endpoint ini akan melambat linear dengan jumlah baris.

**Kenapa tidak di-fix sekarang:** menambah pagination mengubah kontrak response yang dikonsumsi DataTables client-side di banyak view sekaligus (butuh penyesuaian JS per halaman) — di luar scope perbaikan bug murni pada task ini. Direkomendasikan sebagai task terpisah: pindahkan tabel besar (`bahan_masuk`, `bahan_keluar`) ke **DataTables server-side processing** (library-nya sudah ter-load di `master.blade.php`, tinggal diaktifkan mode `serverSide: true` + endpoint AJAX per tabel).
