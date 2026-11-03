<?php

// Panggil header pelanggan
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

    <!-- FILTER PILLS & TOMBOL + TAMBAH KARYAWAN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
      <!-- Kelompok Filter Kategori -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterGroup">
        <button type="button"
                data-filter="all"
                class="filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
          Semua 7
        </button>
        <button type="button"
                data-filter="teknisi"
                class="filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
          Teknisi
        </button>
        <button type="button"
                data-filter="aktif"
                class="filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
          Aktif
        </button>
        <button type="button"
                data-filter="nonaktif"
                class="filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
          Nonaktif
        </button>
      </div>

      <!-- Tombol + Tambah Karyawan (Orange) -->
      <a href="tambah_karyawan.php"
         class="inline-flex items-center justify-center gap-1.5 px-12 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition-all whitespace-nowrap self-start sm:self-auto cursor-pointer">
        <span class="text-sm font-semibold">+</span>
        <span>Tambah Karyawan</span>
      </a>
    </div>

    <!-- KARTU TABEL DAFTAR KARYAWAN -->
    <div class="bg-white-light rounded-2xl p-6 lg:p-8 border border-gray-200">
      <!-- Header Tabel -->
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-blue-darker tracking-tight">Daftar Karyawan</h2>
        <span class="text-sm text-white-dark-hover font-medium">7 karyawan</span>
      </div>

      <!-- Tabel Responsif -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm" id="tableKaryawan">
          <thead>
            <tr class="border-b border-white-hover text-white-darker font-semibold text-[13px] tracking-wider">
              <th class="py-4 px-5">Karyawan</th>
              <th class="py-4 px-5 text-center">Peran</th>
              <th class="py-4 px-5">No. HP</th>
              <th class="py-4 px-5 text-center">Unit Aktif</th>
              <th class="py-4 px-5 text-center">Status</th>
              <th class="py-4 px-5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white-hover text-white-darker font-medium" id="tableBodyKaryawan">
            
            <!-- ROW 1: Admin Ilham Ramadhan -->
            <tr class="hover:bg-warning-light/40 transition-colors row-karyawan"
                data-role="admin"
                data-status="aktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-5">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-blue-light text-blue font-bold text-xs flex items-center justify-center shrink-0">
                    AD
                  </div>
                  <div>
                    <h3 class="font-semibold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-5 text-center">
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-blue-light text-blue-hover tracking-wide">
                  Admin
                </span>
              </td>
              <td class="py-4 px-5 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-5 text-center font-semibold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-5 text-center">
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-success-light text-success-dark tracking-wide">
                  Aktif
                </span>
              </td>
              <td class="py-4 px-5 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 2: Teknisi Ilham Ramadhan (Aktif) -->
            <tr class="hover:bg-warning-light/40 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="aktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-5">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-danger-dark font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-semibold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-5 text-center">
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-orange-light text-danger-dark tracking-wide">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-5 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-5 text-center font-semibold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-5 text-center">
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-success-light text-success-dark tracking-wide">
                  Aktif
                </span>
              </td>
              <td class="py-4 px-5 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 3: Teknisi Ilham Ramadhan (Nonaktif) -->
            <tr class="hover:bg-warning-light/40 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="nonaktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-5">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-danger-dark font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-semibold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-5 text-center">
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-orange-light text-danger-dark tracking-wide">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-5 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-5 text-center font-semibold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-5 text-center">
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-white-hover text-white-darker tracking-wide">
                  Nonaktif
                </span>
              </td>
              <td class="py-4 px-5 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

      <!-- BANNER PERINGATAN BAWAH TABEL -->
      <div class="mt-8 p-4 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium flex items-center gap-2">
        <p>Password tidak ditampilkan di daftar. Gunakan Edit untuk mengatur ulang password karyawan.</p>
      </div>
    </div>


<?php
require_once __DIR__ . '/../includes/footer.php';
?>