<?php
// ==============================================================================
// 1. INISIALISASI SESSION, HELPER, DAN KEAMANAN
// ==============================================================================
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../includes/helpers.php';

/**
 * Data session demo untuk role Admin
 */
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id'       => 1,
        'username' => 'admin',
        'nama'     => 'Admin',
        'role'     => 'admin',
        'inisial'  => 'AD'
    ];
}

// ==============================================================================
// 2. DATA DUMMY / DEMO TABEL ANTREAN (SESUAI DESAIN FIGMA)
// ==============================================================================
$tanggal_hari_ini = "Sabtu, 3 Oktober 2026";

// Data daftar servis dalam antrean
$daftar_antrean = [
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Selesai',
        'layanan'   => 'Express'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Selesai',
        'layanan'   => 'Express'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Dikerjakan',
        'layanan'   => 'Express'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Dikerjakan',
        'layanan'   => 'Reguler'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Diterima',
        'layanan'   => 'Reguler'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Diterima',
        'layanan'   => 'Reguler'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Dibatalkan',
        'layanan'   => 'Reguler'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Menunggu..',
        'layanan'   => 'Reguler'
    ],
    [
        'nota'      => '#5143',
        'pelanggan' => 'Fajar',
        'perangkat' => 'Laptop',
        'teknisi'   => 'Ilham',
        'masuk'     => '29 Sep',
        'estimasi'  => '1 Okt',
        'status'    => 'Dibatalkan',
        'layanan'   => 'Reguler'
    ],
];

/**
 * Helper untuk menentukan badge warna status berdasarkan token input.css
 */
function getBadgeStatusClass($status) {
    switch ($status) {
        case 'Selesai':
            return 'bg-success-light text-success-dark';
        case 'Dikerjakan':
            return 'bg-lightblue-light text-lightblue-dark';
        case 'Diterima':
            return 'bg-warning-light text-warning-dark';
        case 'Dibatalkan':
            return 'bg-danger-light text-danger-dark';
        case 'Menunggu..':
        case 'Menunggu':
            return 'bg-blue-light text-blue-hover';
        default:
            return 'bg-white-hover text-white-darker';
    }
}

