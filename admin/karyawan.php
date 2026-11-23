<?php
require_once __DIR__ . '/../includes/auth.php';   
// Panggil koneksi database
require_once __DIR__ . '/../includes/koneksi.php'; 

$page_title = 'Karyawan';
$active_menu = 'karyawan';
$search_placeholder = 'Cari Karyawan...';

// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Menyiapkan array kosong untuk menampung data
$karyawan_list = [];

try {
    // 1. Ambil seluruh data dari tabel karyawan di Supabase
    $stmt = $pdo->query("SELECT * FROM karyawan ORDER BY id_karyawan ASC");
    $data_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Ubah format data dari database agar cocok dengan tabel HTML
    foreach ($data_db as $row) {
        // Bikin inisial otomatis (AD untuk Admin, TK untuk Teknisi)
        $inisial = (strtolower($row['peran']) === 'admin') ? 'AD' : 'TK';
        
        // Ambil status dari database (default 'Aktif' jika belum diset)
        $status_db = $row['status'] ?? 'Aktif';

        $karyawan_list[] = [
            'id'       => $row['id_karyawan'],
            'nama'     => $row['nama_karyawan'],
            'username' => $row['username'],
            'peran'    => $row['peran'],
            'unit'     => '0 unit', 
            'status'   => $status_db,
            'inisial'  => $inisial
        ];
    }
} catch (PDOException $e) {
    echo "<script>alert('Gagal mengambil data dari database: " . addslashes($e->getMessage()) . "');</script>";
}

