<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

$nota = trim((string) ($_GET['nota'] ?? ''));
$idKaryawan = (int) ($_SESSION['user_id'] ?? 0);

$errorPage = '';
$servis = null;
$riwayat = [];
$sparepartTerpakai = [];
$totalBiayaSparepart = 0;
$nilaiKeterangan = '';

$flash = $_SESSION['flash_servis'] ?? null;
unset($_SESSION['flash_servis']);

$opsiStatus = [
  'Menunggu',
  'Diterima',
  'Menunggu Persetujuan',
  'Dikerjakan',
  'Selesai',
  'Dibatalkan',
];

if (empty($_SESSION['csrf_edit_servis'])) {
  $_SESSION['csrf_edit_servis'] = bin2hex(random_bytes(32));
}

// ========================================
// AMBIL DATA SERVIS
// ========================================

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
            JOIN pelanggan AS p
                ON p.id_pelanggan = s.id_pelanggan
            LEFT JOIN karyawan AS k
                ON k.id_karyawan = s.id_karyawan
            WHERE s.no_nota = :nota
              AND s.id_karyawan = :karyawan
            LIMIT 1
        ");

    $stmt->execute([
      'nota' => $nota,
      'karyawan' => $idKaryawan
    ]);

    $servis = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($servis === null) {
      $errorPage = 'Data servis tidak ditemukan atau bukan tugas Anda.';
    } else {

      // AMBIL SPAREPART TERPAKAI
      $stmtSp = $pdo->prepare("
                SELECT
                    ds.id_detail,
                    ds.id_sparepart,
                    ds.jumlah_dipakai,
                    ds.harga_satuan,
                    ds.subtotal_harga,
                    sp.nama_sparepart
                FROM detail_servis AS ds
                JOIN sparepart AS sp
                    ON sp.id_sparepart = ds.id_sparepart
                WHERE ds.no_nota = :nota
                ORDER BY ds.id_detail ASC
            ");

      $stmtSp->execute(['nota' => $nota]);

      $sparepartTerpakai = $stmtSp->fetchAll(PDO::FETCH_ASSOC);

      foreach ($sparepartTerpakai as $sp) {
        $totalBiayaSparepart += (float) $sp['subtotal_harga'];
      }

      // AMBIL RIWAYAT PERUBAHAN
      $stmtRiwayat = $pdo->prepare("
                SELECT
                    t.status_pengerjaan,
                    t.keterangan,
                    t.waktu_update,
                    k.nama_karyawan
                FROM tracking_servis AS t
                JOIN karyawan AS k
                    ON k.id_karyawan = t.id_karyawan
                WHERE t.no_nota = :nota
                ORDER BY
                    t.waktu_update ASC,
                    t.id_tracking ASC
            ");

      $stmtRiwayat->execute(['nota' => $nota]);

      $riwayat = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
    error_log('edit_servis.php (load): ' . $e->getMessage());

    $errorPage = 'Terjadi kesalahan saat mengambil data servis.';
  }
}


// PROSES SIMPAN PERUBAHAN
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  $servis !== null &&
  $errorPage === ''
) {
  $tokenForm = (string) ($_POST['csrf_edit_servis'] ?? '');
  $tokenSesi = (string) ($_SESSION['csrf_edit_servis'] ?? '');

  if (
    $tokenForm === '' ||
    $tokenSesi === '' ||
    !hash_equals($tokenSesi, $tokenForm)
  ) {
    $flash = [
      'tipe' => 'error',
      'pesan' => 'Sesi formulir tidak valid. Muat ulang halaman.'
    ];
  } else {
    $keteranganInput = trim((string) ($_POST['keterangan'] ?? ''));
    $estimasiInput = trim((string) ($_POST['estimasi_selesai'] ?? ''));
    $statusInput = trim((string) ($_POST['status_pengerjaan'] ?? ''));

    // Simpan status awal sebelum nilai form diperbarui.
    $statusSebelumnya = (string) $servis['status_pengerjaan'];

    $nilaiKeterangan = $keteranganInput;
    $servis['estimasi_pengambilan'] = $estimasiInput !== '' ? $estimasiInput : null;
    $tanggalValid = true;

    if ($estimasiInput !== '') {
      $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $estimasiInput);
      $tanggalValid = $tanggal !== false
        && $tanggal->format('Y-m-d') === $estimasiInput;
    }

    $panjangKeterangan = function_exists('mb_strlen')
      ? mb_strlen($keteranganInput)
      : strlen($keteranganInput);

    if ($panjangKeterangan > 255) {
      $flash = [
        'tipe' => 'error',
        'pesan' => 'Keterangan maksimal 255 karakter.'
      ];
    } elseif (!in_array($statusInput, $opsiStatus, true)) {
      $flash = [
        'tipe' => 'error',
        'pesan' => 'Status pengerjaan tidak valid.'
      ];
    } elseif (!$tanggalValid) {
      $flash = [
        'tipe' => 'error',
        'pesan' => 'Tanggal estimasi tidak valid.'
      ];
    } else {
      try {
        $pdo->beginTransaction();

        // Kunci dan verifikasi servis serta role teknisi.
        $cek = $pdo->prepare("
                    SELECT
                        s.status_pengerjaan,
                        s.persetujuan_pelanggan,
                        k.peran
                    FROM servis AS s
                    JOIN karyawan AS k
                        ON k.id_karyawan = s.id_karyawan
                    WHERE s.no_nota = :nota
                      AND s.id_karyawan = :karyawan
                    FOR UPDATE OF s
                ");

        $cek->execute([
          'nota' => $nota,
          'karyawan' => $idKaryawan
        ]);

        $dataTerkunci = $cek->fetch(PDO::FETCH_ASSOC);

        if (!$dataTerkunci || $dataTerkunci['peran'] !== 'Teknisi') {
          throw new RuntimeException(
            'Servis tidak ditemukan atau bukan tugas teknisi ini.'
          );
        }

        // Gunakan status terbaru dari database, bukan data halaman yang mungkin sudah lama.
        $statusSebelumnya = (string) $dataTerkunci['status_pengerjaan'];

        // Keputusan pelanggan dan izin bongkar hanya dapat diubah oleh admin.
        // Status serta riwayat ditangani oleh fn_update_status().
        if (
          $statusSebelumnya === 'Menunggu Persetujuan' &&
          $statusInput === 'Dikerjakan' &&
          !in_array($dataTerkunci['persetujuan_pelanggan'], ['Ya', 'Tidak'], true)
        ) {
          throw new RuntimeException(
            'Tunggu keputusan pelanggan dicatat oleh admin sebelum melanjutkan servis.'
          );
        }

        $update = $pdo->prepare("
          UPDATE servis
          SET estimasi_pengambilan = :estimasi
          WHERE no_nota = :nota
            AND id_karyawan = :karyawan
        ");

        $update->execute([
          'estimasi' => $estimasiInput !== '' ? $estimasiInput : null,
          'nota' => $nota,
          'karyawan' => $idKaryawan
        ]);

        if ($update->rowCount() < 1) {
          throw new RuntimeException(
            'Servis tidak ditemukan atau bukan tugas Anda.'
          );
        }

        // Panggil function database hanya ketika status benar-benar berubah.

        if ($statusInput !== $statusSebelumnya) {
          $fnStatus = $pdo->prepare("
            SELECT public.fn_update_status(
              :nota,
              :status,
              :keterangan,
              :karyawan
            )
          ");

          $fnStatus->execute([
            'nota' => $nota,
            'status' => $statusInput,
            'keterangan' => $keteranganInput !== ''
              ? $keteranganInput
              : null,
            'karyawan' => $idKaryawan
          ]);

          $fnStatus->fetchColumn();
        } elseif ($keteranganInput !== '') {
          throw new RuntimeException(
            'Keterangan hanya boleh diisi ketika status berubah.'
          );
        }

        $pdo->commit();

        $_SESSION['flash_servis'] = [
          'tipe' => 'success',
          'pesan' => 'Data servis berhasil diperbarui.'
        ];

        header(
          'Location: /teknisi/edit_servis.php?nota=' . urlencode($nota)
        );
        exit;
      } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
          $pdo->rollBack();
        }

        error_log('edit_servis.php (save): ' . $e->getMessage());

        // Kembalikan badge ke status database jika penyimpanan gagal.
        if (isset($statusSebelumnya)) {
          $servis['status_pengerjaan'] = $statusSebelumnya;
        }

        $flash = [
          'tipe' => 'error',
          'pesan' => $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Gagal menyimpan perubahan. Periksa status servis dan coba lagi.'
        ];
      }
    }
  }
}


