<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

$page_title = 'Detail Sparepart';
$active_menu = 'sparepart';

$idRaw = $_GET['id'] ?? null;
$idSparepart = is_scalar($idRaw)
  ? filter_var($idRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
  : false;

if ($idSparepart === false) {
  $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'ID sparepart tidak valid.'];
  header('Location: index.php');
  exit;
}

try {
  $stmt = $pdo->prepare("
        SELECT id_sparepart, nama_sparepart, stok_tersedia, harga_satuan
        FROM public.sparepart
        WHERE id_sparepart = :id
        LIMIT 1
    ");
  $stmt->bindValue(':id', (int) $idSparepart, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'Sparepart tidak ditemukan.'];
    header('Location: index.php');
    exit;
  }
} catch (PDOException $e) {
  error_log('sparepart/detail.php: ' . $e->getMessage());
  $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'Gagal mengambil detail sparepart.'];
  header('Location: index.php');
  exit;
}

$batasMenipis = defined('BATAS_MENIPIS') ? max(0, (int) BATAS_MENIPIS) : 5;
$stok = (int) $row['stok_tersedia'];
$status_sparepart = $stok <= 0 ? 'Habis' : ($stok <= $batasMenipis ? 'Menipis' : 'Aman');

$sparepart = [
  'nama' => (string) $row['nama_sparepart'],
  'stok' => $stok,
  'harga' => 'Rp ' . number_format((float) $row['harga_satuan'], 0, ',', '.'),
];
$badge_class = getBadgeSparepartClass($status_sparepart);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- DETAIL SPAREPART SESUAI DESAIN FIGMA (2 KOLOM: DATA & RINGKASAN) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">

  <!-- ==========================================
             KARTU KIRI: DATA SPAREPART (READ-ONLY)
             ========================================== -->
  <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
    <div>
      <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Sparepart</h2>

      <div class="space-y-5 lg:space-y-6 max-w-xl">
        <!-- Nama Sparepart -->
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Nama Sparepart</label>
          <input type="text"
            value="<?= htmlspecialchars($sparepart['nama']) ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
        </div>

        <!-- Stok -->
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Stok</label>
          <input type="text"
            value="<?= (int) $sparepart['stok'] ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
        </div>

        <!-- Harga -->
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Harga</label>
          <input type="text"
            value="<?= htmlspecialchars($sparepart['harga']) ?>"
            readonly
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
        </div>

        <!-- Status (otomatis dari stok) -->
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Status</label>
          <span class="inline-block w-40 sm:w-48 px-4 py-3 rounded-2xl text-sm font-semibold text-center cursor-default <?= $badge_class ?>">
            <?= htmlspecialchars($status_sparepart) ?>
          </span>
          <p class="text-[11px] text-white-dark-hover mt-2 font-medium">
            Status otomatis mengikuti stok.
          </p>
        </div>
      </div>
    </div>

    <!-- Tombol Aksi Bawah: Batal / Kembali Sesuai Desain Figma -->
    <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
      <a href="index.php"
        class="px-8 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
        Batal
      </a>
    </div>
  </div>

  <!-- ==========================================
             KARTU KANAN: RINGKASAN SPAREPART
             ========================================== -->
  <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
    <div>
      <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Ringkasan Sparepart</h2>

      <!-- Banner Nama Sparepart Oranye -->
      <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
        <p class="text-xs text-orange-dark font-medium mb-1">Nama</p>
        <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight"><?= htmlspecialchars($sparepart['nama']) ?></h3>
        <p class="text-[11px] text-orange-dark/80 mt-2 font-medium">Dibuat otomatis saat disimpan</p>
      </div>

      <!-- Daftar Informasi Ringkasan -->
      <div class="text-xs font-medium text-blue-darker">
        <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
          <span class="text-blue-darker/70">Status</span>
          <span class="inline-block w-28 py-2 text-center rounded-full text-xs font-semibold <?= $badge_class ?>">
            <?= htmlspecialchars($status_sparepart) ?>
          </span>
        </div>

        <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
          <span class="text-blue-darker/70">Harga</span>
          <span class="font-bold text-blue-darker text-sm"><?= htmlspecialchars($sparepart['harga']) ?></span>
        </div>

        <div class="flex justify-between items-center py-3.5">
          <span class="text-blue-darker/70">Stok</span>
          <span class="font-bold text-blue-darker text-sm"><?= (int) $sparepart['stok'] ?></span>
        </div>
      </div>
    </div>

    <!-- Banner Biru Muda: Setelah disimpan (sesuai screenshot Figma) -->
    <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
      <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Setelah disimpan</h4>
      <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
        <li>Menambah Sparepart</li>
        <li>Dapat di edit lagi</li>
      </ul>
    </div>
  </div>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>