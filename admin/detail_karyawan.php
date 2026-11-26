<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$page_title = 'Detail Karyawan';
$active_menu = 'karyawan';

$id_karyawan = (int)($_GET['id'] ?? 0);

if ($id_karyawan <= 0) {
    echo "<script>alert('ID Karyawan tidak ditemukan!'); window.location.href='karyawan.php';</script>";
    exit;
}

$karyawan = null;
$total_unit_aktif = 0;
$total_unit_selesai = 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id LIMIT 1");
    $stmt->execute(['id' => $id_karyawan]);
    $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$karyawan) {
        echo "<script>alert('Data karyawan tidak ditemukan!'); window.location.href='karyawan.php';</script>";
        exit;
    }

    // Hitung jumlah servis yang ditangani karyawan ini
    $stmtServis = $pdo->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE LOWER(COALESCE(status_pengerjaan, '')) NOT IN ('selesai', 'dibatalkan')) AS aktif,
            COUNT(*) FILTER (WHERE LOWER(COALESCE(status_pengerjaan, '')) = 'selesai') AS selesai
        FROM servis
        WHERE id_karyawan = :id
    ");
    $stmtServis->execute(['id' => $id_karyawan]);
    $rekap = $stmtServis->fetch(PDO::FETCH_ASSOC);
    $total_unit_aktif = (int)($rekap['aktif'] ?? 0);
    $total_unit_selesai = (int)($rekap['selesai'] ?? 0);
} catch (PDOException $e) {
    echo "<script>alert('Terjadi kesalahan database: " . addslashes($e->getMessage()) . "'); window.location.href='karyawan.php';</script>";
    exit;
}

$status_saat_ini = !empty($karyawan['status']) ? $karyawan['status'] : 'Aktif';
$peran_saat_ini  = !empty($karyawan['peran']) ? $karyawan['peran'] : 'Teknisi';
$email_tampil    = !empty($karyawan['email']) ? $karyawan['email'] : '-';
$is_aktif        = (strtolower($status_saat_ini) === 'aktif');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- TAMPILAN DETAIL KARYAWAN (READ-ONLY) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">

        <!-- KARTU KIRI: DATA KARYAWAN -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between gap-2 mb-6">
              <h2 class="text-xl font-bold text-blue-darker tracking-tight">
                Data <?= e($peran_saat_ini) ?>
              </h2>
              <span class="px-3.5 py-1 rounded-full bg-white-light-active border border-white-active text-xs font-semibold text-white-dark-hover">
                Mode Lihat Detail
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
              <!-- Nama -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
                <input type="text"
                       value="<?= e($karyawan['nama_karyawan'] ?? '-') ?>"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
              </div>

              <!-- Username -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Username</label>
                <input type="text"
                       value="<?= e($karyawan['username'] ?? '-') ?>"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password</label>
                <input type="text"
                       value="••••••••"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm text-blue-darker tracking-wider focus:outline-none cursor-default">
              </div>

              <!-- Status Karyawan -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Status Karyawan</label>
                <div class="w-full px-4 py-3.5 rounded-2xl border <?= $is_aktif ? 'border-success-light-active/60 bg-success-light text-success-dark' : 'border-white-active bg-white-hover text-blue-darker' ?> text-sm font-semibold select-none">
                  <?= e($status_saat_ini) ?>
                </div>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Email</label>
                <input type="text"
                       value="<?= e($email_tampil) ?>"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-blue-darker focus:outline-none cursor-default">
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Kembali & Edit -->
          <div class="mt-10 pt-6 flex items-center justify-end gap-3.5">
            <a href="karyawan.php"
               class="px-8 py-3 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Kembali
            </a>
            <a href="edit_karyawan.php?id=<?= (int)$karyawan['id_karyawan'] ?>"
               class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Edit Karyawan
            </a>
          </div>
        </div>

        <!-- KARTU KANAN: RINGKASAN KARYAWAN -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              Ringkasan <?= e($peran_saat_ini) ?>
            </h2>

            <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
              <p class="text-xs text-orange-dark font-medium mb-1">Username</p>
              <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight break-all">
                <?= e($karyawan['username'] ?? '-') ?>
              </h3>
              <p class="text-[11px] text-orange-dark/80 mt-2 font-medium">Data terdaftar di sistem</p>
            </div>

            <div class="text-xs font-medium text-blue-darker">
              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Status akun</span>
                <span class="inline-block px-5 py-1 rounded-full text-xs font-semibold <?= $is_aktif ? 'bg-success-light text-success-dark' : 'bg-white-hover text-blue-darker' ?>">
                  <?= e($status_saat_ini) ?>
                </span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Peran</span>
                <span class="font-bold text-blue-darker text-sm"><?= e($peran_saat_ini) ?></span>
              </div>

              <?php if (strtolower($peran_saat_ini) === 'teknisi'): ?>
                <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                  <span class="text-blue-darker/70">Servis aktif</span>
                  <span class="font-bold text-blue-darker text-sm"><?= $total_unit_aktif ?> unit</span>
                </div>

                <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                  <span class="text-blue-darker/70">Servis selesai</span>
                  <span class="font-bold text-success-dark text-sm"><?= $total_unit_selesai ?> unit</span>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Informasi Akun</h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Password disembunyikan demi keamanan</li>
              <li>Gunakan tombol Edit untuk mengubah data atau mengirim tautan reset password</li>
            </ul>
          </div>
        </div>

      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>