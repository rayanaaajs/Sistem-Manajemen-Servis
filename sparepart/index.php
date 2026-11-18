<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

$page_title = 'Sparepart';
$active_menu = 'sparepart';
$search_placeholder = 'Cari nama atau kode sparepart...';

$sparepart_list = [];
$errorPage = '';

// Jika helpers.php menyediakan konstanta batas stok, gunakan nilainya.
// Database sparepart saat ini tidak memiliki kolom minimal stok per item.
$batasMenipis = defined('BATAS_MENIPIS') ? max(0, (int) BATAS_MENIPIS) : 5;

try {
  // Ambil seluruh daftar sekali. Search, filter dan pagination berlangsung di browser.
  // Agregasi menghindari query terpisah untuk tiap sparepart (N+1).
  $stmt = $pdo->query("
    SELECT
      sp.id_sparepart,
      sp.nama_sparepart,
      sp.stok_tersedia,
      sp.harga_satuan,
      COALESCE(pemakaian.total_dipakai, 0) AS total_dipakai
    FROM public.sparepart AS sp
    LEFT JOIN (
      SELECT
        id_sparepart,
        SUM(jumlah_dipakai) AS total_dipakai
      FROM public.detail_servis
      GROUP BY id_sparepart
    ) AS pemakaian
      ON pemakaian.id_sparepart = sp.id_sparepart
    ORDER BY sp.nama_sparepart ASC, sp.id_sparepart ASC
  ");

  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $sp) {
    $nama = (string) $sp['nama_sparepart'];
    $stok = (int) $sp['stok_tersedia'];
    $status = $stok <= 0 ? 'Habis' : ($stok <= $batasMenipis ? 'Menipis' : 'Aman');

    $sparepart_list[] = [
      'id'       => (int) $sp['id_sparepart'],
      'nama'     => $nama,
      'stok'     => $stok,
      'min_stok' => $batasMenipis,
      'status'   => $status,
      'harga'    => (float) $sp['harga_satuan'],
      'terpakai' => (int) $sp['total_dipakai'] . ' item',
      'inisial'  => function_exists('inisialNama')
        ? inisialNama($nama)
        : strtoupper(substr($nama, 0, 2)),
    ];
  }
} catch (PDOException $e) {
  error_log('sparepart/index.php: ' . $e->getMessage());
  $errorPage = 'Gagal mengambil data sparepart. Silakan coba lagi.';
}

$jumlahSparepart = count($sparepart_list);

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($errorPage !== ''): ?>
  <div role="alert" class="mb-5 rounded-2xl border border-danger bg-danger-light px-5 py-4 text-sm font-medium text-danger-dark">
    <?= e($errorPage) ?>
  </div>
<?php endif; ?>

<!-- AREA AKSI: FILTER (KIRI) & TOMBOL TAMBAH (KANAN) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">

  <!-- Kiri: Kelompok Filter Kategori Status -->
  <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterStatusSparepart">
    <button type="button" data-filter="all" class="filter-sparepart-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
      Semua <?= $jumlahSparepart ?>
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
    <span class="text-sm text-white-dark-hover font-medium"><?= $jumlahSparepart ?> sparepart</span>
  </div>

  <!-- Tabel Responsif -->
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse text-sm" id="tableInventarisSparepart">
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
      <tbody id="tableBody" class="divide-y divide-white-hover text-white-darker font-medium">
        <?php foreach ($sparepart_list as $row): ?>
          <tr class="hover:bg-warning-light/40 transition-colors row-sparepart"
            data-status="<?= e($row['status']) ?>"
            data-search="<?= e($row['nama'] . ' ' . $row['id']) ?>">

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
              <p class="font-semibold text-blue-darker text-sm"><?= (int) $row['stok'] ?> pcs</p>
              <p class="text-[11px] text-white-dark-hover font-medium">min. <?= (int) $row['min_stok'] ?></p>
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
              <?= e($row['terpakai']) ?>
            </td>

            <!-- Aksi -->
            <td class="py-4 px-5 text-center">
              <div class="inline-flex items-center gap-2">

                <!-- Tombol Edit (HANYA UNTUK ADMIN) -->
                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                  <a href="edit.php?id=<?= (int) $row['id'] ?>"
                    class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Edit
                  </a>
                <?php endif; ?>

                <!-- Tombol Detail (BISA DILIHAT SEMUA ROLE) -->
                <a href="detail.php?id=<?= (int) $row['id'] ?>"
                  class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                  Detail
                </a>

                <!-- Tombol Hapus (HANYA UNTUK ADMIN) -->
                <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                  <button type="button"
                    onclick="bukaModalHapus(<?= e(json_encode($row['nama'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>, 'hapus_sparepart.php?id=<?= (int) $row['id'] ?>')"
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
  <div id="emptySparepart" class="hidden text-center py-12 text-white-dark-hover">
    Tidak ada sparepart yang cocok.
  </div>

  <!-- Footer Pagination (client-side) -->
  <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-white-dark-hover mt-8 pt-4 border-t border-gray-100">
    <p id="infoSparepart">Menampilkan <?= $jumlahSparepart ? 1 : 0 ?>-<?= $jumlahSparepart ?> dari <?= $jumlahSparepart ?> sparepart</p>

    <div id="pagerSparepart" class="flex items-center gap-1.5"></div>
  </div>
