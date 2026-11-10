<?php
// ==============================================================================
// 1. DATA DUMMY (nanti diganti query database antrean milik teknisi)
// ==============================================================================
require_once __DIR__ . '/../includes/auth.php';   
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Dibaca oleh header.php
$page_title         = 'Dashboard';       
$_SESSION['nama'];
$search_placeholder = 'Cari Antrean';

// Statistik (dummy, nanti diganti COUNT dari database)
$stat = [
    'selesai'    => 49,
    'bulan_lalu' => 45,
    'antrean'    => 6,
    'menunggu'   => 2,
    'dikerjakan' => 2,
    'unit'       => 3,
];
$delta = $stat['bulan_lalu'] > 0
    ? round(($stat['selesai'] - $stat['bulan_lalu']) / $stat['bulan_lalu'] * 100)
    : 0;

// Daftar servis milik teknisi
$daftar_servis = [
    ['nota' => '#5143', 'perangkat' => 'Lenovo LOQ 15',   'pelanggan' => 'Sari Lestari',   'status' => 'Selesai',    'layanan' => 'Express', 'estimasi' => '6 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5144', 'perangkat' => 'Lenovo LOQ 15',   'pelanggan' => 'Sari Lestari',   'status' => 'Dikerjakan', 'layanan' => 'Express', 'estimasi' => '6 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5145', 'perangkat' => 'Lenovo LOQ 15',   'pelanggan' => 'Sari Lestari',   'status' => 'Diterima',   'layanan' => 'Express', 'estimasi' => '6 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5146', 'perangkat' => 'Lenovo LOQ 15',   'pelanggan' => 'Sari Lestari',   'status' => 'Menunggu',   'layanan' => 'Express', 'estimasi' => '6 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5147', 'perangkat' => 'Lenovo LOQ 15',   'pelanggan' => 'Sari Lestari',   'status' => 'Menunggu',   'layanan' => 'Express', 'estimasi' => '6 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5148', 'perangkat' => 'Lenovo LOQ 15',   'pelanggan' => 'Sari Lestari',   'status' => 'Dibatalkan', 'layanan' => 'Express', 'estimasi' => '6 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5149', 'perangkat' => 'ASUS TUF Gaming', 'pelanggan' => 'Budi Santoso',   'status' => 'Dikerjakan', 'layanan' => 'Reguler', 'estimasi' => '8 Okt', 'masuk' => '5 Okt'],
    ['nota' => '#5150', 'perangkat' => 'HP Pavilion 14',  'pelanggan' => 'Dewi Anggraini', 'status' => 'Selesai',    'layanan' => 'Reguler', 'estimasi' => '7 Okt', 'masuk' => '4 Okt'],
];

// Kalau fungsi ini sudah dipindah ke helpers.php, hapus blok ini
if (!function_exists('getBadgeStatusClass')) {
    function getBadgeStatusClass($status) {
        switch ($status) {
            case 'Selesai':     return 'bg-success-light text-success-dark';
            case 'Dikerjakan':  return 'bg-lightblue-light text-lightblue-dark';
            case 'Diterima':    return 'bg-warning-light text-warning-dark';
            case 'Dibatalkan':  return 'bg-danger-light text-danger-dark';
            case 'Menunggu':
            case 'Menunggu..':  return 'bg-blue-light text-blue-hover';
            default:            return 'bg-white-hover text-white-darker';
        }
    }
}

// ==============================================================================
// 2. PAGINATION (6 card per halaman = 3 kolom x 2 baris)
// ==============================================================================
$per_page      = 6;
$total         = count($daftar_servis);
$total_halaman = max(1, (int) ceil($total / $per_page));
$halaman       = max(1, min((int) ($_GET['page'] ?? 1), $total_halaman));
$tampil        = array_slice($daftar_servis, ($halaman - 1) * $per_page, $per_page);
$awal          = $total ? ($halaman - 1) * $per_page + 1 : 0;
$akhir         = $total ? $awal + count($tampil) - 1 : 0;

// Sidebar + header (sesuai role) sudah ada di header.php

