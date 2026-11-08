<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Servis - Singhasari Computer</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/global.css">
<link rel="stylesheet" href="assets/css/servis.css">
</head>
<body data-title="Edit Servis" data-active="dashboard" data-search="" data-page="edit" data-mode="">
<div class="app">
  <aside class="sidebar" id="sidebar"></aside>
  <main class="main">
    <header class="page-head" id="page-head"></header>
    <div class="cards3">
          <section class="panel">
      <h2>Data Pelanggan</h2>
      <div class="field"><label for="f-pelanggan">Pelanggan</label><input class="input" id="f-pelanggan" data-f="pelanggan" ></div>
      <div class="field"><label for="f-nomor">Nomor</label><input class="input" id="f-nomor" data-f="nomor" inputmode="tel" ></div>
      <div class="field"><label for="f-alamat">Alamat</label><input class="input" id="f-alamat" data-f="alamat" ></div>
    </section>

    <section class="panel">
      <h2>Data Perangkat</h2>
      <div class="row2">
        <div class="field"><label for="f-jenis">Jenis Barang</label><input class="input" id="f-jenis" data-f="jenis" ></div>
        <div class="field"><label for="f-kondisi">Kondisi Awal Unit</label>
          <select class="input input-center" id="f-kondisi" data-f="kondisi" ><option>Hidup</option><option>Mati</option></select></div>
      </div>
      <div class="field"><label for="f-spec">Merek Dan Spesifikasi</label><input class="input" id="f-spec" data-f="spec" ></div>
      <div class="field"><label for="f-kelengkapan">Kelengkapan</label><input class="input" id="f-kelengkapan" data-f="kelengkapan" ></div>
      <div class="field"><label for="f-password">Password Perangkat</label>
        <div class="input-wrap">
          <input class="input" id="f-password" data-f="password" type="password" >
          <button type="button" class="eye" id="toggle-pass" aria-label="Tampilkan atau sembunyikan password">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <div class="row2">
        <div class="field"><label>Teknisi</label><div class="input tech"><span class="avatar-sm">IL</span><span data-f-text="teknisi">Ilham</span></div></div>
        <div class="field"><label>Persetujuan Bongkar</label>
          <label class="check-box" id="bongkar-box"><input type="checkbox" id="f-bongkar" data-f="bongkar" ><span class="tick"></span>Setuju Dibongkar</label></div>
      </div>
      <div class="field"><label for="f-layanan">Layanan</label>
        <select class="input input-center w-120" id="f-layanan" data-f="layanan" ><option>Express</option><option>Reguler</option></select></div>
    </section>

      <section class="panel">
        <div class="panel-title"><h2>Edit Tracking</h2><span class="badge" id="track-badge"></span></div>
        <div class="field"><label for="f-keluhan">Keluhan</label><input class="input" id="f-keluhan" data-f="keluhan"></div>
        <div class="field"><label for="f-catatan">Catatan Teknisi</label><input class="input" id="f-catatan" data-f="catatan" placeholder="Catatan bila ada kerusakan tambahan"></div>
        <div class="row2">
          <div class="field"><label for="f-estimasi">Estimasi</label><input class="input" type="date" id="f-estimasi" data-f="estimasi"></div>
          <div class="field"><label for="f-status">Ubah Status</label>
            <select class="input" id="f-status" data-f="status">
              <option>Diterima</option><option>Menunggu</option><option>Dikerjakan</option><option>Selesai</option><option>Dibatalkan</option>
            </select></div>
        </div>
        <div class="field"><label>Persetujuan Pelanggan</label>
          <div class="row2">
            <label class="check-box"><input type="radio" name="persetujuan" value="setuju" id="p-setuju"><span class="tick"></span>Setuju Dibongkar</label>
            <label class="check-box"><input type="radio" name="persetujuan" value="tidak" id="p-tidak"><span class="tick"></span>Tidak Setuju</label>
          </div>
        </div>
        <div class="part-head"><label>Penggunaan Sparepart</label>
          <a class="btn btn-outline btn-sm" id="btn-add-part" href="tambah_sparepart.html">+ Tambah Sparepart</a></div>
        <div id="part-list" class="part-list"></div>
        <div class="total"><span>Total Biaya :</span><strong id="total">Rp. 0</strong></div>
      </section>
    </div>
    <div class="form-actions">
      <a class="btn btn-outline btn-lg" id="btn-batal" href="dashboard.html">Batal</a>
      <button class="btn btn-primary btn-lg" id="btn-save" type="button">Simpan Perubahan</button>
    </div>

  </main>
</div>
<div id="toasts" class="toasts" aria-live="polite"></div>
<script src="assets/js/global.js"></script>
<script src="assets/js/data.js"></script>
<script src="assets/js/layout.js"></script>
<script src="assets/js/servis.js"></script>
</body>
</html>