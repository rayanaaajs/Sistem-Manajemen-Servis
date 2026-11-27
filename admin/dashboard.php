<?php
// File tampilan saja: data, otorisasi, dan POST ada di dashboard_data.php.
define('SIPASTI_ADMIN_DASHBOARD', true);
require_once __DIR__ . '/dashboard_data.php';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($flashDashboard): ?>
  <div role="alert" class="mb-5 rounded-2xl border border-gray-200 bg-white-light px-5 py-4 text-sm font-medium <?= ($flashDashboard['tipe'] ?? '') === 'success' ? 'text-success-dark' : 'text-danger-dark' ?>">
    <?= e((string) ($flashDashboard['pesan'] ?? '')) ?>
  </div>
<?php endif; ?>
<style>
  #filterWaktuGroup {
    position: relative;
  }

  #customRangeWrap {
    position: relative;
  }

  #formRentangCustom {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 18rem;
    max-width: calc(100vw - 3rem);
    z-index: 30;
  }

  #formRentangCustom[hidden] {
    display: none;
  }

  @media (max-width: 639px) {
    #customRangeWrap {
      position: static;
    }

    #formRentangCustom {
      left: 0;
      right: auto;
      max-width: 100%;
    }
  }

  .dashboard-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(20, 83, 88, 0.3) transparent;
  }

  .dashboard-scroll::-webkit-scrollbar {
    width: 5px;
    height: 5px;
  }

  .dashboard-scroll::-webkit-scrollbar-track {
    background: transparent;
  }

  .dashboard-scroll::-webkit-scrollbar-thumb {
    background: rgba(20, 83, 88, 0.3);
    border-radius: 999px;
  }

  .dashboard-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(20, 83, 88, 0.48);
  }

  /* Ukuran tabel untuk layar kecil; di desktop mengisi sisa kolom kiri. */
  #dashboardWaitingCard {
    height: 320px;
  }

  @media (min-width: 1280px) {
    #dashboardWaitingCard {
      height: auto;
      min-height: 260px;
      flex-grow: 1;
    }
  }
</style>

<?php if ($errorPage !== ''): ?>
  <div role="alert" class="mb-5 rounded-2xl border border-danger bg-danger-light px-5 py-4 text-sm font-medium text-danger-dark">
    <?= e($errorPage) ?>
  </div>
<?php endif; ?>

<?php if ($errorRentang !== ''): ?>
  <p role="alert" class="mb-4 text-sm text-danger-dark"><?= e($errorRentang) ?></p>
