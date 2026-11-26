<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

$nota = trim((string) ($_GET['nota'] ?? ''));
$idKaryawan = (int) ($_SESSION['user_id'] ?? 0);
$servis = null;
$daftar = [];
$error_page = '';
$flash = $_SESSION['flash_pakai'] ?? null;
unset($_SESSION['flash_pakai']);

// Token khusus form pemakaian sparepart, sama dengan handler proses_pakai.php.
if (empty($_SESSION['csrf_pakai_sparepart'])) {
  $_SESSION['csrf_pakai_sparepart'] = bin2hex(random_bytes(32));
}

if ($nota === '' || strlen($nota) > 10 || $idKaryawan <= 0) {
  $error_page = 'Nomor nota tidak valid atau sesi login telah berakhir.';
} else {
  try {
    $stmt = $pdo->prepare("
            SELECT s.no_nota, s.status_pengerjaan AS status,
                   CONCAT_WS(' ', s.jenis_barang, s.merek_dan_spesifikasi) AS perangkat
            FROM servis AS s
            WHERE s.no_nota = :nota AND s.id_karyawan = :karyawan
            LIMIT 1
        ");
    $stmt->execute(['nota' => $nota, 'karyawan' => $idKaryawan]);
    $servis = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($servis === null) {
      $error_page = 'Nota tidak ditemukan atau bukan servis yang ditugaskan kepada Anda.';
    } else {
      $daftar = $pdo->query("
                SELECT id_sparepart, nama_sparepart, stok_tersedia, harga_satuan
                FROM sparepart
                ORDER BY nama_sparepart ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (PDOException $e) {
    error_log('pakai.php: ' . $e->getMessage());
    $error_page = 'Terjadi kesalahan saat mengambil data sparepart.';
  }
}

$bisa_pakai = $servis !== null && !in_array($servis['status'], ['Selesai', 'Dibatalkan'], true);
$page_title = 'Pakai Sparepart';
$search_placeholder = 'Cari Sparepart';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($flash): ?>
  <div role="alert" class="mb-5 p-4 rounded-2xl text-sm font-medium <?= ($flash['tipe'] ?? '') === 'success' ? 'bg-success-light text-success-dark' : 'bg-danger-light text-danger-dark' ?>">
    <?= e($flash['pesan'] ?? '') ?>
  </div>
<?php endif; ?>

<?php if ($error_page !== ''): ?>
  <div class="bg-white-light rounded-2xl p-8 border border-gray-200 text-center max-w-xl mx-auto mt-8">
    <p class="text-sm text-blue-darker mb-5"><?= e($error_page) ?></p>
    <a href="/teknisi/dashboard.php" class="inline-block px-8 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition">Kembali ke Dashboard</a>
  </div>
<?php else: ?>
  <div class="bg-white-light rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
      <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Sparepart</h2>
      <span class="text-sm text-orange-dark font-medium">Nota <?= e($servis['no_nota']) ?> · <?= e($servis['perangkat']) ?></span>
    </div>
    <div class="mb-4 flex items-center gap-3 flex-wrap">
      <?= badgeStatus($servis['status']) ?>
      <a href="/teknisi/edit_servis.php?nota=<?= urlencode($nota) ?>" class="text-xs font-semibold text-tosca-darker hover:underline">Kembali ke Edit Servis</a>
    </div>

    <?php if (!$bisa_pakai): ?>
      <div class="mb-3 p-3 rounded-xl bg-warning-light text-warning-dark text-xs font-medium">Servis sudah <?= e(strtolower($servis['status'])) ?>. Sparepart tidak dapat ditambahkan lagi.</div>
    <?php endif; ?>

    <div class="overflow-x-auto mt-3">
      <table
        id="tableSparepart"
        class="w-full min-w-160 whitespace-nowrap text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-white-hover text-white-darker font-bold text-[13px] tracking-wider">
            <th class="py-4 px-5">Sparepart</th>
            <th class="py-4 px-5">Stok</th>
            <th class="py-4 px-5">Harga</th>
            <th class="py-4 px-5 text-center">Status</th>
            <th class="py-4 px-5 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white-hover text-white-darker font-medium" id="tbodySparepart">
          <?php foreach ($daftar as $sp):
            $stok = (int) $sp['stok_tersedia'];
            $statusStok = hitungStatusSparepart($stok);
          ?>
            <tr
              class="row-item hover:bg-warning-light/40 transition-colors"
              data-search="<?= e(strtolower($sp['nama_sparepart'])) ?>"
              data-status="<?= e($statusStok) ?>">
              <td class="py-4 px-5">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-xl bg-orange-light-active text-orange-dark font-bold text-xs flex items-center justify-center shrink-0"><?= e(strtoupper(substr($sp['nama_sparepart'], 0, 2))) ?></div>
                  <span class="font-semibold text-blue-darker"><?= e($sp['nama_sparepart']) ?></span>
                </div>
              </td>
              <td class="py-4 px-5"><b class="text-blue-darker"><?= $stok ?></b> <small class="text-xs text-white-dark-active">unit</small></td>
              <td class="py-4 px-5 font-semibold text-blue-darker">Rp <?= number_format((float) $sp['harga_satuan'], 0, ',', '.') ?></td>
              <td class="py-4 px-5 text-center"><?= badgeStatus($statusStok) ?></td>
              <td class="py-4 px-5 text-center">
                <button type="button" class="btn-pakai px-6 py-2 rounded-full bg-white-light border border-tosca-dark hover:bg-tosca-dark text-tosca-dark hover:text-white-light  text-xs font-semibold transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                  data-id="<?= (int) $sp['id_sparepart'] ?>" data-nama="<?= e($sp['nama_sparepart']) ?>" data-stok="<?= $stok ?>" data-harga="<?= e((string) $sp['harga_satuan']) ?>"
                  <?= $stok <= 0 || !$bisa_pakai ? 'disabled' : '' ?>>Pakai</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="emptySparepart" class="hidden text-center py-12 text-white-dark-hover">Sparepart tidak ditemukan.</div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-white-dark-hover mt-6">
      <p id="infoSparepart" class="pl-1"></p>
      <div id="pagerSparepart" class="flex flex-wrap items-center gap-1.5"></div>
    </div>
  </div>

  <div id="modalPakai" class="fixed inset-0 z-99 hidden items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="judulModal">
    <div id="modalPakaiBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    <form id="formPakai" action="proses_pakai.php" method="POST" class="modal-box relative bg-white-light rounded-3xl p-6 md:p-8 max-w-md w-full mx-4 shadow-xl transform scale-95 opacity-0 transition-all duration-300">
      <input type="hidden" name="csrf_pakai_sparepart" value="<?= e($_SESSION['csrf_pakai_sparepart']) ?>">
      <input type="hidden" name="nota" value="<?= e($nota) ?>">
      <input type="hidden" name="id_sparepart" id="inputIdSparepart">
      <h3 id="judulModal" class="text-xl font-bold text-blue-darker tracking-tight">Pakai Sparepart</h3>
      <p class="text-sm text-white-dark-active mt-1"><span id="modalNama" class="font-semibold text-blue-darker"></span> · stok tersedia <span id="modalStok" class="font-semibold text-blue-darker"></span></p>
      <label for="inputJumlah" class="block text-sm font-bold text-blue-darker mt-6 mb-2">Jumlah dipakai</label>
      <div class="flex items-center gap-3">
        <button type="button" id="btnKurang" class="w-11 h-11 rounded-full border border-white-active text-blue-darker text-lg font-bold hover:bg-white-hover transition">−</button>
        <input type="number" name="jumlah" id="inputJumlah" value="1" min="1" step="1" required class="flex-1 min-w-0 text-center px-4 py-3 rounded-2xl border border-white-active bg-white-light text-base font-bold text-blue-darker focus:outline-none focus:border-tosca-dark">
        <button type="button" id="btnTambah" class="w-11 h-11 rounded-full border border-white-active text-blue-darker text-lg font-bold hover:bg-white-hover transition">+</button>
      </div>
      <p id="errJumlah" class="text-xs text-danger-dark font-medium mt-2 min-h-4"></p>
      <div class="mt-4 rounded-2xl bg-orange-light-hover p-4 text-sm">
        <div class="flex justify-between py-1"><span class="text-orange-dark/80 font-medium">Harga satuan</span><span id="modalHarga" class="font-bold text-orange-dark"></span></div>
        <div class="flex justify-between pt-2 mt-1 border-t border-orange-dark/15"><span class="text-orange-dark font-semibold">Total harga</span><span id="modalTotal" class="text-lg font-extrabold text-orange-dark"></span></div>
      </div>
      <div class="flex flex-col-reverse sm:flex-row gap-3 mt-6">
        <button type="button" id="btnBatalModal" class="w-full px-6 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold">Batal</button>
        <button type="submit" id="btnSimpanPakai" class="w-full px-6 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold disabled:opacity-40">Tambahkan</button>
      </div>
    </form>
  </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>