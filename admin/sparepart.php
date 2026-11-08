<?php
$page_title = 'Sparepart';
$active_menu = 'sparepart';
$search_placeholder = 'Cari nama atau kode sparepart...';
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

<!-- AREA AKSI: FILTER (KIRI) & TOMBOL TAMBAH (KANAN) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">

  <!-- Kiri: Kelompok Filter Kategori Status -->
  <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterStatusSparepart">
    <button type="button" data-filter="all" class="filter-sparepart-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
      Semua 7
    </button>
    <button type="button" data-filter="Aman" class="filter-sparepart-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
      Aman
    </button>
    <button type="button" data-filter="Menipis" class="filter-sparepart-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
      Menipis
    </button>
    <button type="button" data-filter="Habis" class="filter-sparepart-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
      Habis
    </button>
  </div>

  <!-- Kanan: Tombol + Tambah Sparepart (HANYA UNTUK ADMIN) -->
  <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
    <a href="tambah_sparepart.php" class="inline-flex items-center justify-center gap-1.5 px-12 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition-all whitespace-nowrap self-start sm:self-auto cursor-pointer">
      <span class="text-sm font-bold">+</span>
      <span>Tambah Sparepart</span>
    </a>
  <?php endif; ?>

</div>

<!-- KARTU TABEL DAFTAR SPAREPART -->
<div class="bg-white-light rounded-2xl p-6 lg:p-8 border border-gray-200">
  <!-- Header Tabel & Info Jumlah -->
  <div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Sparepart</h2>
    <span class="text-sm text-white-dark-hover font-medium">7 sparepart</span>
  </div>

  <!-- Tabel Responsif -->
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse text-sm" id="tableSparepart">
      <thead>
        <tr class="border-b border-white-hover text-white-darker font-bold text-[13px] tracking-wider">
          <th class="py-4 px-5">Nama Sparepart</th>
          <th class="py-4 px-5 text-center">Stok</th>
          <th class="py-4 px-5 text-center">Status</th>
          <th class="py-4 px-5">Harga Satuan</th>
          <th class="py-4 px-5">Terpakai</th>
          <th class="py-4 px-5 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody id="tableBody" class="divide-y divide-white-hover text-white-darker font-medium" id="tableBodySparepart">
        <?php foreach ($sparepart_list as $row): ?>
          <tr class="hover:bg-warning-light/40 transition-colors row-sparepart"
            data-status="<?= htmlspecialchars($row['status']) ?>">

            <!-- Nama Sparepart -->
            <td class="py-4 px-5">
              <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-full bg-orange-light text-orange font-bold text-xs flex items-center justify-center shrink-0">
                  <?= htmlspecialchars($row['inisial']) ?>
                </div>
                <div>
                  <h3 class="font-semibold text-blue-darker text-sm"><?= htmlspecialchars($row['nama']) ?></h3>
                </div>
              </div>
            </td>

            <!-- Stok -->
            <td class="py-4 px-5 text-center">
              <p class="font-semibold text-blue-darker text-sm"><?= $row['stok'] ?> pcs</p>
              <p class="text-[11px] text-white-dark-hover font-medium">min. <?= $row['min_stok'] ?></p>
            </td>

            <!-- Status Badge (Ukurannya w-28) -->
            <td class="py-4 px-5 text-center">
              <?php if ($row['status'] === 'Aman'): ?>
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-success-light text-success-dark tracking-wide">
                  Aman
                </span>
              <?php elseif ($row['status'] === 'Menipis'): ?>
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-warning-light text-warning-dark tracking-wide">
                  Menipis
                </span>
              <?php else: ?>
                <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold bg-danger-light text-danger-dark tracking-wide">
                  Habis
                </span>
              <?php endif; ?>
            </td>

            <!-- Harga Satuan -->
            <td class="py-4 px-5 text-blue-darker text-sm font-semibold">
              Rp <?= number_format($row['harga'], 0, ',', '.') ?>
            </td>

            <!-- Terpakai -->
            <td class="py-4 px-5 text-blue-darker text-sm font-medium">
              <?= htmlspecialchars($row['terpakai']) ?>
            </td>

            <!-- Aksi -->
            <td class="py-4 px-5 text-center">
              <div class="inline-flex items-center gap-2">

                <!-- Tombol Edit (HANYA UNTUK ADMIN) -->
                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                  <a href="edit_sparepart.php?id=<?= $row['id'] ?>"
                    class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Edit
                  </a>
                <?php endif; ?>

                <!-- Tombol Detail (BISA DILIHAT SEMUA ROLE) -->
                <a href="detail_sparepart.php?id=<?= $row['id'] ?>"
                  class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                  Detail
                </a>

                <!-- Tombol Hapus (HANYA UNTUK ADMIN) -->
                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                  <button type="button"
                    onclick="bukaModalHapus('<?= htmlspecialchars($row['nama']) ?>', 'hapus_sparepart.php?id=<?= $row['id'] ?>')"
                    class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                <?php endif; ?>

              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Footer Pagination -->
  <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-white-dark-hover mt-8 pt-4 border-t border-gray-100">
    <p>Menampilkan 1-7 dari 7 sparepart</p>

    <div class="flex items-center gap-1.5">
      <span class="w-8 h-8 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center cursor-pointer shadow-xs">
        1
      </span>
      <a href="#page-2" class="w-8 h-8 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer">
        2
      </a>
      <a href="#page-next" class="text-xs text-blue-darker hover:text-orange font-bold transition px-2 cursor-pointer">
        &gt;
      </a>
    </div>
  </div>
</div>

<!-- JAVASCRIPT: Client-side Filter Status (Tanpa Search Bar) -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const filterButtons = document.querySelectorAll(".filter-sparepart-btn");
    const rows = document.querySelectorAll(".row-sparepart");

    filterButtons.forEach(btn => {
      btn.addEventListener("click", function() {
        // Reset semua tombol ke style abu-abu outline
        filterButtons.forEach(b => {
          b.className = "filter-sparepart-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
        });
        // Bikin tombol yang diklik jadi tosca solid
        this.className = "filter-sparepart-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap";

        const filter = this.getAttribute("data-filter");

        // Filter baris tabel berdasarkan status
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