$alurStatus = [
  'Menunggu' => ['Diterima', 'Dibatalkan'],
  'Diterima' => [
    'Menunggu Persetujuan',
    'Dikerjakan',
    'Dibatalkan'
  ],
  'Menunggu Persetujuan' => [
    'Dikerjakan',
    'Dibatalkan'
  ],
  'Dikerjakan' => ['Selesai', 'Dibatalkan'],
  'Selesai' => [],
  'Dibatalkan' => []
];

$opsiStatusTersedia = [];

if ($servis !== null) {
  $statusAktif = (string) $servis['status_pengerjaan'];

  $statusBerikutnya = $alurStatus[$statusAktif] ?? [];

  // Keputusan Ya atau Tidak memungkinkan servis awal dilanjutkan.
  if (
    $statusAktif === 'Menunggu Persetujuan' &&
    !in_array($servis['persetujuan_pelanggan'] ?? null, ['Ya', 'Tidak'], true)
  ) {
    $statusBerikutnya = array_values(array_diff($statusBerikutnya, ['Dikerjakan']));
  }

  $opsiStatusTersedia = array_merge([$statusAktif], $statusBerikutnya);
}


$bisaUbahSparepart = $servis !== null && !in_array(
  $servis['status_pengerjaan'],
  ['Selesai', 'Dibatalkan'],
  true
);

