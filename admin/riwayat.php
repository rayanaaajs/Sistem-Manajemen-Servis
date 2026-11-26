<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/helpers.php';

$role = strtolower((string) ($_SESSION['role'] ?? ''));
$idKaryawan = (int) ($_SESSION['user_id'] ?? 0);

if (!in_array($role, ['admin', 'teknisi'], true) || $idKaryawan <= 0) {
  http_response_code(403);
  exit('Akses ditolak.');
}

$isAdmin = $role === 'admin';

if ($isAdmin && empty($_SESSION['csrf_hapus_riwayat'])) {
  $_SESSION['csrf_hapus_riwayat'] = bin2hex(random_bytes(32));
}

$page_title = 'Riwayat Servis';
$active_menu = 'riwayat';
$search_placeholder = 'Cari riwayat nota, pelanggan, atau perangkat...';

require_once __DIR__ . '/../includes/header.php';

/**
 * Helper format tanggal singkat Indonesia (contoh: 29 Sep)
 */
function formatTanggalSingkatRiwayat($datetime)
{
  if (empty($datetime)) {
    return '-';
  }
  $timestamp = strtotime($datetime);
  if (!$timestamp) {
    return '-';
  }
  $bulan = [
    1 => 'Jan',
    2 => 'Feb',
    3 => 'Mar',
    4 => 'Apr',
    5 => 'Mei',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Agt',
    9 => 'Sep',
    10 => 'Okt',
    11 => 'Nov',
    12 => 'Des'
  ];
  $tgl = date('j', $timestamp);
  $bln = $bulan[(int)date('n', $timestamp)] ?? '';
  return $tgl . ' ' . $bln;
}

/**
 * Helper badge warna status riwayat
 */
function getBadgeStatusRiwayatClass($status)
{
  $s = strtolower(trim(preg_replace('/\s+/', ' ', (string)$status)));
  if ($s === 'selesai') {
    return 'bg-success-light text-success-dark';
  } elseif ($s === 'dibatalkan') {
    return 'bg-danger-light text-danger-dark';
  }
  return 'bg-white-hover text-white-darker';
}

// ==============================================================================
// AMBIL DATA RIWAYAT SERVIS:
// - Status Selesai + Lunas + Sudah Diambil
// - Status Dibatalkan + Sudah Diambil
// Diurutkan dari nota terbaru (no_nota DESC)
// ==============================================================================

$daftar_riwayat = [];

try {

  $sql = "
    SELECT
        s.no_nota,
        s.jenis_barang,
        s.merek_dan_spesifikasi,
        s.tanggal_masuk,
        s.estimasi_pengambilan,
        s.status_pengerjaan,
        s.jenis_layanan,
        p.nama_pelanggan,
        k.nama_karyawan,
        COALESCE(v.status_pembayaran, 'Belum Dibayar') AS status_pembayaran,
        COALESCE(v.status_pengambilan, 'Belum Diambil') AS status_pengambilan
    FROM servis s
    LEFT JOIN pelanggan p
        ON s.id_pelanggan = p.id_pelanggan
    LEFT JOIN karyawan k
        ON s.id_karyawan = k.id_karyawan
    LEFT JOIN validasi v
        ON s.no_nota = v.no_nota
";

  if ($isAdmin) {
    // Riwayat admin berdasarkan penyelesaian transaksi servis.
    $sql .= "
        WHERE (
            (
                s.status_pengerjaan = 'Selesai'
                AND v.status_pembayaran = 'Lunas'
                AND v.status_pengambilan = 'Sudah Diambil'
            )
            OR (
                s.status_pengerjaan = 'Dibatalkan'
                AND v.status_pengambilan = 'Sudah Diambil'
            )
        )
    ";
  } else {
    // Riwayat teknisi berdasarkan status akhir pengerjaan.
    $sql .= "
        WHERE s.id_karyawan = :karyawan
        AND s.status_pengerjaan IN ('Selesai', 'Dibatalkan')
    ";
  }

  $sql .= " ORDER BY s.no_nota DESC";

  $stmt = $pdo->prepare($sql);

  if (!$isAdmin) {
    $stmt->bindValue(':karyawan', $idKaryawan, PDO::PARAM_INT);
  }

  $stmt->execute();

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


  foreach ($rows as $row) {
    $statusRaw = $row['status_pengerjaan'] ?? 'Selesai';

    $statusTampil = function_exists('convertStatusAdmin')
      ? convertStatusAdmin($statusRaw)
      : $statusRaw;

    $daftar_riwayat[] = [
      'nota'      => $row['no_nota'],
      'pelanggan' => $row['nama_pelanggan'] ?? 'Pelanggan Umum',
      'perangkat' => $row['jenis_barang'] ?? '-',
      'teknisi'   => $row['nama_karyawan'] ?? '-',
      'masuk'     => formatTanggalSingkatRiwayat($row['tanggal_masuk']),
      'estimasi'  => formatTanggalSingkatRiwayat($row['estimasi_pengambilan']),
      'status'    => $statusTampil,
      'layanan'   => $row['jenis_layanan'] ?? 'Reguler'
    ];
  }
} catch (PDOException $e) {
  error_log('riwayat.php: ' . $e->getMessage());

  echo '<p class="text-sm text-danger-dark mb-4">
        Gagal mengambil data riwayat.
    </p>';
}


