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
| 7 | Pencarian & filter | Done | Nama/WhatsApp/alamat + filter status + tab arsip |
| 8 | Validasi server & input sticky | Done | Koordinat berpasangan + rentang, URL Maps, field wajib |
| 9 | Peringatan WhatsApp duplikat | Done | Peringatan + tombol "Simpan Tetap" (boleh dilanjutkan) |
| 10 | Status lokasi otomatis | Done | incomplete/unverified/verified; verified butuh bukti lokasi |
| 11 | Google Maps + Salin Alamat | Done | `rel="noopener noreferrer"`, koordinat diprioritaskan |
| 12 | Ekspor CSV | Done | Mengikuti filter aktif, BOM UTF-8, aman dari CSV injection |
| 13 | UI/UX Tailwind (CDN) | Done | Hijau #16A34A, sidebar desktop, nav ringkas HP, empty/loading/konfirmasi |
| 14 | Dokumentasi (README, backup/restore) | Done | Instalasi, konfigurasi, login awal, backup/restore |
| 15 | Perlindungan seluruh halaman | Done | `Welcome` juga dialihkan lewat `MY_Controller` |
| 16 | Ikon Maps + simpan tautan realtime di kolom daftar | Done | Kolom "Peta": ikon buka Maps + editor inline AJAX (`customers/{id}/maps`) |

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
