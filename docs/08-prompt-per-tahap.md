# 08 — Prompt Siap Pakai per Tahap

<!-- Fungsi: kumpulan prompt yang tinggal tempel. Satu prompt = satu tahap di 07-progress.md. -->

Cara pakai: satu prompt = satu tahap → jalankan & cek manual → commit Git → lanjut.

## Prompt pembuka (tiap sesi baru)
```
[Perintah baca docs/, ringkas dulu, jangan tulis kode sebelum disetujui]
```

## T1 — [Nama tahap]
```
[Kerjakan tahap ... Buat file ... Aturan khusus ... Jangan ... Jelaskan cara menjalankannya.]
```

## T2 — [Nama tahap]
```
[...]
```

## T3 — [Nama tahap]
```
[...]
```

<!-- Salin blok di atas untuk tiap tahap -->

## Prompt revisi tampilan
```
Bandingkan halaman [file] dengan docs/design/[gambar].png.
Yang mau diperbaiki: (1) [...], (2) [...]. Ubah hanya file itu dan custom.css.
```

## Kalau AI melenceng
- [Gejala] → "[kalimat koreksi singkat yang menunjuk aturan di docs]"
- [Gejala] → "[...]"
- Error → tempel pesan error **utuh** + nama file & baris.
