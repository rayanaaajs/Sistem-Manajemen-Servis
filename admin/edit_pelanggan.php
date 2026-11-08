<?php
$page_title = 'Edit Pelanggan';
$active_menu = 'pelanggan';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Data dummy pelanggan yang lagi diedit
// (jumlah servis TIDAK diedit di sini, diambil otomatis dari data antrean pelanggan)
$pelanggan = [
    'nama'   => 'Sari Lestari',
    'alamat' => 'Jl. Kertanegara No. 21, Singosari',
    'nomor'  => '0812-3344-5566',
];
?>

      <!-- FORM EDIT PELANGGAN SESUAI FIGMA (KARTU TUNGGAL DATA PELANGGAN) -->
      <div class="max-w-xl">
        <form action="pelanggan.php" method="GET" class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200">
          <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Pelanggan</h2>

          <div class="space-y-5 lg:space-y-6">
            <!-- Nama -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
              <input type="text"
                     name="nama"
                     placeholder="Masukkan Nama"
                     value="<?= htmlspecialchars($pelanggan['nama']) ?>"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Alamat -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
              <input type="text"
                     name="alamat"
                     placeholder="Masukkan Alamat"
                     value="<?= htmlspecialchars($pelanggan['alamat']) ?>"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Nomor -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
              <input type="tel"
                     name="nomor"
                     placeholder="Masukkan Nomor Whatsapp"
                     value="<?= htmlspecialchars($pelanggan['nomor']) ?>"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Simpan Perubahan -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a href="pelanggan.php"
               class="px-8 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>