<?php endif; ?>
<!-- ================= KONTEN UTAMA ================= -->
<div class="flex flex-wrap items-center gap-2.5 mb-6" id="filterWaktuGroup">
  <button type="button" data-range="today" class="px-6 py-2.5 rounded-full bg-tosca-active text-white-light text-xs font-semibold transition">Hari ini</button>
  <button type="button" data-range="7d" class="px-6 py-2.5 rounded-full bg-white-light text-gray-600 border border-gray-200 text-xs font-medium hover:bg-gray-50 transition">7 Hari</button>
  <button type="button" data-range="30d" class="px-6 py-2.5 rounded-full bg-white-light text-gray-600 border border-gray-200 text-xs font-medium hover:bg-gray-50 transition">30 Hari</button>
  <div id="customRangeWrap">
    <button type="button" data-range="custom" aria-controls="formRentangCustom" aria-expanded="false" class="px-6 py-2.5 rounded-full bg-white-light text-gray-600 border border-gray-200 text-xs font-medium hover:bg-gray-50 transition">
      <span class="inline-flex items-center gap-2">
        <span><?= e($rentang === 'custom' ? $labelPeriode : 'Custom') ?></span>
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <rect x="3" y="5" width="18" height="16" rx="2" />
          <path d="M16 3v4M8 3v4M3 10h18" />
        </svg>
      </span>
    </button>
    <form method="GET" action="dashboard.php" id="formRentangCustom" hidden class="rounded-2xl border border-gray-200 bg-white-light p-4 shadow-lg space-y-3">
      <input type="hidden" name="range" value="custom">
      <?php if ($selesai_cari !== ''): ?><input type="hidden" name="selesai_q" value="<?= e($selesai_cari) ?>"><?php endif; ?>
      <label class="block text-xs font-medium text-blue-darker">Dari tanggal
        <input type="date" name="start_date" required value="<?= e($tanggalMulaiCustom) ?>" class="block w-full mt-1 rounded-xl border border-gray-200 bg-white-light-hover px-3 py-2 text-xs text-blue-darker">
      </label>
      <label class="block text-xs font-medium text-blue-darker">Sampai tanggal
        <input type="date" name="end_date" required value="<?= e($tanggalAkhirCustom) ?>" class="block w-full mt-1 rounded-xl border border-gray-200 bg-white-light-hover px-3 py-2 text-xs text-blue-darker">
      </label>
      <button type="submit" class="w-full px-5 py-2.5 rounded-full bg-tosca-active text-white-light text-xs font-semibold transition">Terapkan</button>
    </form>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-stretch">
  <div class="xl:col-span-3 flex flex-col gap-6 min-w-0" id="dashboardTopMain">

    <!-- 4 METRIK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Selesai (Orange) -->
      <div class="bg-orange text-white-light rounded-3xl p-6 flex flex-col justify-between min-h-40">
        <div>
          <p class="text-[14px] font-semibold mb-2">Selesai</p>
          <h3 class="text-4xl font-bold tracking-tight"><?= e($stat_selesai['total']) ?></h3>
        </div>
        <div class="flex justify-between items-center mt-6">
          <p class="text-[12px] font-medium opacity-90"><?= e($stat_selesai['label_bawah']) ?></p>
          <span class="bg-white-light text-success px-3.5 py-1 rounded-full text-[11px] font-semibold"><?= e($stat_selesai['badge']) ?></span>
        </div>
      </div>
      <!-- Servis Masuk (Tosca) -->
      <div class="bg-tosca text-white-light rounded-3xl p-6 flex flex-col justify-between min-h-40">
        <div>
          <p class="text-[14px] font-semibold mb-2">Servis Masuk</p>
          <h3 class="text-4xl font-bold tracking-tight"><?= e($stat_servis_masuk['total']) ?></h3>
        </div>
        <div class="flex justify-between items-center mt-6">
          <p class="text-[12px] font-medium opacity-90"><?= e($stat_servis_masuk['label_bawah']) ?></p>
          <span class="bg-white-light text-success px-3.5 py-1 rounded-full text-[11px] font-bold"><?= e($stat_servis_masuk['badge']) ?></span>
        </div>
      </div>
      <!-- Dikerjakan (Tosca) -->
      <div class="bg-tosca text-white-light rounded-3xl p-6 flex flex-col justify-between min-h-40">
        <div>
          <p class="text-[14px] font-semibold mb-2">Dikerjakan</p>
          <h3 class="text-4xl font-bold tracking-tight"><?= e($stat_dikerjakan['total']) ?></h3>
        </div>
        <div class="flex justify-between items-center mt-6">
          <p class="text-[12px] font-medium opacity-90"><?= e($stat_dikerjakan['label_bawah']) ?></p>
          <span class="bg-white-light text-success px-3.5 py-1 rounded-full text-[11px] font-bold"><?= e($stat_dikerjakan['badge']) ?></span>
        </div>
      </div>
      <!-- Omzet (Tosca) -->
      <div class="bg-tosca text-white-light rounded-3xl p-6 flex flex-col justify-between min-h-40">
        <div>
          <p class="text-[14px] font-semibold mb-2">Omzet</p>
          <h3 class="text-4xl font-bold tracking-tight"><?= e($stat_omzet['total']) ?></h3>
        </div>
        <div class="flex justify-between items-center mt-6">
          <p class="text-[12px] font-medium opacity-90"><?= e($stat_omzet['label_bawah']) ?></p>
          <span class="bg-white-light text-success px-3.5 py-1 rounded-full text-[11px] font-bold"><?= e($stat_omzet['badge']) ?></span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- GRAFIK SERVIS -->
      <div class="lg:col-span-3 bg-white-light rounded-3xl p-6 border border-gray-200 flex flex-col justify-between h-[320px] min-h-0">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-md font-semibold text-blue-darker tracking-tight">Servis Periode Terpilih</h2>
          <div class="flex items-center gap-4 text-[11px] font-medium text-gray-500">
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange"></span><span>Masuk</span></div>
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-tosca"></span><span>Selesai</span></div>
          </div>
        </div>
        <div class="relative w-full h-48 mt-2">
          <canvas id="servisChart"></canvas>
        </div>
      </div>

      <!-- BEBAN TEKNISI -->
      <div class="lg:col-span-2 bg-white-light rounded-3xl p-6 border border-gray-200 flex flex-col h-[320px] min-h-0">
        <h2 class="text-md font-semibold text-blue-darker tracking-tight mb-7">Beban Teknisi</h2>
        <div class="space-y-6 min-h-0 flex-1 overflow-y-auto pr-2 dashboard-scroll" tabindex="0" aria-label="Daftar beban teknisi, dapat digulir">
          <?php foreach ($beban_teknisi as $bt):
            $persen = min(($bt['unit'] / $max_beban_teknisi) * 100, 100);
          ?>
            <div>
              <div class="flex justify-between items-center text-xs font-semibold mb-2">
                <span class="text-gray-700"><?= e($bt['nama']) ?></span>
                <span class="text-[#1C2434] font-bold"><?= e($bt['unit']) ?> unit</span>
              </div>
              <div class="w-full h-3 bg-[#F4F5F7] rounded-full overflow-hidden">
                <div class="h-full <?= $bt['warna'] ?> rounded-full transition-all duration-500" style="width: <?= $persen ?>%;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- TABEL MENUNGGU PERSETUJUAN: BARU, STYLING MENGIKUTI PALETTE SIPASTI -->
    <div class="bg-white-light rounded-3xl p-6 border border-gray-200 flex flex-col min-h-0 overflow-hidden h-[320px] xl:h-auto xl:flex-1" id="dashboardWaitingCard">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
          <h2 class="text-md font-semibold text-blue-darker tracking-tight">Menunggu Persetujuan</h2>
          <!-- <p class="text-xs text-gray-500 mt-1">Hubungi pelanggan, catat keputusan yang diberikan.</p> -->
        </div>
        <div class="flex items-center gap-2">
          <span class="rounded-full bg-warning-light px-3 py-1 text-xs font-semibold text-warning-dark">
            <?= count($daftar_menunggu_persetujuan) ?> permintaan
          </span>
        </div>
      </div>
      <div class="overflow-x-auto overflow-y-auto min-h-0 flex-1 dashboard-scroll" tabindex="0" aria-label="Daftar servis menunggu persetujuan, dapat digulir">

        <table class="w-full min-w-[950px] table-fixed text-left border-collapse text-xs" id="tableMenungguPersetujuan">
          <thead class="sticky top-0 z-10 bg-white-light">
            <tr class="border-b border-gray-100 text-white-darker font-normal text-[13px]">
              <th class="py-4 px-3 w-[8%]">Nota</th>
              <th class="py-4 px-3 w-[13%]">Pelanggan</th>
              <th class="py-4 px-3 w-[22%]">Perangkat</th>
              <th class="py-4 px-3 w-[13%]">Teknisi</th>
              <th class="py-4 px-3 w-[20%]">Keterangan</th>
              <th class="py-4 px-3 w-[8%] text-center">Hubungi</th>
              <th class="py-4 px-3 w-[16%] text-center">Keputusan</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 text-blue-darker font-medium">
            <?php foreach ($daftar_menunggu_persetujuan as $item): ?>
              <?php
              $urlDetail = $urlDetailDashboard((string) $item['nota']);

              $pesanWA = 'Halo ' . $item['pelanggan']
                . ', kami dari Singhasari Computer menghubungi mengenai servis nota ' . $item['nota']
                . ' (' . $item['perangkat'] . '). Teknisi membutuhkan persetujuan Anda: '
                . $item['keterangan'] . '. Apakah Anda menyetujui?';

              $urlWA = $tautanWhatsApp(
                (string) $item['whatsapp'],
                $pesanWA
              );
              ?>

              <tr class="hover:bg-gray-50/50 transition-colors" data-waiting-row>

                <!-- NOTA: SATU-SATUNYA LINK KE DETAIL -->
                <td class="py-4 px-3 font-bold text-blue-darker">
                  <a
                    href="<?= e($urlDetail) ?>"
                    class="hover:text-tosca-dark hover:underline transition-colors"
                    title="Lihat detail servis <?= e($item['nota']) ?>">
                    <?= e($item['nota']) ?>
                  </a>
                </td>

                <!-- PELANGGAN -->
                <td class="py-4 px-3">
                  <span
                    class="block truncate"
                    title="<?= e($item['pelanggan']) ?>">
                    <?= e($item['pelanggan']) ?>
                  </span>
                </td>

                <!-- PERANGKAT -->
                <td class="py-4 px-3 text-gray-500">
                  <span
                    class="block truncate"
                    title="<?= e($item['perangkat']) ?>">
                    <?= e($item['perangkat']) ?>
                  </span>
                </td>

                <!-- TEKNISI -->
                <td class="py-4 px-3 text-gray-500">
                  <span
                    class="block truncate"
                    title="<?= e($item['teknisi']) ?>">
                    <?= e($item['teknisi']) ?>
                  </span>
                </td>

                <!-- KETERANGAN -->
                <td class="py-4 px-3 text-gray-500">
                  <span
                    class="block truncate"
                    title="<?= e((string) $item['keterangan']) ?>">
                    <?= e((string) $item['keterangan']) ?>
                  </span>
                </td>

                <!-- HUBUNGI: IKON WHATSAPP BULAT -->
                <td class="py-4 px-3 text-center">
                  <?php if ($urlWA !== ''): ?>
                    <a
                      href="<?= e($urlWA) ?>"
                      target="_blank"
                      rel="noopener noreferrer"
                      onclick="event.stopPropagation()"
                      aria-label="Hubungi <?= e($item['pelanggan']) ?> via WhatsApp"
                      title="Hubungi via WhatsApp"
                      class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-tosca text-white-light hover:bg-tosca-hover transition-colors">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="1.6em"
                        height="1.6em"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        aria-hidden="true">
                        <path d="M20.52 3.48A11.82 11.82 0 0 0 12.08 0C5.49 0 .13 5.36.13 11.95c0 2.1.55 4.16 1.59 5.99L0 24l6.25-1.64a11.9 11.9 0 0 0 5.82 1.49h.01C18.67 23.85 24 18.5 24 11.92a11.83 11.83 0 0 0-3.48-8.44Zm-8.44 18.35h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.75.98 1-3.65-.23-.37a9.86 9.86 0 0 1-1.51-5.25c0-5.45 4.44-9.89 9.9-9.89a9.8 9.8 0 0 1 6.98 2.89 9.83 9.83 0 0 1 2.9 6.99c0 5.45-4.44 9.89-9.88 9.89Z" />
                        <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.07.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35Z" />
                      </svg>
                    </a>
                  <?php else: ?>
                    <span
                      class="text-gray-500"
                      title="Nomor WhatsApp belum tersedia">-</span>
                  <?php endif; ?>
                </td>

                <!-- KEPUTUSAN -->
                <td class="py-4 px-3 text-center">
                  <form
                    method="POST"
                    action="<?= e($urlDashboard) ?>"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap"
                    onclick="event.stopPropagation()"
                    onsubmit="return confirm('Sudah menghubungi pelanggan dan yakin mencatat keputusan ini?');">
                    <input
                      type="hidden"
                      name="csrf_persetujuan"
                      value="<?= e($_SESSION['csrf_persetujuan']) ?>">
                    <input
                      type="hidden"
                      name="nota"
                      value="<?= e($item['nota']) ?>">

                    <button
                      type="submit"
                      name="keputusan"
                      value="Ya"
                      class="rounded-full bg-success-light px-3 py-2 text-success-dark font-semibold hover:bg-success-light-hover transition">
                      Setuju
                    </button>

                    <button
                      type="submit"
                      name="keputusan"
                      value="Tidak"
                      class="rounded-full bg-danger-light px-3 py-2 text-danger-dark font-semibold hover:bg-danger-light-hover transition">
                      Tolak
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>

            <?php if (empty($daftar_menunggu_persetujuan)): ?>
              <tr>
                <td colspan="7" class="py-8 px-3 text-center text-gray-500">
                  Tidak ada persetujuan pelanggan yang menunggu keputusan.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>

  <div class="flex flex-col gap-6 min-w-0" id="dashboardTopSummary">
    <!-- KARTU REKAP HASIL PEKERJAAN -->
    <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200">

      <h3 class="text-[16px] font-semibold text-blue-darker tracking-tight mb-5">Hasil Pekerjaan</h3>
      <div class="space-y-4 text-[14px]">
        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Selesai :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['selesai']) ?></span></div>
        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Cancel :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['cancel']) ?></span></div>
      </div>

      <div class="my-6 border-t border-gray-400"></div>

      <h3 class="text-[16px] font-semibold text-blue-darker tracking-tight mb-5">Jumlah Pekerjaan Teknisi</h3>
      <div class="space-y-4 text-[14px]">
        <?php foreach ($pekerjaan_teknisi as $nama_tek => $jml): ?>
          <div class="flex justify-between items-center text-gray-700"><span class="font-medium"><?= e($nama_tek) ?> :</span><span class="font-bold text-[#1C2434]"><?= e($jml) ?></span></div>
        <?php endforeach; ?>
      </div>

      <div class="my-6 border-t border-gray-400"></div>

      <h3 class="text-[16px] font-semibold text-blue-darker tracking-tight mb-5">Status</h3>
      <div class="space-y-4 text-[14px]">
        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Di Ambil User :</span><span class="font-bold text-blue-darker"><?= e($status_barang['di_ambil_user']) ?></span></div>
        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Barang Di Toko :</span><span class="font-bold text-blue-darker"><?= e($status_barang['barang_di_toko']) ?></span></div>
      </div>

    </div>

    <!-- LAPORAN BULANAN (Kode Duplikat dihapus) -->
    <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 shrink-0">
      <h3 class="text-[16px] font-semibold text-blue-darker tracking-tight">Laporan Periode</h3>
      <p class="text-[12px] text-gray-500 font-medium mb-6 mt-1"><?= e($bulan_laporan) ?></p>

      <div class="space-y-6">
        <!-- Progress Selesai -->
        <div>
          <div class="flex justify-between items-center text-sm font-semibold mb-2">
            <span class="text-gray-700">Selesai</span>
            <span class="text-blue-darker font-bold"><?= e($selesai_text) ?></span>
          </div>
          <div class="w-full h-3 bg-[#F4F5F7] rounded-full overflow-hidden">
            <div class="h-full bg-tosca rounded-full transition-all duration-500" style="width: <?= $persen_selesai ?>%;"></div>
          </div>
        </div>

        <!-- Progress Sparepart -->
        <div>
          <div class="flex justify-between items-center text-sm font-semibold mb-2">
            <span class="text-gray-700">Sparepart</span>
            <span class="text-blue-darker font-bold"><?= e($total_sparepart) ?> item</span>
          </div>
          <div class="w-full h-3 bg-[#F4F5F7] rounded-full overflow-hidden">
            <div class="h-full bg-orange rounded-full transition-all duration-500" style="width: <?= $persen_sparepart ?>%;"></div>
          </div>
        </div>
      </div>
    </div>


  </div>
