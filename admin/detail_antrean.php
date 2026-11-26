<?php

// Panggil header pelanggan
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>
    <!-- FORM 3 KOLOM DETAIL ANTREAN (SESUAI DESAIN FIGMA DENGAN DATA PRE-FILLED) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">

      <!-- ==========================================
           KOLOM 1: DATA PELANGGAN
           ========================================== -->
      <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
        <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">Data Pelanggan</h2>

        <div class="space-y-5 lg:space-y-6">
          <!-- Pelanggan -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Pelanggan</label>
            <input type="text"
                   value="Varell"
                   readonly
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
          </div>

          <!-- Nomor Whatsapp -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
            <input type="tel"
                   value="087612341234"
                   readonly
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
          </div>

          <!-- Alamat -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
            <input type="text"
                   value="JL.Danau Sentani Tengah"
                   readonly
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
          </div>
        </div>
      </div>

      <!-- ==========================================
           KOLOM 2: DATA PERANGKAT
           ========================================== -->
      <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60 flex flex-col justify-between">
        <div>
          <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">Data Perangkat</h2>

          <div class="space-y-5 lg:space-y-6">

            <!-- Jenis Barang (Dropdown Select dengan Panah) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Barang</label>
              <div class="relative">
                <select disabled
                        class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none cursor-default">
                  <option value="Computer" selected>Computer</option>
                  <option value="Laptop">Laptop</option>
                  <option value="Printer">Printer</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-white-dark-hover">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Merek dan Spesifikasi -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Merek dan Spesifikasi</label>
              <input type="text"
                     value="ROG - i9 - RAM 64 GB"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-blue-darker/60 bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
            </div>

            <!-- Kelengkapan -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Kelengkapan</label>
              <input type="text"
                     value="Charger, tas"
                     readonly
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
            </div>

            <!-- Kondisi Awal Unit (Pill Selection: Hidup / Mati / Blank) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Kondisi Awal Unit</label>
              <div class="grid grid-cols-3 gap-2.5" id="groupKondisiUnit">
                <div class="py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold text-center">
                  Hidup
                </div>
                <div class="py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker text-sm font-medium text-center">
                  Mati
                </div>
                <div class="py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker text-sm font-medium text-center">
                  Blank
                </div>
              </div>
            </div>

            <!-- Password Perangkat (Dengan toggle lihat password) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Password Perangkat</label>
              <div class="relative">
                <input type="password"
                       id="passwordPerangkat"
                       value="12345678"
                       readonly
                       class="w-full pl-4 pr-11 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker tracking-wider focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
                <button type="button"
                        id="togglePasswordBtn"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-white-dark-hover hover:text-blue-darker cursor-pointer">
                  <!-- Eye Icon -->
                  <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ==========================================
           KOLOM 3: KELUHAN DAN PENANGANAN
           ========================================== -->
      <div class="bg-white-light rounded-3xl p-7 lg:p-8 -sm border border-white-hover/60 flex flex-col justify-between">
        <div class="space-y-5 lg:space-y-6">
          <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">Keluhan dan Penanganan</h2>

          <!-- Keluhan Kerusakan -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Keluhan Kerusakan</label>
            <input type="text"
                   value="pc saya matot"
                   readonly
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
          </div>

          <!-- Catatan Cek Fisik -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Catatan Cek Fisik</label>
            <input type="text"
                   value="Baret di sudut kiri bawah, engsel kanan agak longgar."
                   readonly
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
          </div>

          <!-- Row: Pilih Teknisi Aktif & Servis Masuk -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <!-- Pilih Teknisi Aktif -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Teknisi Aktif</label>
              <div class="relative">
                <select disabled
                        class="w-full pl-9 pr-8 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none cursor-default">
                  <option value="Ilham" selected>Ilham</option>
                  <option value="Bagas">Bagas</option>
                  <option value="Dimas">Dimas</option>
                </select>
                <!-- Avatar Inisial Kecil -->
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <div class="w-5 h-5 rounded-full bg-orange-light-active text-orange text-[9px] font-bold flex items-center justify-center">
                    IL
                  </div>
                </div>
                <!-- Panah Dropdown -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
              </div>
            </div>

            <!-- Servis Masuk (Tanggal) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Servis Masuk</label>
              <div class="relative">
                <input type="text"
                       value="6 Okt 2026"
                       readonly
                       class="w-full pl-4 pr-9 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-default">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Jenis Layanan (Reguler / Express) -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Layanan</label>
            <div class="grid grid-cols-2 gap-3" id="groupLayanan">
              <div class="py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold -xs text-center">
                Reguler
              </div>
              <div class="py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker text-sm font-medium text-center">
                Express
              </div>
            </div>
          </div>

          <!-- Persetujuan Bongkar (Checkbox Style) -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Persetujuan Bongkar</label>
            <div class="grid grid-cols-2 gap-3">
              <div class="flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-blue-darker/60 bg-white-light">
                <div class="w-4 h-4 rounded bg-tosca-dark text-white-light flex items-center justify-center text-[10px] font-bold">✓</div>
                <span class="text-sm font-semibold text-blue-darker">Setuju Dibongkar</span>
              </div>
              <div class="flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light opacity-60">
                <div class="w-4 h-4 rounded border border-white-active bg-white-light"></div>
                <span class="text-sm font-medium text-blue-darker">Tidak Setuju</span>
              </div>
            </div>
          </div>

          <!-- Status Pembayaran & Status Barang -->
          <div class="grid grid-cols-2 gap-3">
            <!-- Status Pembayaran -->
            <div class="relative">
              <select disabled
                      class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none cursor-default">
                <option value="Sudah Dibayar" selected>Sudah Dibayar</option>
                <option value="Belum Dibayar">Belum Dibayar</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </div>
            </div>

            <!-- Status Pengambilan -->
            <div class="relative">
              <select disabled
                      class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none cursor-default">
                <option value="Belum Diambil" selected>Belum Diambil</option>
                <option value="Sudah Diambil">Sudah Diambil</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi Bawah: Hanya Tombol "Kembali" Sesuai Desain Detail -->
        <div class="mt-10 pt-4 flex items-center justify-end">
          <a href="antrean.php"
             class="px-10 py-3 rounded-full border border-tosca-dark hover:bg-tosca-dark text-tosca-dark hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
            Kembali
          </a>
        </div>
      </div>
    </div>

  <!-- JAVASCRIPT: Toggle Lihat Password -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const togglePasswordBtn = document.getElementById('togglePasswordBtn');
      const passwordInput = document.getElementById('passwordPerangkat');
      if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
          const isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';
        });
      }
    });
  </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

