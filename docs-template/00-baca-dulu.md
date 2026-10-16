# 00 — Baca Dulu (Aturan Main untuk AI)

Proyek: **[PASTI-Pusat Manajemen Antrian Servis dan Teknologi Informasi]** — [website pencatatan nota servis dan tracking progres perbaikan (ala ekspedisi) untuk Singosari Computer, dipakai oleh Admin/Kepala Teknisi, Teknisi, dan Pelanggan.
]

## Urutan baca
1. `00-baca-dulu.md` — aturan main (file ini)
2. `01-overview.md` — sistem ini apa, siapa penggunanya, alur bisnis
3. `02-tech-stack.md` — teknologi wajib, struktur folder, aturan keamanan
4. `03-database.md` — tabel, kolom, relasi, view/function yang sudah ada
5. `04-business-rules.md` — aturan yang tidak kelihatan dari database (alur status, stok, biaya)
6. `05-features-per-role.md` — daftar fitur tiap role
7. `06-ui-flow.md` — daftar halaman dan hubungannya dengan desain Figma
8. `07-progress.md` — status pengerjaan terakhir (baca ini kalau lanjut sesi baru)
9. `design/` — screenshot desain Figma dan `tokens.md` (warna, font)

## Rules Utama
1. Stack wajib: PHP Native 8.x, PostgreSQL (lewat PDO), HTML, Tailwind CSS, JavaScript (vanilla). Dilarang: framework backend (Laravel, CodeIgniter, dll), ORM, React/Vue.
2. Sumber kebenaran database adalah `database/database.sql`. Jangan menambah/mengubah tabel atau kolom tanpa konfirmasi saya.
3. Logika yang sudah ada di database dan harus dipakai (bukan ditulis ulang):
   - ubah status servis → `fn_update_status`
   - total tagihan → `fn_total_tagihan`
   - daftar servis → view `v_daftar_servis`
   - timeline tracking → view `v_timeline_servis`
   - laporan bulanan → materialized view `mv_laporan_bulanan`
4. Kerjakan satu tahap per tugas sesuai `07-progress.md`. Jangan mengubah file di luar tugas, dan sebutkan file apa saja yang dibuat/diubah.
5. Keamanan wajib: semua query pakai prepared statement, semua output HTML lewat `htmlspecialchars()`, cek login + role di baris paling atas tiap halaman, dan CSRF token di semua form POST.
6. Kalau dokumen bertentangan atau ada hal yang tidak jelas, tanya saya dulu, jangan menebak. Prioritas dokumen kalau bentrok: `database.sql` > `03-database.md` > `04-business-rules.md` > dokumen lain.
7. Bahasa: tampilan website bahasa Indonesia; komentar kode bahasa Indonesia dan singkat; nama variabel mengikuti nama kolom database.
8. Setelah selesai tiap tahap: jelaskan cara menjalankan dan mengetesnya, lalu update `07-progress.md`.
