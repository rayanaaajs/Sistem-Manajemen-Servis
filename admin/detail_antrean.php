<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (strtolower((string) ($_SESSION['role'] ?? '')) !== 'admin') {
  http_response_code(403);
  exit('Akses ditolak.');
}

$dariRiwayat = ($_GET['from'] ?? '') === 'riwayat';
$urlKembali = $dariRiwayat ? 'riwayat.php' : 'antrean.php';

$kembaliDenganPesan = static function (string $pesan) use ($urlKembali): void {
  $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE;
  echo '<script>alert(' . json_encode($pesan, $flags) . '); window.location.href=' . json_encode($urlKembali, $flags) . ';</script>';
  exit;
};

$no_nota = is_string($_GET['nota'] ?? null) ? trim($_GET['nota']) : '';

if ($no_nota === '' || strlen($no_nota) > 10) {
  $kembaliDenganPesan('Nomor nota tidak valid!');
}

$data = null;
$total_sparepart = 0;
$daftar_sparepart_terpakai = [];

try {
  $stmt = $pdo->prepare("
        SELECT 
            s.*,
            p.nama_pelanggan,
            p.no_hp,
            p.alamat,
            k.nama_karyawan,
            v.status_pembayaran,
            v.status_pengambilan
        FROM servis s
        LEFT JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
        LEFT JOIN karyawan k ON s.id_karyawan = k.id_karyawan
        LEFT JOIN validasi v ON s.no_nota = v.no_nota
        WHERE s.no_nota = :nota
        LIMIT 1
    ");
  $stmt->execute(['nota' => $no_nota]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$data) {
    $kembaliDenganPesan('Data nota tidak ditemukan di database!');
  }

  $stmtSp = $pdo->prepare("
        SELECT ds.*, sp.*
        FROM detail_servis ds
        JOIN sparepart sp ON ds.id_sparepart = sp.id_sparepart
        WHERE ds.no_nota = :nota
    ");
  $stmtSp->execute(['nota' => $no_nota]);
  $rawSp = $stmtSp->fetchAll(PDO::FETCH_ASSOC);

  foreach ($rawSp as $sp) {
    $qty   = (int)($sp['jumlah'] ?? $sp['qty'] ?? 1);
    $harga = (int)($sp['harga_jual'] ?? $sp['harga'] ?? $sp['harga_satuan'] ?? 0);
    $sub   = (int)($sp['subtotal'] ?? ($qty * $harga));
    $namaSp = $sp['nama_sparepart'] ?? $sp['nama_barang'] ?? 'Sparepart';

    $daftar_sparepart_terpakai[] = [
      'nama_sparepart' => $namaSp,
      'jumlah'         => $qty,
      'subtotal'       => $sub
    ];
    $total_sparepart += $sub;
  }
} catch (\Throwable $e) {
  error_log('detail_antrean.php: ' . $e->getMessage());
  $kembaliDenganPesan('Gagal memuat detail servis.');
}

// Ambil riwayat yang sama dengan halaman teknisi, berdasarkan nomor nota.
$riwayat = [];
$errorRiwayat = '';

try {
    $stmtRiwayat = $pdo->prepare("
        SELECT
            t.status_pengerjaan,
            t.keterangan,
            t.waktu_update,
            COALESCE(k.nama_karyawan, 'Sistem') AS nama_karyawan
        FROM tracking_servis AS t
        LEFT JOIN karyawan AS k ON k.id_karyawan = t.id_karyawan
        WHERE t.no_nota = :nota
        ORDER BY t.waktu_update ASC, t.id_tracking ASC
    ");
    $stmtRiwayat->execute(['nota' => $no_nota]);
    $riwayat = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('detail_antrean.php (tracking): ' . $e->getMessage());
    $errorRiwayat = 'Gagal memuat riwayat perubahan.';
}

$kondisi_val     = $data['kondisi_awal_unit'] ?: 'Hidup';
$layanan_val     = $data['jenis_layanan'] ?: 'Reguler';
$persetujuan_val = (stripos((string)($data['persetujuan_bongkar'] ?? ''), 'tidak') !== false) ? 'tidak_setuju' : 'setuju';
$biaya_jasa_val  = (int)($data['biaya_jasa'] ?? 0);
$biaya_exp_val   = ($layanan_val === 'Express') ? 50000 : 0;
$total_biaya_val = $biaya_jasa_val + $biaya_exp_val + $total_sparepart;

$nama_teknisi = $data['nama_karyawan'] ?? '-';
$partsTek     = preg_split('/\s+/', trim($nama_teknisi));
$inisialTek   = !empty($partsTek[0]) ? strtoupper(substr($partsTek[0], 0, 2)) : 'TK';
if (count($partsTek) > 1 && !empty($partsTek[1])) {
  $inisialTek = strtoupper($partsTek[0][0] . $partsTek[1][0]);
}

$page_title = $dariRiwayat ? 'Detail Riwayat' : 'Detail Antrean';
$active_menu = $dariRiwayat ? 'riwayat' : 'antrean';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

$status_val = (string) ($data['status_pengerjaan'] ?? 'Menunggu');
$status_tampil_detail = match (strtolower(trim($status_val))) {
  'proses servis' => 'Dikerjakan',
  'sedang dicek'  => 'Diterima',
  default         => trim($status_val),
};
?>

<!-- HEADER INFO NOTA (READ-ONLY) -->
<div class="mb-4 flex flex-wrap items-center justify-between gap-2 bg-white-light px-6 py-4 rounded-2xl border border-white-hover/60">
  <div class="flex items-center gap-3">
    <span class="text-xs font-semibold text-white-dark-hover uppercase tracking-wider">Nomor Nota</span>
    <span class="px-3.5 py-1 rounded-full bg-tosca-light text-tosca-dark text-sm font-bold"><?= e($data['no_nota']) ?></span>
  </div>
  <span class="text-xs font-medium text-white-dark-hover">
    Status Pengerjaan: <?= badgeStatus($status_tampil_detail) ?>
  </span>
</div>

<!-- TAMPILAN 3 KOLOM READ-ONLY -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

  <!-- KOLOM 1: DATA PELANGGAN + RIWAYAT PERUBAHAN -->
  <div class="flex min-w-0 flex-col gap-4">
    <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
      <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
        Data Pelanggan
      </h2>

      <div class="space-y-5 lg:space-y-6">
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Pelanggan</label>
          <input
            type="text"
            value="<?= e($data['nama_pelanggan'] ?? '-') ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
          <input
            type="text"
            value="<?= e($data['no_hp'] ?? '-') ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
          <input
            type="text"
            value="<?= e($data['alamat'] ?? '-') ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>
      </div>
    </div>

    <!-- RIWAYAT PERUBAHAN -->
    <section class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
      <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
        Riwayat Perubahan
      </h2>
      <?php if ($errorRiwayat !== ''): ?>
        <p class="rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500">
          <?= e($errorRiwayat) ?>
        </p>
      <?php elseif (empty($riwayat)): ?>
        <p class="rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500">
          Belum ada riwayat perubahan status.
        </p>
      <?php else: ?>
        <ol class="space-y-0">
          <?php foreach ($riwayat as $i => $item): ?>
            <?php
            $terakhir = $i === array_key_last($riwayat);
            ?>
            <li class="relative flex gap-4 pb-7 last:pb-0">
              <!-- GARIS TIMELINE -->
              <?php if (!$terakhir): ?>
                <span
                  class="absolute left-3 top-7 bottom-0 w-px bg-tosca-dark"
                  aria-hidden="true"></span>
              <?php endif; ?>
              <!-- ICON TIMELINE -->
              <span
                class="relative z-10 flex h-6 w-6 shrink-0 items-center justify-center rounded-full <?= $terakhir ? 'bg-orange text-white-light ring-4 ring-orange/20' : 'bg-[#167D88] text-white-light' ?>">
                <?php if ($terakhir): ?>
                  <span class="h-2 w-2 rounded-full bg-white-light"></span>
                <?php else: ?>
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
                    class="h-4 w-4"
                    aria-hidden="true">
                    <path
                      d="m5 12 4 4L19 6"
                      stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                <?php endif; ?>
              </span>
              <!-- INFORMASI TRACKING -->
              <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-blue-darker">
                  <?= e((string) $item['status_pengerjaan']) ?>
                </p>
                <p class="mt-1 break-words text-xs leading-relaxed text-gray-500">
                  <?= e((string) (
                    $item['keterangan']
                    ?: 'Pembaruan status servis'
                  )) ?>
                </p>
                <p class="mt-1 text-[11px] text-gray-400">
                  <?= e(date(
                    'd M Y, H:i',
                    strtotime($item['waktu_update'])
                  )) ?>
                  · <?= e((string) $item['nama_karyawan']) ?>
                </p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </section>
  </div>

  <!-- KOLOM 2: DATA PERANGKAT + RINCIAN BIAYA DI BAWAHNYA (READ-ONLY) -->
  <div class="flex flex-col gap-4">
    <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
      <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
        Data Perangkat
      </h2>

      <div class="space-y-5 lg:space-y-6">
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Barang</label>
          <input
            type="text"
            value="<?= e($data['jenis_barang'] ?? '-') ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Merek dan Spesifikasi</label>
          <input
            type="text"
            value="<?= e($data['merek_dan_spesifikasi'] ?? '-') ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Kelengkapan</label>
          <input
            type="text"
            value="<?= e($data['kelengkapan_barang'] ?: '-') ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Kondisi Awal Unit</label>
          <div class="grid grid-cols-3 gap-2.5">
            <?php foreach (['Hidup', 'Mati', 'Blank'] as $optKondisi): ?>
              <?php $isAktif = (strcasecmp($kondisi_val, $optKondisi) === 0); ?>
              <div class="py-3 px-4 rounded-2xl text-sm text-center select-none <?= $isAktif ? 'bg-tosca-dark text-white-light font-semibold shadow-xs' : 'border border-white-active bg-white-light-hover text-white-dark-hover font-medium' ?>">
                <?= $optKondisi ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Password Perangkat</label>
          <div class="relative">
            <input
              type="password"
              id="passwordPerangkatDetail"
              value="<?= e($data['password_perangkat'] ?? '') ?>"
              placeholder="Tidak ada password"
              readonly
              class="w-full pl-4 pr-11 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm text-blue-darker focus:outline-none cursor-default" />
            <?php if (!empty($data['password_perangkat'])): ?>
              <button
                type="button"
                id="togglePasswordDetailBtn"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-white-dark-hover hover:text-blue-darker cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- KARTU RINCIAN BIAYA DI BAWAH DATA PERANGKAT -->
    <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
      <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-5">
        Rincian Biaya
      </h2>

      <div class="rounded-2xl bg-white-light-hover border border-white-hover p-4 space-y-2.5 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-white-dark-hover font-medium">Biaya Jasa Servis</span>
          <span class="font-semibold text-blue-darker">
            Rp <?= number_format($biaya_jasa_val, 0, ',', '.') ?>
          </span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-white-dark-hover font-medium">Biaya Layanan Express</span>
          <span class="font-semibold text-blue-darker">
            Rp <?= number_format($biaya_exp_val, 0, ',', '.') ?>
          </span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-white-dark-hover font-medium">
            Sparepart Terpakai (<?= count($daftar_sparepart_terpakai) ?> item)
          </span>
          <span class="font-semibold text-blue-darker">
            Rp <?= number_format($total_sparepart, 0, ',', '.') ?>
          </span>
        </div>

        <?php if (!empty($daftar_sparepart_terpakai)): ?>
          <div class="pt-1 pl-2 border-l-2 border-tosca-light-active space-y-1 text-xs text-white-dark-hover">
            <?php foreach ($daftar_sparepart_terpakai as $sp): ?>
              <div class="flex justify-between">
                <span><?= e($sp['nama_sparepart']) ?> (x<?= (int)$sp['jumlah'] ?>)</span>
                <span>Rp <?= number_format((int)$sp['subtotal'], 0, ',', '.') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="border-t border-white-hover pt-2.5 flex items-center justify-between">
          <span class="font-bold text-blue-darker">Total Biaya</span>
          <span class="text-base font-bold text-orange-active">
            Rp <?= number_format($total_biaya_val, 0, ',', '.') ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- KOLOM 3: KELUHAN DAN PENANGANAN (READ-ONLY) -->
  <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60 flex flex-col justify-between">
    <div class="space-y-5 lg:space-y-6">
      <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
        Keluhan dan Penanganan
      </h2>

      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Keluhan Kerusakan</label>
        <input
          type="text"
          value="<?= e($data['keluhan_kerusakan'] ?? '-') ?>"
          readonly
          class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
      </div>

      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Catatan Cek Fisik</label>
        <input
          type="text"
          value="<?= e($data['catatan_cek_fisik'] ?: '-') ?>"
          readonly
          class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
      </div>

      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Teknisi Penanggung Jawab</label>
        <div class="flex items-center gap-2.5 w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker">
          <div class="w-6 h-6 rounded-full bg-orange-light-active text-orange text-[10px] font-bold flex items-center justify-center uppercase">
            <?= e($inisialTek) ?>
          </div>
          <span><?= e($nama_teknisi) ?></span>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Servis Masuk</label>
          <input
            type="text"
            value="<?= !empty($data['tanggal_masuk']) ? date('d M Y', strtotime($data['tanggal_masuk'])) : '-' ?>"
            readonly
            class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>

        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Estimasi Selesai</label>
          <input
            type="text"
            value="<?= !empty($data['estimasi_pengambilan']) ? date('d M Y', strtotime($data['estimasi_pengambilan'])) : '-' ?>"
            readonly
            class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-blue-darker focus:outline-none cursor-default" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Layanan</label>
        <div class="grid grid-cols-2 gap-3">
          <?php foreach (['Reguler', 'Express'] as $optLayanan): ?>
            <?php $isLayAktif = (strcasecmp($layanan_val, $optLayanan) === 0); ?>
            <div class="py-3 px-4 rounded-2xl text-sm text-center select-none <?= $isLayAktif ? 'bg-tosca-dark text-white-light font-semibold shadow-xs' : 'border border-white-active bg-white-light-hover text-white-dark-hover font-medium' ?>">
              <?= $optLayanan ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Persetujuan Bongkar</label>
        <div class="grid grid-cols-2 gap-3">
          <div class="flex items-center gap-2.5 px-4 py-3 rounded-2xl border bg-white-light-hover select-none <?= $persetujuan_val === 'setuju' ? 'border-blue-darker/60' : 'border-white-active opacity-60' ?>">
            <div class="w-5 h-5 rounded-md flex items-center justify-center text-xs font-bold <?= $persetujuan_val === 'setuju' ? 'bg-tosca-dark text-white-light' : 'border border-white-active text-transparent' ?>">
              ✓
            </div>
            <span class="text-sm text-blue-darker <?= $persetujuan_val === 'setuju' ? 'font-semibold' : 'font-medium' ?>">Setuju Dibongkar</span>
          </div>

          <div class="flex items-center gap-2.5 px-4 py-3 rounded-2xl border bg-white-light-hover select-none <?= $persetujuan_val === 'tidak_setuju' ? 'border-blue-darker/60' : 'border-white-active opacity-60' ?>">
            <div class="w-5 h-5 rounded-md flex items-center justify-center text-xs font-bold <?= $persetujuan_val === 'tidak_setuju' ? 'bg-tosca-dark text-white-light' : 'border border-white-active text-transparent' ?>">
              ✓
            </div>
            <span class="text-sm text-blue-darker <?= $persetujuan_val === 'tidak_setuju' ? 'font-semibold' : 'font-medium' ?>">Tidak Setuju</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="px-4 py-3 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-white-darker">
          <?= e($data['status_pembayaran'] ?? 'Belum Dibayar') ?>
        </div>
        <div class="px-4 py-3 rounded-2xl border border-white-active bg-white-light-hover text-sm font-medium text-white-darker">
          <?= e($data['status_pengambilan'] ?? 'Belum Diambil') ?>
        </div>
      </div>
    </div>

    <div class="mt-10 pt-4 flex items-center justify-end gap-3.5">
      <a
        href="<?= e($urlKembali) ?>"
        class="px-8 py-3 rounded-full border border-tosca-dark hover:bg-tosca-dark-hover text-tosca-dark hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
        Kembali
      </a>
      <?php if (!$dariRiwayat): ?>
        <a
          href="edit_antrean.php?nota=<?= urlencode($data['no_nota']) ?>"
          class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition text-center cursor-pointer">
          Edit Antrean
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const btnPass = document.getElementById("togglePasswordDetailBtn");
    const inpPass = document.getElementById("passwordPerangkatDetail");
    if (btnPass && inpPass) {
      btnPass.addEventListener("click", function() {
        inpPass.type = inpPass.type === "password" ? "text" : "password";
      });
    }
  });
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>