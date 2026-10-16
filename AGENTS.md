# AGENTS.md

Panduan teknis untuk agen/developer. Perbarui **hanya** saat ada keputusan penting soal
arsitektur, struktur, atau konvensi — bukan untuk pekerjaan rutin.

## Ringkasan

CRM Faqih Mart: aplikasi **CodeIgniter 3.1.13** (bukan CI4) untuk mengelola data pelanggan
distribusi sembako, fokus pencarian & verifikasi alamat + Google Maps.

- PHP 8.2 (XAMPP: `C:\xampp\php\php.exe`), MySQL/MariaDB (`C:\xampp\mysql\bin\`).
- URL lokal: `http://localhost/Faqihmart-CRM/` (`base_url` harus sesuai nama folder).
- `system/` = framework vendored — **jangan diubah**. Semua kode di `application/`.
- Login awal: `admin` / `admin123` (dari `database.sql`); wajib diganti lewat menu Ganti Password.

## Arsitektur (keputusan yang mengikat)

- **Auth**: tabel `admins`, password `password_hash()` bcrypt. Tidak ada registrasi publik.
  `application/core/MY_Controller.php` adalah gerbang autentikasi; semua controller internal
  (`Dashboard`, `Customers`, `Deliveries`, `Welcome`) meng-extends-nya. `Auth` dan `Driver`
  extends `CI_Controller` karena menangani halaman publik (login/ganti password, akses driver
  berbasis token).
- **Layout**: view dirender via `MY_Controller::render($view, $data)` →
  `layouts/header.php` (memuat sidebar + flash) + view + `layouts/footer.php`.
  `header.php` membutuhkan `$admin`; controller wajib mengirim `page_title`.
  Halaman driver (`views/driver/*`) memakai layout minimal sendiri (tanpa sidebar admin).
- **Model**: `Customer_model` (query/filter/stats/kode), `Admin_model`, dan `Delivery_model`
  (daftar & tujuan pengiriman, token, progres). Query Builder, tanpa ORM.
- **Soft delete** lewat `customers.archived_at`; daftar aktif memfilter `archived_at IS NULL`.
- **customer_code** otomatis format `FM-00001` (prefix + nomor urut, cek unik).
- **location_status**: `incomplete` (Alamat lengkap kosong) → `unverified` (default) →
  `verified` hanya bila admin mencentang konfirmasi **dan** ada koordinat/URL Maps valid.
  Jangan pernah mengarang koordinat atau auto-verified.
- **Google Maps**: `application/helpers/maps_helper.php` memvalidasi URL di server (hanya
  host `google.<tld>`/`maps.google.<tld>`/`maps.app.goo.gl`/`goo.gl/maps`, wajib https).
  Tautan eksternal wajib `target="_blank" rel="noopener noreferrer"`. Tanpa API berbayar.
- **Duplikat WhatsApp**: peringatan + tombol "Simpan Tetap" (`confirm_duplicate=1`), bukan blokir keras.
- **Daftar Pengiriman**: tabel `delivery_lists` (per tanggal + `session` pagi/sore, `status`
  draft/published/completed/cancelled) dan `delivery_items` (FK ke `customers` + `delivery_lists`,
  `sort_order`, `note`, `status` pending/in_progress/done). UNIQUE(list,customer) mencegah
  pelanggan ganda dalam satu daftar, tetapi pelanggan sama boleh di sesi lain. Riwayat tidak
  dihapus permanen (batalkan = status `cancelled`); item pelanggan terarsip tetap tampil (LEFT JOIN).
- **Akses driver berbasis token**: `Driver` extends `CI_Controller` (publik, **bukan**
  `MY_Controller`). Token acak 64 hex; disimpan sebagai `token_hash` SHA-256 (untuk lookup) +
  `token_enc` terenkripsi (agar admin dapat menyalin ulang tautan). `find_by_token()` hanya
  mengembalikan daftar berstatus `published`/`completed` dan belum dicabut. Cabut/regenerate
  mematikan tautan lama. Driver hanya boleh memperbarui status tujuan (POST + CSRF), tidak ada
  akses ke data pelanggan atau daftar lain.
- **DB config** membaca env (`DB_HOSTNAME/USERNAME/PASSWORD/DATABASE`) di `database.php` —
  jangan hardcode kredensial. Default XAMPP: root tanpa password, DB `faqihmart_crm`.

## Konvensi kode

- Gaya CI3: indentasi **tab**, LF (`.editorconfig`), setiap file PHP diawali
  `defined('BASEPATH') OR exit('No direct script access allowed');`.
- Controller **PascalCase**, method/variabel **camelCase**, tabel/kolom **snake_case**.
- Selalu escape output dinamis dengan `html_escape()`; validasi input di server
  (Form Validation), pertahankan input saat validasi gagal.
- CSRF aktif (`csrf_protection = TRUE`) — gunakan `form_open()` untuk semua form POST.
  Jangan matikan CSRF untuk mengatasi error.
- Jangan tampilkan error DB internal ke pengguna; set `CI_ENV=production` saat produksi.

## Hal yang mudah terlewat

- Autoload sudah memuat `database`, `session`, `form_validation`, dan helper `url`/`form`.
  Helper `maps` dan `delivery` dimuat per-controller (Deliveries & Driver).
- `delivery_lists.session` adalah nama kolom (kata kunci non-reserved MySQL); Query Builder
  otomatis mem-backtick. Jangan menulis query mentah tanpa backtick.
- Sesi file-based di `application/cache/sessions` (di-gitignore). Direktori ini harus ada;
  buat bila hilang.
- **PHP 8.2 + CI3** memunculkan deprecation "dynamic property". `index.php` mengecualikan
  `E_DEPRECATED` di development — jangan dikembalikan ke `error_reporting(-1)` atau halaman
  akan penuh peringatan (bukan cara memperbaiki; jangan patch `system/`).
- `.htaccess` root memblokir akses web ke dotfile dan `.sql .md .log .json .lock` dll., dan
  rewrite ke `index.php` (URL bersih). `AGENTS.md`/`README.md` tidak dapat diakses via browser.
- Endpoint arsip/pulihkan hanya menerima **POST** (`show_404()` bila bukan POST).
- **Simpan cepat tautan Maps (realtime)**: endpoint JSON `customers/{id}/maps` (POST) → `Customers::saveMapsUrl`.
  Karena `csrf_regenerate = TRUE`, setiap respons JSON menyertakan `csrf_token` dan klien **wajib**
  memutakhirkannya ke meta `csrf-token` + semua input `csrf_test_name` (lihat JS di
  `views/customers/index.php`) sebelum request berikutnya, atau POST akan kena 403.

## Perintah

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

Tidak ada automated test (sesuai permintaan). Verifikasi = `php -l` + uji manual sesuai
checklist di `TASKS.md`. Jangan membuat test framework tanpa diminta.

## Dokumentasi lain

- `README.md` — instalasi, konfigurasi, backup/restore.
- `TASKS.md` — tracker tugas dan catatan terbuka.
- `database.sql` — sumber kebenaran skema; harus konsisten dengan `Customer_model`.
