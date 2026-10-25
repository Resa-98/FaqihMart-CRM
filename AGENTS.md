# AGENTS.md

Panduan teknis untuk agen/developer. Perbarui **hanya** saat ada keputusan penting soal
arsitektur, struktur, atau konvensi — bukan untuk pekerjaan rutin.

## Ringkasan

CRM Faqih Mart: aplikasi **CodeIgniter 3.1.13** (bukan CI4) untuk mengelola data pelanggan
distribusi sembako, fokus pencarian & verifikasi alamat + Google Maps.

- PHP 8.2 (XAMPP: `C:\xampp\php\php.exe`), MySQL/MariaDB (`C:\xampp\mysql\bin\`).
- URL lokal: `http://localhost/FaqihMart-CRM/` lewat Apache XAMPP (`.htaccess` tidak berlaku
  di `php -S` tanpa router, jadi URL bersih tidak jalan).
- `system/` = framework vendored — **jangan diubah**. Semua kode di `application/`.
- Tanpa build frontend: Tailwind via CDN, JavaScript inline; jangan cari `package.json`/npm.
- Login awal: `admin` / `admin123` (dari `database.sql`); wajib diganti lewat menu Ganti Password.

## Arsitektur (keputusan yang mengikat)

- **Auth**: tabel `admins`, password `password_hash()` bcrypt. Tidak ada registrasi publik.
  `application/core/MY_Controller.php` adalah gerbang autentikasi; semua controller internal
  (`Dashboard`, `Customers`, `Deliveries`, `Welcome`) meng-extends-nya. `Auth` dan `Driver`
  extends `CI_Controller` karena menangani halaman publik (login/ganti password, akses driver
  berbasis token). `Auth::changePassword` memeriksa sesi sendiri dan merender layout manual;
  method baru di `Auth` **tidak** otomatis terlindungi.
- **Layout**: view dirender via `MY_Controller::render($view, $data)` →
  `layouts/header.php` (memuat sidebar + flash) + view + `layouts/footer.php`.
  `header.php` mengharapkan `$admin`; kirim `page_title` untuk `<title>`.
  Halaman driver (`views/driver/*`) memakai layout minimal sendiri (tanpa sidebar admin).
- **Model**: `Customer_model` (query/filter/stats/kode), `Admin_model`, dan `Delivery_model`
  (daftar & tujuan pengiriman, token, progres). Query Builder, tanpa ORM.
- **Soft delete** lewat `customers.archived_at`; daftar aktif memfilter `archived_at IS NULL`.
- **customer_code** otomatis format `FM-00001` (prefix + nomor urut, cek unik).
- **location_status** dihitung di `Customers::_input()`: default `unverified`; `incomplete` bila
  `full_address` kosong; `verified` hanya bila checkbox konfirmasi **dan** ada koordinat/URL Maps
  valid (checkbox menang atas `incomplete`). Jangan pernah mengarang koordinat atau auto-verified.
- **Google Maps**: `application/helpers/maps_helper.php` memvalidasi URL di server (hanya
  host `google.<tld>`/`maps.google.<tld>`/`maps.app.goo.gl`/`goo.gl/maps`, wajib https).
  `customer_maps_link()` memprioritaskan koordinat, lalu URL. Tautan eksternal wajib
  `target="_blank" rel="noopener noreferrer"`. Tanpa API berbayar.
- **Duplikat WhatsApp**: peringatan + tombol "Simpan Tetap" (`confirm_duplicate=1`), bukan blokir
  keras; pengecekan hanya terhadap pelanggan aktif (bukan arsip).
- **Saran pencarian**: `GET customers/suggest` (admin) memakai
  `Customer_model::search_suggestions()`; 5 tipe saran = nama, WhatsApp, alamat/perumahan,
  blok/RT-RW, kode; maksimal 6, tanpa konteks pelanggan dan tanpa cache. Endpoint menghormati
  `view`/`status` dari `_filters()`. Pencarian WA dinormalkan (`08...`/`62...`, spasi/tanda
  hubung) dan filter `_apply_filters()` mencakup `block_number`/`rt_rw` serta awalan
  `RT`/`RW`/`Blok`. JS autocomplete bersama ada di `layouts/footer.php` dan hanya dimuat bila
  data view mengirim `customer_suggest`. Tanpa perubahan skema.
- **Daftar Pengiriman**: tabel `delivery_lists` (per tanggal + `session` pagi/sore, `status`
  draft/published/completed/cancelled) dan `delivery_items` (FK ke `customers` + `delivery_lists`,
  `sort_order`, `note`, `status` pending/in_progress/done). UNIQUE(list,customer) mencegah
  pelanggan ganda dalam satu daftar, tetapi pelanggan sama boleh di sesi lain. Daftar tidak
  dihapus permanen (batalkan = status `cancelled`); tujuan masih bisa diubah/dihapus selama
  daftar `draft`/`published`. Item pelanggan terarsip tetap tampil (LEFT JOIN).
- **Akses driver berbasis token**: `Driver` extends `CI_Controller` (publik, **bukan**
  `MY_Controller`). Token acak 64 hex; disimpan sebagai `token_hash` SHA-256 (untuk lookup) +
  `token_enc` terenkripsi (agar admin dapat menyalin ulang tautan). `find_by_token()` hanya
  mengembalikan daftar berstatus `published`/`completed` dan belum dicabut. Cabut/regenerate
  mematikan tautan lama. Driver hanya boleh memperbarui status tujuan saat daftar `published`
  (POST + CSRF); daftar `completed` read-only, dan tidak ada akses ke daftar/pelanggan lain.
