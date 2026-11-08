<?php
$page_title = 'Tambah Sparepart';
$active_menu = 'sparepart';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- FORM TAMBAH SPAREPART SESUAI DESAIN FIGMA -->
      <div class="max-w-xl">
        <form action="sparepart.php" method="GET" class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60">
          <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Sparepart</h2>

          <div class="space-y-5 lg:space-y-6">
            <!-- Nama Sparepart -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Nama Sparepart</label>
              <input type="text"
                     name="nama"
                     placeholder="Masukkan Nama"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Stok (Border Navy / Blue-darker sesuai screenshot Figma) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Stok</label>
              <input type="text"
                     name="stok"
                     placeholder="Masukkan Stok"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Harga -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Harga</label>
              <input type="text"
                     name="harga"
                     placeholder="Masukkan Harga Satuan"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Status</label>
              <div class="relative w-40 sm:w-48">
                <select name="status"
                        class="w-full px-4 py-3 rounded-2xl border border-white-active bg-success-light text-sm font-semibold text-success-dark text-center appearance-none focus:outline-none transition cursor-pointer">
                  <option value="Aman" selected>Aman</option>
                  <option value="Menipis">Menipis</option>
                  <option value="Habis">Habis</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Tambah Sparepart -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a href="sparepart.php"
               class="px-8 py-2.5 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-sm font-bold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-bold transition cursor-pointer">
              Tambah Sparepart
            </button>
          </div>
        </form>
      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

