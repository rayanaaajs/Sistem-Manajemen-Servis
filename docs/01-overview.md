# 01 — Overview

## Gambaran sistem
PASTI adalah website untuk mencatat nota servis di Singosari Computer (toko servis komputer, laptop, dan printer) dan menampilkan progres perbaikannya seperti tracking ekspedisi. Pelanggan bisa memantau status barangnya sendiri tanpa harus bertanya ke toko.


### Masalah yang diselesaikan
- Pencatatan servis masih manual (nota kertas), jadi data mudah hilang dan sulit dicari.
- Status perbaikan sulit dikomunikasikan, sering terjadi miskomunikasi antara toko dan pelanggan.
- Antrean barang servis menumpuk dan tidak terorganisir.
- Pelanggan tidak bisa melacak progres servis secara mandiri.

## Role
| Role | Jumlah akun | Cara login | Hak akses |
|---|---|---|---|
| Admin / Kepala Teknisi | 1 akun | username + password | Semua urusan operasional: kelola pelanggan, nota, karyawan, sparepart, laporan, serta validasi pembayaran dan pengambilan barang. Boleh mengubah status servis mana pun. |
| Teknisi | Beberapa akun | username + password | Hanya servis yang ditugaskan kepadanya: lihat antrean, update status, catatan temuan, persetujuan, dan catat sparepart yang dipakai. |
| Pelanggan | Tanpa akun | no. nota + no. WhatsApp | Hanya melihat (read-only): lacak status dan lihat riwayat servis miliknya. |

> Catatan role: Owner tidak punya role atau dashboard sendiri. Pemilik toko menyerahkan semua urusan servis ke Admin/Kepala Teknisi, jadi fitur laporan, statistik, dan kelola karyawan digabung ke role Admin. Teknisi dibuatkan akun oleh admin. Akun admin di set dari awal

## Alur bisnis
1. Pelanggan datang membawa barang, lalu Admin membuat nota (data pelanggan, barang, kelengkapan, keluhan). Nomor nota dibuat otomatis (SC-001, SC-002, dst).
2. Admin menugaskan Teknisi, status berubah menjadi Sedang Dicek.
3. Teknisi cek fisik dan diagnosa,jika ada kerusakan yang membutuhkan sparepart akan meminta persetujuan pelanggan (Menunggu Persetujuan).
4. Jika disetujui, status menjadi Proses Servis dan teknisi mencatat sparepart yang dipakai (kalau ada).
5. Setelah selesai, status menjadi Selesai. Admin mencatat pembayaran dan pengambilan barang.
6. Sepanjang proses, pelanggan memantau status lewat halaman tracking

## Skala data demo
- 1 admin, 5 teknisi, 70 pelanggan, 25 sparepart, 120 nota servis (lihat `database/seed.sql`).