$total_riwayat = count($daftar_riwayat);
?>

<!-- FILTER STATUS RIWAYAT -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
  <div class="flex flex-wrap items-center gap-2" id="filterRiwayatGroup">
    <button type="button" class="filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap" data-filter="all">
      Semua <?= $total_riwayat ?>
    </button>
    <button type="button" class="filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="selesai">
      Selesai
    </button>
    <button type="button" class="filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="dibatalkan">
      Dibatalkan
    </button>
    <button type="button" class="filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="express">
      Express
    </button>
    <button type="button" class="filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="reguler">
      Reguler
    </button>
  </div>
</div>

<!-- ==============================================================================
           KARTU TABEL RIWAYAT SERVIS
           ============================================================================== -->
<div class="bg-white-light rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200">
  <!-- Header Tabel & Info Jumlah -->
  <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
    <h2 class="text-lg font-bold text-blue-darker tracking-tight">Riwayat Servis Selesai</h2>
    <span id="headerCountRiwayat" class="text-sm text-white-dark-hover font-medium"><?= $total_riwayat ?> riwayat</span>
  </div>

  <!-- Tabel Responsif -->
  <div class="overflow-x-auto">
    <table class="w-full min-w-160 whitespace-nowrap text-left border-collapse text-sm" id="tableRiwayat">
      <thead>
        <tr class="border-b border-white-hover text-white-darker font-bold text-[13px] tracking-wider">
          <th class="py-4 px-5">Nota</th>
          <th class="py-4 px-5">Pelanggan</th>
          <th class="py-4 px-5">Perangkat</th>
          <th class="hidden lg:table-cell py-4 px-5">Teknisi</th>
          <th class="hidden lg:table-cell py-4 px-5">Masuk</th>
          <th class="hidden lg:table-cell py-4 px-5">Estimasi</th>
          <th class="py-4 px-5 text-center">Status</th>
          <th class="py-4 px-5 text-center">Layanan</th>
          <th class="py-4 px-5 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody id="tableBodyRiwayat" class="divide-y divide-white-hover text-white-darker font-medium">
        <?php foreach ($daftar_riwayat as $item): ?>
          <?php
          $status_norm  = strtolower(trim(rtrim((string)$item['status'], '.')));
          $layanan_norm = strtolower(trim($item['layanan']));
          $search_blob  = strtolower($item['nota'] . ' ' . $item['pelanggan'] . ' ' . $item['perangkat'] . ' ' . $item['teknisi']);
          ?>
          <tr class="hover:bg-warning-light/40 transition-colors row-riwayat"
            data-status="<?= e($status_norm) ?>"
            data-layanan="<?= e($layanan_norm) ?>"
            data-search="<?= e($search_blob) ?>">

            <!-- Nota -->
            <td class="py-4 px-5 font-semibold text-blue-darker"><?= e($item['nota']) ?></td>

            <!-- Pelanggan -->
            <td class="py-4 px-5 font-medium text-blue-darker"><?= e($item['pelanggan']) ?></td>

            <!-- Perangkat -->
            <td class="py-4 px-5"><?= e($item['perangkat']) ?></td>

            <!-- Teknisi -->
            <td class="hidden lg:table-cell py-4 px-5"><?= e($item['teknisi']) ?></td>

            <!-- Masuk -->
            <td class="hidden lg:table-cell py-4 px-5 text-white-dark-active"><?= e($item['masuk']) ?></td>

            <!-- Estimasi -->
            <td class="hidden lg:table-cell py-4 px-5 text-white-dark-active"><?= e($item['estimasi']) ?></td>

            <!-- Status -->
            <td class="py-4 px-5 text-center">
              <span style="display: inline-block; min-width: 7.5rem; padding: 0.5rem 1.1rem;"
                class="rounded-full text-xs font-semibold tracking-wide <?= getBadgeStatusRiwayatClass($item['status']) ?>">
                <?= e($item['status']) ?>
              </span>
            </td>

            <!-- Layanan (Express / Reguler) -->
            <td class="py-4 px-5 text-center">
              <?php if ($layanan_norm === 'express'): ?>
                <span class="font-semibold text-danger-hover text-sm">Express</span>
              <?php else: ?>
                <span class="font-medium text-blue-darker text-sm">Reguler</span>
              <?php endif; ?>
            </td>

            <!-- Aksi -->

            <td class="py-4 px-5 text-center">
              <?php
              $urlDetail = $isAdmin
                ? 'detail_antrean.php'
                : '../teknisi/detail_servis.php';
              $urlDetail .= '?nota=' . rawurlencode($item['nota'])
                . '&from=riwayat';
              ?>
              <div class="inline-flex items-center gap-2">
                <a href="<?= e($urlDetail) ?>"
                  class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                  Detail
                </a>
                <?php if ($isAdmin): ?>
                  <button
                    type="button"
                    data-nota="<?= e($item['nota']) ?>"
                    onclick="bukaModalHapusRiwayat(this.dataset.nota)"
                    class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                    Hapus
                  </button>
                <?php endif; ?>
              </div>
            </td>

          </tr>
        <?php endforeach; ?>

        <!-- Baris Kosong Jika Data Kosong / Filter Tidak Cocok -->
        <tr id="emptyRiwayatRow" style="<?= $total_riwayat === 0 ? '' : 'display: none;' ?>">
          <td colspan="9" class="py-10 text-center text-sm text-white-dark-hover">
            Belum ada riwayat servis yang selesai dan diambil.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- PAGINATION CONTROLS -->
  <div id="paginationRiwayatContainer" class="mt-6 pt-4 border-t border-white-hover flex flex-col sm:flex-row items-center justify-between gap-3">
    <p id="paginationRiwayatInfo" class="text-xs text-white-dark-hover font-medium"></p>
    <div id="paginationRiwayatButtons" class="flex items-center gap-1.5 flex-wrap"></div>
  </div>
