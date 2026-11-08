<?php
$page_title = 'Pelanggan';
$active_menu = 'pelanggan';
$search_placeholder = 'Cari nama atau no WA pelanggan...';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// Data dummy pelanggan sesuai screenshot Figma
$pelanggan_list = [
    [
        'id'       => 1,
        'nama'     => 'Sari Lestari',
        'kode'     => '#5128',
        'whatsapp' => '0812-3344-5566',
        'alamat'   => 'Jl. Kertanegara No. 21, Singosari',
        'servis'   => '1 unit',
        'inisial'  => 'PL'
    ],
    [
        'id'       => 2,
        'nama'     => 'Sari Lestari',
        'kode'     => '#5128',
        'whatsapp' => '0812-3344-5566',
        'alamat'   => 'Jl. Kertanegara No. 21, Singosari',
        'servis'   => '1 unit',
        'inisial'  => 'PL'
    ],
    [
        'id'       => 3,
        'nama'     => 'Sari Lestari',
        'kode'     => '#5228',
        'whatsapp' => '0812-3344-5566',
        'alamat'   => 'Jl. Kertanegara No. 21, Singosari',
        'servis'   => '1 unit',
        'inisial'  => 'PL'
    ],
    [
        'id'       => 4,
        'nama'     => 'Sari Lestari',
        'kode'     => '#5128',
        'whatsapp' => '0812-3344-5566',
        'alamat'   => 'Jl. Kertanegara No. 21, Singosari',
        'servis'   => '1 unit',
        'inisial'  => 'PL'
    ],
    [
        'id'       => 5,
        'nama'     => 'Sari Lestari',
        'kode'     => '#0128',
        'whatsapp' => '0812-3344-5566',
        'alamat'   => 'Jl. Kertanegara No. 21, Singosari',
        'servis'   => '1 unit',
        'inisial'  => 'PL'
    ],
    [
        'id'       => 6,
        'nama'     => 'Sari Lestari',
        'kode'     => '#0128',
        'whatsapp' => '0812-3344-5566',
        'alamat'   => 'Jl. Kertanegara No. 21, Singosari',
        'servis'   => '1 unit',
        'inisial'  => 'PL'
    ]
];
?>

<!-- AREA AKSI: SEARCH & TOMBOL TAMBAH -->
        <!-- Kanan: Tombol + Tambah Pelanggan (Orange) -->
      <div class="flex justify-end mb-5">
        <a href="tambah_pelanggan.php"
           class="inline-flex items-center justify-center gap-1.5 px-12 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition-all whitespace-nowrap cursor-pointer">
          <span class="text-sm font-bold">+</span>
          <span>Tambah Pelanggan</span>
        </a>
      </div>

      <!-- KARTU TABEL DAFTAR PELANGGAN -->
      <div class="bg-white-light rounded-2xl p-6 lg:p-8 border border-gray-200">
        <!-- Header Tabel -->
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Pelanggan</h2>
          <span class="text-sm text-white-dark-hover font-medium">16 pelanggan</span>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm" id="tablePelanggan">
            <thead>
              <tr class="border-b border-white-hover text-white-darker font-bold text-[13px] tracking-wider">
                <th class="py-4 px-5">Nama Pelanggan</th>
                <th class="py-4 px-5 text-center">Kode</th>
                <th class="py-4 px-5">Whatsapp</th>
                <th class="py-4 px-5">Alamat</th>
                <th class="py-4 px-5 text-center">Servis</th>
                <th class="py-4 px-5 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="tableBody" class="divide-y divide-white-hover text-white-darker font-medium" id="tableBodyPelanggan">
              <?php foreach ($pelanggan_list as $row): ?>
              <tr class="hover:bg-warning-light/40 transition-colors row-pelanggan" data-nama="<?= strtolower(htmlspecialchars($row['nama'])) ?>">
                <!-- Nama Pelanggan -->
                <td class="py-4 px-5">
                  <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-lightblue-light text-lightblue-darker font-bold text-xs flex items-center justify-center shrink-0">
                      <?= htmlspecialchars($row['inisial']) ?>
                    </div>
                    <div>
                      <h3 class="font-semibold text-blue-darker text-sm"><?= htmlspecialchars($row['nama']) ?></h3>
                    </div>
                  </div>
                </td>

                <!-- Kode -->
                <td class="py-4 px-5 text-center font-semibold text-blue-darker text-sm">
                  <?= htmlspecialchars($row['kode']) ?>
                </td>

                <!-- Whatsapp -->
                <td class="py-4 px-5 font-medium text-white-darker">
                  <?= htmlspecialchars($row['whatsapp']) ?>
                </td>

                <!-- Alamat -->
                <td class="py-4 px-5 text-white-dark-active text-sm">
                  <?= htmlspecialchars($row['alamat']) ?>
                </td>

                <!-- Servis -->
                <td class="py-4 px-5 text-center font-semibold text-blue-darker text-sm">
                  <?= htmlspecialchars($row['servis']) ?>
                </td>

                <!-- Aksi -->
                <td class="py-4 px-5 text-center">
                  <div class="inline-flex items-center gap-2">
                    <a href="edit_pelanggan.php?id=<?= $row['id'] ?>"
                       class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition cursor-pointer">
                      Edit
                    </a>
                    <a href="detail_pelanggan.php?id=<?= $row['id'] ?>"
                       class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                      Detail
                    </a>
                  <button type="button" 
                          onclick="bukaModalHapus('<?= htmlspecialchars($row['nama']) ?>', 'hapus_pelanggan.php?id=<?= $row['id'] ?>')"
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

        <!-- Footer Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-white-dark-hover mt-8 pt-4 border-t border-gray-100">
          <p>Menampilkan 1-6 dari 16 pelanggan</p>

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

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