</div>

<!-- TABEL SERVIS -->
<div class="bg-white-light rounded-3xl p-6 border border-gray-200 mt-6 min-w-0" id="servis-selesai">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h2 class="text-md font-semibold text-blue-darker tracking-tight">Servis Selesai</h2>
    <form method="GET" action="dashboard.php#servis-selesai" class="relative w-full sm:w-60">
      <?php foreach ($parameterRentang as $key => $value): ?>
        <input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>">
      <?php endforeach; ?>
      <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </span>
      <input type="text" id="cariServisSelesai" name="selesai_q" value="<?= e($selesai_cari) ?>" placeholder="Cari Pelanggan" class="w-full pl-10 pr-4 py-2 bg-white-light border border-gray-400 rounded-full text-sm text-gray-600 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-600 transition">
      <button type="submit" class="sr-only">Cari pelanggan</button>
    </form>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse text-xs">
      <thead>
        <tr class="border-b border-gray-100 text-white-darker font-normal text-[13px]">
          <th class="py-4 px-3">Nota</th>
          <th class="py-4 px-3">Pelanggan</th>
          <th class="py-4 px-3">Perangkat</th>
          <th class="py-4 px-3">Teknisi</th>
          <th class="py-4 px-3 text-center">Status</th>
          <th class="py-4 px-3 text-center">Layanan</th>
          <th class="py-4 px-3 text-center">Status Pembayaran</th>
          <th class="py-4 px-3 text-center">Status Pengambilan</th>
          <th class="py-4 px-3 text-center">Hubungi</th>
        </tr>
      </thead>
      <tbody id="tableBody" class="divide-y divide-gray-200 text-blue-darker font-medium">
        <?php foreach ($daftar_servis_selesai as $item): ?>
          <?php
          $urlDetail = $urlDetailDashboard((string) $item['nota']);
          $urlWA = $tautanWhatsApp(
            (string) $item['whatsapp'],
            'Halo ' . $item['pelanggan'] . ', kami dari SIPASTI menghubungi terkait servis nota ' . $item['nota'] . '.'
          );
          ?>
          <tr class="hover:bg-gray-50/50 transition-colors" data-detail-url="<?= e($urlDetail) ?>" tabindex="0" title="Klik untuk melihat detail servis">
            <td class="py-4 px-3 font-bold text-blue-darker"><a href="<?= e($urlDetail) ?>"><?= e($item['nota']) ?></a></td>
            <td class="py-4 px-3"><?= e($item['pelanggan']) ?></td>
            <td class="py-4 px-3 text-gray-500"><?= e($item['perangkat']) ?></td>
            <td class="py-4 px-3 text-gray-500"><?= e($item['teknisi']) ?></td>
            <td class="py-4 px-3 text-center">
              <!-- Badge Selesai warna pastel sesuai desain -->
              <span><?= badgeStatus((string) $item['status']) ?></span>
            </td>
            <td class="py-4 px-3 text-center">
              <?= badgeStatus((string) ($item['layanan'] ?: 'Reguler')) ?>
            </td>
            <td class="py-4 px-3 text-center">
              <?= badgeStatus((string) $item['status_pembayaran']) ?>
            </td>
            <td class="py-4 px-3 text-center">
              <?php $sudahDiambil = strtolower(trim((string) $item['status_pengambilan'])) === 'sudah diambil'; ?>
              <span class="inline-flex items-center justify-center min-w-28 px-4 py-2 rounded-full whitespace-nowrap text-xs font-semibold <?= $sudahDiambil ? 'bg-success-light text-success-dark' : 'bg-warning-light text-warning-dark' ?>"><?= e($item['status_pengambilan']) ?></span>
            </td>
            <td class="py-4 px-3 text-center">
              <?php if ($urlWA !== ''): ?>
                <a href="<?= e($urlWA) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-tosca text-white-light hover:bg-tosca-hover transition-colors" aria-label="Hubungi <?= e($item['pelanggan']) ?> via WhatsApp">
                  <svg xmlns="http://www.w3.org/2000/svg" width="1.6em" height="1.6em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <g fill="none">
                      <g clip-path="url(#SVGXv8lpc2Y)">
                        <path fill="currentColor" fill-rule="evenodd" d="M17.415 14.382c-.298-.149-1.759-.867-2.031-.967s-.47-.148-.669.15c-.198.297-.767.966-.94 1.164c-.174.199-.347.223-.644.075c-.297-.15-1.255-.463-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059c-.173-.297-.019-.458.13-.606c.134-.133.297-.347.446-.52s.198-.298.297-.497c.1-.198.05-.371-.025-.52c-.074-.149-.668-1.612-.916-2.207c-.241-.579-.486-.5-.668-.51c-.174-.008-.372-.01-.57-.01s-.52.074-.792.372c-.273.297-1.04 1.016-1.04 2.479c0 1.462 1.064 2.875 1.213 3.074s2.095 3.2 5.076 4.487c.71.306 1.263.489 1.694.625c.712.227 1.36.195 1.872.118c.57-.085 1.758-.719 2.006-1.413s.247-1.289.173-1.413s-.272-.198-.57-.347m-5.422 7.403h-.004a9.87 9.87 0 0 1-5.032-1.378l-.36-.214l-3.742.982l.999-3.648l-.235-.374a9.86 9.86 0 0 1-1.511-5.26c.002-5.45 4.436-9.884 9.889-9.884a9.8 9.8 0 0 1 6.988 2.899a9.82 9.82 0 0 1 2.892 6.992c-.002 5.45-4.436 9.885-9.884 9.885m8.412-18.297A11.82 11.82 0 0 0 11.992 0C5.438 0 .102 5.335.1 11.892a11.86 11.86 0 0 0 1.587 5.945L0 24l6.304-1.654a11.9 11.9 0 0 0 5.684 1.448h.005c6.554 0 11.89-5.335 11.892-11.893a11.82 11.82 0 0 0-3.48-8.413" clip-rule="evenodd" />
                      </g>
                      <defs>
                        <clipPath id="SVGXv8lpc2Y">
                          <path fill="#fff" d="M0 0h24v24H0z" />
                        </clipPath>
                      </defs>
                    </g>
                  </svg>
                </a>
              <?php else: ?>
                <span class="text-gray-500" title="Nomor WhatsApp tidak tersedia">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($daftar_servis_selesai)): ?>
          <tr>
            <td colspan="9" class="py-8 px-3 text-center text-gray-500"><?= $selesai_cari !== '' ? 'Tidak ada servis selesai yang sesuai pencarian.' : 'Tidak ada servis selesai yang masih menunggu finalisasi.' ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="mt-5 pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
    <p>Menampilkan <?= $selesai_awal ?>–<?= $selesai_akhir ?> dari <?= $selesai_total ?> servis selesai</p>
    <?php if ($selesai_jumlah_halaman > 1): ?>
      <nav class="flex items-center gap-1.5" aria-label="Halaman servis selesai">
        <?php if ($selesai_halaman > 1): ?>
          <a href="<?= e($urlHalamanSelesai($selesai_halaman - 1)) ?>" class="px-3 py-2 rounded-full border border-gray-200 text-blue-darker hover:bg-white-hover transition" aria-label="Halaman sebelumnya">‹</a>
        <?php else: ?>
          <span class="px-3 py-2 rounded-full border border-gray-200 text-gray-400" aria-disabled="true">‹</span>
        <?php endif; ?>
        <?php
        $awalNomorHalaman = max(1, $selesai_halaman - 2);
        $akhirNomorHalaman = min($selesai_jumlah_halaman, $selesai_halaman + 2);
        if ($akhirNomorHalaman - $awalNomorHalaman < 4) {
          $awalNomorHalaman = max(1, $akhirNomorHalaman - 4);
          $akhirNomorHalaman = min($selesai_jumlah_halaman, $awalNomorHalaman + 4);
        }
        ?>
        <?php for ($halaman = $awalNomorHalaman; $halaman <= $akhirNomorHalaman; $halaman++): ?>
          <?php if ($halaman === $selesai_halaman): ?>
            <span class="inline-flex w-9 h-9 items-center justify-center rounded-full bg-orange text-white-light font-semibold" aria-current="page"><?= $halaman ?></span>
          <?php else: ?>
            <a href="<?= e($urlHalamanSelesai($halaman)) ?>" class="inline-flex w-9 h-9 items-center justify-center rounded-full bg-white-light text-blue-darker hover:bg-white-hover transition"><?= $halaman ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($selesai_halaman < $selesai_jumlah_halaman): ?>
          <a href="<?= e($urlHalamanSelesai($selesai_halaman + 1)) ?>" class="px-3 py-2 rounded-full border border-gray-200 text-blue-darker hover:bg-white-hover transition" aria-label="Halaman berikutnya">›</a>
        <?php else: ?>
          <span class="px-3 py-2 rounded-full border border-gray-200 text-gray-400" aria-disabled="true">›</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</div>


