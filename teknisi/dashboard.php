<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php'; // Koneksi PDO

$page_title         = 'Dashboard';
$search_placeholder = 'Cari Antrean';
$id_karyawan        = $_SESSION['user_id'];


$stat = [
  'selesai'    => 0,
  'bulan_lalu' => 0,
  'antrean'    => 0,
  'menunggu'   => 0,
  'dikerjakan' => 0,
  'unit'       => 0, // Sama dengan dikerjakan untuk saat ini
];

try {
  // Stat 1: Total Selesai bulan ini
  $stmtSelesai = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_karyawan = :k AND status_pengerjaan = 'Selesai' AND EXTRACT(MONTH FROM tanggal_masuk) = EXTRACT(MONTH FROM CURRENT_DATE)");
  $stmtSelesai->execute(['k' => $id_karyawan]);
  $stat['selesai'] = (int) $stmtSelesai->fetchColumn();

  // Stat 2: Total Selesai bulan lalu
  $stmtBulanLalu = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_karyawan = :k AND status_pengerjaan = 'Selesai' AND EXTRACT(MONTH FROM tanggal_masuk) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')");
  $stmtBulanLalu->execute(['k' => $id_karyawan]);
  $stat['bulan_lalu'] = (int) $stmtBulanLalu->fetchColumn();

  // Stat 3: Total Antrean Aktif (semua yang belum selesai/batal)
  $stmtAntrean = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_karyawan = :k AND status_pengerjaan NOT IN ('Selesai', 'Dibatalkan')");
  $stmtAntrean->execute(['k' => $id_karyawan]);
  $stat['antrean'] = (int) $stmtAntrean->fetchColumn();

  // Stat 4: Total Menunggu
  $stmtMenunggu = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_karyawan = :k AND status_pengerjaan IN ('Diterima', 'Menunggu', 'Menunggu Persetujuan')");
  $stmtMenunggu->execute(['k' => $id_karyawan]);
  $stat['menunggu'] = (int) $stmtMenunggu->fetchColumn();

  // Stat 5: Total Dikerjakan
  $stmtDikerjakan = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_karyawan = :k AND status_pengerjaan = 'Dikerjakan'");
  $stmtDikerjakan->execute(['k' => $id_karyawan]);
  $stat['dikerjakan'] = (int) $stmtDikerjakan->fetchColumn();
  $stat['unit']       = $stat['dikerjakan'];
} catch (PDOException $e) {
  error_log("Gagal memuat statistik dashboard teknisi: " . $e->getMessage());
}

$delta = $stat['bulan_lalu'] > 0
  ? round(($stat['selesai'] - $stat['bulan_lalu']) / $stat['bulan_lalu'] * 100)
  : ($stat['selesai'] > 0 ? 100 : 0);

// ==============================================================================
// 2. DAFTAR SERVIS (PAGINATION DI BROWSER)
// ==============================================================================
$per_page = 6;

$tampil = [];
$total  = 0;

