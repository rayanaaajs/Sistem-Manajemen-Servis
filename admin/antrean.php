<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$page_title = 'Antrean';
$active_menu = 'antrean';
$search_placeholder = 'Cari nota, pelanggan, atau perangkat...';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

/**
 * Helper format tanggal singkat Indonesia (contoh: 29 Sep)
 */
function formatTanggalSingkat($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return '-';
    }
    $bulan = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $tgl = date('j', $timestamp);
    $bln = $bulan[(int)date('n', $timestamp)] ?? '';
    return $tgl . ' ' . $bln;
}

/**
 * Helper badge warna status pengerjaan
 */
function getBadgeStatusClass($status) {
    $s = strtolower(trim(preg_replace('/\s+/', ' ', (string)$status)));
    if ($s === 'selesai') {
        return 'bg-success-light text-success-dark';
    } elseif ($s === 'dikerjakan' || $s === 'proses servis') {
        return 'bg-lightblue-light text-lightblue-dark';
    } elseif ($s === 'diterima' || $s === 'sedang dicek') {
        return 'bg-warning-light text-warning-dark';
    } elseif ($s === 'dibatalkan') {
        return 'bg-danger-light text-danger-dark';
    } elseif (stripos($s, 'menunggu') !== false) {
        return 'bg-blue-light text-blue-hover';
    }
    return 'bg-white-hover text-white-darker';
}

/**
 * Normalisasi status untuk atribut data-status filter tab
 */
function normalizeFilterStatus($status) {
    $s = strtolower(trim(rtrim((string)$status, '.')));
    if ($s === 'proses servis') return 'dikerjakan';
    if ($s === 'sedang dicek') return 'diterima';
    return $s;
}

// ==============================================================================
// AMBIL DATA ANTREAN AKTIF (YANG SUDAH SELESAI+LUNAS+DIAMBIL OTOMATIS KE RIWAYAT)
// Urutan prioritas:
// 0 -> Selesai dikerjakan, tapi belum Lunas atau belum Diambil (PALING ATAS)
// 1 -> Dibatalkan, tapi unit belum diambil pelanggan
// 2 -> Antrean aktif layanan Express (FIFO berdasarkan no_nota)
// 3 -> Antrean aktif layanan Reguler (FIFO berdasarkan no_nota)
// ==============================================================================
$daftar_antrean = [];

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
        LEFT JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
        LEFT JOIN karyawan k ON s.id_karyawan = k.id_karyawan
        LEFT JOIN validasi v ON s.no_nota = v.no_nota
        WHERE NOT (
            (
                LOWER(TRIM(COALESCE(s.status_pengerjaan, ''))) = 'selesai'
                AND LOWER(TRIM(COALESCE(v.status_pembayaran, ''))) = 'lunas'
                AND LOWER(TRIM(COALESCE(v.status_pengambilan, ''))) = 'sudah diambil'
            )
            OR (
                LOWER(TRIM(COALESCE(s.status_pengerjaan, ''))) = 'dibatalkan'
                AND LOWER(TRIM(COALESCE(v.status_pengambilan, ''))) = 'sudah diambil'
            )
        )
        ORDER BY 
            CASE 
                WHEN LOWER(TRIM(COALESCE(s.status_pengerjaan, ''))) = 'selesai' THEN 0
                WHEN LOWER(TRIM(COALESCE(s.status_pengerjaan, ''))) = 'dibatalkan' THEN 1
                WHEN LOWER(TRIM(COALESCE(s.jenis_layanan, ''))) = 'express' THEN 2
                ELSE 3
            END ASC,
            CASE 
                WHEN LOWER(TRIM(COALESCE(s.jenis_layanan, ''))) = 'express' THEN 0 
                ELSE 1 
            END ASC,
            s.no_nota ASC
    ";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $status_raw    = $row['status_pengerjaan'] ?? 'Menunggu';
        $status_tampil = function_exists('convertStatusAdmin') ? convertStatusAdmin($status_raw) : $status_raw;
        $layanan_raw   = $row['jenis_layanan'] ?? 'Reguler';

        $daftar_antrean[] = [
            'nota'      => $row['no_nota'],
            'pelanggan' => $row['nama_pelanggan'] ?? 'Pelanggan Umum',
            'perangkat' => $row['jenis_barang'] ?? '-',
            'teknisi'   => $row['nama_karyawan'] ?? '-',
            'masuk'     => formatTanggalSingkat($row['tanggal_masuk']),
            'estimasi'  => formatTanggalSingkat($row['estimasi_pengambilan']),
            'status'    => $status_tampil,
            'layanan'   => $layanan_raw
        ];
    }
} catch (PDOException $e) {
    echo "<script>alert('Gagal mengambil data antrean: " . addslashes($e->getMessage()) . "');</script>";
}