</div>

<!-- ==============================================================================
           MODAL POP-UP KONFIRMASI HAPUS RIWAYAT
           ============================================================================== -->
<?php if ($isAdmin): ?>
  <div id="modalHapusRiwayat" onclick="if(event.target === this) tutupModalHapusRiwayat()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
    <div class="bg-white-light w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-gray-200">
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-danger-light mb-5">
        <svg class="h-8 w-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </div>

      <h3 class="mb-2 text-xl font-bold text-blue-darker">Hapus Riwayat?</h3>
      <p class="mb-7 text-sm text-white-dark-hover leading-relaxed">
        Data riwayat nota <span id="textNotaHapusRiwayat" class="font-bold text-blue-darker"></span> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
      </p>
      <form method="POST" action="hapus_antrean.php" class="flex items-center justify-center gap-3">
        <input type="hidden" name="nota" id="inputNotaHapusRiwayat">
        <input type="hidden" name="from" value="riwayat">
        <input type="hidden" name="csrf_hapus_riwayat" value="<?= e($_SESSION['csrf_hapus_riwayat']) ?>">

        <button type="button" onclick="tutupModalHapusRiwayat()" class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer">
          Batal
        </button>
        <button type="submit" class="w-full rounded-full bg-danger py-3 text-sm font-semibold text-white-light hover:bg-danger-dark transition cursor-pointer">
          Ya, Hapus
        </button>
      </form>
    </div>
  </div>
<?php endif; ?>

