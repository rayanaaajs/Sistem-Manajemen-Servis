<?php

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

      <!-- ACTION BAR: TOMBOL + TAMBAH PELANGGAN -->
      <div class="flex justify-end mb-6">
        <a href="#tambah-pelanggan"
           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs sm:text-sm font-semibold shadow-sm transition-all whitespace-nowrap cursor-pointer">
          <span class="text-base font-bold">+</span>
          <span>Tambah Pelanggan</span>
        </a>
      </div>

      <!-- KARTU TABEL DAFTAR PELANGGAN -->
      <div class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60">
        <!-- Header Tabel -->
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-base lg:text-lg font-bold text-blue-darker tracking-tight">Daftar Pelanggan</h2>
          <span class="text-sm text-white-dark-hover font-medium">7 karyawan</span>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm" id="tablePelanggan">
            <thead>
              <tr class="border-b border-white-hover text-white-dark-hover font-semibold text-xs tracking-wider">
                <th class="py-4 px-4">Nama Pelanggan</th>
                <th class="py-4 px-4 text-center">Kode</th>
                <th class="py-4 px-4">Whatsapp</th>
                <th class="py-4 px-4">Alamat</th>
                <th class="py-4 px-4 text-center">Servis</th>
                <th class="py-4 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white-hover/50 text-white-darker font-medium" id="tableBodyPelanggan">
              <?php foreach ($pelanggan_list as $row): ?>
              <tr class="hover:bg-warning-light/30 transition-colors row-pelanggan" data-nama="<?= strtolower(htmlspecialchars($row['nama'])) ?>">
                <!-- Nama Pelanggan -->
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-lightblue-light text-lightblue-darker font-bold text-xs flex items-center justify-center shrink-0">
                      <?= htmlspecialchars($row['inisial']) ?>
                    </div>
                    <div>
                      <h3 class="font-bold text-blue-darker text-sm"><?= htmlspecialchars($row['nama']) ?></h3>
                    </div>
                  </div>
                </td>

                <!-- Kode -->
                <td class="py-4 px-4 text-center font-bold text-blue-darker text-xs">
                  <?= htmlspecialchars($row['kode']) ?>
                </td>

                <!-- Whatsapp -->
                <td class="py-4 px-4 text-blue-darker text-xs font-medium">
                  <?= htmlspecialchars($row['whatsapp']) ?>
                </td>

                <!-- Alamat -->
                <td class="py-4 px-4 text-blue-darker/80 text-xs font-medium">
                  <?= htmlspecialchars($row['alamat']) ?>
                </td>

                <!-- Servis -->
                <td class="py-4 px-4 text-center font-bold text-blue-darker text-xs">
                  <?= htmlspecialchars($row['servis']) ?>
                </td>

                <!-- Aksi -->
                <td class="py-4 px-4 text-center">
                  <div class="inline-flex items-center gap-2">
                    <a href="edit_pelanggan.php?id=<?= $row['id'] ?>"
                       class="px-4 py-1.5 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-xs font-semibold transition cursor-pointer">
                      Edit
                    </a>
                    <a href="#detail-pelanggan"
                       class="px-4 py-1.5 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                      Detail
                    </a>
                    <button type="button"
                            class="px-4 py-1.5 rounded-full border border-danger/60 text-danger hover:bg-danger-light text-xs font-semibold transition cursor-pointer">
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
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 pt-4 border-t border-white-hover/60">
          <p class="text-xs text-white-dark-hover">
            Menampilkan 1-6 dari 16 pelanggan
          </p>

          <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-orange text-white-light font-bold text-xs flex items-center justify-center cursor-pointer shadow-xs">
              1
            </span>
            <a href="#page-2" class="w-7 h-7 rounded-full text-blue-darker hover:bg-white-hover font-medium text-xs flex items-center justify-center transition cursor-pointer">
              2
            </a>
            <a href="#page-next" class="text-xs text-blue-darker hover:text-orange font-bold transition px-1 cursor-pointer">
              &gt;
            </a>
          </div>
        </div>
      </div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