- **Rincian pesanan & struk**: `delivery_items.order_details` menyimpan teks bebas (satu baris
  per produk); `delivery_items.receipt_file` menyimpan **nama file acak** di
  `uploads/receipts/` (bukan path lengkap). Folder itu diblokir dari akses langsung lewat
  `.htaccess`; file hanya disajikan `Deliveries::itemReceipt` (sesi admin) atau
  `Driver::receipt` (token `published`/`completed` valid) dengan MIME JPG/PNG. Hapus file disk
  saat struk diganti/dihapus atau tujuan dihapus. Jangan pernah menautkan path file langsung.
- **DB config** membaca `getenv()` (`DB_HOSTNAME/USERNAME/PASSWORD/DATABASE`) di `database.php` —
  jangan hardcode kredensial. Default XAMPP: root tanpa password, DB `faqihmart_crm`.

## Konvensi kode

- Gaya CI3: indentasi **tab**, LF (`.editorconfig`), setiap file PHP diawali
  `defined('BASEPATH') OR exit('No direct script access allowed');`.
- Controller **PascalCase**, method/variabel **camelCase**, tabel/kolom **snake_case**.
- Teks UI, komentar, dan dokumentasi memakai bahasa Indonesia.
- Selalu escape output dinamis dengan `html_escape()`; validasi input di server
  (Form Validation), pertahankan input saat validasi gagal.
- Callback Form Validation (`_validate_latitude`, `_valid_maps_url`, `_valid_verified`,
  `_valid_date`) harus **public** meski berawalan `_`, karena dipanggil framework.
- CSRF aktif (`csrf_protection = TRUE`) — gunakan `form_open()` untuk semua form POST.
  Jangan matikan CSRF untuk mengatasi error.
- Jangan tampilkan error DB internal ke pengguna. `index.php` membaca `$_SERVER['CI_ENV']`
  (mis. Apache `SetEnv CI_ENV production`), **bukan** `getenv()`; default `development`.

## Hal yang mudah terlewat

- Autoload sudah memuat `database`, `session`, `form_validation`, dan helper `url`/`form`.
  `maps` dimuat manual di Dashboard/Customers/Deliveries/Driver; `delivery` hanya di
  Deliveries/Driver.
- `delivery_lists.session` adalah nama kolom (kata kunci non-reserved MySQL); Query Builder
  otomatis mem-backtick. Jangan menulis query mentah tanpa backtick.
- `config.php` membuat `base_url` secara dinamis dari `HTTP_HOST` + direktori `SCRIPT_NAME`.
  Tautan driver mengikuti host yang dipakai admin; untuk dibagikan ke HP, buka admin lewat IP LAN.
  Di produksi ganti dengan URL HTTPS tetap dan hapus blok dinamis.
- `encryption_key` dipakai untuk `delivery_lists.token_enc`; jangan menggantinya sembarangan.
  Token lama tidak bisa didekripsi/di-copy ulang setelah key berubah (lookup `token_hash` tetap
  jalan sampai token di-regenerate).
- Sesi file-based di `application/cache/sessions` (gitignored). `config.php` membuat direktori
  otomatis; bila gagal karena permission, login/session akan rusak.
- Timezone tidak diset aplikasi: `date()` ikut `php.ini`, `CURRENT_TIMESTAMP` ikut MySQL. Samakan
  keduanya (Asia/Jakarta) agar tanggal pengiriman tidak bergeser; `SET time_zone` di `database.sql`
  hanya berlaku saat impor.
- **PHP 8.2 + CI3** memunculkan deprecation "dynamic property". `index.php` mengecualikan
  `E_DEPRECATED` di development — jangan dikembalikan ke `error_reporting(-1)` atau halaman
  akan penuh peringatan (bukan cara memperbaiki; jangan patch `system/`).
- `.htaccess` root memblokir akses web ke dotfile dan `.sql .md .log .json .lock` dll., dan
  rewrite ke `index.php` (URL bersih). `AGENTS.md`/`README.md` tidak dapat diakses via browser.
- Semua endpoint mutasi (arsip/pulihkan, Maps, aksi pengiriman, status driver) hanya **POST**;
  GET ke sana -> `show_404()`.
- **Simpan cepat tautan Maps (realtime)**: endpoint JSON `customers/{id}/maps` (POST) → `Customers::saveMapsUrl`.
  Karena `csrf_regenerate = TRUE`, setiap respons JSON menyertakan `csrf_token` dan klien **wajib**
  memutakhirkannya ke meta `csrf-token` + semua input `csrf_test_name` (lihat JS di
  `views/customers/index.php`) sebelum request berikutnya, atau POST akan kena 403.

## Perintah

Tidak ada migration runner; `database.sql` adalah sumber kebenaran skema. Perubahan skema
diverifikasi dengan impor ulang, yang **menghapus** tabel yang ada.

```bash
# Import / reset skema (data pelanggan hilang!)
C:\xampp\mysql\bin\mysql.exe -u root < database.sql

# Cek sintaks satu file
C:\xampp\php\php.exe -l application\controllers\Customers.php

# Backup / restore
C:\xampp\mysql\bin\mysqldump.exe -u root faqihmart_crm > backup.sql
C:\xampp\mysql\bin\mysql.exe -u root faqihmart_crm < backup.sql
```

## Pengujian

Tidak ada automated test (sesuai permintaan). `composer.json` adalah bawaan CodeIgniter; script
test-nya menunjuk `tests/` yang tidak ada dan `vendor/` tidak dipakai — jangan jalankan
`composer install/test` untuk memverifikasi. Verifikasi = `php -l` + uji manual sesuai checklist
di `TASKS.md`. Jangan membuat test framework tanpa diminta.

## Dokumentasi lain

- `README.md` — instalasi, konfigurasi, backup/restore.
- `TASKS.md` — tracker tugas dan catatan terbuka.
- `database.sql` — sumber kebenaran skema; harus konsisten dengan `Customer_model`.