<!-- Interaksi dashboard: navigasi baris, pencarian lokal, dan rentang tanggal. -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const rentangAktif = <?= json_encode($rentang, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const kelasAktif = 'px-6 py-2.5 rounded-full bg-tosca-active text-white-light text-xs font-semibold transition';
    const kelasBiasa = 'px-6 py-2.5 rounded-full bg-white-light text-gray-600 border border-gray-200 text-xs font-medium hover:bg-gray-50 transition';
    document.querySelectorAll('#filterWaktuGroup button[data-range]').forEach(button => {
      button.className = button.dataset.range === rentangAktif ? kelasAktif : kelasBiasa;
      button.addEventListener('click', () => {
        if (button.dataset.range === 'custom') {
          const form = document.getElementById('formRentangCustom');
          if (!form) return;
          form.hidden = !form.hidden;
          button.setAttribute('aria-expanded', String(!form.hidden));
          if (!form.hidden) form.querySelector('[name="start_date"]').focus();
          return;
        }
        const url = new URL(window.location.href);
        url.hash = '';
        url.searchParams.set('range', button.dataset.range);
        ['start_date', 'end_date', 'selesai_page'].forEach(key => url.searchParams.delete(key));
        window.location.assign(url.toString());
      });
    });

    const formCustom = document.getElementById('formRentangCustom');
    if (formCustom) {
      const chipCustom = document.querySelector('#filterWaktuGroup button[data-range="custom"]');
      const tutupCustom = (fokus = false) => {
        formCustom.hidden = true;
        chipCustom.setAttribute('aria-expanded', 'false');
        if (fokus) chipCustom.focus();
      };
      document.addEventListener('click', event => {
        if (!formCustom.hidden && !event.target.closest('#customRangeWrap')) tutupCustom();
      });
      document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !formCustom.hidden) tutupCustom(true);
      });
      const mulai = formCustom.elements.start_date;
      const sampai = formCustom.elements.end_date;
      const validasiTanggal = () => {
        sampai.min = mulai.value;
        sampai.setCustomValidity(mulai.value && sampai.value && sampai.value < mulai.value ? 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.' : '');
      };
      mulai.addEventListener('input', validasiTanggal);
      sampai.addEventListener('input', validasiTanggal);
      validasiTanggal();
    }

    document.querySelectorAll('tr[data-detail-url]').forEach(row => {
      row.addEventListener('click', event => {
        if (event.target.closest('a, button, input, form, select, textarea')) return;
        window.location.assign(row.dataset.detailUrl);
      });
      row.addEventListener('keydown', event => {
        if (event.target !== row || !['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        window.location.assign(row.dataset.detailUrl);
      });
    });

    const cariHeader = document.getElementById('searchNotaInput');
    const barisPersetujuan = [...document.querySelectorAll('#tableMenungguPersetujuan tr[data-waiting-row]')];

    if (cariHeader) {
      cariHeader.addEventListener('input', () => {
        const kata = cariHeader.value.toLocaleLowerCase('id-ID').trim();
        barisPersetujuan.forEach(row => {
          row.hidden = !row.textContent.toLocaleLowerCase('id-ID').includes(kata);
        });
      });
      cariHeader.closest('form')?.addEventListener('submit', event => event.preventDefault());
    }
  });
</script>

<!-- JS Data Passing -->
<script>
  window.grafikServisData = <?= json_encode($data_grafik_servis, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="../assets/js/dashboard_admin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Hanya mengubah label sumbu X grafik servis. Tooltip tetap memakai tanggal lengkap.
  if (typeof Chart !== 'undefined') {
    Chart.register({
      id: 'sipastiCompactServiceDates',
      beforeInit(chart) {
        if (chart.canvas.id !== 'servisChart') return;
        const ticks = chart.options.scales.x.ticks;
        ticks.autoSkip = true;
        ticks.maxTicksLimit = 7;
        ticks.minRotation = 0;
        ticks.maxRotation = 0;
        ticks.callback = function(value) {
          return window.grafikServisData?.[value]?.label_singkat ??
            this.getLabelForValue(value);
        };
      }
    });
  }
</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>