// Hitung total karyawan untuk UI
$total_karyawan = count($karyawan_list);
?>

    <!-- AREA AKSI: FILTER (KIRI) & TOMBOL TAMBAH (KANAN) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
      <!-- Kelompok Filter Kategori -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterGroup">
        <button type="button" data-filter="all" class="filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
          Semua <?= $total_karyawan ?>
        </button>
        <button type="button" data-filter="teknisi" class="filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
          Teknisi
        </button>
        <button type="button" data-filter="aktif" class="filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
          Aktif
        </button>
        <button type="button" data-filter="nonaktif" class="filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap">
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
        <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Karyawan</h2>
        <span class="text-sm text-white-dark-hover font-medium"><?= $total_karyawan ?> karyawan</span>
      </div>

      <!-- Tabel Responsif -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm" id="tableKaryawan">
          <thead>
            <tr class="border-b border-white-hover text-white-darker font-semibold text-[13px] tracking-wider">
              <th class="py-4 px-5">Karyawan</th>
              <th class="py-4 px-5 text-center">Peran</th>
              <th class="py-4 px-5 text-center">Unit Aktif</th>
              <th class="py-4 px-5 text-center">Status</th>
              <th class="py-4 px-5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBodyKaryawan" class="divide-y divide-white-hover text-white-darker font-medium">
            
            <?php foreach ($karyawan_list as $row): ?>
              <?php
                  // Logika Warna Berdasarkan Peran
                  if (strtolower($row['peran']) === 'admin') {
                      $avatar_class = 'bg-blue-light text-blue';
                      $role_badge   = 'bg-blue-light text-blue-hover';
                  } else {
                      $avatar_class = 'bg-orange-light text-danger-dark';
                      $role_badge   = 'bg-orange-light text-danger-dark';
                  }

                  // Logika Warna Berdasarkan Status Dinamis
                  if (strtolower($row['status']) === 'aktif') {
                      $status_badge = 'bg-success-light text-success-dark';
                  } else {
                      $status_badge = 'bg-white-hover text-white-darker';
                  }
              ?>
              
              <tr class="hover:bg-warning-light/40 transition-colors row-karyawan"
                  data-role="<?= strtolower(htmlspecialchars($row['peran'])) ?>"
                  data-status="<?= strtolower(htmlspecialchars($row['status'])) ?>">
                
                <!-- Karyawan -->
                <td class="py-4 px-5">
                  <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full <?= $avatar_class ?> font-bold text-xs flex items-center justify-center shrink-0">
                      <?= htmlspecialchars($row['inisial']) ?>
                    </div>
                    <div>
                      <h3 class="font-semibold text-blue-darker text-sm"><?= htmlspecialchars($row['nama']) ?></h3>
                      <p class="text-xs text-white-dark-hover"><?= htmlspecialchars($row['username']) ?></p>
                    </div>
                  </div>
                </td>
                
                <!-- Peran -->
                <td class="py-4 px-5 text-center">
                  <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold tracking-wide <?= $role_badge ?>">
                    <?= htmlspecialchars($row['peran']) ?>
                  </span>
                </td>
                
                <!-- Unit Aktif -->
                <td class="py-4 px-5 text-center font-semibold text-blue-darker">
                  <?= htmlspecialchars($row['unit']) ?>
                </td>
                
                <!-- Status Dinamis -->
                <td class="py-4 px-5 text-center">
                  <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold tracking-wide <?= $status_badge ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
                
                <!-- Aksi (Tombol Hapus Memanggil Modal) -->
                <td class="py-4 px-5 text-center">
                  <div class="inline-flex items-center gap-2">
                    <a href="edit_karyawan.php?id=<?= $row['id'] ?>" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition cursor-pointer">
                      Edit
                    </a>
                    <a href="detail_karyawan.php?id=<?= $row['id'] ?>" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                      Detail
                    </a>
                    <button type="button" 
                            onclick="bukaModalHapus('<?= htmlspecialchars(addslashes($row['nama']), ENT_QUOTES) ?>', 'hapus_karyawan.php?id=<?= $row['id'] ?>')"
                            class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>

            <!-- Baris Kosong Jika Filter Tidak Menemukan Data -->
            <tr id="emptyFilterRow" style="display: none;">
              <td colspan="5" class="py-8 text-center text-sm text-white-dark-hover">
                Tidak ada data karyawan yang sesuai.
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

      <!-- BANNER PERINGATAN BAWAH TABEL -->
      <div class="mt-6 p-4 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium flex items-center gap-2">
        <p>Password tidak ditampilkan di daftar. Gunakan Edit untuk mengatur ulang password karyawan.</p>
      </div>
    </div>

    <!-- ==========================================
         MODAL POP-UP KONFIRMASI HAPUS
         ========================================== -->
    <div id="modalHapus" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
      <div class="bg-white-light w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-gray-200">
        
        <!-- Ikon Tempat Sampah Merah -->
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-danger-light mb-5">
          <svg class="h-8 w-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </div>
        
        <!-- Teks Validasi -->
        <h3 class="mb-2 text-xl font-bold text-blue-darker">Hapus Data?</h3>
        <p class="mb-7 text-sm text-white-dark-hover leading-relaxed">
          Data <span id="namaKaryawanHapus" class="font-bold text-blue-darker"></span> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
        </p>
        
        <!-- Tombol Batal & Ya, Hapus -->
        <div class="flex items-center justify-center gap-3">
          <button type="button" onclick="tutupModalHapus()" class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer">
            Batal
          </button>
          <a id="linkHapusKaryawan" href="#" class="w-full rounded-full bg-danger py-3 text-sm font-semibold text-white-light hover:bg-danger-dark transition cursor-pointer">
            Ya, Hapus
          </a>
        </div>

      </div>
    </div>

    <!-- JAVASCRIPT: Filter, Pagination (8 Baris/Halaman) & Modal Logic -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const filterButtons = document.querySelectorAll(".filter-tab-btn");
        const allRows = Array.from(document.querySelectorAll(".row-karyawan"));
        const emptyRow = document.getElementById("emptyFilterRow");
        const paginationInfo = document.getElementById("paginationInfo");
        const paginationButtons = document.getElementById("paginationButtons");

        const rowsPerPage = 8;
        let currentPage = 1;
        let currentFilter = "all";

        // Ambil baris yang lolos filter aktif
        function getFilteredRows() {
          return allRows.filter(row => {
            const role = row.getAttribute("data-role");
            const status = row.getAttribute("data-status");
            return currentFilter === "all" || role === currentFilter || status === currentFilter;
          });
        }

        // Render tabel & tombol pagination
        function renderTable() {
          const filteredRows = getFilteredRows();
          const totalItems = filteredRows.length;
          const totalPages = Math.ceil(totalItems / rowsPerPage) || 1;

          if (currentPage > totalPages) {
            currentPage = totalPages;
          }

          const startIndex = (currentPage - 1) * rowsPerPage;
          const endIndex = startIndex + rowsPerPage;

          // Sembunyikan semua baris dulu
          allRows.forEach(row => {
            row.style.display = "none";
          });

          // Tampilkan maksimal 8 baris untuk halaman aktif
          const visibleRows = filteredRows.slice(startIndex, endIndex);
          visibleRows.forEach(row => {
            row.style.display = "";
          });

          // Tampilkan pesan jika data kosong
          if (emptyRow) {
            emptyRow.style.display = totalItems === 0 ? "" : "none";
          }

          // Update info teks pagination
          if (paginationInfo) {
            if (totalItems === 0) {
              paginationInfo.textContent = "Menampilkan 0 karyawan";
            } else {
              const showStart = startIndex + 1;
              const showEnd = Math.min(endIndex, totalItems);
              paginationInfo.textContent = `Menampilkan ${showStart} - ${showEnd} dari ${totalItems} karyawan`;
            }
          }

          // Render tombol nomor halaman (muncul otomatis jika > 8 baris / > 1 halaman)
          if (paginationButtons) {
            paginationButtons.innerHTML = "";

            if (totalPages > 1) {
              // Tombol Sebelumnya
              const prevBtn = document.createElement("button");
              prevBtn.type = "button";
              prevBtn.textContent = "Sebelumnya";
              prevBtn.disabled = currentPage === 1;
              prevBtn.className = currentPage === 1
                ? "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed"
                : "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:bg-white-light-hover text-xs font-medium transition cursor-pointer";
              prevBtn.addEventListener("click", () => {
                if (currentPage > 1) {
                  currentPage--;
                  renderTable();
                }
              });
              paginationButtons.appendChild(prevBtn);

              // Tombol Angka Halaman (1, 2, 3, ...)
              for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement("button");
                pageBtn.type = "button";
                pageBtn.textContent = i;
                pageBtn.className = i === currentPage
                  ? "w-9 h-9 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer"
                  : "w-9 h-9 rounded-full bg-white-light text-white-darker border border-gray-200 hover:bg-white-light-hover text-xs font-medium transition cursor-pointer";
                pageBtn.addEventListener("click", () => {
                  currentPage = i;
                  renderTable();
                });
                paginationButtons.appendChild(pageBtn);
              }

              // Tombol Selanjutnya
              const nextBtn = document.createElement("button");
              nextBtn.type = "button";
              nextBtn.textContent = "Selanjutnya";
              nextBtn.disabled = currentPage === totalPages;
              nextBtn.className = currentPage === totalPages
                ? "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed"
                : "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:bg-white-light-hover text-xs font-medium transition cursor-pointer";
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

        // Event listener tombol filter tab
        filterButtons.forEach(btn => {
          btn.addEventListener("click", function () {
            filterButtons.forEach(b => {
              b.className = "filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
            });
            this.className = "filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap";
            
            currentFilter = this.getAttribute("data-filter");
            currentPage = 1; // Reset ke halaman 1 setiap ganti filter
            renderTable();
          });
        });

        // Jalankan pertama kali saat halaman dimuat
        renderTable();
      });

      // Fungsi Buka Modal Hapus
      function bukaModalHapus(nama, link) {
        document.getElementById('namaKaryawanHapus').textContent = nama;
        document.getElementById('linkHapusKaryawan').href = link;
        
        const modal = document.getElementById('modalHapus');
        modal.style.display = 'flex';
      }

      // Fungsi Tutup Modal Hapus
      function tutupModalHapus() {
        const modal = document.getElementById('modalHapus');
        modal.style.display = 'none';
      }
    </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>