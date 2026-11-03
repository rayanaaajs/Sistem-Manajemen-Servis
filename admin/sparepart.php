<?php

// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Data dummy sparepart sesuai screenshot Figma
$sparepart_list = [
    [
        'id'       => 1,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 12,
        'min_stok' => 5,
        'status'   => 'Aman',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ],
    [
        'id'       => 2,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 12,
        'min_stok' => 5,
        'status'   => 'Aman',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ],
    [
        'id'       => 3,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 5,
        'min_stok' => 5,
        'status'   => 'Menipis',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ],
    [
        'id'       => 4,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 12,
        'min_stok' => 5,
        'status'   => 'Aman',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ],
    [
        'id'       => 5,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 5,
        'min_stok' => 5,
        'status'   => 'Menipis',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ],
    [
        'id'       => 6,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 0,
        'min_stok' => 5,
        'status'   => 'Habis',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ],
    [
        'id'       => 7,
        'nama'     => 'RAM DDR4 8GB SODIMM',
        'stok'     => 12,
        'min_stok' => 5,
        'status'   => 'Aman',
        'harga'    => 285000,
        'terpakai' => '8 item',
        'inisial'  => 'RM'
    ]
];
?>

      <!-- FILTER KATEGORI & TOMBOL + TAMBAH SPAREPART -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
        <!-- Kelompok Filter Kategori Status -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 sm:pb-0" id="filterStatusSparepart">
          <button type="button"
                  data-filter="all"
                  class="filter-sparepart-btn px-5 py-2 rounded-full bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
            Semua 7
          </button>
          <button type="button"
                  data-filter="Aman"
                  class="filter-sparepart-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap">
            Aman
          </button>
          <button type="button"
                  data-filter="Habis"
                  class="filter-sparepart-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap">
            Habis
          </button>
          <button type="button"
                  data-filter="Menipis"
                  class="filter-sparepart-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap">
            Menipis
          </button>
        </div>

        <!-- Tombol + Tambah Sparepart -->
        <a href="tambah_sparepart.php"
           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold shadow-sm transition-all whitespace-nowrap self-start sm:self-auto cursor-pointer">
          <span class="text-base font-bold">+</span>
          <span>Tambah Sparepart</span>
        </a>
      </div>

      <!-- KARTU TABEL DAFTAR SPAREPART -->
      <div class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60">
        <!-- Header Tabel -->
        <div class="mb-6">
          <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight">Daftar Sparepart</h2>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm" id="tableSparepart">
            <thead>
              <tr class="border-b border-white-hover text-white-dark-hover font-semibold text-xs tracking-wider">
                <th class="py-4 px-4">Nama Sparepart</th>
                <th class="py-4 px-4 text-center">Stok</th>
                <th class="py-4 px-4 text-center">Status</th>
                <th class="py-4 px-4">Harga Satuan</th>
                <th class="py-4 px-4">Terpakai</th>
                <th class="py-4 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white-hover/50 text-white-darker font-medium" id="tableBodySparepart">
              <?php foreach ($sparepart_list as $row): ?>
              <tr class="hover:bg-warning-light/30 transition-colors row-sparepart"
                  data-status="<?= htmlspecialchars($row['status']) ?>"
                  data-nama="<?= strtolower(htmlspecialchars($row['nama'])) ?>">
                
                <!-- Nama Sparepart -->
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                      <?= htmlspecialchars($row['inisial']) ?>
                    </div>
                    <div>
                      <h3 class="font-bold text-blue-darker text-sm"><?= htmlspecialchars($row['nama']) ?></h3>
                    </div>
                  </div>
                </td>

                <!-- Stok -->
                <td class="py-4 px-4 text-center">
                  <p class="font-bold text-blue-darker text-sm"><?= $row['stok'] ?> pcs</p>
                  <p class="text-[11px] text-white-dark-hover font-medium">min. <?= $row['min_stok'] ?></p>
                </td>

                <!-- Status Badge -->
                <td class="py-4 px-4 text-center">
                  <?php if ($row['status'] === 'Aman'): ?>
                    <span class="inline-block px-5 py-1.5 rounded-full text-xs font-semibold bg-success-light text-success-dark">
                      Aman
                    </span>
                  <?php elseif ($row['status'] === 'Menipis'): ?>
                    <span class="inline-block px-5 py-1.5 rounded-full text-xs font-semibold bg-warning-light text-warning-dark">
                      Menipis
                    </span>
                  <?php else: ?>
                    <span class="inline-block px-5 py-1.5 rounded-full text-xs font-semibold bg-danger-light text-danger-dark">
                      Habis
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Harga Satuan -->
                <td class="py-4 px-4 text-blue-darker text-sm font-semibold">
                  Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                </td>

                <!-- Terpakai -->
                <td class="py-4 px-4 text-blue-darker/80 text-sm font-medium">
                  <?= htmlspecialchars($row['terpakai']) ?>
                </td>

                <!-- Aksi -->
                <td class="py-4 px-4 text-center">
                  <div class="inline-flex items-center gap-2">
                    <a href="edit_sparepart.php?id=<?= $row['id'] ?>"
                       class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-xs font-semibold transition cursor-pointer">
                      Edit
                    </a>
                    <a href="detail_sparepart.php?id=<?= $row['id'] ?>"
                       class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                      Detail
                    </a>
                    <button type="button"
                            class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Footer Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 pt-4 border-t border-white-hover/60">
          <p class="text-xs text-white-dark-hover">
            Menampilkan 1-8 dari 48 antrean
          </p>

          <div class="flex items-center gap-1.5">
            <button type="button" class="w-7 h-7 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer">
              &lt;
            </button>
            <span class="w-7 h-7 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center cursor-pointer shadow-xs">
              1
            </span>
            <button type="button" class="w-7 h-7 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer">
              2
            </button>
            <button type="button" class="w-7 h-7 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer">
              3
            </button>
            <button type="button" class="w-7 h-7 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer">
              &gt;
            </button>
          </div>
        </div>
      </div>

    <!-- JAVASCRIPT: Client-side Filter Status & Search -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const filterButtons = document.querySelectorAll(".filter-sparepart-btn");
        const rows = document.querySelectorAll(".row-sparepart");

        filterButtons.forEach(btn => {
          btn.addEventListener("click", function () {
            // Reset active style
            filterButtons.forEach(b => {
              b.className = "filter-sparepart-btn px-5 py-2 rounded-full bg-white-light text-white-darker border border-white-active text-sm font-medium shadow-2xs hover:bg-white-hover transition cursor-pointer whitespace-nowrap";
            });

            // Set active style on clicked button
            this.className = "filter-sparepart-btn px-5 py-2 rounded-full bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition cursor-pointer whitespace-nowrap";

            const filter = this.getAttribute("data-filter");

            rows.forEach(row => {
              const status = row.getAttribute("data-status");
              if (filter === "all" || status === filter) {
                row.style.display = "";
              } else {
                row.style.display = "none";
              }
            });
          });
        });
      });
    </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