$total_antrean = count($daftar_antrean);
?>

      <!-- FILTER STATUS & TOMBOL + ANTREAN BARU -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <!-- Kelompok Pill Filter Kategori -->
        <div class="flex flex-wrap items-center gap-2" id="filterStatusGroup">
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap" data-filter="all">
            Semua <?= $total_antrean ?>
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="selesai">
            Selesai
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="dikerjakan">
            Dikerjakan
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="diterima">
            Diterima
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="dibatalkan">
            Dibatalkan
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="menunggu">
            Menunggu
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="express">
            Express
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="reguler">
            Reguler
          </button>
        </div>

        <!-- Tombol + Antrean Baru (Orange) -->
        <a href="tambah_antrean.php" class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto sm:px-12 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition-all whitespace-nowrap cursor-pointer">
          <span class="text-sm font-bold">+</span>
          <span>Antrean Baru</span>
        </a>
      </div>

      <!-- ==============================================================================
           KARTU TABEL DAFTAR SERVIS SAAT INI
           ============================================================================== -->
      <div class="bg-white-light rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200">
        <!-- Header Tabel & Info Jumlah -->
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
          <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Servis Saat Ini</h2>
          <span id="headerCountInfo" class="text-sm text-white-dark-hover font-medium"><?= $total_antrean ?> antrean</span>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
          <table class="w-full min-w-160 whitespace-nowrap text-left border-collapse text-sm" id="tableAntrean">
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
            <tbody id="tableBodyAntrean" class="divide-y divide-white-hover text-white-darker font-medium">
              <?php foreach ($daftar_antrean as $item): ?>
                <?php
                  $status_norm  = normalizeFilterStatus($item['status']);
                  $layanan_norm = strtolower(trim($item['layanan']));
                  $search_blob  = strtolower($item['nota'] . ' ' . $item['pelanggan'] . ' ' . $item['perangkat'] . ' ' . $item['teknisi']);
                ?>
                <tr class="hover:bg-warning-light/40 transition-colors row-antrean"
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
                          class="rounded-full text-xs font-semibold tracking-wide <?= getBadgeStatusClass($item['status']) ?>">
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
                    <div class="inline-flex items-center gap-2">
                      <a href="edit_antrean.php?nota=<?= urlencode($item['nota']) ?>" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
                        Edit
                      </a>
                      <a href="detail_antrean.php?nota=<?= urlencode($item['nota']) ?>" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                        Detail
                      </a>
                      <button type="button"
                              onclick="bukaModalHapusAntrean(<?= htmlspecialchars(json_encode($item['nota']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode('hapus_antrean.php?nota=' . urlencode($item['nota'])), ENT_QUOTES) ?>)"
                              class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                        Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>

              <!-- Baris Kosong Jika Data Kosong / Filter Tidak Cocok -->
              <tr id="emptyFilterRow" style="<?= $total_antrean === 0 ? '' : 'display: none;' ?>">
                <td colspan="9" class="py-10 text-center text-sm text-white-dark-hover">
                  Belum ada data antrean servis yang sesuai.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION CONTROLS -->
        <div id="paginationContainer" class="mt-6 pt-4 border-t border-white-hover flex flex-col sm:flex-row items-center justify-between gap-3">
          <p id="paginationInfo" class="text-xs text-white-dark-hover font-medium"></p>
          <div id="paginationButtons" class="flex items-center gap-1.5 flex-wrap"></div>
        </div>
      </div>

      <!-- ==============================================================================
           MODAL POP-UP KONFIRMASI HAPUS (Posisi Presisi Tengah Layar)
           ============================================================================== -->
      <div id="modalHapusAntrean" onclick="if(event.target === this) tutupModalHapusAntrean()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-gray-200">
          <!-- Ikon Tempat Sampah Merah -->
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-danger-light mb-5">
            <svg class="h-8 w-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>

          <h3 class="mb-2 text-xl font-bold text-blue-darker">Hapus Data?</h3>
          <p class="mb-7 text-sm text-white-dark-hover leading-relaxed">
            Data nota <span id="textNotaHapusAntrean" class="font-bold text-blue-darker"></span> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
          </p>

          <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="tutupModalHapusAntrean()" class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer">
              Batal
            </button>
            <a href="#" id="btnConfirmHapusAntrean" class="w-full rounded-full bg-danger py-3 text-sm font-semibold text-white-light hover:bg-danger-dark transition cursor-pointer">
              Ya, Hapus
            </a>
          </div>
        </div>
      </div>

      <!-- JAVASCRIPT: Filter, Live Search, Pagination (8 Baris/Halaman) & Modal -->
      <script>
        document.addEventListener("DOMContentLoaded", function () {
          const filterButtons = document.querySelectorAll(".filter-tab-btn");
          const allRows = Array.from(document.querySelectorAll(".row-antrean"));
          const emptyRow = document.getElementById("emptyFilterRow");
          const headerCountInfo = document.getElementById("headerCountInfo");
          const paginationInfo = document.getElementById("paginationInfo");
          const paginationButtons = document.getElementById("paginationButtons");
          const searchInput = document.querySelector('input[placeholder*="Cari"], header input[type="text"], header input[type="search"]');

          const rowsPerPage = 8;
          let currentPage = 1;
          let currentFilter = "all";
          let currentSearch = "";

          function getFilteredRows() {
            return allRows.filter(row => {
              const status = row.getAttribute("data-status") || "";
              const layanan = row.getAttribute("data-layanan") || "";
              const searchText = row.getAttribute("data-search") || "";

              const matchFilter = currentFilter === "all"
                || status === currentFilter
                || (currentFilter === "menunggu" && status.includes("menunggu"))
                || layanan === currentFilter;
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
              headerCountInfo.textContent = `${visibleRows.length} dari ${totalItems} antrean`;
            }

            if (paginationInfo) {
              if (totalItems === 0) {
                paginationInfo.textContent = "Menampilkan 0 antrean";
              } else {
                const showStart = startIndex + 1;
                const showEnd = Math.min(endIndex, totalItems);
                paginationInfo.textContent = `Menampilkan ${showStart}-${showEnd} dari ${totalItems} antrean`;
              }
            }

            if (paginationButtons) {
              paginationButtons.innerHTML = "";

              if (totalPages > 1) {
                const prevBtn = document.createElement("button");
                prevBtn.type = "button";
                prevBtn.textContent = "Sebelumnya";
                prevBtn.disabled = currentPage === 1;
                prevBtn.className = currentPage === 1
                  ? "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed"
                  : "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
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
                  pageBtn.className = i === currentPage
                    ? "w-9 h-9 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold shadow-xs transition cursor-pointer"
                    : "w-9 h-9 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
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
                nextBtn.className = currentPage === totalPages
                  ? "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed"
                  : "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
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
              parentForm.addEventListener("submit", function (e) {
                e.preventDefault();
              });
            }

            searchInput.addEventListener("input", function () {
              currentSearch = this.value.toLowerCase().trim();
              currentPage = 1;
              renderTable();
            });
          }

          filterButtons.forEach(btn => {
            btn.addEventListener("click", function () {
              filterButtons.forEach(b => {
                b.className = "filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
              });
              this.className = "filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap";

              currentFilter = this.getAttribute("data-filter");
              currentPage = 1;
              renderTable();
            });
          });

          renderTable();
        });

        function bukaModalHapusAntrean(nota, link) {
          document.getElementById('textNotaHapusAntrean').textContent = nota;
          document.getElementById('btnConfirmHapusAntrean').href = link;
          document.getElementById('modalHapusAntrean').style.display = 'flex';
        }

        function tutupModalHapusAntrean() {
          document.getElementById('modalHapusAntrean').style.display = 'none';
        }
      </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>