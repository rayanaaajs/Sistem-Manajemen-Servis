<?php
// ==============================================================================
// 1. INISIALISASI SESSION, HELPER, DAN KEAMANAN
// ==============================================================================
require_once __DIR__ . '/../includes/helpers.php';

/**
 * Data session demo untuk role Admin
 */

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
        <!-- Kelompok Pill Filter Kategori (scroll horizontal di HP, scrollbar disembunyiin) -->
        <div class="flex flex-wrap items-center gap-2" id="filterStatusGroup">
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap" data-filter="all">
            Semua 9
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Selesai">
            Selesai
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Dikerjakan">
            Dikerjakan
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Diterima">
            Diterima
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Dibatalkan">
            Dibatalkan
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Menunggu">
            Menunggu
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Express">
            Express
          </button>
          <button type="button" class="filter-tab-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Reguler">
            Reguler
          </button>
        </div>

        <!-- Tombol + Antrean Baru (Orange) - full width di HP -->
        <a href="tambah_antrean.php" class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto sm:px-12 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition-all whitespace-nowrap cursor-pointer">
          <span class="text-sm font-bold">+</span>
          <span>Antrean Baru</span>
        </a>
      </div>

      <!-- ==============================================================================
          BAGIAN 3: KARTU TABEL DAFTAR SERVIS SAAT INI
          ============================================================================== -->
      <div class="bg-white-light rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200">
        <!-- Header Tabel & Info Jumlah -->
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
          <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Servis Saat Ini</h2>
          <span class="text-sm text-white-dark-hover font-medium">8 dari 48 antrean</span>
        </div>

        <!-- Tabel Responsif (geser horizontal di HP) -->
        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px] whitespace-nowrap text-left border-collapse text-sm" id="tableAntrean">
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
            <tbody class="divide-y divide-white-hover text-white-darker font-medium" id="tableBodyAntrean">
              <?php foreach ($daftar_antrean as $item): ?>
                <tr class="hover:bg-warning-light/40 transition-colors row-item"
                    data-status="<?= e($item['status']) ?>"
                    data-layanan="<?= e($item['layanan']) ?>"
                    data-nota="<?= e(strtolower($item['nota'])) ?>">

                  <!-- Nota -->
                  <td class="py-4 px-5 font-semibold text-blue-darker"><?= e($item['nota']) ?></td>

                  <!-- Pelanggan -->
                  <td class="py-4 px-5 font-medium text-blue-darker"><?= e($item['pelanggan']) ?></td>

                  <!-- Perangkat -->
                  <td class="py-4 px-5"><?= e($item['perangkat']) ?></td>

                  <!-- Teknisi (disembunyiin di layar < lg) -->
                  <td class="hidden lg:table-cell py-4 px-5"><?= e($item['teknisi']) ?></td>

                  <!-- Masuk (disembunyiin di layar < lg) -->
                  <td class="hidden lg:table-cell py-4 px-5 text-white-dark-active"><?= e($item['masuk']) ?></td>

                  <!-- Estimasi (disembunyiin di layar < lg) -->
                  <td class="hidden lg:table-cell py-4 px-5 text-white-dark-active"><?= e($item['estimasi']) ?></td>

                  <!-- Status (Badge Warna Semantik) -->
                  <td class="py-4 px-5 text-center">
                    <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold tracking-wide <?= getBadgeStatusClass($item['status']) ?>">
                      <?= e($item['status']) ?>
                    </span>
                  </td>

                  <!-- Layanan (Express / Reguler) -->
                  <td class="py-4 px-5 text-center">
                    <?php if ($item['layanan'] === 'Express'): ?>
                      <span class="font-semibold text-danger-hover text-sm">Express</span>
                    <?php else: ?>
                      <span class="font-medium text-blue-darker text-sm">Reguler</span>
                    <?php endif; ?>
                  </td>

                  <!-- Aksi (3 Tombol Nyaman Diklik) -->
                  <td class="py-4 px-5 text-center">
                    <div class="inline-flex items-center gap-2">
                      <!-- Tombol Edit (Outline) -->
                      <a href="edit_antrean.php?id=<?= e($item['nota']) ?>" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition">
                        Edit
                      </a>
                      <!-- Tombol Detail (Solid Tosca) -->
                      <a href="detail_antrean.php?id=<?= e($item['nota']) ?>" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition">
                        Detail
                      </a>
                      <!-- Tombol Hapus (Outline Danger) -->
                      <button type="button"
                              onclick="bukaModalHapus('<?= e($item['nota']) ?>', 'hapus_antrean.php?nota=<?= e($item['nota']) ?>')"
                              class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
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
        <p class="pl-2 py-2">Menampilkan 1-8 dari 48 antrean</p>
      </div>


<!-- ==============================================================================
     MODAL POP-UP KONFIRMASI HAPUS (Fix Blur Mulus)
============================================================================== -->
<div id="modalHapus" class="fixed inset-0 z-[99] hidden flex items-center justify-center">
  <!-- Backdrop Gelap (Transisi Opacity ditaruh langsung di sini) -->
  <div id="modalBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="tutupModalHapus()"></div>

  <!-- Kotak Pop-up (Transisi Scale & Opacity ditaruh di sini) -->
  <div id="modalBox" class="relative bg-white-light rounded-3xl p-6 md:p-8 max-w-sm w-full mx-4 shadow-xl transform scale-95 opacity-0 transition-all duration-300 text-center">
    <!-- Icon Peringatan -->
    <div class="w-16 h-16 rounded-full bg-danger/10 text-danger-dark-hover mx-auto flex items-center justify-center mb-5">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
      </svg>
    </div>

    <h3 class="text-xl font-semibold text-danger-dark-hover tracking-tight mb-2">Hapus Data?</h3>
    <p class="text-sm text-white-dark-active mb-6">
      Data nota <span id="textNotaHapus" class="font-bold text-danger-dark-hover"></span> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
    </p>

    <div class="flex flex-col-reverse sm:flex-row gap-3 justify-center">
      <button type="button" onclick="tutupModalHapus()" class="px-6 py-2.5 bg-white-light rounded-full border border-gray-300 text-blue-darker font-medium hover:bg-white-light-active transition w-full cursor-pointer">
        Batal
      </button>
      <a href="#" id="btnConfirmHapus" class="px-6 py-2.5 rounded-full bg-danger hover:bg-danger-hover text-white-light font-medium transition w-full text-center">
        Ya, Hapus
      </a>
    </div>
  </div>
</div>


<?php
require_once __DIR__ . '/../includes/footer.php';
?>