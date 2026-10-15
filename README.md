# CRM Faqih Mart

Aplikasi web untuk mengelola data pelanggan bisnis distribusi sembako. Fokus utamanya
menyimpan, mencari, dan memverifikasi alamat pelanggan agar admin mudah membagikan
informasi lokasi kepada driver melalui Google Maps.

Dibangun dengan **CodeIgniter 3.1.13**, **PHP 8.2+**, **MySQL/MariaDB**, dan
**Tailwind CSS (CDN)**. JavaScript ditulis tanpa framework.

## Fitur

- Login/logout admin (tanpa registrasi publik).
- Dashboard: pelanggan aktif, lokasi terverifikasi, belum diverifikasi, belum lengkap, dan pelanggan terbaru.
- CRUD pelanggan: tambah, detail, edit, arsipkan, pulihkan.
- Pencarian berdasarkan nama, nomor WhatsApp, atau alamat; filter status lokasi.
- Peringatan nomor WhatsApp duplikat.
- Tombol Buka Google Maps dan Salin Alamat pada detail pelanggan.
- Ikon Maps di kolom daftar + simpan/ubah tautan Maps tanpa reload (realtime).
- Ekspor CSV.
- Ganti password admin.

## Kebutuhan

- XAMPP (Apache + PHP 8.2 atau lebih baru + MySQL/MariaDB). Path contoh: `C:\xampp`.
- Ekstensi PHP: `mysqli`, `mbstring`, `openssl`, `fileinfo` (aktif secara default di XAMPP).

## Instalasi

1. Salin folder proyek ini ke dalam web root XAMPP, misalnya
   `C:\xampp\htdocs\Faqihmart-CRM`.
2. Pastikan Apache dan MySQL berjalan dari XAMPP Control Panel.
3. Impor skema database:
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root < database.sql
   ```
   Atau impor `database.sql` melalui phpMyAdmin.
4. Buka `http://localhost/Faqihmart-CRM/login`.
5. Login awal:
   - **Username:** `admin`
   - **Password:** `admin123`

> **Penting:** segera ganti password default melalui menu **Ganti Password** setelah login
> pertama. Jangan gunakan password default di lingkungan nyata.

## Konfigurasi

### URL

`application/config/config.php` mengatur `base_url`:

```php
$config['base_url'] = 'http://localhost/Faqihmart-CRM/';
```

Sesuaikan bila nama folder atau domain berbeda, dan ubah menjadi alamat produksi saat
deploy.

### Database

`application/config/database.php` membaca kredensial dari environment variable agar tidak
ada password di dalam source code. Nilai default cocok untuk XAMPP (`root` tanpa password):

| Variabel | Default |
|---|---|
| `DB_HOSTNAME` | `localhost` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | (kosong) |
| `DB_DATABASE` | `faqihmart_crm` |

Untuk produksi, set environment variable tersebut pada server alih-alih mengubah file.

### Environment aplikasi

`index.php` memakai `CI_ENV` (default `development`). Untuk produksi set `CI_ENV=production`
agar error internal tidak ditampilkan dan deprecation disembunyikan.

## Menjalankan Aplikasi

Setelah instalasi, aplikasi dapat diakses di `http://localhost/Faqihmart-CRM/`. URL bersih
ditangani oleh `.htaccess` di root (rewrite ke `index.php`), jadi `index.php` tidak perlu
ditulis di URL.

## Status Lokasi

- `incomplete` — informasi alamat wajib belum lengkap (Alamat lengkap kosong).
- `unverified` — alamat tersedia, titik lokasi belum dikonfirmasi.
- `verified` — admin sudah memeriksa dan memastikan titik sesuai rumah pelanggan.

Status hanya menjadi `verified` bila admin mencentang konfirmasi verifikasi **dan** ada
koordinat atau tautan Google Maps yang valid. Aplikasi tidak mengarang koordinat.

## Google Maps

Hanya tautan Google Maps biasa yang diterima (mis. `https://google.com/maps/...`,
`https://maps.google.com/...`, `https://maps.app.goo.gl/...`). Tautan divalidasi di server
dan dibuka pada tab baru dengan `rel="noopener noreferrer"`. Tidak ada API berbayar.

## Ekspor CSV

Tombol **Ekspor CSV** pada daftar pelanggan mengikuti pencarian dan filter yang aktif.
Berkas memakai BOM UTF-8 agar terbaca benar di Excel.

## Backup dan Restore Database

Backup:
```bash
C:\xampp\mysql\bin\mysqldump.exe -u root faqihmart_crm > backup\faqihmart_crm-YYYYMMDD.sql
```

Restore:
```bash
C:\xampp\mysql\bin\mysql.exe -u root faqihmart_crm < backup\faqihmart_crm-YYYYMMDD.sql
```

Simpan hasil backup di luar web root atau di folder yang tidak dapat diakses publik.
Catatan: `.htaccess` memblokir akses langsung ke file `.sql` dan `.md`.

## Struktur Proyek

```
application/
  config/        konfigurasi CI (config, database, routes, autoload)
  controllers/   Auth, Dashboard, Customers, Welcome
  core/          MY_Controller (gerbang autentikasi)
  helpers/       maps_helper.php (validasi Maps, label status, alamat)
  models/        Admin_model, Customer_model
  views/         layouts, auth, dashboard, customers
  cache/sessions sesi file-based
system/          framework CodeIgniter (jangan diubah)
database.sql     skema + admin awal
TASKS.md         task tracker
AGENTS.md        panduan teknis untuk agen/developer
```

## Pengujian

Tidak ada automated test pada MVP ini. Lakukan pemeriksaan sintaks dan uji manual:

```bash
C:\xampp\php\php.exe -l application\controllers\Customers.php
```

Lihat `TASKS.md` untuk daftar tugas dan statusnya.