$page_title = 'Edit Servis';
$active_menu = 'dashboard';

require_once __DIR__ . '/../includes/header.php';
?>
<!-- NOTIFIKASI -->
<?php if ($flash): ?>
  <div
    role="alert"
    class="mb-5 rounded-2xl border border-gray-200 bg-white-light px-5 py-4 text-sm font-medium <?= $flash['tipe'] === 'success' ? 'text-success-dark' : 'text-danger-dark' ?>">
    <?= e($flash['pesan']) ?>
  </div>
<?php endif; ?>
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
  <form
    method="POST"
    action=""
    id="formEditServis"
    data-nota="<?= e($servis['no_nota']) ?>"
    data-status-awal="<?= e($servis['status_pengerjaan']) ?>">
    <input
      type="hidden"
      name="csrf_edit_servis"
      value="<?= e($_SESSION['csrf_edit_servis']) ?>">
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
            <label class="mb-1.5 block text-sm font-bold text-blue-darker">
              Password Perangkat
            </label>
            <input
              type="password"
              value="<?= e((string) $servis['password_perangkat']) ?>"
              readonly
              autocomplete="off"
              class="w-full rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-gray-600 focus:outline-none">
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
      <!-- KOLOM 3: EDIT TRACKING -->
      <section class="min-w-0 rounded-[30px] border border-[#D9D1C5] bg-white-light p-6">
        <!-- HEADER TRACKING -->
        <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
          <h2 class="text-xl font-bold text-blue-darker">
            Edit Tracking
          </h2>
          <div class="flex items-center gap-2">
            <?= badgeStatus(
              (string) ($servis['jenis_layanan'] ?: 'Reguler')
            ) ?>
            <?= badgeStatus(
              (string) $servis['status_pengerjaan']
            ) ?>
          </div>
        </div>
        <div class="space-y-4">
          <!-- KETERANGAN TRACKING -->
          <div>
            <label
              for="keterangan"
              class="mb-1.5 block text-sm font-bold text-blue-darker">
              Keterangan
              <span class="text-xs font-normal text-gray-400">(Opsional)</span>
            </label>
            <textarea
              id="keterangan"
              name="keterangan"
              maxlength="255"
              rows="2"
              placeholder="Tambahkan keterangan perubahan status jika diperlukan..."
              class="w-full resize-none rounded-xl border border-[#B8B0A6] bg-white-light px-4 py-3 text-sm text-blue-darker focus:border-tosca-dark focus:outline-none"><?= e($nilaiKeterangan ?? '') ?></textarea>
          </div>
          <!-- ESTIMASI DAN STATUS -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label
                for="estimasi_selesai"
                class="mb-1.5 block text-sm font-bold text-blue-darker">
                Estimasi
              </label>
              <input
                id="estimasi_selesai"
                name="estimasi_selesai"
                type="date"
                value="<?= e(
                          $servis['estimasi_pengambilan']
                            ? substr(
                              (string) $servis['estimasi_pengambilan'],
                              0,
                              10
                            )
                            : ''
                        ) ?>"
                class="w-full min-w-0 rounded-xl border border-[#B8B0A6] bg-white-light px-3 py-3 text-sm text-blue-darker focus:border-tosca-dark focus:outline-none">
            </div>
            <div>
              <label
                for="status_pengerjaan"
                class="mb-1.5 block text-sm font-bold text-blue-darker">
                Ubah Status
              </label>
              <select
                id="status_pengerjaan"
                name="status_pengerjaan"
                required
                class="w-full min-w-0 rounded-xl border border-[#B8B0A6] bg-white-light px-3 py-3 text-sm font-semibold text-blue-darker focus:border-tosca-dark focus:outline-none">
                <?php foreach ($opsiStatusTersedia as $opsi): ?>
                  <option
                    value="<?= e($opsi) ?>"
                    <?= $servis['status_pengerjaan'] === $opsi
                      ? 'selected'
                      : '' ?>>
                    <?= e($opsi) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <?php
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
              Persetujuan Pekerjaan Tambahan
            </legend>

            <div class="grid grid-cols-2 gap-3">
              <!-- SETUJU -->
              <div class="flex min-h-12 items-center justify-center rounded-xl border px-2 py-2 text-center text-xs font-semibold <?= $kelasSetuju ?>">
                Setuju
              </div>

              <!-- TIDAK SETUJU -->
              <div class="flex min-h-12 items-center justify-center rounded-xl border px-2 py-2 text-center text-xs font-semibold <?= $kelasTidakSetuju ?>">
                Tidak Setuju
              </div>
            </div>

            <p class="mt-2 text-xs text-tosca">
              <?= match ($persetujuanPelanggan) {
                'Ya' => 'Pekerjaan tambahan disetujui pelanggan.',
                'Tidak' => 'Pekerjaan tambahan ditolak. Lanjutkan servis awal tanpa pekerjaan tambahan.',
                default => 'Belum ada keputusan pekerjaan tambahan dari pelanggan.',
              } ?>
            </p>
          </fieldset>
          <!-- Penggunaan sparepart hanya dapat dilakukan jika status servis belum selesai atau dibatalkan. -->
          <div class="pt-2">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
              <h3 class="text-sm font-bold text-blue-darker">
                Penggunaan Sparepart
              </h3>
              <?php if ($bisaUbahSparepart): ?>
                <a
                  href="/sparepart/pakai.php?nota=<?= urlencode($nota) ?>"
                  class="inline-flex items-center justify-center rounded-xl border border-[#29365F] px-4 py-2.5 text-xs font-semibold text-blue-darker transition hover:bg-gray-50">
                  + Tambah Sparepart
                </a>
              <?php endif; ?>
            </div>
            <!-- LIST SPAREPART -->
            <div class="flex max-h-60 flex-col gap-2.5 overflow-y-auto pr-1">
              <?php if (empty($sparepartTerpakai)): ?>
                <p class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-xs text-gray-500">
                  Belum ada sparepart digunakan.
                </p>
              <?php else: ?>
                <?php foreach ($sparepartTerpakai as $sp): ?>
                  <div class="flex items-center gap-2">
                    <!-- DATA SPAREPART -->
                    <div class="grid min-w-0 flex-1 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 rounded-xl border border-[#B8B0A6] px-3 py-2.5 text-xs sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                      <!-- NAMA -->
                      <span
                        class="truncate text-blue-darker"
                        title="<?= e((string) $sp['nama_sparepart']) ?>">
                        <?= e((string) $sp['nama_sparepart']) ?>
                      </span>
                      <!-- JUMLAH -->
                      <span class="whitespace-nowrap text-gray-600">
                        <?= (int) $sp['jumlah_dipakai'] ?> unit
                      </span>
                      <!-- SUBTOTAL -->
                      <span class="col-span-2 whitespace-nowrap text-right text-gray-600 sm:col-span-1">
                        Rp<?= number_format(
                            (float) $sp['subtotal_harga'],
                            0,
                            ',',
                            '.'
                          ) ?>
                      </span>
                    </div>
                    <!-- TOMBOL HAPUS -->
                    <?php if ($bisaUbahSparepart): ?>
                      <button
                        type="submit"
                        form="formHapusSparepart"
                        name="id_detail"
                        value="<?= (int) $sp['id_detail'] ?>"
                        class="rounded-full border border-danger px-3 py-2 text-xs font-semibold text-danger transition hover:bg-danger-light">
                        Hapus
                      </button>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <!-- TOTAL BIAYA SPAREPART -->
            <div class="mt-5 flex items-center justify-between gap-3 rounded-xl border border-[#B8B0A6] px-4 py-3 text-xs">
              <span class="text-gray-600">
                Total Biaya:
              </span>
              <strong class="font-semibold text-gray-700">
                Rp <?= number_format(
                      $totalBiayaSparepart,
                      0,
                      ',',
                      '.'
                    ) ?>
              </strong>
            </div>
          </div>
        </div>
      </section>
    </div>


    <!-- TOMBOL BATAL DAN SIMPAN -->
    <div class="mt-5 flex flex-wrap justify-end gap-3">

      <a
        href="/teknisi/dashboard.php"
        class="inline-flex min-w-28 items-center justify-center rounded-full border border-[#29365F] bg-white-light px-8 py-3 text-sm font-semibold text-blue-darker transition hover:bg-gray-50">
        Kembali
      </a>

      <button
        type="submit"
        class="inline-flex min-w-48 items-center justify-center rounded-full bg-orange px-8 py-3 text-sm font-semibold text-white-light transition hover:bg-orange-hover">
        Simpan Perubahan
      </button>

    </div>

  </form>
  <div
    id="modalKonfirmasiStatus"
    class="fixed inset-0 z-100 hidden items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="judulKonfirmasiStatus">
    <div
      id="backdropKonfirmasiStatus"
      class="absolute inset-0 bg-black/40 backdrop-blur-sm">
    </div>
    <div class="relative w-full max-w-md rounded-[30px] bg-white-light p-7 text-center shadow-xl">
      <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-orange-light text-orange">
        <svg
          class="h-8 w-8"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v4m0 4h.01M10.3 3.9 2.4 17.5A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.5L13.7 3.9a2 2 0 0 0-3.4 0Z" />
        </svg>
      </div>
      <h3
        id="judulKonfirmasiStatus"
        class="mb-3 text-xl font-bold text-blue-darker">
        Konfirmasi Perubahan Status
      </h3>
      <p
        id="pesanKonfirmasiStatus"
        class="mb-3 text-sm leading-relaxed text-gray-600">
      </p>
      <p class="mb-7 text-xs font-medium text-orange-dark">
        Pastikan status sudah benar. Perubahan tidak dapat dikembalikan ke status sebelumnya.
      </p>
      <div class="flex flex-col-reverse gap-3 sm:flex-row">
        <button
          type="button"
          id="btnBatalStatus"
          class="flex-1 rounded-full border border-[#29365F] px-5 py-3 text-sm font-semibold text-blue-darker transition hover:bg-gray-50">
          Batal
        </button>
        <button
          type="button"
          id="btnYakinStatus"
          class="flex-1 rounded-full bg-orange px-5 py-3 text-sm font-semibold text-white-light transition hover:bg-orange-hover">
          Ya, Simpan
        </button>
      </div>
    </div>
  </div>



  <!-- =====================================
     FORM HAPUS SPAREPART
     Harus berada di luar form edit.
====================================== -->

  <?php if ($bisaUbahSparepart): ?>

    <form
      id="formHapusSparepart"
      action="/sparepart/hapus_pakai.php"
      method="POST">
      <input
        type="hidden"
        name="csrf_edit_servis"
        value="<?= e($_SESSION['csrf_edit_servis']) ?>">
      <input
        type="hidden"
        name="nota"
        value="<?= e($nota) ?>">
    </form>

  <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>