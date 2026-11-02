<?php

// Panggil header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- FORM 2 KOLOM (KIRI: FORM DATA TEKNISI, KANAN: RINGKASAN TEKNISI) -->
      <form action="karyawan.php" method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-7 items-stretch">

        <!-- ==========================================
             KARTU KIRI: DATA TEKNISI (2 KOLOM GRID)
             ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              <span class="border-b-[2.5px] border-orange pb-0.5">Data</span> Teknisi
            </h2>

            <!-- Grid Input Form -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">

              <!-- Nama -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
                <input type="text"
                       name="nama"
                       value="Aditya Tek"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Username -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Username</label>
                <input type="text"
                       name="username"
                       value="Aditya Tek"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Alamat -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
                <input type="text"
                       name="alamat"
                       value="Jl.depan kopstud malang"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password</label>
                <input type="text"
                       name="password"
                       value="••••••••"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker tracking-wider focus:outline-none transition cursor-default">
              </div>

              <!-- Nomor Whatsapp (Border Navy/Blue-darker sesuai desain) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
                <input type="tel"
                       name="nomor"
                       value="0872526256253"
                       class="w-full px-4 py-3.5 rounded-2xl border border-blue-darker bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Pilih Status Teknisi -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Status Teknisi</label>
                <div class="relative">
                  <select name="status_teknisi"
                          class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-success-light-active/60 bg-success-light text-sm font-semibold text-success-dark appearance-none focus:outline-none transition cursor-pointer">
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                  </select>
                  <div id="statusArrowIcon" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-success-dark">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Email Teknisi -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Email Teknisi</label>
                <input type="email"
                       name="email"
                       value="Example@gmail.com"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

            </div>
          </div>

          <!-- ==========================================
               BAGIAN UBAH PASSWORD (RESET VIA EMAIL)
               ========================================== -->
          <div class="mt-8">
            <h3 class="text-sm font-bold text-blue-darker mb-3">Ubah Password</h3>
            <div class="flex flex-col sm:flex-row items-center gap-4 p-5 rounded-2xl bg-lightblue-light/40 border border-white-active/40">
              <!-- Ikon Gembok -->
              <div class="w-14 h-14 rounded-full bg-orange-light-active flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <!-- Teks Instruksi -->
              <div class="flex-1 text-center sm:text-left">
                <p class="text-sm font-bold text-blue-darker">Kirim Tautan Reset Password</p>
                <p class="text-xs text-blue-darker/70 mt-0.5">Kirimkan instruksi ke email Example@gmail.com untuk perbarui kata sandi teknisi.</p>
              </div>
              <!-- Tombol Kirim -->
              <button type="button"
                      class="px-5 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-bold shadow-sm transition cursor-pointer whitespace-nowrap shrink-0">
                Kirim Link Reset Password
              </button>
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Simpan Perubahan -->
          <div class="mt-8 pt-6 flex items-center justify-end gap-3.5">
            <a href="karyawan.php"
               class="px-8 py-3 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-sm font-bold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-bold shadow-sm transition cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </div>

        <!-- ==========================================
             KARTU KANAN: RINGKASAN TEKNISI (TERISI DATA)
             ========================================== -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Ringkasan Teknisi</h2>

            <!-- Banner Username Oranye -->
            <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
              <p class="text-xs text-orange-dark font-medium mb-1">Username</p>
              <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight">Aditya.Tek</h3>
              <p class="text-[11px] text-orange-dark/80 mt-2 font-medium">Dibuat otomatis saat disimpan</p>
            </div>

            <!-- Daftar Informasi Ringkasan -->
            <div class="text-xs font-medium text-blue-darker">
              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Tanggal Akun Dibuat</span>
                <span class="font-bold text-blue-darker text-sm">3 Okt 2026</span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Status awal</span>
                <span id="ringkasanStatusBadge" class="inline-block px-6 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark transition-all">
                  Aktif
                </span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Peran</span>
                <span class="font-bold text-blue-darker text-sm">Teknisi</span>
              </div>

              <div class="flex justify-between items-center py-3.5">
                <span class="text-blue-darker/70">Teknisi</span>
                <span class="font-bold text-blue-darker text-sm">ke-6</span>
              </div>
            </div>
          </div>

          <!-- Banner Biru Muda: Setelah disimpan -->
          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Setelah disimpan</h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Akun bisa di loginkan</li>
              <li>Dapat di edit lagi</li>
            </ul>
          </div>
        </div>

      </form>

    <!-- JAVASCRIPT: Status Dropdown/Badge Color Sync -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const statusSelect = document.querySelector('select[name="status_teknisi"]');
        const statusArrow = document.getElementById("statusArrowIcon");
        const ringkasanBadge = document.getElementById("ringkasanStatusBadge");

        if (statusSelect) {
          function updateStatusColor() {
            const isAktif = statusSelect.value === "Aktif";

            if (isAktif) {
              statusSelect.className = "w-full pl-4 pr-10 py-3.5 rounded-2xl border border-success-light-active/60 bg-success-light text-sm font-semibold text-success-dark appearance-none focus:outline-none transition cursor-pointer";
              if (statusArrow) {
                statusArrow.className = "absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-success-dark";
              }
            } else {
              statusSelect.className = "w-full pl-4 pr-10 py-3.5 rounded-2xl border border-white-active bg-white-hover text-sm font-semibold text-blue-darker appearance-none focus:outline-none transition cursor-pointer";
              if (statusArrow) {
                statusArrow.className = "absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-blue-darker";
              }
            }

            if (ringkasanBadge) {
              ringkasanBadge.textContent = statusSelect.value;
              if (isAktif) {
                ringkasanBadge.className = "inline-block px-6 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark transition-all";
              } else {
                ringkasanBadge.className = "inline-block px-6 py-1 rounded-full text-xs font-semibold bg-white-hover text-blue-darker transition-all";
              }
            }
          }

          statusSelect.addEventListener("change", updateStatusColor);
          updateStatusColor();
        }
      });
    </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

