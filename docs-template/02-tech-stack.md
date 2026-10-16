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
[nama-project]/
├──
├──
└──
```

## Koneksi database
<!-- Cara koneksi (PDO/dll), lokasi kredensial, apa yang tidak boleh di-commit -->


## Keamanan (wajib di semua halaman)
<!-- Satu aturan per nomor. Contoh topik: query, output HTML, cek role, CSRF, hash password, enkripsi data sensitif, pesan error -->
1.
2.
3.
4.
5.

## Gaya kode
<!-- Konvensi penamaan, struktur file, format tanggal/uang, bahasa komentar -->
-
-
-