</div>

<!-- Search, filter status, dan pagination semuanya di sisi client. -->
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const table = document.getElementById("tableInventarisSparepart");
    if (!table) return;

    const PER_PAGE = 7;
    const entries = [...document.querySelectorAll("#tableBody .row-sparepart")].map(row => ({
      row,
      search: (row.dataset.search ?? "").toLocaleLowerCase("id-ID"),
      status: row.dataset.status ?? "",
    }));

    const searchInput = document.getElementById("searchNotaInput")
      ?? document.querySelector('input[placeholder="Cari nama atau kode sparepart..."]');
    const filterButtons = [...document.querySelectorAll(".filter-sparepart-btn")];
    const emptyEl = document.getElementById("emptySparepart");
    const infoEl = document.getElementById("infoSparepart");
    const pagerEl = document.getElementById("pagerSparepart");

    const FILTER_INACTIVE = "filter-sparepart-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
    const FILTER_ACTIVE = "filter-sparepart-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap";
    const PAGE_INACTIVE = "w-8 h-8 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer";
    const PAGE_ACTIVE = "w-8 h-8 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center cursor-pointer shadow-xs";
    const PAGE_ARROW = "text-xs text-blue-darker hover:text-orange font-bold transition px-2 cursor-pointer";

    let activeFilter = "all";
    let searchQuery = "";
    let currentPage = 1;

    const render = () => {
      const filtered = entries.filter(({ search, status }) =>
        search.includes(searchQuery) &&
        (activeFilter === "all" || status === activeFilter)
      );
      const total = filtered.length;
      const pageCount = Math.max(1, Math.ceil(total / PER_PAGE));
      currentPage = Math.min(currentPage, pageCount);

      // Tidak melakukan fetch ulang ke Supabase ketika filter/search berubah.
      entries.forEach(({ row }) => { row.style.display = "none"; });
      filtered
        .slice((currentPage - 1) * PER_PAGE, currentPage * PER_PAGE)
        .forEach(({ row }) => { row.style.display = ""; });

      emptyEl.classList.toggle("hidden", total !== 0);
      const start = total ? (currentPage - 1) * PER_PAGE + 1 : 0;
      const end = Math.min(currentPage * PER_PAGE, total);
      infoEl.textContent = `Menampilkan ${start}-${end} dari ${total} sparepart`;

      pagerEl.replaceChildren();
      const addPage = (label, destination, active, disabled = false, arrow = false) => {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = label;
        button.className = active ? PAGE_ACTIVE : (arrow ? PAGE_ARROW : PAGE_INACTIVE);
        button.disabled = disabled;
        if (disabled) button.classList.add("opacity-40", "cursor-default");
        if (!active && !disabled) {
          button.addEventListener("click", () => {
            currentPage = destination;
            render();
          });
        }
        pagerEl.appendChild(button);
      };

      if (pageCount > 1) addPage("‹", currentPage - 1, false, currentPage === 1, true);
      for (let i = 1; i <= pageCount; i++) {
        addPage(String(i), i, i === currentPage);
      }
      if (pageCount > 1) addPage(">", currentPage + 1, false, currentPage === pageCount, true);
    };

    if (searchInput) {
      searchQuery = searchInput.value.toLocaleLowerCase("id-ID").trim();
      searchInput.addEventListener("input", () => {
        searchQuery = searchInput.value.toLocaleLowerCase("id-ID").trim();
        currentPage = 1;
        render();
      });

      // Jika search pada header berada di dalam form, Enter tidak me-reload halaman.
      searchInput.closest("form")?.addEventListener("submit", event => {
        event.preventDefault();
      });
    }

    filterButtons.forEach(button => {
      button.addEventListener("click", () => {
        filterButtons.forEach(item => { item.className = FILTER_INACTIVE; });
        button.className = FILTER_ACTIVE;
        activeFilter = button.dataset.filter ?? "all";
        currentPage = 1;
        render();
      });
    });

    render();
  });
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>