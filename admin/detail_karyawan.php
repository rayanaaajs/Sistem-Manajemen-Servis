<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php'; // Tambahkan koneksi database

$page_title = 'Detail Karyawan';
$active_menu = 'karyawan';

// Panggil header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// 1. Tangkap ID Karyawan dari URL (Contoh: detail_karyawan.php?id=1)
$id_karyawan = $_GET['id'] ?? 0;

if (!$id_karyawan) {
    echo "<script>alert('ID Karyawan tidak ditemukan!'); window.location.href='karyawan.php';</script>";
    exit;
}

try {
    // 2. Ambil data karyawan spesifik dari Supabase
    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute(['id' => $id_karyawan]);
    $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$karyawan) {
        echo "<script>alert('Data karyawan tidak ditemukan!'); window.location.href='karyawan.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert('Terjadi kesalahan database!'); window.location.href='karyawan.php';</script>";
    exit;
}
?>

      <!-- GRID 2 KOLOM (KIRI: DATA TEKNISI, KANAN: RINGKASAN TEKNISI) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">

        <!-- ==========================================
             KARTU KIRI: DATA TEKNISI (READ-ONLY)
             ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              <span class="border-b-[2.5px] border-orange pb-0.5">Data</span> <?= htmlspecialchars($karyawan['peran']) ?>
            </h2>

            <!-- Grid Input Form (Read-only) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">

              <!-- Nama Dinamis -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
                <input type="text"
                       name="nama"
                       value="<?= htmlspecialchars($karyawan['nama_karyawan']) ?>"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
              </div>

              <!-- Username Dinamis -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Username</label>
                <input type="text"
                       name="username"
                       value="<?= htmlspecialchars($karyawan['username']) ?>"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password</label>
                <input type="text"
                       name="password"
                       value="••••••••"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker tracking-wider focus:outline-none cursor-default transition">
              </div>

              <!-- Pilih Status Teknisi -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Status Karyawan</label>
                <div class="relative">
                  <div class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-success-light-active/60 bg-success-light text-sm font-semibold text-success-dark flex items-center justify-between cursor-default">
                    <span>Aktif</span>
                    <svg class="w-4 h-4 text-success-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Email Teknisi Dinamis -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Email</label>
                <input type="email"
                       name="email"
                       value="<?= htmlspecialchars($karyawan['email']) ?>"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none cursor-default transition">
              </div>

            </div>
          </div>

          <div class="mt-8 pt-6 flex items-center justify-end">
            <a href="karyawan.php"
               class="px-8 py-3 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-bold transition text-center cursor-pointer">
              Kembali
            </a>
          </div>
        </div>

        <!-- ==========================================
             KARTU KANAN: RINGKASAN TEKNISI
             ========================================== -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Ringkasan <?= htmlspecialchars($karyawan['peran']) ?></h2>

            <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
              <p class="text-xs text-orange-dark font-medium mb-1">Username</p>
              <!-- Username Dinamis -->
              <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight"><?= htmlspecialchars($karyawan['username']) ?></h3>
            </div>

            <div class="text-xs font-medium text-blue-darker">
              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Status awal</span>
                <span class="inline-block px-6 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark">
                  Aktif
                </span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Peran</span>
                <span class="font-bold text-blue-darker text-sm"><?= htmlspecialchars($karyawan['peran']) ?></span>
              </div>
            </div>
          </div>

          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Informasi</h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Akun ini dapat login ke sistem</li>
              <li>Data bersifat read-only di halaman ini</li>
            </ul>
          </div>
        </div>

      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>