<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$page_title = 'Detail Pelanggan';
$active_menu = 'pelanggan';

$id_pelanggan = (int)($_GET['id'] ?? 0);

if ($id_pelanggan <= 0) {
    echo "<script>alert('ID Pelanggan tidak ditemukan!'); window.location.href='pelanggan.php';</script>";
    exit;
}

$pelanggan = null;
$jumlah_servis = 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = :id LIMIT 1");
    $stmt->execute(['id' => $id_pelanggan]);
    $pelanggan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pelanggan) {
        echo "<script>alert('Data pelanggan tidak ditemukan!'); window.location.href='pelanggan.php';</script>";
        exit;
    }

    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_pelanggan = :id");
    $stmtCount->execute(['id' => $id_pelanggan]);
    $jumlah_servis = (int)$stmtCount->fetchColumn();
} catch (PDOException $e) {
    echo "<script>alert('Terjadi kesalahan database: " . addslashes($e->getMessage()) . "'); window.location.href='pelanggan.php';</script>";
    exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- DETAIL PELANGGAN (KARTU TUNGGAL, READ-ONLY) -->
      <div class="max-w-xl">
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200">
          <div class="flex items-center justify-between gap-2 mb-6">
            <h2 class="text-xl font-bold text-blue-darker tracking-tight">Data Pelanggan</h2>
            <span class="px-3.5 py-1 rounded-full bg-white-light-active border border-white-active text-xs font-semibold text-white-dark-hover">
              Mode Lihat Detail
            </span>
          </div>

          <div class="space-y-5 lg:space-y-6">
            <!-- Nama -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
              <input type="text"
                     value="<?= e($pelanggan['nama_pelanggan'] ?? '-') ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
            </div>

            <!-- Alamat -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
              <input type="text"
                     value="<?= e($pelanggan['alamat'] ?? '-') ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
            </div>

            <!-- Nomor -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
              <input type="text"
                     value="<?= e($pelanggan['no_hp'] ?? '-') ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
            </div>

            <!-- Servis (otomatis dari antrean) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Servis</label>
              <input type="text"
                     value="<?= $jumlah_servis ?> unit"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
              <p class="text-[11px] text-white-dark-hover mt-2 font-medium">
                Jumlah servis otomatis diambil dari riwayat antrean pelanggan ini.
              </p>
            </div>
          </div>

          <!-- Tombol Aksi Bawah -->
          <div class="mt-10 pt-6 flex items-center justify-end gap-3.5">
            <a href="pelanggan.php"
               class="px-8 py-3 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Kembali
            </a>
            <a href="edit_pelanggan.php?id=<?= (int)$pelanggan['id_pelanggan'] ?>"
               class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Edit Pelanggan
            </a>
          </div>
        </div>
      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>