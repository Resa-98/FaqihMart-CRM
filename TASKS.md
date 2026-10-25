# TASKS - CRM Faqih Mart

Status: **Done**, **In Progress**, **Todo**, **Blocked**.
Ruang lingkup: MVP sesuai spesifikasi. Driver tidak memiliki akun/akses CRM.

## Progres keseluruhan

MVP selesai dan lolos uji manual dasar (lihat bagian Verifikasi). Tidak ada automated test
sesuai permintaan.

## Daftar Tugas

| # | Tugas | Status | Catatan |
|---|-------|--------|---------|
| 1 | Perbaiki konfigurasi dasar (`base_url`, session, CSRF, autoload) | Done | CSRF aktif; `cookie_httponly` aktif; session files di `application/cache/sessions` |
| 2 | Skema database (`database.sql`) | Done | InnoDB, utf8mb4, CHECK koordinat, index pencarian, admin awal |
| 3 | Login/logout + ganti password | Done | bcrypt, session regenerate saat login, tanpa registrasi publik |
| 4 | Gerbang autentikasi (`MY_Controller`) | Done | Semua controller internal extends `MY_Controller` |
| 5 | Dashboard + statistik | Done | Aktif/verified/unverified/incomplete + pelanggan terbaru |
| 6 | CRUD pelanggan | Done | Tambah, detail, edit, arsipkan, pulihkan |
| 7 | Pencarian & filter | Done | Nama/WhatsApp/alamat/blok/RT-RW + filter status + tab arsip |
| 8 | Validasi server & input sticky | Done | Koordinat berpasangan + rentang, URL Maps, field wajib |
| 9 | Peringatan WhatsApp duplikat | Done | Peringatan + tombol "Simpan Tetap" (boleh dilanjutkan) |
| 10 | Status lokasi otomatis | Done | incomplete/unverified/verified; verified butuh bukti lokasi |
| 11 | Google Maps + Salin Alamat | Done | `rel="noopener noreferrer"`, koordinat diprioritaskan |
| 12 | Ekspor CSV | Done | Mengikuti filter aktif, BOM UTF-8, aman dari CSV injection |
| 13 | UI/UX Tailwind (CDN) | Done | Hijau #16A34A, sidebar desktop, nav ringkas HP, empty/loading/konfirmasi |
| 14 | Dokumentasi (README, backup/restore) | Done | Instalasi, konfigurasi, login awal, backup/restore |
| 15 | Perlindungan seluruh halaman | Done | `Welcome` juga dialihkan lewat `MY_Controller` |
| 16 | Ikon Maps + simpan tautan realtime di kolom daftar | Done | Kolom "Peta": ikon buka Maps + editor inline AJAX (`customers/{id}/maps`) |
| 17 | Skema pengiriman (`delivery_lists`, `delivery_items`) | Done | FK ke `customers`/`admins`; UNIQUE(list,customer); token hash + terenkripsi |
| 18 | Model pengiriman (`Delivery_model`) | Done | CRUD daftar/tujuan, urutan, status, token, progres & statistik |
| 19 | Dashboard & daftar pengiriman (admin) | Done | Ringkasan hari ini (pagi/sore, belum/selesai) + filter tanggal/sesi/status |
| 20 | Pembuatan daftar & pemilihan pelanggan | Done | Tanggal, sesi, judul; cari pelanggan aktif + Tambah; cegah duplikat dalam daftar |
| 21 | Kelola tujuan | Done | Urutkan naik/turun, catatan per tujuan, hapus; terkunci saat selesai/dibatalkan |
| 22 | Publikasi & tautan driver | Done | Publikasi menghasilkan token; salin/Bagikan WhatsApp; regenerate & cabut akses |
| 23 | Halaman driver (mobile-first, tanpa login) | Done | Alamat, patokan, catatan, Buka Maps, Salin Alamat, ubah status, progres |
| 24 | Keamanan pengiriman | Done | Token acak 64 hex disimpan sebagai hash; akses dicabut langsung ditolak; CSRF semua POST |
| 25 | Rincian pesanan & foto struk per tujuan | Done | Teks multiline + foto JPG/PNG maks. 5 MB; tampil di admin & driver; file privat di `uploads/receipts` disajikan lewat sesi/token |
| 26 | Saran kata kunci pencarian | Done | `GET customers/suggest` (admin) + autocomplete ringkas di Pelanggan & Tambah Tujuan; tipe nama/WA/alamat-perumahan/blok-RT-RW/kode, maksimal 6 saran; hormati filter aktif/arsip/status; tanpa perubahan skema |

## Verifikasi Manual

Diuji melalui Apache + MySQL lokal:

- Login benar → redirect dashboard; login salah → pesan umum.
- Akses tanpa login ke halaman internal → redirect ke login.
- Tambah pelanggan lengkap → tersimpan dengan kode `FM-00001`, status `verified`.
- Tanpa "Alamat lengkap" → status `incomplete`.
- WhatsApp duplikat → muncul peringatan; tanpa konfirmasi tidak tersimpan; dengan
  "Simpan Tetap" tersimpan.
- Tautan Maps tidak valid / latitude tanpa longitude → ditolak dengan pesan.
- Menandai terverifikasi tanpa koordinat/tautan → ditolak.
- Pencarian & filter status berfungsi.
- Arsip → hilang dari daftar aktif, muncul di tab arsip, pulihkan berfungsi.
- Ekspor CSV berisi data sesuai filter.
- Ikon Peta membuka Google Maps; simpan tautan lewat editor inline memvalidasi URL, memperbarui
  baris tanpa reload, dan menolak URL non-Maps (422).
- Google Maps membuka tautan dengan `rel="noopener noreferrer"`.
- 404 untuk pelanggan tidak ada; endpoint arsip hanya menerima POST.
- Tidak ada error/deprecation tampil pada halaman.
- Pengiriman: buat daftar (tanggal+sesi) → tambah 3 pelanggan → duplikat ditolak dengan
  peringatan → catatan tersimpan → urutan naik/turun berubah.
- Publikasi menghasilkan token 64 hex; hash tersimpan; halaman driver tampil (nama, alamat,
  patokan, Maps untuk yang punya lokasi, peringatan untuk yang belum terverifikasi).
- Driver ubah status tujuan → progres "1 dari 3 selesai" benar; completed_at terisi.
- Daftar selesai → halaman driver tetap tampil read-only, perubahan status ditolak.
- Batalkan/draft → tautan driver 404; cabut akses → tautan lama 404.
- Regenerate → tautan baru aktif, tautan lama mati; token acak 404.
- Status driver tanpa token CSRF → 403; endpoint POST-only via GET → 404.
- Statistik dashboard pengiriman (Pagi/Sore/Belum/Selesai) sesuai data.
- Rincian pesanan tersimpan per tujuan dan tampil di admin serta halaman driver; line break
  dipertahankan.
- Foto struk JPG/PNG tersimpan dengan nama acak; thumbnail dan tombol lihat struk berfungsi di
  admin (termasuk saat draft) dan driver; akses langsung ke `uploads/receipts/...` ditolak 403.
- Ganti struk menghapus file lama; hapus struk menghapus file; hapus tujuan ikut menghapus file.
- Upload file bukan gambar/ekstensi palsu/lebih dari 5 MB ditolak dengan pesan ramah; teks
  rincian yang sudah diisi tetap tersimpan.
- Daftar selesai membaca rincian/struk secara read-only; tautan dicabut/di-regenerate membuat
  URL struk lama 404.
- Saran pencarian muncul di halaman Pelanggan dan Tambah Tujuan; minimal 2 karakter, debounce
  ±250 ms, maksimal 6 saran, dan duplikat digabung.
- Saran nama/WA/alamat-perumahan/blok-RT-RW/kode tampil ringkas sebagai label tipe + kata kunci
  (tanpa konteks pelanggan); urutan exact → prefix → contains.
- Saran menghormati tab aktif/arsip dan filter status; panel Tambah Tujuan hanya data aktif.
- WA `08...` dan `62...` saling menemukan meski ada spasi/tanda hubung; `RT 03`, `Blok Q`, dan
  angka blok/RT menemukan data terkait.
- Klik saran dan Enter pada saran mengisi kolom lalu langsung mencari; panah atas/bawah,
  Escape, dan Tab bekerja; atribut combobox/listbox/`aria-activedescendant` terpasang.
- Uji mobile 390 px: dropdown selebar kolom, opsi ≥48 px, input 16 px, tanpa scroll horizontal;
  uji desktop juga berfungsi.
- Endpoint `customers/suggest` tanpa login → redirect; wildcard `%`/`_` tidak membocorkan data;
  nama berisi HTML di-escape dan ditampilkan sebagai teks.

## Masalah / Catatan Terbuka

- **PHP 8.2 + CodeIgniter 3.1.13**: framework memunculkan `E_DEPRECATED`
  ("Creation of dynamic property"). Sudah disiasati di `index.php` (development) dengan
  mengecualikan `E_DEPRECATED`/`E_USER_DEPRECATED`; `system/` tidak diubah.
- **Belum ada automated test** (sesuai permintaan). Bila diinginkan, dapat ditambahkan
  kemudian dengan PHPUnit + `tests/`.
- **Registrasi admin**: hanya satu admin awal dari `database.sql`; belum ada UI kelola admin
  (di luar MVP).
- **Peta tersemat**: detail pelanggan belum menampilkan peta inline, hanya tautan ke Google
  Maps (sesuai kebutuhan MVP).