?>

      <!-- ==============================================================================
          STATISTIK RINGKAS (3 KARTU)
          ============================================================================== -->
      <section class="flex flex-row gap-4 mb-8">

        <!-- Selesai (Orange) -->
        <div class="w-full sm:w-[17.3rem] bg-orange text-white-light rounded-3xl px-6 py-5 flex flex-col min-h-[11.3rem]">
          <span class="text-sm font-bold">Selesai</span>
          <strong class="text-4xl lg:text-[2.6rem] leading-tight mt-1"><?= (int) $stat['selesai'] ?></strong>
          <div class="mt-auto flex items-center justify-between gap-2">
            <small class="text-[11px]">Bulan lalu: <?= (int) $stat['bulan_lalu'] ?></small>
            <span class="px-6 py-2 rounded-full text-xs font-bold <?= $delta >= 0 ? 'bg-success-light text-success-dark' : 'bg-danger-light text-danger-dark' ?>">
              <?= $delta >= 0 ? '+' : '' ?><?= $delta ?>%
            </span>
          </div>
        </div>

        <!-- Antrean Saya -->
        <div class="w-full sm:w-[17.3rem] bg-tosca-dark text-white-light rounded-3xl px-6 py-5 flex flex-col min-h-[11.3rem]">
          <span class="text-sm font-bold">Antrean Saya</span>
          <strong class="text-4xl lg:text-[2.6rem] leading-tight mt-1"><?= (int) $stat['antrean'] ?></strong>
          <div class="mt-auto">
            <small class="text-[11px]">Menunggu: <?= (int) $stat['menunggu'] ?></small>
          </div>
        </div>

        <!-- Sedang Dikerjakan -->
        <div class="w-full sm:w-[17.3rem] bg-tosca-dark text-white-light rounded-3xl px-6 py-5 flex flex-col min-h-[11.3rem]">
          <span class="text-sm font-bold">Sedang Dikerjakan</span>
          <strong class="text-4xl lg:text-[2.6rem] leading-tight mt-1"><?= (int) $stat['dikerjakan'] ?></strong>
          <div class="mt-auto">
            <small class="text-[11px]"><?= (int) $stat['unit'] ?> unit</small>
          </div>
        </div>
      </section>

      <!-- ==============================================================================
          FILTER CHIP
          ============================================================================== -->
      <div class="flex flex-wrap items-center gap-3 mb-6" id="filterChipGroup">
        <button type="button" class="chip-btn px-8 py-3 rounded-full bg-tosca-dark text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap" data-filter="all">Semua <?= $total ?></button>
        <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Selesai">Selesai</button>
        <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Dikerjakan">Dikerjakan</button>
        <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Express">Express</button>
        <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Reguler">Reguler</button>
      </div>

      <!-- ==============================================================================
          DAFTAR SERVIS SAAT INI (GRID CARD)
          ============================================================================== -->
      <h2 class="text-xl lg:text-2xl font-bold text-blue-darker tracking-tight mb-4 ml-1">Daftar Servis Saat Ini</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-3 gap-4 lg:gap-5" id="svcGrid">
        <?php foreach ($tampil as $s): ?>
          <div class="svc-card bg-white-light border border-gray-200 rounded-2xl p-5 lg:p-6 flex flex-col min-h-46"
               data-status="<?= e($s['status']) ?>"
               data-layanan="<?= e($s['layanan']) ?>">

            <!-- Baris atas: nota, layanan, status -->
            <div class="flex items-center justify-between gap-3 text-sm">
              <span class="font-bold text-blue-darker"><?= e($s['nota']) ?></span>
              <div class="flex items-center gap-4 sm:gap-6">
                <?php if ($s['layanan'] === 'Express'): ?>
                  <span class="font-semibold text-danger-hover text-xs">Express</span>
                <?php else: ?>
                  <span class="font-semibold text-lightblue-dark text-xs">Reguler</span>
                <?php endif; ?>
                <span class="inline-block w-28 py-2 rounded-full text-center text-xs font-semibold <?= getBadgeStatusClass($s['status']) ?>">
                  <?= e($s['status']) ?>
                </span>
              </div>
            </div>

            <!-- Perangkat & pelanggan -->
            <h3 class="text-xl lg:text-2xl font-bold text-blue-darker mt-2"><?= e($s['perangkat']) ?></h3>
            <p class="text-sm text-white-dark-active mt-1.5"><?= e($s['pelanggan']) ?></p>

            <!-- Footer: estimasi + tombol aksi -->
            <div class="mt-auto pt-6 flex items-end justify-between gap-3">
              <span class="text-xs text-white-dark-active leading-snug min-w-0">
                Estimasi <?= e($s['estimasi']) ?> · Masuk <?= e($s['masuk']) ?>
              </span>
              <div class="flex gap-2 shrink-0">
                <a href="edit_antrean.php?id=<?= e($s['nota']) ?>"
                   class="px-4 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
                  Edit
                </a>
                <a href="detail_antrean.php?id=<?= e($s['nota']) ?>"
                   class="px-4 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                  Detail
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Empty state (muncul kalau hasil filter/pencarian kosong) -->
      <div id="emptyState" class="hidden text-center py-14 px-4 text-white-dark-hover bg-white-light border border-dashed border-gray-200 rounded-2xl">
        Tidak ada servis yang cocok.
      </div>

      <!-- ==============================================================================
          PAGINATION FOOTER
          ============================================================================== -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-white-dark-hover mt-6">
        <p class="pl-1">Menampilkan <?= $awal ?>-<?= $akhir ?> dari <?= $total ?> antrean</p>

        <div class="flex flex-wrap items-center gap-1.5">
          <?php if ($halaman > 1): ?>
            <a href="?page=<?= $halaman - 1 ?>" class="text-xs text-blue-darker hover:text-orange font-bold transition px-2">&lt;</a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
            <?php if ($i === $halaman): ?>
              <span class="w-9 h-9 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center"><?= $i ?></span>
            <?php else: ?>
              <a href="?page=<?= $i ?>" class="w-9 h-9 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($halaman < $total_halaman): ?>
            <a href="?page=<?= $halaman + 1 ?>" class="text-xs text-blue-darker hover:text-orange font-bold transition px-2">&gt;</a>
          <?php endif; ?>
        </div>
      </div>

  <!-- ==============================================================================
      SCRIPT: FILTER CHIP + PENCARIAN ANTREAN
    ============================================================================== -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const searchInput = document.getElementById('searchNotaInput'); // search bar dari header.php
      const chips = document.querySelectorAll('#filterChipGroup .chip-btn');
      const cards = document.querySelectorAll('#svcGrid .svc-card');
      const emptyState = document.getElementById('emptyState');

      const CHIP_BASE     = 'chip-btn px-8 py-3 rounded-full text-xs transition cursor-pointer whitespace-nowrap';
      const CHIP_INACTIVE = CHIP_BASE + ' bg-white-light text-blue-darker border border-gray-200 font-medium hover:bg-white-light-hover';
      const CHIP_ACTIVE   = CHIP_BASE + ' bg-tosca-dark text-white-light font-semibold';

      let currentFilter = 'all';

      function applyFilter() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visible = 0;

        cards.forEach(card => {
          const status  = card.dataset.status;
          const layanan = card.dataset.layanan;

          let matchFilter = true;
          if (currentFilter === 'Express' || currentFilter === 'Reguler') matchFilter = (layanan === currentFilter);
          else if (currentFilter !== 'all') matchFilter = (status === currentFilter);

          const matchSearch = card.textContent.toLowerCase().includes(query);
          const show = matchFilter && matchSearch;

          card.classList.toggle('hidden', !show);
          if (show) visible++;
        });

        emptyState.classList.toggle('hidden', visible > 0);
      }

      chips.forEach(chip => {
        chip.addEventListener('click', function () {
          chips.forEach(c => { c.className = CHIP_INACTIVE; });
          this.className = CHIP_ACTIVE;
          currentFilter = this.dataset.filter;
          applyFilter();
        });
      });

      if (searchInput) searchInput.addEventListener('input', applyFilter);
    });
  </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>