$page_title = 'Antrean';
$active_menu = 'antrean';
require_once __DIR__ . '/../includes/header.php';
?>

      <!-- 2.2 FILTER STATUS & TOMBOL + ANTREAN BARU -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <!-- Kelompok Pill Filter Kategori -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterStatusGroup">
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap" data-filter="all">
            Semua 9
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Selesai">
            Selesai
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Dikerjakan">
            Dikerjakan
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Diterima">
            Diterima
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Dibatalkan">
            Dibatalkan
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Menunggu">
            Menunggu
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Express">
            Express
          </button>
          <button type="button" class="filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Reguler">
            Reguler
          </button>
        </div>

        <!-- Tombol + Antrean Baru (Orange) -->
        <a href="Tambah_Antrean.html" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold shadow-xs transition-all whitespace-nowrap self-start sm:self-auto cursor-pointer">
          <span class="text-sm font-bold">+</span>
          <span>Antrean Baru</span>
        </a>
      </div>

      <!-- ==============================================================================
          BAGIAN 3: KARTU TABEL DAFTAR SERVIS SAAT INI
          ============================================================================== -->
      <div class="bg-white-light rounded-2xl p-6 lg:p-8 shadow-sm border border-white-hover/60">
        <!-- Header Tabel & Info Jumlah -->
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Servis Saat Ini</h2>
          <span class="text-sm text-white-dark-hover font-medium">8 dari 48 antrean</span>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm" id="tableAntrean">
            <thead>
              <tr class="border-b border-white-hover text-white-dark-hover font-bold text-xs uppercase tracking-wider">
                <th class="py-4 px-5">Nota</th>
                <th class="py-4 px-5">Pelanggan</th>
                <th class="py-4 px-5">Perangkat</th>
                <th class="py-4 px-5">Teknisi</th>
                <th class="py-4 px-5">Masuk</th>
                <th class="py-4 px-5">Estimasi</th>
                <th class="py-4 px-5 text-center">Status</th>
                <th class="py-4 px-5 text-center">Layanan</th>
                <th class="py-4 px-5 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white-hover/50 text-white-darker font-medium" id="tableBodyAntrean">
              <?php foreach ($daftar_antrean as $item): ?>
                <tr class="hover:bg-warning-light/40 transition-colors row-item"
                    data-status="<?= e($item['status']) ?>"
                    data-layanan="<?= e($item['layanan']) ?>"
                    data-nota="<?= e(strtolower($item['nota'])) ?>">
                  
                  <!-- Nota -->
                  <td class="py-4 px-5 font-bold text-blue-darker"><?= e($item['nota']) ?></td>
                  
                  <!-- Pelanggan -->
                  <td class="py-4 px-5 font-semibold text-blue-darker"><?= e($item['pelanggan']) ?></td>
                  
                  <!-- Perangkat -->
                  <td class="py-4 px-5 text-white-dark-active"><?= e($item['perangkat']) ?></td>
                  
                  <!-- Teknisi -->
                  <td class="py-4 px-5"><?= e($item['teknisi']) ?></td>
                  
                  <!-- Masuk -->
                  <td class="py-4 px-5 text-white-dark-active"><?= e($item['masuk']) ?></td>
                  
                  <!-- Estimasi -->
                  <td class="py-4 px-5 text-white-dark-active"><?= e($item['estimasi']) ?></td>
                  
                  <!-- Status (Badge Warna Semantik) -->
                  <td class="py-4 px-5 text-center">
                    <span class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold tracking-wide <?= getBadgeStatusClass($item['status']) ?>">
                      <?= e($item['status']) ?>
                    </span>
                  </td>
                  
                  <!-- Layanan (Express / Reguler) -->
                  <td class="py-4 px-5 text-center">
                    <?php if ($item['layanan'] === 'Express'): ?>
                      <span class="font-bold text-danger text-sm">Express</span>
                    <?php else: ?>
                      <span class="font-semibold text-blue-darker text-sm">Reguler</span>
                    <?php endif; ?>
                  </td>
                  
                  <!-- Aksi (3 Tombol Nyaman Diklik) -->
                  <td class="py-4 px-5 text-center">
                    <div class="inline-flex items-center gap-2">
                      <!-- Tombol Edit (Outline) -->
                      <a href="edit_antrean_admin.html" class="px-3.5 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-blue-light text-xs font-semibold transition">
                        Edit
                      </a>
                      <!-- Tombol Detail (Solid Tosca) -->
                      <a href="detail_antrean_admin.html" class="px-3.5 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                        Detail
                      </a>
                      <!-- Tombol Hapus (Outline Danger) -->
                      <button type="button" class="px-3.5 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
                        Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==============================================================================
        BAGIAN 4: PAGINATION FOOTER
        ============================================================================== -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm text-white-dark-hover">
        <p>Menampilkan 1-8 dari 48 antrean</p>

        <!-- Pagination Nav -->
        <div class="flex items-center gap-2">
          <!-- Tombol Prev -->
          <button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-white-dark-hover hover:bg-white-hover transition cursor-pointer text-xs font-bold">
            &lt;
          </button>
          <!-- Halaman 1 (Aktif - Orange) -->
          <button type="button" class="w-8 h-8 rounded-full bg-orange text-white-light font-bold flex items-center justify-center shadow-xs cursor-pointer text-xs">
            1
          </button>
          <!-- Halaman 2 -->
          <button type="button" class="w-8 h-8 rounded-full text-white-dark-hover hover:bg-white-hover font-medium flex items-center justify-center transition cursor-pointer text-xs">
            2
          </button>
          <!-- Halaman 3 -->
          <button type="button" class="w-8 h-8 rounded-full text-white-dark-hover hover:bg-white-hover font-medium flex items-center justify-center transition cursor-pointer text-xs">
            3
          </button>
          <!-- Tombol Next -->
          <button type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-white-dark-hover hover:bg-white-hover transition cursor-pointer text-xs font-bold">
            &gt;
          </button>
        </div>
      </div>

    </div> <!-- Penutup <div class="space-y-6"> dari header.php -->
  </main> <!-- Penutup <main> dari header.php -->

  <!-- ==============================================================================
      BAGIAN 5: SCRIPT JAVASCRIPT (FILTER REAL-TIME & PENCARIAN NOTA)
    ============================================================================== -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const searchNotaInput = document.getElementById('searchNotaInput');
      const filterTabs = document.querySelectorAll('#filterStatusGroup .filter-tab-btn');
      const rows = document.querySelectorAll('#tableBodyAntrean .row-item');

      let currentFilter = 'all';

      // Fungsi menyaring baris tabel
      function filterTable() {
        const query = searchNotaInput ? searchNotaInput.value.toLowerCase().trim() : '';

        rows.forEach(row => {
          const nota = row.getAttribute('data-nota') || '';
          const status = row.getAttribute('data-status') || '';
          const layanan = row.getAttribute('data-layanan') || '';

          const matchNota = nota.includes(query) || row.textContent.toLowerCase().includes(query);
          
          let matchFilter = false;
          if (currentFilter === 'all') {
            matchFilter = true;
          } else if (currentFilter === 'Express' || currentFilter === 'Reguler') {
            matchFilter = (layanan === currentFilter);
          } else {
            matchFilter = status.toLowerCase().includes(currentFilter.toLowerCase());
          }

          row.style.display = (matchNota && matchFilter) ? '' : 'none';
        });
      }

      // Event Listener: Pencarian Real-Time
      if (searchNotaInput) {
        searchNotaInput.addEventListener('input', filterTable);
      }

      // Event Listener: Tab Filter Status
      filterTabs.forEach(btn => {
        btn.addEventListener('click', function() {
          // Reset style semua tab
          filterTabs.forEach(b => {
            b.className = 'filter-tab-btn px-4 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap';
          });

          // Set style tab aktif (tosca-dark)
          this.className = 'filter-tab-btn px-4 py-1.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap';

          currentFilter = this.getAttribute('data-filter') || 'all';
          filterTable();
        });
      });
    });
  </script>

</body>
</html>
