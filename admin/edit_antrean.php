<?php

// Panggil header pelanggan
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

    <!-- FORM 3 KOLOM SESUAI DESAIN FIGMA (EDIT ANTREAN DENGAN DATA PRE-FILLED) -->
    <form action="antrean_admin.php" method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">

      <!-- ==========================================
           KOLOM 1: DATA PELANGGAN
           ========================================== -->
      <div class="bg-white-light rounded-3xl p-6 border border-white-hover/60">
        <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">Data Pelanggan</h2>

        <div class="space-y-5 lg:space-y-6">
          <!-- Pelanggan -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Pelanggan</label>
            <input type="text"
                   name="pelanggan"
                   value="Varell"
                   placeholder="Pilih atau cari pelanggan"
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
          </div>

          <!-- Nomor Whatsapp -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
            <input type="tel"
                   name="nomor"
                   value="087812341234"
                   placeholder="Masukkan Nomor Whatsapp"
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
          </div>

          <!-- Alamat -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
            <input type="text"
                   name="alamat"
                   value="JL.Soekarno Hatta no 112 Blk 20"
                   placeholder="Masukkan Alamat Pelanggan"
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
          </div>
        </div>
      </div>

      <!-- ==========================================
           KOLOM 2: DATA PERANGKAT
           ========================================== -->
      <div class="bg-white-light rounded-3xl p-6 border border-white-hover/60 flex flex-col justify-between">
        <div>
          <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">Data Perangkat</h2>

          <div class="space-y-5 lg:space-y-6">

            <!-- Jenis Barang (Dropdown Select dengan Panah) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Barang</label>
              <div class="relative">
                <select name="jenis_barang"
                        class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer">
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
                     name="merek_spesifikasi"
                     value="ROG - i9 - RAM 64 GB"
                     placeholder="ROG - i9 - RAM 64 GB"
                     class="w-full px-4 py-3.5 rounded-2xl border border-blue-darker/60 bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Kelengkapan -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Kelengkapan</label>
              <input type="text"
                     name="kelengkapan"
                     value="Charger, tas"
                     placeholder="Charger, tas"
                     class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
            </div>

            <!-- Kondisi Awal Unit (Pill Selection: Hidup / Mati / Blank) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Kondisi Awal Unit</label>
              <div class="grid grid-cols-3 gap-2.5" id="groupKondisiUnit">
                <button type="button"
                        data-val="Hidup"
                        class="kondisi-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold transition text-center cursor-pointer">
                  Hidup
                </button>
                <button type="button"
                        data-val="Mati"
                        class="kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer">
                  Mati
                </button>
                <button type="button"
                        data-val="Blank"
                        class="kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer">
                  Blank
                </button>
              </div>
              <input type="hidden" name="kondisi_unit" id="inputKondisiUnit" value="Hidup">
            </div>

            <!-- Password Perangkat (Dengan toggle lihat password) -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Password Perangkat</label>
              <div class="relative">
                <input type="password"
                       id="passwordPerangkat"
                       name="password_perangkat"
                       value="12345678"
                       placeholder="••••••••"
                       class="w-full pl-4 pr-11 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker tracking-wider focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
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
      <div class="bg-white-light rounded-3xl p-6 border border-white-hover/60 flex flex-col justify-between">
        <div class="space-y-5 lg:space-y-6">
          <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">Keluhan dan Penanganan</h2>

          <!-- Keluhan Kerusakan -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Keluhan Kerusakan</label>
            <input type="text"
                   name="keluhan"
                   value="pc saya matot"
                   placeholder="pc saya matot"
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
          </div>

          <!-- Catatan Cek Fisik (Sesuai Desain Edit Antrean) -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Catatan Cek Fisik</label>
            <input type="text"
                   name="catatan_cek_fisik"
                   value="Baret di sudut kiri bawah, engsel kanan agak longgar."
                   placeholder="Baret di sudut kiri bawah, engsel kanan agak longgar."
                   class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
          </div>

          <!-- Row: Pilih Teknisi Aktif & Servis Masuk -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <!-- Pilih Teknisi Aktif -->
            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Teknisi Aktif</label>
              <div class="relative">
                <select name="teknisi" class="w-full pl-9 pr-8 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer">
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
                <input type="date"
                       name="tgl_masuk"
                       value="2026-10-06"
                       class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer">
              </div>
            </div>
          </div>

          <!-- Jenis Layanan (Reguler / Express) -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Layanan</label>
            <div class="grid grid-cols-2 gap-3" id="groupLayanan">
              <button type="button"
                      data-val="Reguler"
                      class="layanan-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold transition text-center cursor-pointer">
                Reguler
              </button>
              <button type="button"
                      data-val="Express"
                      class="layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer">
                Express
              </button>
            </div>
            <input type="hidden" name="jenis_layanan" id="inputJenisLayanan" value="Reguler">
          </div>

          <!-- Persetujuan Bongkar (Checkbox Style Centang Persegi Konsisten dengan Detail) -->
          <div>
            <label class="block text-sm font-bold text-blue-darker mb-2">Persetujuan Bongkar</label>
            <div class="grid grid-cols-2 gap-3" id="groupPersetujuan">
              <label class="persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-blue-darker/60 bg-white-light cursor-pointer transition select-none">
                <input type="radio" name="persetujuan_bongkar" value="setuju" checked class="sr-only">
                <div class="check-box-icon w-5 h-5 rounded-md bg-tosca-dark text-white-light flex items-center justify-center text-xs font-bold transition">
                  ✓
                </div>
                <span class="text-sm font-semibold text-blue-darker">Setuju Dibongkar</span>
              </label>
              <label class="persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light cursor-pointer hover:bg-white-hover transition select-none">
                <input type="radio" name="persetujuan_bongkar" value="tidak_setuju" class="sr-only">
                <div class="check-box-icon w-5 h-5 rounded-md border border-white-active bg-white-light flex items-center justify-center text-xs font-bold text-transparent transition">
                  ✓
                </div>
                <span class="text-sm font-medium text-blue-darker">Tidak Setuju</span>
              </label>
            </div>
          </div>

          <!-- Status Pembayaran & Status Barang -->
          <div class="grid grid-cols-2 gap-3">
            <!-- Status Pembayaran -->
            <div class="relative">
              <select name="status_bayar" class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer">
                <option value="Sudah Dibayar" selected>Sudah Dibayar</option>
                <option value="Belum Dibayar">Belum Dibayar</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </div>
            </div>

            <!-- Status Pengambilan -->
            <div class="relative">
              <select name="status_ambil" class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer">
                <option value="Belum Diambil" selected>Belum Diambil</option>
                <option value="Sudah Diambil">Sudah Diambil</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi Bawah: Batal & Simpan Perubahan -->
        <div class="mt-10 pt-4 flex items-center justify-end gap-3.5">
          <a href="antrean.php"
             class="px-8 py-3 rounded-full border border-tosca-dark hover:bg-tosca-dark-hover text-tosca-dark hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
            Batal
          </a>
          <button type="submit"
                  class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer">
            Simpan Perubahan
          </button>
        </div>
      </div>
    </form>

  <!-- ==============================================================================
       JAVASCRIPT INTERAKTIF
       ============================================================================== -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // 1. Toggle Lihat Password Perangkat
      const togglePasswordBtn = document.getElementById('togglePasswordBtn');
      const passwordInput = document.getElementById('passwordPerangkat');
      if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
          const isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';
        });
      }

      // 2. Toggle Pilihan Kondisi Awal Unit (Hidup / Mati / Blank)
      const kondisiBtns = document.querySelectorAll('#groupKondisiUnit .kondisi-btn');
      const inputKondisi = document.getElementById('inputKondisiUnit');
      kondisiBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          kondisiBtns.forEach(b => {
            b.className = 'kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer';
          });
          this.className = 'kondisi-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold transition text-center cursor-pointer';
          if (inputKondisi) inputKondisi.value = this.getAttribute('data-val');
        });
      });

      // 3. Toggle Pilihan Jenis Layanan (Reguler / Express)
      const layananBtns = document.querySelectorAll('#groupLayanan .layanan-btn');
      const inputLayanan = document.getElementById('inputJenisLayanan');
      layananBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          layananBtns.forEach(b => {
            b.className = 'layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer';
          });
          this.className = 'layanan-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold transition text-center cursor-pointer';
          if (inputLayanan) inputLayanan.value = this.getAttribute('data-val');
        });
      });

      // 4. Update Style Persetujuan Bongkar saat Radio berubah (Tampilan Centang Persegi)
      const persetujuanCards = document.querySelectorAll('#groupPersetujuan .persetujuan-card');
      persetujuanCards.forEach(card => {
        card.addEventListener('click', function() {
          persetujuanCards.forEach(c => {
            c.className = 'persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light cursor-pointer hover:bg-white-hover transition select-none';
            const icon = c.querySelector('.check-box-icon');
            if (icon) {
              icon.className = 'check-box-icon w-5 h-5 rounded-md border border-white-active bg-white-light flex items-center justify-center text-xs font-bold text-transparent transition';
            }
            const span = c.querySelector('span');
            if (span) {
              span.className = 'text-sm font-medium text-blue-darker';
            }
            const radio = c.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
          });

          this.className = 'persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-blue-darker/60 bg-white-light cursor-pointer transition select-none';
          const activeIcon = this.querySelector('.check-box-icon');
          if (activeIcon) {
            activeIcon.className = 'check-box-icon w-5 h-5 rounded-md bg-tosca-dark text-white-light flex items-center justify-center text-xs font-bold transition';
          }
          const activeSpan = this.querySelector('span');
          if (activeSpan) {
            activeSpan.className = 'text-sm font-semibold text-blue-darker';
          }
          const currentRadio = this.querySelector('input[type="radio"]');
          if (currentRadio) currentRadio.checked = true;
        });
      });
    });
  </script>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
