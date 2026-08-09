# Coffee Shop Inventory App

Aplikasi manajemen inventaris berbasis web untuk kebutuhan operasional Coffee Shop, dibangun dengan **Laravel 10**. Aplikasi ini mencatat seluruh siklus hidup bahan baku — mulai dari bahan masuk, proses produksi (racikan/batch), penjualan menu, hingga stok akhir harian (stok opname) — lengkap dengan kontrol akses berbasis peran (role-based access control), notifikasi stok menipis secara real-time, serta pelaporan dalam format PDF.

---

## Daftar Isi

- [Ringkasan](#ringkasan)
- [Tech Stack](#tech-stack)
- [Peran & Hak Akses (RBAC)](#peran--hak-akses-rbac)
- [Model Data & Relasi](#model-data--relasi)
- [Alur Business Logic](#alur-business-logic)
- [Modul Aplikasi](#modul-aplikasi)
- [Notifikasi Stok Menipis (Real-time)](#notifikasi-stok-menipis-real-time)
- [Pelaporan](#pelaporan)
- [Struktur Direktori](#struktur-direktori)
- [Requirements](#requirements)
- [Instalasi (Local Development)](#instalasi-local-development)
- [Environment Variables Penting](#environment-variables-penting)
- [Deployment](#deployment)
- [Demo](#demo)
- [Lisensi](#lisensi)

---

## Ringkasan

Coffee Shop biasanya memiliki dua jenis bahan baku dengan alur berbeda:

1. **Bahan Non-Proses** — bahan baku yang langsung dipakai apa adanya (mis. gula, cup, sedotan).
2. **Bahan Proses** — bahan baku yang merupakan hasil olahan/batch dari kombinasi bahan non-proses (dan/atau bahan proses lain) dengan takaran (gramasi) tertentu (mis. sirup racikan, susu steam, saus).

Aplikasi ini mengotomasi **pengurangan stok bahan baku secara berjenjang**: setiap kali bahan proses dibuat (batch masuk), sistem otomatis mengurangi stok bahan-bahan penyusunnya sesuai komposisi/gramasi. Ketika sebuah menu terjual, sistem membandingkan pemakaian bahan yang **seharusnya** (berdasarkan resep menu × jumlah terjual) dengan bahan yang **benar-benar keluar** (dicatat manual/stok opname), sehingga menghasilkan laporan **Waste / Balance / Plus** untuk memantau efisiensi dan kebocoran bahan baku.

Selain itu tersedia modul untuk mengelola dua kategori operasional bar/kitchen (`BBAR` untuk Bar, `BBKTC` untuk Kitchen), manajemen inventaris aset non-bahan-baku (peralatan Bar/Kitchen/Operasional) dengan pencetakan QR Code, serta manajemen pengguna dan role.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 10 (PHP `^8.1` / `8.4`) |
| Autentikasi | Laravel Breeze (session-based, `routes/auth.php`) |
| Otorisasi (Role & Permission) | `spatie/laravel-permission` |
| Realtime broadcasting | `pusher/pusher-php-server` (server) + Pusher JS via CDN (client) |
| PDF generator | `barryvdh/laravel-dompdf` |
| Barcode | `milon/barcode` |
| QR Code | `simplesoftwareio/simple-qrcode` (format SVG, tanpa dependensi Imagick) |
| API token (opsional) | `laravel/sanctum` |
| UI | Bootstrap 5 + template admin "KaiAdmin" (`public/admin/assets`), jQuery, DataTables, SweetAlert, **Select2** (searchable dropdown) — semua di-load via CDN/aset lokal, **tanpa build step** (Vite/Tailwind/Alpine sudah dihapus karena tidak pernah dipakai, lihat [docs/ANALISIS_BUG_DAN_KENDALA.md](docs/ANALISIS_BUG_DAN_KENDALA.md)) |
| Design tokens | CSS custom properties terpusat di `resources/views/layouts/theme.blade.php` |
| Database | MySQL / PostgreSQL |

---

## Peran & Hak Akses (RBAC)

Role dikelola dengan `spatie/laravel-permission` dan di-seed lewat `database/seeders/RoleSeeder.php`. Terdapat **5 role**:

| Role | Cakupan Kategori Bahan | Hak Akses Utama |
|---|---|---|
| **Admin** | `BBAR` + `BBKTC` (semua) | Akses penuh ke seluruh modul, termasuk Kategori, Manajemen Pengguna, dan semua laporan |
| **Headbar** | `BBAR` (Bar) | CRUD bahan, bahan masuk/keluar/akhir, menu, inventaris Bar, laporan area Bar |
| **Headkitchen** | `BBKTC` (Kitchen) | CRUD bahan, bahan masuk/keluar/akhir, menu, inventaris Kitchen, laporan area Kitchen |
| **Bar** | `BBAR` (Bar) | Operasional harian bahan/menu area Bar (tanpa akses ke sebagian laporan Head-level) |
| **Kitchen** | `BBKTC` (Kitchen) | Operasional harian bahan/menu area Kitchen (tanpa akses ke sebagian laporan Head-level) |

Segmentasi data dilakukan lewat **prefix kode bahan**: kode yang diawali `BBAR` otomatis difilter khusus untuk role Bar/Headbar, dan `BBKTC` untuk Kitchen/Headkitchen. Admin dapat melihat/memfilter keduanya. Pembatasan ini diterapkan berulang kali di level controller (`BahanController`, `BahanMasukController`, `BahanProcessController`, `BahanAkhirController`, `BahanKeluarController`, `DashboardController`).

Middleware `role:` (dari Spatie) dipasang di `routes/web.php` untuk membatasi route per-role, contoh:

```php
Route::middleware(['role:Admin|Headbar|Headkitchen|Bar|Kitchen'])->group(...);
Route::middleware(['role:Admin'])->group(...); // Kategori & Manajemen Pengguna khusus Admin
Route::middleware('role:Admin|Headbar|Headkitchen')->group(...); // Laporan level Head ke atas
```

User default hasil seeding: `superadmin@gmail.com` / `Admin123` dengan role `Admin`.

---

## Model Data & Relasi

| Model | Tabel | Fungsi |
|---|---|---|
| `Kategori` | `kategoris` | Kategori bahan/menu/inventaris (kode dipakai untuk pengelompokan, mis. `BBAR`, `BBKTC`, `INVB`, `INVK`, `INVO`) |
| `Bahan` | `bahans` | Bahan baku **non-proses**. Field kunci: `kode_bahan`, `tipe`, `sisa_stok`, `batas_minimum`, `satuan`, `status` |
| `BahanProcess` | `bahan_processes` | Bahan hasil **proses/batch** (mis. sirup racikan). Punya `jumlah_batch`, `sisa_stok`, `batas_minimum` |
| `BahanProcessBahan` (pivot) | `bahan_process_bahan` | Komposisi: bahan non-proses apa saja + gramasi yang dipakai untuk membentuk 1 bahan proses |
| `Komposisis` (pivot) | `komposisis` | Komposisi bahan proses **di dalam** bahan proses lain (proses berlapis) |
| `BahanMasuk` | `bahan_masuk` | Catatan penerimaan/pembuatan batch bahan (baik non-proses maupun proses) |
| `BahanKeluar` | `bahan_keluar` | Catatan bahan keluar — bisa otomatis (dari pembuatan batch proses) atau manual (dari stok opname harian) |
| `BahanAkhir` | `bahan_akhir` | Snapshot stok akhir per hari (stok opname) per bahan |
| `Menu` | `menu` | Menu jual, terhubung ke `Kategori` |
| `MenuBahan` (pivot) | `menu_bahan` | Komposisi menu → bahan non-proses + gramasi (resep) |
| `BahanProcessMenu` (pivot) | `bahan_process_menu` | Komposisi menu → bahan proses + gramasi (resep) |
| `MenuTerjual` | `menu_terjual` | Catatan penjualan menu harian (menu + tanggal + jumlah terjual) |
| `Inventaris` | `inventaris` | Aset non-bahan-baku (peralatan Bar/Kitchen/Operasional), dengan cetak QR Code |
| `User` | `users` | Pengguna aplikasi, punya relasi role via Spatie (`HasRoles`) + foto profil |

### Ringkasan Relasi Penting

```
Kategori 1───N Menu
Menu N───N Bahan            (pivot: menu_bahan, +gramasi)          → resep bahan non-proses
Menu N───N BahanProcess     (pivot: bahan_process_menu, +gramasi)  → resep bahan proses
BahanProcess N───N Bahan        (pivot: bahan_process_bahan, +gramasi) → komposisi non-proses
BahanProcess N───N BahanProcess (pivot: komposisis, +gramasi)          → komposisi proses berlapis
Bahan/BahanProcess 1───N BahanMasuk
Bahan/BahanProcess 1───N BahanKeluar  (terhubung ke BahanMasuk via bahan_masuk_id, nullable)
Bahan/BahanProcess 1───N BahanAkhir   (snapshot harian via kode_bahan)
Menu 1───N MenuTerjual
User N───N Role (Spatie laravel-permission)
```

---

## Alur Business Logic

### 1. Bahan Masuk (`BahanMasukController::store`)

Form input bisa berisi banyak baris sekaligus (array), masing-masing dengan `tipe` = `proses` atau `non-proses`. Semua diproses dalam **satu DB transaction**:

- **Non-proses**: `jumlah_masuk` langsung menambah (`increment`) `sisa_stok` pada `Bahan`.
- **Proses** (pembuatan batch, mis. 1 batch sirup):
  1. `jumlah_batch` dan `sisa_stok` pada `BahanProcess` bertambah — `sisa_stok` dihitung dari `jumlah_masuk × total gramasi komposisi`.
  2. Untuk **setiap bahan non-proses** dalam komposisinya (`komposisis`/`bahan_process_bahan`), stok dikurangi (`decrement`) sebesar `jumlah_masuk × gramasi`, dan otomatis dicatat sebagai baris baru di `BahanKeluar` (terhubung ke `bahan_masuk_id`).
  3. Jika komposisinya juga memuat bahan proses lain (proses berlapis, via `komposisiBahanProses`), stok bahan proses tersebut juga dikurangi & dicatat sebagai `BahanKeluar`.

Update (`update`) dan hapus (`destroy`) pada Bahan Masuk akan **menghitung ulang selisih** dan menyesuaikan kembali stok bahan (termasuk stok komposisi) beserta catatan `BahanKeluar` terkait secara konsisten dalam transaction.

### 2. Bahan Keluar (`BahanKeluar`)

Sumber pencatatan `BahanKeluar` ada dua:
1. **Otomatis** — hasil efek samping pembuatan batch bahan proses (lihat di atas).
2. **Manual/Stok Opname** — dari selisih input stok akhir harian (lihat poin 3).

Model `BahanKeluar` punya accessor:
- `hasil_seharusnya` — total pemakaian bahan yang **seharusnya** terjadi, dihitung dari resep menu (gramasi) × jumlah menu terjual.
- `hasil_akhir` — membandingkan `jumlah_keluar` aktual vs `hasil_seharusnya`: `Balance` (sama), `Waste: ...` (keluar lebih banyak dari seharusnya → ada bahan terbuang/kurang efisien), atau `Plus: +...` (keluar lebih sedikit dari seharusnya → indikasi selisih stok/kesalahan input).

### 3. Bahan Akhir / Stok Opname Harian (`BahanAkhirController`, `DatabahanAkhirController`)

Alur input stok akhir harian:
1. User memilih **tanggal** (`showTanggalInput` → `prosesTanggal`), disimpan di session.
2. Sistem menampilkan daftar bahan non-proses dan bahan proses sesuai role user.
3. User menginput `sisa_stok` (stok fisik akhir) per bahan pada form (`update`):
   - Sistem mengambil **stok sebelumnya** (dari `BahanAkhir` hari yang sama jika sudah ada, atau hari sebelumnya, atau fallback ke `sisa_stok` bahan saat ini).
   - `jumlah_keluar = stok_sebelumnya - stok_akhir_input`.
   - `sisa_stok` pada `Bahan`/`BahanProcess` di-update langsung ke nilai baru.
   - Snapshot disimpan/diperbarui di `BahanAkhir` (`updateOrCreate` berdasarkan tanggal + kode bahan).
   - Jika `jumlah_keluar > 0`, dicatat/diakumulasi ke `BahanKeluar` pada tanggal tersebut (`bahan_masuk_id = null` karena bukan hasil produksi batch).
4. `DatabahanAkhirController::updateSingle` memungkinkan **koreksi** satu entri `BahanAkhir` — menyesuaikan ulang `sisa_stok` Bahan dan **menambah/mengurangi** `BahanKeluar` sesuai selisih (increment jika stok naik, decrement jika stok turun), dibungkus dalam DB transaction.

### 4. Menu & Resep (`MenuController`)

- Kode menu digenerate otomatis: `{kode_kategori}{3 digit random}` (mis. `BBAR045`).
- Setiap menu bisa memiliki kombinasi **bahan non-proses** (`bahan_biasa` + `gramasi_biasa`) dan **bahan proses** (`bahan_process` + `gramasi_process`) sebagai resep, disimpan lewat `sync()` ke tabel pivot `menu_bahan` / `bahan_process_menu`.
- `status_menu` dihitung otomatis saat create (`cekStatusMenu`): **Tersedia** jika seluruh bahan penyusun stoknya `> 0`, selain itu **Tidak Tersedia**. Accessor `Menu::getStatusMenuAttribute()` juga menyediakan status real-time berbasis `sisa_stok` bahan non-proses terkini.

### 5. Menu Terjual (`MenuTerjualController`)

- Mencatat jumlah menu yang terjual per tanggal (input bisa banyak menu sekaligus).
- Data ini menjadi **dasar perhitungan pemakaian bahan seharusnya** yang dipakai di modul Pemantauan dan pada accessor `hasil_seharusnya` di `BahanKeluar`.
- **Catatan penting**: pencatatan penjualan menu **tidak otomatis mengurangi stok bahan** — pengurangan stok riil terjadi lewat proses Bahan Masuk (batch) dan Stok Opname (Bahan Akhir). Menu Terjual dipakai murni sebagai pembanding "seharusnya" vs "aktual" untuk mendeteksi selisih (waste/kebocoran).

### 6. Pemantauan Bahan Baku (`PemantauanController`)

Laporan analitik utama aplikasi. Untuk rentang tanggal tertentu:
1. Menjumlahkan **pemakaian seharusnya** = Σ (gramasi resep menu × jumlah menu terjual) per kode bahan, mencakup bahan non-proses maupun bahan proses.
2. Menjumlahkan **total keluar aktual** dari tabel `bahan_keluar` pada rentang tanggal yang sama.
3. Status:
   - `selisih == 0` → **Balance**
   - `selisih > 0` (keluar aktual > seharusnya) → **Waste (n satuan)**
   - `selisih < 0` (keluar aktual < seharusnya) → **Plus (+n satuan)**
4. Bisa diunduh sebagai PDF (`downloadPDF`).

### 7. Kontrol Stok Minimum & Notifikasi

- Setiap `Bahan`/`BahanProcess` punya `batas_minimum`. Status ditentukan dari perbandingan `sisa_stok` vs `batas_minimum`:
  - `sisa_stok > batas_minimum` → **AMAN**
  - `0 < sisa_stok <= batas_minimum` → **MENIPIS**
  - `sisa_stok <= 0` → **HABIS**
- Dashboard menampilkan daftar bahan dengan stok ≤ batas minimum, difilter sesuai role.

### 8. Inventaris (Aset Non-Bahan-Baku)

- CRUD peralatan/aset dengan `kode_inventaris` (prefix `INVB` = Bar, `INVK` = Kitchen, `INVO` = Operasional) dan `kondisi`.
- Setiap aset bisa dicetak **QR Code** (format SVG, di-generate langsung tanpa Imagick) yang berisi tautan `inventaris/{id}` — ketika dipindai & user login, akan menampilkan detail aset (`scan_result`) — cocok untuk *asset tracking* fisik di lokasi.

---

## Modul Aplikasi

| Modul | Controller | Deskripsi Singkat |
|---|---|---|
| Dashboard | `DashboardController` | Ringkasan stok menipis + status menu, terfilter per role |
| Kategori | `KategoriController` | CRUD kategori (khusus Admin) |
| Daftar Bahan | `BahanController` | CRUD bahan non-proses, filter kategori/role, indikator status stok |
| Bahan Proses | `BahanProcessController` | CRUD bahan hasil racikan/batch + komposisi (bahan non-proses & bahan proses lain) |
| Bahan Masuk | `BahanMasukController` | Input penerimaan/pembuatan batch, otomatis memicu pengurangan stok komposisi |
| Bahan Keluar | `BahanKeluarController` | Riwayat & laporan bahan keluar (otomatis + manual) |
| Bahan Akhir | `BahanAkhirController`, `DatabahanAkhirController` | Stok opname harian per tanggal + koreksi data |
| Menu | `MenuController` | CRUD menu & resep (bahan non-proses + bahan proses) |
| Menu Terjual | `MenuTerjualController` | Input penjualan harian per menu |
| Pemantauan Bahan | `PemantauanController` | Laporan Waste/Balance/Plus antara pemakaian seharusnya vs aktual |
| Laporan Keseluruhan | `LaporanBahanBakuController` | Ringkasan gabungan masuk/keluar/stok akhir per bahan |
| Inventaris | `InventarisController` | CRUD aset + cetak QR Code |
| Manajemen Pengguna | `UserController` | CRUD user + assign role (khusus Admin) |
| Profil | `ProfileController` | Update profil & foto pengguna |
| Notifikasi Stok | `StokController` | Endpoint pengiriman notifikasi email untuk stok menipis/habis |

---

## Notifikasi Stok Menipis (Real-time)

- `BahanController::cekStokMenipis()` dan `BahanProcessController::cekStokMenipis()` memindai bahan dengan `sisa_stok <= batas_minimum` (atau `<= 5` untuk endpoint global `/kirim-notifikasi-stok`), lalu men-dispatch event `StokMenipisEvent` (`ShouldBroadcastNow`) ke channel Pusher **`stok-menipis`** dengan alias event **`stokMenipis`** — dikonsumsi di sisi frontend lewat `laravel-echo` + `pusher-js` untuk menampilkan notifikasi tanpa reload halaman.
- `StokController::kirimNotifikasiStok()` mengirim **email** (`StokMenipisNotification` mailable) ke `owner@coffeeshop.com` berisi daftar bahan dengan stok ≤ 5.
- Endpoint-endpoint ini idealnya dipanggil terjadwal (mis. via cron/scheduler eksternal, atau `php artisan schedule:run`) agar pemantauan berjalan otomatis.

---

## Pelaporan

Seluruh laporan tersedia dalam tampilan web maupun **export PDF** (`barryvdh/laravel-dompdf`), difilter berdasarkan rentang tanggal:

- Laporan Daftar Bahan
- Laporan Bahan Masuk
- Laporan Bahan Keluar
- Laporan Bahan Akhir (stok opname)
- Laporan Keseluruhan Bahan Baku (gabungan masuk/keluar/stok akhir)
- Laporan Pemantauan Bahan Baku (Waste/Balance/Plus)

---

## Struktur Direktori

```
app/
├── Console/            # Scheduled command (jika ada)
├── Events/             # StokMenipisEvent (broadcast realtime)
├── Http/Controllers/   # Seluruh business logic (lihat tabel Modul Aplikasi)
├── Mail/               # StokMenipisNotification (mailable)
├── Models/             # Eloquent models & relasi
database/
├── migrations/         # Skema tabel (bahan, proses, komposisi, menu, dll.)
├── seeders/            # RoleSeeder (5 role + akun Super Admin)
resources/
├── views/
│   ├── layouts/          # master.blade.php (layout utama), theme.blade.php (design tokens), sidebar, header
│   ├── components/       # Blade component reusable (mis. <x-status-badge>)
│   └── ...                # View per modul
routes/
├── web.php              # Seluruh route aplikasi + middleware role
├── auth.php              # Route autentikasi (Breeze)
public/
├── admin/                # Aset template UI KaiAdmin (Bootstrap 5) — ini yang benar-benar dipakai
├── landingpage/          # Aset landing page tambahan
docs/
├── ANALISIS_BUG_DAN_KENDALA.md  # Dokumentasi audit bug & perbaikan
```

---

## Requirements

- PHP `>= 8.1` (disarankan 8.2/8.4)
- Ekstensi PHP: `pdo_mysql`/`pdo_pgsql`, `mbstring`, `bcmath`, `gd`, `zip`, `xml`, `curl`, `intl`
- Composer 2.x
- MySQL 8 / PostgreSQL 13+
- Tidak butuh Node.js/NPM — UI berbasis Bootstrap 5 (aset statis di `public/admin`), tanpa build step
- (Opsional, untuk notifikasi realtime) Akun Pusher atau layanan kompatibel

---

## Instalasi (Local Development)

```bash
git clone <repository-url>
cd Inventory-App

cp .env.example .env

composer install

php artisan key:generate
php artisan storage:link       # wajib, untuk foto profil & aset publik

# Set DB_* di .env sebelum migrate
php artisan migrate --seed     # men-seed 5 role + akun superadmin@gmail.com / Admin123

php artisan serve
```

---

## Environment Variables Penting

| Variabel | Keterangan |
|---|---|
| `APP_KEY` | Wajib di-generate (`php artisan key:generate`) |
| `APP_URL` | URL publik aplikasi (dipakai untuk link QR Code inventaris) |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Koneksi database (MySQL/PostgreSQL) |
| `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER` | Wajib untuk notifikasi stok menipis realtime |
| `MAIL_*` | Wajib untuk pengiriman notifikasi email stok menipis (`StokController`) |
| `FILESYSTEM_DISK` | Gunakan `public` agar foto profil & upload tersimpan dapat diakses via `storage:link` |
| `QUEUE_CONNECTION` | Set ke driver queue (`database`/`redis`) jika notifikasi/mail ingin diproses asinkron |

---

## Deployment

Project ini sebelumnya dikonfigurasi untuk platform serverless PHP (`vercel.json`, `api/index.php`) dan Nixpacks (`nixpacks.toml`). Untuk deployment berbasis container (mis. **Dokploy**), gunakan `Dockerfile` yang tersedia di root project — lihat panduan lengkap yang disediakan terpisah untuk konfigurasi build, environment variable, volume persisten (`storage/`), serta perintah migrasi database saat rilis.

---

## Demo

![Screenshot](demo-pic/welcome.png)
![Screenshot](demo-pic/login.png)
![Screenshot](demo-pic/Dashboard.png)
![Screenshot](demo-pic/Kategori.png)
![Screenshot](demo-pic/BahanBaku.png)
![Screenshot](demo-pic/BahanMasuk.png)
![Screenshot](demo-pic/BahanKeluar.png)
![Screenshot](demo-pic/BahanAkhir.png)
![Screenshot](demo-pic/BahanProses.png)
![Screenshot](demo-pic/Menu.png)
![Screenshot](demo-pic/MenuTerjual.png)
![Screenshot](demo-pic/DaftarInventaris.png)
![Screenshot](demo-pic/pengguna.png)
![Screenshot](demo-pic/Laporan1.png)
![Screenshot](demo-pic/Laporan2.png)
![Screenshot](demo-pic/Pemantauan.png)

---

## Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
