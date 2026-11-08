<?php
$page_title = 'Detail Pelanggan';
$active_menu = 'pelanggan';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Data dummy pelanggan yang lagi dilihat
// (jumlah servis diambil otomatis dari data antrean milik pelanggan ini)
$pelanggan = [
    'nama'   => 'Sari Lestari',
    'alamat' => 'Jl. Kertanegara No. 21, Singosari',
    'nomor'  => '0812-3344-5566',
    'servis' => '1 unit',
];
?>

      <!-- DETAIL PELANGGAN SESUAI FIGMA (KARTU TUNGGAL, READ-ONLY) -->
      <div class="max-w-xl">
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200">
          <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Pelanggan</h2>

          <div class="space-y-5 lg:space-y-6">
            <!-- Nama -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
              <input type="text"
                     value="<?= htmlspecialchars($pelanggan['nama']) ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
            </div>

            <!-- Alamat -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
              <input type="text"
                     value="<?= htmlspecialchars($pelanggan['alamat']) ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
            </div>

            <!-- Nomor -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
              <input type="tel"
                     value="<?= htmlspecialchars($pelanggan['nomor']) ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
            </div>

            <!-- Servis (otomatis dari antrean) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Servis</label>
              <input type="text"
                     value="<?= htmlspecialchars($pelanggan['servis']) ?>"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
              <p class="text-[11px] text-white-dark-hover mt-2 font-medium">
                Jumlah servis otomatis diambil dari antrean pelanggan ini.
              </p>
            </div>
          </div>

          <!-- Tombol Aksi Bawah -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a href="pelanggan.php"
               class="px-8 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Batal
            </a>
          </div>
        </div>
      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>