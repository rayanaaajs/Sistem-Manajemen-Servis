# 02 — Tech Stack & Aturan Teknis

## Stack (WAJIB)
- Bahasa: PHP Native 8.x (tanpa framework backend)
- Database: PostgreSQL 15 lewat PDO (driver `pgsql`)
- UI: HTML + Tailwind CSS (dibuild jadi satu file `assets/css/app.css`)
- JavaScript: vanilla (tanpa jQuery/React/Vue)
- Dilarang: framework backend (Laravel, CodeIgniter, dll), ORM, React/Vue, library JS berat

> Catatan setup: 
> - Extension `pdo_pgsql` dan `pgsql` harus aktif di `php.ini`.
> - Jalankan saat development: `php -S localhost:8000` dari folder project.
> - Tailwind dipakai lewat file hasil build (bukan CDN) supaya demo tetap jalan tanpa internet.

## Struktur folder
```
Sistem-Manajemen-Servis/
├── config/
│   ├── db.php               # koneksi PDO
│   └── secret.php           # kunci enkripsi (JANGAN di-commit)
├── includes/
│   ├── auth.php             # session, cek login, require_role()
│   ├── functions.php        # helper: e(), rupiah(), flash(), csrf, normalisasi no WA
│   ├── header.php           # <head>, navbar, sidebar sesuai role
│   └── footer.php
├── admin/                   # halaman Admin/Kepala Teknisi
├── teknisi/                 # halaman Teknisi
├── pelanggan/               # tracking pelanggan
├── assets/
│   ├── css/app.css          # hasil build Tailwind
│   ├── js/
│   └── img/                 # logo & ikon dari Figma
├── database/                # database.sql, seed.sql, objek_database.sql
├── docs/
├── index.php                # login karyawan
└── logout.php
```

## Koneksi database
Pakai PDO dengan mode exception dan `FETCH_ASSOC`. Prepared statement asli (`ATTR_EMULATE_PREPARES` = false). Kredensial disimpan di `config/config.local.php` yang masuk `.gitignore`, tidak boleh ditulis langsung di file yang di-commit.


## Keamanan (wajib di semua halaman)
1. Semua query yang membawa input user pakai prepared statement. Dilarang menggabung string SQL dari input.
2. Semua output ke HTML lewat `htmlspecialchars()` (helper `e()`).
3. Cek session + role di baris paling atas setiap halaman.
4. Semua form POST pakai CSRF token.
5. Login karyawan pakai `password_verify()`, lalu panggil `session_regenerate_id(true)`. Password karyawan disimpan dengan `password_hash()`.
6. `servis.password_perangkat` dienkripsi (`openssl_encrypt`, AES-256), bukan di-hash, karena teknisi perlu membacanya. Kunci di `config/secret.php`. Kalau dekripsi gagal (data demo masih teks biasa), tampilkan nilai apa adanya.
7. Login pelanggan: pesan error generik, jangan membocorkan nota mana yang ada.
8. Jangan tampilkan error PDO mentah ke user. Pengecualian: pesan dari function database yang memakai `RAISE EXCEPTION`.

## Gaya kode
- Satu halaman = satu file PHP. Logika bersama ditaruh di `includes/functions.php`.
- Bagian atas file untuk proses POST dan query, bagian bawah untuk HTML.
- Setelah POST berhasil: redirect + flash message (pola PRG).
- Format rupiah `Rp 1.250.000`, tanggal `d M Y H:i` zona Asia/Jakarta.
- Komentar kode bahasa Indonesia, singkat. Hindari kode yang terlalu "pintar" karena harus bisa kamu jelaskan saat sidang.