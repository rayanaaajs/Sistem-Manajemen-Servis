<?php
require_once __DIR__ . '/../includes/auth.php';   
$page_title = 'Tambah Pelanggan';
$active_menu = 'pelanggan';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- FORM TAMBAH PELANGGAN SESUAI FIGMA (KARTU TUNGGAL DATA PELANGGAN) -->
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
                     required
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Alamat -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
              <input type="text"
                     name="alamat"
                     placeholder="Masukkan Alamat"
                     required
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Nomor -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
              <input type="tel"
                     name="nomor"
                     placeholder="Masukkan Nomor Whatsapp"
                     required
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Simpan -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a href="pelanggan.php"
               class="px-8 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer">
              Simpan
            </button>
          </div>
        </form>
      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>