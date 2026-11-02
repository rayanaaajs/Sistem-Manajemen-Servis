<?php

// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- FORM EDIT SPAREPART SESUAI DESAIN FIGMA (2 KOLOM: FORM DATA & RINGKASAN) -->
      <form action="sparepart.php" method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-7 items-stretch">

        <!-- ==========================================
             KARTU KIRI: DATA SPAREPART
             ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Sparepart</h2>

            <div class="space-y-5 lg:space-y-6 max-w-xl">
              <!-- Nama Sparepart -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama Sparepart</label>
                <input type="text"
                       name="nama"
                       placeholder="Masukkan Nama"
                       value="RAM FR CUB"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Stok (Border Navy / Blue-darker sesuai screenshot Figma) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Stok</label>
                <input type="text"
                       name="stok"
                       placeholder="Masukkan Stok"
                       value="60"
                       class="w-full px-4 py-3.5 rounded-2xl border border-blue-darker bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Harga -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Harga</label>
                <input type="text"
                       name="harga"
                       placeholder="Masukkan Harga Satuan"
                       value="Rp 30.000.000"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Status -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Status</label>
                <div class="relative w-40 sm:w-48">
                  <select name="status"
                          id="statusSparepartSelect"
                          class="w-full px-4 py-3 rounded-2xl border border-white-active bg-success-light text-sm font-semibold text-success-dark text-center appearance-none focus:outline-none transition cursor-pointer">
                    <option value="Aman" selected>Aman</option>
                    <option value="Menipis">Menipis</option>
                    <option value="Habis">Habis</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Simpan Perubahan -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a href="sparepart.php"
               class="px-8 py-2.5 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-sm font-bold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-bold shadow-sm transition cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </div>

        <!-- ==========================================
             KARTU KANAN: RINGKASAN SPAREPART
             ========================================== -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Ringkasan Sparepart</h2>

            <!-- Banner Nama Sparepart Oranye -->
            <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
              <p class="text-xs text-orange-dark font-medium mb-1">Nama</p>
              <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight">RAM FR CUB</h3>
              <p class="text-[11px] text-orange-dark/80 mt-2 font-medium">Dibuat otomatis saat disimpan</p>
            </div>

            <!-- Daftar Informasi Ringkasan -->
            <div class="text-xs font-medium text-blue-darker">
              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Status</span>
                <span id="ringkasanStatusSparepart" class="inline-block px-6 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark transition-all">
                  Aktif
                </span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Harga</span>
                <span class="font-bold text-blue-darker text-sm">Rp. 30.000.000</span>
              </div>

              <div class="flex justify-between items-center py-3.5">
                <span class="text-blue-darker/70">Stok</span>
                <span class="font-bold text-blue-darker text-sm">60</span>
              </div>
            </div>
          </div>

          <!-- Banner Biru Muda: Setelah disimpan -->
          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Setelah disimpan</h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Perubahan akan di simpan</li>
              <li>Dapat di edit lagi</li>
            </ul>
          </div>
        </div>

      </form>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

