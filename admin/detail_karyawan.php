<?php

// Panggil header
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- GRID 2 KOLOM (KIRI: DATA TEKNISI, KANAN: RINGKASAN TEKNISI) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-7 items-stretch">

        <!-- ==========================================
             KARTU KIRI: DATA TEKNISI (READ-ONLY)
             ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              <span class="border-b-[2.5px] border-orange pb-0.5">Data</span> Teknisi
            </h2>

            <!-- Grid Input Form (Read-only) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">

              <!-- Nama -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
                <input type="text"
                       name="nama"
                       value="AdittyaTek"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
              </div>

              <!-- Username -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Username</label>
                <input type="text"
                       name="username"
                       value="Aditya.Tek"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none cursor-default transition">
              </div>

              <!-- Alamat -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
                <input type="text"
                       name="alamat"
                       placeholder="Masukkan Alamat"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none cursor-default transition">
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

              <!-- Nomor Whatsapp (Border Navy/Blue-darker sesuai desain) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
                <input type="tel"
                       name="nomor"
                       placeholder="Masukkan Nomor Whatsapp"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-blue-darker bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none cursor-default transition">
              </div>

              <!-- Pilih Status Teknisi -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Status Teknisi</label>
                <div class="relative">
                  <div class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-success-light-active/60 bg-success-light text-sm font-semibold text-success-dark flex items-center justify-between cursor-default">
                    <span>Aktif</span>
                    <svg class="w-4 h-4 text-success-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none cursor-default transition">
              </div>

            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal / Kembali Sesuai Desain Figma -->
          <div class="mt-16 pt-6 flex items-center justify-end">
            <a href="karyawan.php"
               class="px-9 py-2.5 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-sm font-bold transition text-center cursor-pointer">
              Batal
            </a>
          </div>
        </div>

        <!-- ==========================================
             KARTU KANAN: RINGKASAN TEKNISI
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
                <span class="inline-block px-6 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark">
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

      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

