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
- Pencarian berdasarkan nama, nomor WhatsApp, alamat, blok/nomor, atau RT/RW; filter status lokasi.
- Saran kata kunci ringkas saat mengetik (nama, WhatsApp, alamat/perumahan, blok/RT-RW, kode)
  di halaman Pelanggan dan panel Tambah Tujuan.
- Peringatan nomor WhatsApp duplikat.
- Tombol Buka Google Maps dan Salin Alamat pada detail pelanggan.
- Ikon Maps di kolom daftar + simpan/ubah tautan Maps tanpa reload (realtime).
- Daftar Pengiriman: susun tujuan per tanggal & sesi (pagi/sore), atur urutan, publikasikan
  tautan akses untuk driver (tanpa akun), dan pantau progres.
- Rincian pesanan per tujuan: teks produk (satu baris per produk) dan foto struk opsional,
  tampil juga di halaman driver.
- Ekspor CSV.
- Ganti password admin.

## Kebutuhan

- XAMPP (Apache + PHP 8.2 atau lebih baru + MySQL/MariaDB). Path contoh: `C:\xampp`.
- Ekstensi PHP: `mysqli`, `mbstring`, `openssl`, `fileinfo` (aktif secara default di XAMPP).

## Instalasi

1. Salin folder proyek ini ke dalam web root XAMPP, misalnya
   `C:\xampp\htdocs\FaqihMart-CRM`.
2. Pastikan Apache dan MySQL berjalan dari XAMPP Control Panel.
3. Impor skema database:
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root < database.sql
   ```
   Atau impor `database.sql` melalui phpMyAdmin.
4. Buka `http://localhost/FaqihMart-CRM/login`.
5. Login awal:
   - **Username:** `admin`
   - **Password:** `admin123`

> **Penting:** segera ganti password default melalui menu **Ganti Password** setelah login
> pertama. Jangan gunakan password default di lingkungan nyata.

### Upgrade dari versi sebelumnya (fitur rincian pesanan)

`database.sql` sudah memuat kolom baru, tetapi **jangan impor ulang** file itu pada database
yang sudah berisi data karena akan menghapus tabel. Jalankan sekali ALTER berikut:

```sql
ALTER TABLE `delivery_items`
  ADD COLUMN `order_details` TEXT NULL AFTER `note`,
  ADD COLUMN `receipt_file` VARCHAR(255) NULL AFTER `order_details`;
```

Setelah itu pastikan folder `uploads/receipts/` dapat ditulis oleh web server (Apache).
Folder tersebut sudah dilengkapi `.htaccess` untuk memblokir akses langsung.

## Konfigurasi

### URL

`application/config/config.php` mengatur `base_url`:

```php
$config['base_url'] = 'http://localhost/FaqihMart-CRM/';
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

Setelah instalasi, aplikasi dapat diakses di `http://localhost/FaqihMart-CRM/`. URL bersih
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

## Saran Kata Kunci Pencarian

Kolom pencarian di halaman **Pelanggan** dan panel **Tambah Tujuan** menampilkan saran
otomatis setelah admin mengetik minimal 2 karakter. Saran ditampilkan ringkas sebagai
**label tipe + kata kunci**, tanpa konteks pelanggan.

- Jenis kata kunci: Nama, WhatsApp, Alamat/Perumahan, Blok/RT-RW, dan Kode.
- Saran diurutkan: kecocokan persis, awalan, lalu yang mengandung kata kunci; maksimal 6 saran
  dan duplikat digabung.
- Alamat/perumahan yang panjang dipotong sekitar 80 karakter di sekitar kata kunci.
- Saran menghormati filter **status lokasi** dan tab **aktif/arsip** yang sedang dipakai.
  Panel Tambah Tujuan hanya mencari pelanggan aktif.
- Pencarian WhatsApp menormalkan format `08...`, `62...`, spasi, tanda hubung, titik, tanda
  plus, dan kurung. Contoh: `0812-3456 7890` tetap menemukan `62 812-3456-7890`.
- Pencarian alamat juga mencakup **Blok/Nomor** dan **RT/RW**; menulis `RT 03` atau `Blok A`
  tetap menemukan data yang tersimpan sebagai `03/05` atau `A/12`.
- Klik saran atau tekan **Enter** pada saran terpilih untuk mengisi kolom pencarian dan langsung
  mencari. Navigasi keyboard: panah atas/bawah, Enter, Escape, dan Tab.
- Di layar kecil dropdown memakai lebar penuh, tinggi opsi minimal 48 px, dan input 16 px agar
  tidak memicu zoom otomatis di iOS.
- Endpoint `GET customers/suggest` hanya dapat diakses admin. Bila JavaScript gagal dimuat,
  pencarian manual lewat tombol **Cari/Terapkan** tetap berfungsi.