<!-- JAVASCRIPT: Filter, Live Search, Pagination (8 Baris/Halaman) & Modal -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const filterButtons = document.querySelectorAll(".filter-riwayat-btn");
    const allRows = Array.from(document.querySelectorAll(".row-riwayat"));
    const emptyRow = document.getElementById("emptyRiwayatRow");
    const headerCountInfo = document.getElementById("headerCountRiwayat");
    const paginationInfo = document.getElementById("paginationRiwayatInfo");
    const paginationButtons = document.getElementById("paginationRiwayatButtons");
    const searchInput = document.getElementById("searchNotaInput");

    const rowsPerPage = 8;
    let currentPage = 1;
    let currentFilter = "all";
    let currentSearch = "";

    function getFilteredRows() {
      return allRows.filter(row => {
        const status = row.getAttribute("data-status") || "";
        const layanan = row.getAttribute("data-layanan") || "";
        const searchText = row.getAttribute("data-search") || "";

        const matchFilter = currentFilter === "all" ||
          status === currentFilter ||
          layanan === currentFilter;
        const matchSearch = currentSearch === "" || searchText.includes(currentSearch);

        return matchFilter && matchSearch;
      });
    }

    function renderTable() {
      const filteredRows = getFilteredRows();
      const totalItems = filteredRows.length;
      const totalPages = Math.ceil(totalItems / rowsPerPage) || 1;

      if (currentPage > totalPages) {
        currentPage = totalPages;
      }

      const startIndex = (currentPage - 1) * rowsPerPage;
      const endIndex = startIndex + rowsPerPage;

      allRows.forEach(row => {
        row.style.display = "none";
      });

      const visibleRows = filteredRows.slice(startIndex, endIndex);
      visibleRows.forEach(row => {
        row.style.display = "";
      });

      if (emptyRow) {
        emptyRow.style.display = totalItems === 0 ? "" : "none";
      }

      if (headerCountInfo) {
        headerCountInfo.textContent = `${visibleRows.length} dari ${totalItems} riwayat`;
      }

      if (paginationInfo) {
        if (totalItems === 0) {
          paginationInfo.textContent = "Menampilkan 0 riwayat";
        } else {
          const showStart = startIndex + 1;
          const showEnd = Math.min(endIndex, totalItems);
          paginationInfo.textContent = `Menampilkan ${showStart}-${showEnd} dari ${totalItems} riwayat`;
        }
      }

      if (paginationButtons) {
        paginationButtons.innerHTML = "";

        if (totalPages > 1) {
          const prevBtn = document.createElement("button");
          prevBtn.type = "button";
          prevBtn.textContent = "Sebelumnya";
          prevBtn.disabled = currentPage === 1;
          prevBtn.className = currentPage === 1 ?
            "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed" :
            "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
          prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
              currentPage--;
              renderTable();
            }
          });
          paginationButtons.appendChild(prevBtn);

          for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement("button");
            pageBtn.type = "button";
            pageBtn.textContent = i;
            pageBtn.className = i === currentPage ?
              "w-9 h-9 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold shadow-xs transition cursor-pointer" :
              "w-9 h-9 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
            pageBtn.addEventListener("click", () => {
              currentPage = i;
              renderTable();
            });
            paginationButtons.appendChild(pageBtn);
          }

          const nextBtn = document.createElement("button");
          nextBtn.type = "button";
          nextBtn.textContent = "Selanjutnya";
          nextBtn.disabled = currentPage === totalPages;
          nextBtn.className = currentPage === totalPages ?
            "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed" :
            "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
          nextBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
              currentPage++;
              renderTable();
            }
          });
          paginationButtons.appendChild(nextBtn);
        }
      }
    }

    if (searchInput) {
      const parentForm = searchInput.closest("form");
      if (parentForm) {
        parentForm.addEventListener("submit", function(e) {
          e.preventDefault();
        });
      }

      searchInput.addEventListener("input", function() {
        currentSearch = this.value.toLowerCase().trim();
        currentPage = 1;
        renderTable();
      });
    }

    filterButtons.forEach(btn => {
      btn.addEventListener("click", function() {
        filterButtons.forEach(b => {
          b.className = "filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
        });
        this.className = "filter-riwayat-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap";

        currentFilter = this.getAttribute("data-filter");
        currentPage = 1;
        renderTable();
      });
    });

    renderTable();
  });

  function bukaModalHapusRiwayat(nota) {
    document.getElementById('textNotaHapusRiwayat').textContent = nota;
    document.getElementById('inputNotaHapusRiwayat').value = nota;
    document.getElementById('modalHapusRiwayat').style.display = 'flex';
  }

  function tutupModalHapusRiwayat() {
    document.getElementById('modalHapusRiwayat').style.display = 'none';
  }
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>