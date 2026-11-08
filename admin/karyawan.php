<?php
$page_title = 'Karyawan';
$active_menu = 'karyawan';
$search_placeholder = 'Cari Karyawan...';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Data dummy karyawan
$karyawan_list = [
    [
        'id'       => 1,
        'nama'     => 'Ilham Ramadhan',
        'username' => '@ilham',
        'peran'    => 'Admin',
        'no_hp'    => '0813-4455-9900',
        'unit'     => '5 unit',
        'status'   => 'Aktif',
        'inisial'  => 'AD'
    ],
    [
        'id'       => 2,
        'nama'     => 'Bagas Putra',
        'username' => '@bagas',
        'peran'    => 'Teknisi',
        'no_hp'    => '0813-4455-9901',
        'unit'     => '3 unit',
        'status'   => 'Aktif',
        'inisial'  => 'TK'
    ],
    [
        'id'       => 3,
        'nama'     => 'Dimas Anggara',
        'username' => '@dimas',
        'peran'    => 'Teknisi',
        'no_hp'    => '0813-4455-9902',
        'unit'     => '8 unit',
        'status'   => 'Aktif',
        'inisial'  => 'TK'
    ],
    [
        'id'       => 4,
        'nama'     => 'Fajar Sidik',
        'username' => '@fajar',
        'peran'    => 'Teknisi',
        'no_hp'    => '0813-4455-9903',
        'unit'     => '2 unit',
        'status'   => 'Aktif',
        'inisial'  => 'TK'
    ],
    [
        'id'       => 5,
        'nama'     => 'Varellino',
        'username' => '@varell',
        'peran'    => 'Teknisi',
        'no_hp'    => '0813-4455-9904',
        'unit'     => '0 unit',
        'status'   => 'Nonaktif',
        'inisial'  => 'TK'
    ],
    [
        'id'       => 6,
        'nama'     => 'Sari Lestari',
        'username' => '@sari',
        'peran'    => 'Admin',
        'no_hp'    => '0813-4455-9905',
        'unit'     => '0 unit',
        'status'   => 'Nonaktif',
        'inisial'  => 'AD'
    ],
    [
        'id'       => 7,
        'nama'     => 'Rudi Tabuti',
        'username' => '@rudi',
        'peran'    => 'Teknisi',
        'no_hp'    => '0813-4455-9906',
        'unit'     => '1 unit',
        'status'   => 'Aktif',
        'inisial'  => 'TK'
    ]
];
?>

    <!-- AREA AKSI: FILTER (KIRI) & TOMBOL TAMBAH (KANAN) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
      <!-- Kelompok Filter Kategori -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="filterGroup">
        <button type="button" data-filter="all" class="filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap">
          Semua 7
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
        <span class="text-sm text-white-dark-hover font-medium">7 karyawan</span>
      </div>

      <!-- Tabel Responsif -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm" id="tableKaryawan">
          <thead>
            <tr class="border-b border-white-hover text-white-darker font-semibold text-[13px] tracking-wider">
              <th class="py-4 px-5">Karyawan</th>
              <th class="py-4 px-5 text-center">Peran</th>
              <th class="py-4 px-5">No. HP</th>
              <th class="py-4 px-5 text-center">Unit Aktif</th>
              <th class="py-4 px-5 text-center">Status</th>
              <th class="py-4 px-5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody" class="divide-y divide-white-hover text-white-darker font-medium" id="tableBodyKaryawan">
            
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

                  // Logika Warna Berdasarkan Status
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
                
                <!-- No. HP -->
                <td class="py-4 px-5 font-medium text-white-darker">
                  <?= htmlspecialchars($row['no_hp']) ?>
                </td>
                
                <!-- Unit Aktif -->
                <td class="py-4 px-5 text-center font-semibold text-blue-darker">
                  <?= htmlspecialchars($row['unit']) ?>
                </td>
                
                <!-- Status -->
                <td class="py-4 px-5 text-center">
                  <span class="inline-block w-28 py-2 rounded-full text-xs font-semibold tracking-wide <?= $status_badge ?>">
                    <?= htmlspecialchars($row['status']) ?>
                  </span>
                </td>
                
                <!-- Aksi -->
                <td class="py-4 px-5 text-center">
                  <div class="inline-flex items-center gap-2">
                    <a href="edit_karyawan.php?id=<?= $row['id'] ?>" class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition cursor-pointer">
                      Edit
                    </a>
                    <a href="detail_karyawan.php?id=<?= $row['id'] ?>" class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                      Detail
                    </a>
                    <!-- Modal Konfirmasi Hapus Dinamis -->
                    <button type="button" 
                            onclick="bukaModalHapus('<?= htmlspecialchars($row['nama']) ?>', 'hapus_karyawan.php?id=<?= $row['id'] ?>')"
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

      <!-- BANNER PERINGATAN BAWAH TABEL -->
      <div class="mt-8 p-4 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium flex items-center gap-2">
        <p>Password tidak ditampilkan di daftar. Gunakan Edit untuk mengatur ulang password karyawan.</p>
      </div>
    </div>

    <!-- JAVASCRIPT: Client-side Filter Status (Karena Search Bar dihapus, tinggal filternya aja) -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const filterButtons = document.querySelectorAll(".filter-tab-btn");
        const rows = document.querySelectorAll(".row-karyawan");

        filterButtons.forEach(btn => {
          btn.addEventListener("click", function () {
            // Reset semua tombol ke style abu-abu outline
            filterButtons.forEach(b => {
              b.className = "filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
            });
            // Bikin tombol yang diklik jadi tosca solid
            this.className = "filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap";
            
            const filter = this.getAttribute("data-filter");

            // Filter baris tabel berdasarkan peran atau status
            rows.forEach(row => {
              const role = row.getAttribute("data-role");
              const status = row.getAttribute("data-status");
              
              if (filter === "all" || role === filter || status === filter) {
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