try {
  // Ambil seluruh daftar milik teknisi login sekali per pemuatan halaman.
  $stmtData = $pdo->prepare("
        SELECT s.no_nota as nota, 
               CONCAT_WS(' ', s.jenis_barang, s.merek_dan_spesifikasi) as perangkat,
               p.nama_pelanggan as pelanggan,
               s.status_pengerjaan, 
               s.jenis_layanan, 
               s.estimasi_pengambilan, 
               s.tanggal_masuk
        FROM servis s
        JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
        WHERE s.id_karyawan = :k
        ORDER BY 
            CASE 
                WHEN s.status_pengerjaan = 'Dikerjakan' THEN 1
                WHEN s.status_pengerjaan IN ('Diterima', 'Menunggu', 'Menunggu Persetujuan') THEN 2
                ELSE 3 
            END,
            s.tanggal_masuk ASC, s.no_nota ASC
    ");
  $stmtData->bindValue(':k', $id_karyawan, PDO::PARAM_INT);
  $stmtData->execute();
  $tampil = $stmtData->fetchAll(PDO::FETCH_ASSOC);
  $total = count($tampil);
} catch (PDOException $e) {
  error_log("Gagal memuat list antrean: " . $e->getMessage());
}

$awal  = $total > 0 ? 1 : 0;
$akhir = min($total, $per_page);

// Header dipanggil sesudah logic biar $page_title kebaca
require_once __DIR__ . '/../includes/header.php';
?>

<!-- ==============================================================================
    STATISTIK RINGKAS (3 KARTU)
    ============================================================================== -->
<section class="flex flex-col sm:flex-row gap-4 mb-8">

  <!-- Selesai (Orange) -->
  <div class="w-full sm:w-[17.3rem] bg-orange text-white-light rounded-3xl px-6 py-5 flex flex-col min-h-[11.3rem]">
    <span class="pt-1.5 text-sm font-semibold">Selesai</span>
    <strong class="text-4xl lg:text-[2.6rem] leading-tight mt-1"><?= $stat['selesai'] ?></strong>
    <div class="mt-auto flex items-center justify-between gap-2">
      <small class="text-[12px]">Bulan lalu: <?= $stat['bulan_lalu'] ?></small>
      <span class="px-6 py-2 rounded-full text-xs font-bold <?= $delta >= 0 ? 'bg-success-light text-success-dark' : 'bg-danger-light text-danger-dark' ?>">
        <?= $delta >= 0 ? '+' : '' ?><?= $delta ?>%
      </span>
    </div>
  </div>

  <!-- Antrean Saya -->
  <div class="w-full sm:w-[17.3rem] bg-tosca text-white-light rounded-3xl px-6 py-5 flex flex-col min-h-[11.3rem]">
    <span class="pt-1.5 text-sm font-semibold">Antrean Aktif</span>
    <strong class="text-4xl lg:text-[2.6rem] leading-tight mt-1"><?= $stat['antrean'] ?></strong>
    <div class="mt-auto">
      <small class="text-[12px]">Menunggu: <?= $stat['menunggu'] ?></small>
    </div>
  </div>

  <!-- Sedang Dikerjakan -->
  <div class="w-full sm:w-[17.3rem] bg-tosca text-white-light rounded-3xl px-6 py-5 flex flex-col min-h-[11.3rem]">
    <span class="pt-1.5 text-sm font-semibold">Sedang Dikerjakan</span>
    <strong class="text-4xl lg:text-[2.6rem] leading-tight mt-1"><?= $stat['dikerjakan'] ?></strong>
    <div class="mt-auto">
      <small class="text-[12px]"><?= $stat['unit'] ?> unit</small>
    </div>
  </div>
</section>


<div class="flex flex-wrap items-center gap-3 mb-6 overflow-x-auto pb-2" id="filterChipGroup">
  <button type="button" class="chip-btn px-8 py-3 rounded-full bg-tosca-dark text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap" data-filter="all">Semua <?= $total ?></button>
  <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Selesai">Selesai</button>
  <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Dikerjakan">Dikerjakan</button>
  <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Express">Express</button>
  <button type="button" class="chip-btn px-8 py-3 rounded-full bg-white-light text-blue-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Reguler">Reguler</button>
</div>


<h2 class="text-xl lg:text-2xl font-bold text-blue-darker tracking-tight mb-4 ml-1">Daftar Servis Saat Ini</h2>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-5" id="svcGrid">
  <?php if (empty($tampil)): ?>
    <div class="col-span-full text-center py-14 text-white-dark-hover bg-white-light border border-dashed border-gray-200 rounded-2xl">
      Belum ada servis yang ditugaskan ke Anda.
    </div>
  <?php else: ?>
    <?php foreach ($tampil as $index => $s):
      // 1. Convert DB ("Proses Servis") ke UI ("Dikerjakan")
      $status_ui = ($s['status_pengerjaan']);
    ?>
      <div class="svc-card bg-white-light border border-gray-200 rounded-2xl p-5 lg:p-6 flex flex-col min-h-46"
        <?= $index >= $per_page ? 'style="display: none;"' : '' ?>
        data-status="<?= e($status_ui) ?>"
        data-layanan="<?= e($s['jenis_layanan']) ?>">
        <!-- Baris atas: nota, layanan, status -->
        <div class="flex items-center justify-between gap-3 text-sm">
          <span class="font-bold text-blue-darker"><?= e($s['nota']) ?></span>
          <div class="flex items-center gap-3 sm:gap-4">
            <?php if ($s['jenis_layanan'] === 'Express'): ?>
              <span class="font-semibold text-danger-hover text-xs tracking-wider">Express</span>
            <?php else: ?>
              <span class="font-semibold text-tosca-darker text-xs tracking-wider">Reguler</span>
            <?php endif; ?>

            <!-- 3. Panggil badgeStatus pakai bahasa UI ("Dikerjakan") -->
            <?= badgeStatus($status_ui) ?>
          </div>
        </div>

        <!-- Perangkat & pelanggan -->
        <h3 class="text-xl lg:text-[1.35rem] font-bold text-blue-darker mt-3 leading-tight"><?= e($s['perangkat']) ?></h3>
        <p class="text-sm text-white-dark-active mt-1"><?= e($s['pelanggan']) ?></p>

        <!-- Footer: estimasi + tombol aksi -->
        <div class="mt-auto pt-6 flex flex-wrap items-end justify-between gap-3">
          <span class="text-[12px] text-white-dark-active leading-snug min-w-0 font-medium">
            Estimasi <?= e(date('d M', strtotime($s['estimasi_pengambilan']))) ?> · Masuk <?= e(date('d M', strtotime($s['tanggal_masuk']))) ?>
          </span>
          <div class="flex gap-2 shrink-0">
            <!-- Link diubah mengarah ke file yang udah kita bikin -->
            <a href="edit_servis.php?nota=<?= urlencode($s['nota']) ?>"
              class="px-5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
              Edit
            </a>
            <a href="detail_servis.php?nota=<?= urlencode($s['nota']) ?>"
              class="px-5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
              Detail
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Empty state (muncul kalau hasil filter/pencarian js kosong) -->
<div id="emptyState" class="hidden col-span-full text-center py-14 px-4 text-white-dark-hover bg-white-light border border-dashed border-gray-200 rounded-2xl">
  Tidak ada servis yang cocok dengan pencarian/filter.
</div>

<!-- ==============================================================================
    PAGINATION FOOTER
    ============================================================================== -->
<?php if ($total > 0): ?>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-white-dark-hover mt-8" id="paginationFooter">
    <p class="pl-1 font-medium" id="paginationInfo" aria-live="polite">Menampilkan <?= $awal ?>-<?= $akhir ?> dari <?= $total ?> antrean</p>
    <nav class="flex flex-wrap items-center gap-1.5" id="paginationNav" aria-label="Halaman antrean servis"></nav>
  </div>
<?php endif; ?>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchNotaInput');
    const chips = document.querySelectorAll('#filterChipGroup .chip-btn');
    const cards = [...document.querySelectorAll('#svcGrid .svc-card')];
    const emptyState = document.getElementById('emptyState');
    const svcGrid = document.getElementById('svcGrid');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationNav = document.getElementById('paginationNav');
    const perPage = <?= (int) $per_page ?>;

    const CHIP_BASE = 'chip-btn px-8 py-3 rounded-full text-xs transition cursor-pointer whitespace-nowrap';
    const CHIP_INACTIVE = CHIP_BASE + ' bg-white-light text-blue-darker border border-gray-200 font-medium hover:bg-white-light-hover';
    const CHIP_ACTIVE = CHIP_BASE + ' bg-tosca-dark text-white-light font-semibold';
    const PAGE_BUTTON = 'w-9 h-9 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition';
    const PAGE_ACTIVE = 'w-9 h-9 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center';
    const PAGE_ARROW = 'text-xs text-blue-darker hover:text-orange font-bold transition px-2';

    let currentFilter = 'all';
    let currentPage = 1;
    let filteredCards = cards;

    const searchableCards = cards.map(card => ({
      card,
      text: card.textContent.toLocaleLowerCase('id-ID')
    }));

    function createPageButton(label, page, className, ariaLabel, active = false) {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = label;
      button.className = className;
      button.setAttribute('aria-label', ariaLabel);
      if (active) button.setAttribute('aria-current', 'page');
      button.addEventListener('click', () => {
        currentPage = page;
        renderPage();
      });
      return button;
    }

    function renderPagination(totalPages) {
      if (!paginationNav) return;
      paginationNav.replaceChildren();
      if (totalPages <= 1) return;

      if (currentPage > 1) {
        paginationNav.append(createPageButton('<', currentPage - 1, PAGE_ARROW, 'Halaman sebelumnya'));
      }

      const firstPage = Math.max(1, Math.min(currentPage - 2, totalPages - 4));
      const lastPage = Math.min(totalPages, firstPage + 4);
      for (let page = firstPage; page <= lastPage; page++) {
        const active = page === currentPage;
        paginationNav.append(createPageButton(String(page), page, active ? PAGE_ACTIVE : PAGE_BUTTON, `Halaman ${page}`, active));
      }

      if (currentPage < totalPages) {
        paginationNav.append(createPageButton('>', currentPage + 1, PAGE_ARROW, 'Halaman berikutnya'));
      }
    }

    function renderPage() {
      const total = filteredCards.length;
      const totalPages = Math.max(1, Math.ceil(total / perPage));
      currentPage = Math.min(Math.max(currentPage, 1), totalPages);

      const start = (currentPage - 1) * perPage;
      const end = Math.min(start + perPage, total);
      const visibleCards = new Set(filteredCards.slice(start, end));

      // Hanya kartu pada halaman aktif yang ditampilkan.
      cards.forEach(card => {
        card.style.display = visibleCards.has(card) ? '' : 'none';
      });

      const noMatch = total === 0 && cards.length > 0;
      emptyState.classList.toggle('hidden', !noMatch);
      svcGrid.style.display = noMatch ? 'none' : '';

      if (paginationInfo) {
        paginationInfo.textContent = `Menampilkan ${total ? start + 1 : 0}-${end} dari ${total} antrean`;
      }
      renderPagination(totalPages);
    }

    function applyFilter() {
      const query = (searchInput?.value ?? '').toLocaleLowerCase('id-ID').trim();
      filteredCards = searchableCards.filter(({
        card,
        text
      }) => {
        const isServiceFilter = currentFilter === 'Express' || currentFilter === 'Reguler';
        const matchFilter = currentFilter === 'all' ||
          (isServiceFilter ? card.dataset.layanan === currentFilter : card.dataset.status === currentFilter);
        return matchFilter && text.includes(query);
      }).map(({
        card
      }) => card);

      // Pencarian dan filter selalu dimulai dari halaman pertama.
      currentPage = 1;
      renderPage();
    }

    chips.forEach(chip => {
      chip.addEventListener('click', () => {
        chips.forEach(c => {
          c.className = CHIP_INACTIVE;
        });
        chip.className = CHIP_ACTIVE;
        currentFilter = chip.dataset.filter;
        applyFilter();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', applyFilter);
      searchInput.closest('form')?.addEventListener('submit', event => event.preventDefault());
    }
    applyFilter();
  });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>