- Tidak ada perubahan skema database untuk fitur ini.

## Ekspor CSV

Tombol **Ekspor CSV** pada daftar pelanggan mengikuti pencarian dan filter yang aktif.
Berkas memakai BOM UTF-8 agar terbaca benar di Excel.

## Daftar Pengiriman (Admin & Driver)

Modul ini membantu admin menyusun daftar tujuan pengiriman harian dan membagikannya ke
driver tanpa akun login.

### Alur admin

1. Buka menu **Pengiriman** → **+ Buat Daftar**.
2. Isi tanggal pengiriman, pilih sesi **Pagi**/**Sore**, beri judul bila perlu, lalu simpan
   (daftar tersimpan sebagai **draft**).
3. Pada halaman kelola, cari pelanggan (nama/WhatsApp/alamat/blok/RT-RW) di panel **Tambah
   Tujuan**; gunakan saran kata kunci bila perlu, lalu tekan **+ Tambah**. Alamat dan status
   lokasi tampil sebelum ditambahkan. Pelanggan yang sama tidak bisa ditambahkan dua kali ke
   daftar yang sama (peringatan), tetapi boleh ikut daftar/sesi lain.
4. Atur urutan tujuan dengan tombol **&uarr; / &darr;**, isi **catatan pengiriman** khusus per
   tujuan, atau hapus tujuan bila perlu.
5. Buka **Tambah rincian pesanan** pada card tujuan untuk menulis daftar produk (satu baris
   per produk) dan/atau mengunggah foto struk (JPG/PNG, maksimal 5 MB). Rincian bisa diubah
   selama daftar masih **draft** atau **dipublikasikan**.
6. Tekan **Publikasikan**. Sistem membuat **tautan akses driver** yang bisa **disalin** atau
   **dibagikan via WhatsApp**.
7. Pantau progres (**x dari y tujuan selesai**) dan tekan **Tandai Selesai** bila pengiriman
   rampung, atau **Batalkan** bila batal.

### Tautan driver

- Tautan berisi token acak panjang yang tidak mudah ditebak dan hanya berlaku untuk satu daftar.
- **Buat ulang tautan** menerbitkan token baru (tautan lama langsung mati). **Cabut akses**
  mematikan tautan tanpa membuat yang baru.
- Token tidak pernah dicatat di log. Di database disimpan **hash**-nya (untuk pencarian) dan
  salinan **terenkripsi** (agar admin dapat menyalin ulang tautan).

### Halaman driver (mobile-first, tanpa login)

Driver membuka tautan dari HP dan melihat tanggal, sesi, status daftar, nomor urut, nama,
alamat lengkap (perumahan, blok/nomor, RT/RW, patokan), **rincian pesanan**, foto struk,
catatan, tombol **Buka Google Maps**, **Salin Alamat**, **Salin rincian**, serta tombol ubah
status tujuan (**Belum / Diproses / Selesai**). Driver hanya dapat memperbarui status tujuan
pada daftar yang dibagikan — bukan mengubah rincian, data pelanggan, atau daftar lain.
Foto struk hanya bisa dibuka lewat tautan driver yang masih aktif; file aslinya tidak dapat
diakses langsung dari URL folder upload.

- Lokasi yang belum terverifikasi atau tanpa tautan Maps ditandai peringatan; aplikasi tidak
  membuat koordinat/tautan palsu.
- Daftar draft/dibatalkan atau tautan yang dicabut menampilkan pesan "tautan tidak berlaku".

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

Mulai versi ini, **foto struk pesanan disimpan di `uploads/receipts/`** (bukan di database).
Saat backup, sertakan folder tersebut bersama file SQL:

```powershell
Compress-Archive -Path backup\faqihmart_crm-YYYYMMDD.sql,uploads\receipts -DestinationPath backup\faqihmart-crm-lengkap-YYYYMMDD.zip
```

Tanpa folder `uploads/receipts`, data pesanan tetap ada tetapi tautan foto struk akan rusak.

## Struktur Proyek

```
application/
  config/        konfigurasi CI (config, database, routes, autoload)
  controllers/   Auth, Dashboard, Customers, Deliveries, Driver, Welcome
  core/          MY_Controller (gerbang autentikasi)
  helpers/       maps_helper.php (validasi Maps, label status, alamat)
                 delivery_helper.php (label sesi/status + path/MIME struk)
  models/        Admin_model, Customer_model, Delivery_model
  views/         layouts, auth, dashboard, customers, deliveries, driver
  cache/sessions sesi file-based
assets/          aset publik (primary-logo.webp, icon-logo.webp) — di luar application/
uploads/receipts/ foto struk pesanan (diblokir dari akses langsung)
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
