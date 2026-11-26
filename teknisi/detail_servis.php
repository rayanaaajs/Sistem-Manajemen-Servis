<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

$nota = trim((string) ($_GET['nota'] ?? ''));
$idKaryawan = (int) ($_SESSION['user_id'] ?? 0);

$servis = null;
$riwayat = [];
$sparepartTerpakai = [];
$totalBiayaSparepart = 0;
$errorPage = '';

// Halaman detail bersifat read-only, dan hanya dapat diakses teknisi yang ditugaskan.
if ($nota === '' || strlen($nota) > 10 || $idKaryawan <= 0) {
  $errorPage = 'Nomor nota tidak valid atau sesi login telah berakhir.';
} else {
  try {
    $stmt = $pdo->prepare("
      SELECT
        s.*,
        p.nama_pelanggan,
        p.no_hp,
        p.alamat,
        k.nama_karyawan AS nama_teknisi
      FROM servis AS s
      JOIN pelanggan AS p ON p.id_pelanggan = s.id_pelanggan
      LEFT JOIN karyawan AS k ON k.id_karyawan = s.id_karyawan
      WHERE s.no_nota = :nota
        AND s.id_karyawan = :karyawan
      LIMIT 1
    ");
    $stmt->execute([
      'nota' => $nota,
      'karyawan' => $idKaryawan,
    ]);

    $servis = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($servis === null) {
      $errorPage = 'Data servis tidak ditemukan atau bukan tugas Anda.';
    } else {
      // Samakan data penggunaan sparepart dengan yang ditampilkan di halaman edit.
      $stmtSp = $pdo->prepare("
        SELECT
          ds.id_detail,
          ds.id_sparepart,
          ds.jumlah_dipakai,
          ds.harga_satuan,
          ds.subtotal_harga,
          sp.nama_sparepart
        FROM detail_servis AS ds
        JOIN sparepart AS sp ON sp.id_sparepart = ds.id_sparepart
        WHERE ds.no_nota = :nota
        ORDER BY ds.id_detail ASC
      ");
      $stmtSp->execute(['nota' => $nota]);
      $sparepartTerpakai = $stmtSp->fetchAll(PDO::FETCH_ASSOC);

      foreach ($sparepartTerpakai as $sp) {
        $totalBiayaSparepart += (float) $sp['subtotal_harga'];
      }

      $stmtRiwayat = $pdo->prepare("
        SELECT
          t.status_pengerjaan,
          t.keterangan,
          t.waktu_update,
          k.nama_karyawan
        FROM tracking_servis AS t
        JOIN karyawan AS k ON k.id_karyawan = t.id_karyawan
        WHERE t.no_nota = :nota
        ORDER BY t.waktu_update ASC, t.id_tracking ASC
      ");
      $stmtRiwayat->execute(['nota' => $nota]);
      $riwayat = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
    error_log('detail_servis.php (load): ' . $e->getMessage());
    $errorPage = 'Terjadi kesalahan saat mengambil data servis.';
  }
}

$page_title = 'Detail Servis';
$active_menu = 'Dashboard';

require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($errorPage !== ''): ?>
  <div class="mx-auto mt-8 max-w-xl rounded-3xl border border-gray-200 bg-white-light p-8 text-center">
    <p class="mb-5 text-sm text-blue-darker">
      <?= e($errorPage) ?>
    </p>
    <a
      href="/teknisi/dashboard.php"
      class="inline-flex rounded-full border border-blue-darker px-7 py-3 text-sm font-semibold text-blue-darker">
      Kembali ke Dashboard
    </a>
  </div>
<?php else: ?>
  <!-- GRID UTAMA -->
  <div class="grid grid-cols-1 gap-5 xl:grid-cols-3 xl:items-stretch">
    <!-- KOLOM 1: PELANGGAN DAN RIWAYAT -->
    <div class="flex min-w-0 flex-col gap-5">
      <!-- DATA PELANGGAN -->
      <section class="rounded-[30px] border border-[#D9D1C5] bg-white-light p-6">
        <h2 class="mb-5 text-xl font-bold text-blue-darker">
          Data Pelanggan
        </h2>
        <div class="space-y-4">
          <div>
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Pelanggan
            </label>
            <input
              type="text"
              value="<?= e((string) $servis['nama_pelanggan']) ?>"
              readonly
              class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none">
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Nomor
            </label>
            <input
              type="text"
              value="<?= e((string) $servis['no_hp']) ?>"
              readonly
              class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none">
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Alamat
            </label>
            <textarea
              rows="2"
              readonly
              class="w-full resize-none rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none"><?= e((string) $servis['alamat']) ?></textarea>
          </div>
        </div>
      </section>
      <!-- RIWAYAT PERUBAHAN -->
      <section class="flex-1 rounded-[30px] border border-[#D9D1C5] bg-white-light p-6">
        <h2 class="mb-6 text-xl font-bold text-blue-darker">
          Riwayat Perubahan
        </h2>
        <?php if (empty($riwayat)): ?>
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
                  <p class="mt-1 text-xs leading-relaxed text-gray-500">
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
    <!--KOLOM 2: DATA SERVIS -->
    <section class="min-w-0 rounded-[30px] border border-[#D9D1C5] bg-white-light p-6">
      <h2 class="mb-5 text-xl font-bold text-blue-darker">
        Data Perangkat
      </h2>
      <div class="space-y-4">
        <!-- JENIS BARANG & KONDISI AWAL -->
        <div class="grid grid-cols-2 gap-4">
          <div class="min-w-0">
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Jenis Barang
            </label>
            <input
              type="text"
              value="<?= e((string) $servis['jenis_barang']) ?>"
              readonly
              class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-3 py-3 text-sm text-gray-600 focus:outline-none">
          </div>
          <div class="min-w-0">
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Kondisi Awal Unit
            </label>
            <input
              type="text"
              value="<?= e((string) $servis['kondisi_awal_unit']) ?>"
              readonly
              class="w-full rounded-xl border border-gray-200 bg-tosca-dark px-3 py-3 text-center text-xs font-semibold text-white-light focus:outline-none">
          </div>
        </div>
        <!-- MEREK DAN SPESIFIKASI -->
        <div>
          <label class="mb-1.5 block text-sm font-bold text-blue-darker">
            Merek dan Spesifikasi
          </label>
          <input
            type="text"
            value="<?= e((string) $servis['merek_dan_spesifikasi']) ?>"
            readonly
            class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none">
        </div>
        <!-- KELENGKAPAN -->
        <div>
          <label class="mb-1.5 block text-sm font-bold text-blue-darker">
            Kelengkapan
          </label>
          <input
            type="text"
            value="<?= e((string) $servis['kelengkapan_barang']) ?>"
            readonly
            class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none">
        </div>
        <!-- PASSWORD PERANGKAT -->
        <div>
          <label for="inputPassword" class="mb-1.5 block text-sm font-bold text-blue-darker">
            Password Perangkat
          </label>
          <div class="relative">
            <input
              id="inputPassword"
              type="password"
              value="<?= e((string) ($servis['password_perangkat'] ?? '')) ?>"
              readonly
              autocomplete="off"
              class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 pr-12 text-sm text-gray-600 focus:outline-none">
            <button
              type="button"
              id="btnTogglePass"
              aria-label="Tampilkan password perangkat"
              aria-pressed="false"
              class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 transition hover:text-tosca-dark">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
            </button>
          </div>
        </div>
        <!-- TEKNISI & PERSETUJUAN BONGKAR -->
        <div class="grid grid-cols-2 gap-4">
          <div class="min-w-0">
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Teknisi
            </label>
            <div class="flex min-h-12 items-center gap-2 rounded-xl border border-[#B8B0A6] px-3 py-2.5 text-sm text-blue-darker">
              <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange-light text-orange-dark text-xs font-bold">
                <?= e(strtoupper(substr(
                  (string) ($servis['nama_teknisi'] ?? 'T'),
                  0,
                  1
                ))) ?>
              </span>
              <span class="truncate">
                <?= e((string) ($servis['nama_teknisi'] ?? '-')) ?>
              </span>
            </div>
          </div>
          <div class="min-w-0">
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Persetujuan Bongkar
            </label>
            <div class="flex min-h-12 items-center justify-center rounded-xl border bg-tosca-dark border-gray-200 px-2 py-2 text-center text-xs font-semibold text-white-light">
              <?= e(match ($servis['persetujuan_bongkar'] ?? null) {
                'Ya' => 'Setuju Dibongkar',
                'Tidak' => 'Tidak Setuju',
                default => 'Belum Disetujui',
              }) ?>
            </div>
          </div>
        </div>
        <!-- KELUHAN KERUSAKAN -->
        <div>
          <label class="mb-1.5 block text-sm font-bold text-blue-darker">
            Keluhan Kerusakan
          </label>
          <textarea
            rows="2"
            readonly
            class="w-full resize-none rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none"><?= e((string) $servis['keluhan_kerusakan']) ?></textarea>
        </div>
        <!-- CATATAN CEK FISIK -->
        <div>
          <label class="mb-1.5 block text-sm font-bold text-blue-darker">
            Catatan Cek Fisik
          </label>
          <textarea
            rows="2"
            readonly
            class="w-full resize-none rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none"><?= e((string) $servis['catatan_cek_fisik']) ?></textarea>
        </div>
      </div>
    </section>
    <!-- KOLOM 3: INFORMASI SERVIS (READ-ONLY) -->
    <section class="min-w-0 rounded-[30px] border border-[#D9D1C5] bg-white-light p-6">
      <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xl font-bold text-blue-darker">Informasi Servis</h2>
        <div class="flex items-center gap-2">
          <?= badgeStatus((string) ($servis['jenis_layanan'] ?: 'Reguler')) ?>
          <?= badgeStatus((string) $servis['status_pengerjaan']) ?>
        </div>
      </div>

      <div class="space-y-4">
        <!-- NOMOR NOTA -->
        <div>
          <label class="mb-1.5 block text-sm font-bold text-blue-darker">Nomor Nota</label>
          <input
            type="text"
            value="<?= e((string) $servis['no_nota']) ?>"
            readonly
            class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm font-semibold text-blue-darker focus:outline-none">
        </div>

        <!-- TANGGAL MASUK -->
        <div>
          <label class="mb-1.5 block text-sm font-bold text-blue-darker">Tanggal Masuk</label>
          <input
            type="text"
            value="<?= e(date('d M Y, H:i', strtotime((string) $servis['tanggal_masuk']))) ?>"
            readonly
            class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none">
        </div>

        <!-- ESTIMASI DAN STATUS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">Estimasi</label>
            <input
              type="text"
              value="<?= e(!empty($servis['estimasi_pengambilan'])
                        ? date('d M Y', strtotime((string) $servis['estimasi_pengambilan']))
                        : 'Belum ditentukan') ?>"
              readonly
              class="w-full min-w-0 rounded-xl border border-[#B8B0A6] bg-white-light px-3 py-3 text-sm text-gray-600 focus:outline-none">
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">Status Pengerjaan</label>
            <input
              type="text"
              value="<?= e((string) $servis['status_pengerjaan']) ?>"
              readonly
              class="w-full min-w-0 rounded-xl border border-[#B8B0A6] bg-white-light px-3 py-3 text-sm font-semibold text-blue-darker focus:outline-none">
          </div>
        </div>

        <?php
        // Nilainya hanya dibaca dari servis; keputusan diubah melalui halaman admin.
        $persetujuanPelanggan = $servis['persetujuan_pelanggan'] ?? null;

        $kelasSetuju = $persetujuanPelanggan === 'Ya'
          ? 'border-success bg-success-light text-success-dark'
          : 'border-white-dark bg-white-light text-white-darker';

        $kelasTidakSetuju = $persetujuanPelanggan === 'Tidak'
          ? 'border-danger bg-danger-light text-danger-dark'
          : 'border-white-dark bg-white-light text-white-darker';
        ?>

        <!-- PERSETUJUAN PELANGGAN -->
        <fieldset>
          <legend class="mb-2 text-sm font-bold text-blue-darker">
            Persetujuan Pelanggan
          </legend>
          <div class="grid grid-cols-2 gap-3">
            <div class="flex min-h-12 items-center justify-center rounded-xl border px-2 py-2 text-center text-xs font-semibold <?= $kelasSetuju ?>">
              Setuju
            </div>
            <div class="flex min-h-12 items-center justify-center rounded-xl border px-2 py-2 text-center text-xs font-semibold <?= $kelasTidakSetuju ?>">
              Tidak Setuju
            </div>
          </div>
          <p class="mt-2 text-xs text-tosca">
            <?= $persetujuanPelanggan === null
              ? 'Menunggu konfirmasi pelanggan oleh admin.'
              : 'Keputusan pelanggan telah dicatat oleh admin.' ?>
          </p>
        </fieldset>

        <!-- PENGGUNAAN SPAREPART -->
        <div class="pt-2">
          <h3 class="mb-4 text-sm font-bold text-blue-darker">Penggunaan Sparepart</h3>

          <div class="flex max-h-60 flex-col gap-2.5 overflow-y-auto pr-1">
            <?php if (empty($sparepartTerpakai)): ?>
              <p class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-xs text-gray-500">
                Belum ada sparepart digunakan.
              </p>
            <?php else: ?>
              <?php foreach ($sparepartTerpakai as $sp): ?>
                <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 rounded-xl border border-[#B8B0A6] px-3 py-2.5 text-xs sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                  <span
                    class="truncate text-blue-darker"
                    title="<?= e((string) $sp['nama_sparepart']) ?>">
                    <?= e((string) $sp['nama_sparepart']) ?>
                  </span>
                  <span class="whitespace-nowrap text-gray-600">
                    <?= (int) $sp['jumlah_dipakai'] ?> unit
                  </span>
                  <span class="col-span-2 whitespace-nowrap text-right text-gray-600 sm:col-span-1">
                    Rp<?= number_format((float) $sp['subtotal_harga'], 0, ',', '.') ?>
                  </span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="mt-5 flex items-center justify-between gap-3 rounded-xl border border-[#B8B0A6] px-4 py-3 text-xs">
            <span class="text-gray-600">Total Biaya:</span>
            <strong class="font-semibold text-gray-700">
              Rp <?= number_format($totalBiayaSparepart, 0, ',', '.') ?>
            </strong>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- TOMBOL AKSI -->
  <div class="mt-5 flex flex-wrap justify-end gap-3">
    <a
      href="/teknisi/dashboard.php"
      class="inline-flex min-w-28 items-center justify-center rounded-full border border-[#29365F] bg-white-light px-8 py-3 text-sm font-semibold text-blue-darker transition hover:bg-gray-50">
      Kembali
    </a>
    <a
      href="/teknisi/edit_servis.php?nota=<?= urlencode((string) $servis['no_nota']) ?>"
      class="inline-flex min-w-48 items-center justify-center rounded-full bg-orange px-8 py-3 text-sm font-semibold text-white-light transition hover:bg-orange-hover">
      Edit Servis
    </a>
  </div>

  <!-- FITUR DETAIL: TAMPILKAN/SEMBUNYIKAN PASSWORD PERANGKAT -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggleBtn = document.getElementById('btnTogglePass');
      const passInput = document.getElementById('inputPassword');
      if (!toggleBtn || !passInput) return;

      toggleBtn.addEventListener('click', () => {
        const tampil = passInput.type === 'password';
        passInput.type = tampil ? 'text' : 'password';
        toggleBtn.setAttribute('aria-pressed', String(tampil));
        toggleBtn.setAttribute(
          'aria-label',
          tampil ? 'Sembunyikan password perangkat' : 'Tampilkan password perangkat'
        );
        toggleBtn.classList.toggle('text-tosca-dark', tampil);
        toggleBtn.classList.toggle('text-gray-400', !tampil);
      });
    });
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>