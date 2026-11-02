<?php

// Panggil header pelanggan
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

    <!-- FILTER PILLS & TOMBOL + TAMBAH KARYAWAN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
      <!-- Kelompok Filter Kategori -->
      <div class="flex items-center gap-2.5 overflow-x-auto pb-1 sm:pb-0" id="filterGroup">
        <button type="button"
                data-filter="all"
                class="filter-tab-btn px-5 py-2 rounded-full bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
          Semua 7
        </button>
        <button type="button"
                data-filter="teknisi"
                class="filter-tab-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap">
          Teknisi
        </button>
        <button type="button"
                data-filter="aktif"
                class="filter-tab-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap">
          Aktif
        </button>
        <button type="button"
                data-filter="nonaktif"
                class="filter-tab-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap">
          Nonaktif
        </button>
      </div>

      <!-- Tombol + Tambah Karyawan (Orange) -->
      <a href="tambah_karyawan_admin.html"
         class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold shadow-sm transition-all whitespace-nowrap self-start sm:self-auto cursor-pointer">
        <span class="text-base font-bold">+</span>
        <span>Tambah Karyawan</span>
      </a>
    </div>

    <!-- KARTU TABEL DAFTAR KARYAWAN -->
    <div class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60">
      <!-- Header Tabel -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-base lg:text-lg font-bold text-blue-darker tracking-tight">Daftar Karyawan</h2>
        <span class="text-sm text-white-dark-hover font-medium">7 karyawan</span>
      </div>

      <!-- Tabel Responsif -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm" id="tableKaryawan">
          <thead>
            <tr class="border-b border-white-hover text-white-dark-hover font-semibold text-xs tracking-wider">
              <th class="py-4 px-4">Karyawan</th>
              <th class="py-4 px-4 text-center">Peran</th>
              <th class="py-4 px-4">No. HP</th>
              <th class="py-4 px-4 text-center">Unit Aktif</th>
              <th class="py-4 px-4 text-center">Status</th>
              <th class="py-4 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white-hover/50 text-white-darker font-medium" id="tableBodyKaryawan">
            
            <!-- ROW 1: Admin Ilham Ramadhan -->
            <tr class="hover:bg-warning-light/30 transition-colors row-karyawan"
                data-role="admin"
                data-status="aktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-blue-light text-blue font-bold text-xs flex items-center justify-center shrink-0">
                    AD
                  </div>
                  <div>
                    <h3 class="font-bold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-blue-light text-blue-hover">
                  Admin
                </span>
              </td>
              <td class="py-4 px-4 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-4 text-center font-bold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark">
                  Aktif
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 2: Teknisi Ilham Ramadhan (Aktif) -->
            <tr class="hover:bg-warning-light/30 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="aktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-bold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-orange-light text-orange">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-4 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-4 text-center font-bold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark">
                  Aktif
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 3: Teknisi Ilham Ramadhan (Aktif) -->
            <tr class="hover:bg-warning-light/30 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="aktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-bold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-orange-light text-orange">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-4 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-4 text-center font-bold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark">
                  Aktif
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 4: Teknisi Ilham Ramadhan (Aktif) -->
            <tr class="hover:bg-warning-light/30 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="aktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-bold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-orange-light text-orange">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-4 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-4 text-center font-bold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark">
                  Aktif
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 5: Teknisi Ilham Ramadhan (Nonaktif) -->
            <tr class="hover:bg-warning-light/30 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="nonaktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-bold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-orange-light text-orange">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-4 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-4 text-center font-bold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-white-hover text-white-darker">
                  Nonaktif
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- ROW 6: Teknisi Ilham Ramadhan (Nonaktif) -->
            <tr class="hover:bg-warning-light/30 transition-colors row-karyawan"
                data-role="teknisi"
                data-status="nonaktif"
                data-nama="ilham ramadhan @ilham">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3.5">
                  <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                    TK
                  </div>
                  <div>
                    <h3 class="font-bold text-blue-darker text-sm">Ilham Ramadhan</h3>
                    <p class="text-xs text-white-dark-hover">@ilham</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-orange-light text-orange">
                  Teknisi
                </span>
              </td>
              <td class="py-4 px-4 font-medium text-white-darker">
                0813-4455-9900
              </td>
              <td class="py-4 px-4 text-center font-bold text-blue-darker">
                5 unit
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-4 py-1 rounded-full text-xs font-semibold bg-white-hover text-white-darker">
                  Nonaktif
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <a href="#edit" class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                    Edit
                  </a>
                  <a href="#detail" class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                    Detail
                  </a>
                  <button type="button" class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

      <!-- BANNER PERINGATAN BAWAH TABEL (SESUAI DESAIN FIGMA) -->
      <div class="mt-8 p-4 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium flex items-center gap-2">
        <p>Password tidak ditampilkan di daftar. Gunakan Edit untuk mengatur ulang password karyawan.</p>
      </div>
    </div>

  <!-- ==============================================================================
       JAVASCRIPT INTERAKTIF (SEARCH REALTIME & FILTER KATEGORI)
       ============================================================================== -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const searchInput = document.getElementById('searchKaryawanInput');
      const filterTabs = document.querySelectorAll('#filterGroup .filter-tab-btn');
      const rows = document.querySelectorAll('#tableBodyKaryawan .row-karyawan');

      let currentFilter = 'all';

      function filterTable() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          const role = row.getAttribute('data-role') || '';
          const status = row.getAttribute('data-status') || '';

          const matchSearch = text.includes(query);

          let matchFilter = false;
          if (currentFilter === 'all') {
            matchFilter = true;
          } else if (currentFilter === 'teknisi') {
            matchFilter = (role === 'teknisi');
          } else if (currentFilter === 'aktif') {
            matchFilter = (status === 'aktif');
          } else if (currentFilter === 'nonaktif') {
            matchFilter = (status === 'nonaktif');
          }

          row.style.display = (matchSearch && matchFilter) ? '' : 'none';
        });
      }

      if (searchInput) {
        searchInput.addEventListener('input', filterTable);
      }

      filterTabs.forEach(btn => {
        btn.addEventListener('click', function() {
          filterTabs.forEach(b => {
            b.className = 'filter-tab-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap';
          });
          this.className = 'filter-tab-btn px-5 py-2 rounded-full bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition cursor-pointer whitespace-nowrap';
          currentFilter = this.getAttribute('data-filter') || 'all';
          filterTable();
        });
      });
    });
  </script>


<?php
require_once __DIR__ . '/../includes/footer.php';
?>

