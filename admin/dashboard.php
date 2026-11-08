<?php

// Panggil header pelanggan
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
$search_placeholder = 'Cari antrean menunggu persetujuan...';
// ==============================================================================
// 2. DATA DUMMY / DEMO (Disesuaikan dengan desain UI)
// ==============================================================================
$tanggal_hari_ini = "Sabtu, 3 Oktober 2026";

// Update metrik biar mirip desain
$stat_selesai = ['total' => 49, 'label_bawah' => 'Bulan lalu: 45', 'badge' => '+9%'];
$stat_servis_masuk = ['total' => 120, 'label_bawah' => 'Bulan lalu: 107', 'badge' => '+12%'];
$stat_dikerjakan = ['total' => 12, 'label_bawah' => 'Menunggu: 2', 'badge' => '3 Teknisi'];
$stat_omzet = ['total' => 'Rp 8,9 jt', 'label_bawah' => 'Bulan lalu: 7,8 jt', 'badge' => '+14%'];

// --- DATA GRAFIK SERVIS ---
$data_grafik_servis = [
    ['minggu' => 'Mg 1', 'masuk' => 10, 'selesai' => 9],
    ['minggu' => 'Mg 2', 'masuk' => 25, 'selesai' => 20],
    ['minggu' => 'Mg 3', 'masuk' => 80, 'selesai' => 50],
    ['minggu' => 'Mg 4', 'masuk' => 120, 'selesai' => 49],
];

// --- DATA BEBAN TEKNISI ---
$max_beban_teknisi = 10;
$beban_teknisi = [
    ['nama' => 'Ilham', 'unit' => 8, 'warna' => 'bg-orange'], // Orange
    ['nama' => 'Bagas', 'unit' => 5, 'warna' => 'bg-tosca'], // Kuning
    ['nama' => 'Dimas', 'unit' => 3, 'warna' => 'bg-tosca'], // Hijau
];

// --- DATA LAPORAN BULANAN ---
$bulan_laporan = "Oktober 2026";
$selesai_text = "49 / 120";
$persen_selesai = round((49 / 120) * 100);

$total_sparepart = 38; 
$max_sparepart = 50;   
$persen_sparepart = round(($total_sparepart / $max_sparepart) * 100);

// --- KOLOM KANAN ---
$hasil_pekerjaan = ['selesai' => 205, 'cancel' => 4, 'cant_repair' => 2];
$pekerjaan_teknisi = ['Ilham' => 52, 'Bagas' => 36, 'Dimas' => 21];
$status_barang = ['di_ambil_user' => 200, 'barang_di_toko' => 5];

$daftar_servis_selesai = [
    ['nota' => '#5145', 'pelanggan' => 'Sari', 'perangkat' => 'Laptop HP', 'teknisi' => 'Ilham', 'status' => 'Selesai', 'layanan' => 'Express', 'status_pembayaran' => 'Lunas', 'whatsapp' => '6281234567890'],
    ['nota' => '#5145', 'pelanggan' => 'Sari', 'perangkat' => 'Laptop HP', 'teknisi' => 'Ilham', 'status' => 'Selesai', 'layanan' => 'Express', 'status_pembayaran' => 'Lunas', 'whatsapp' => '6281234567890'],
    ['nota' => '#5145', 'pelanggan' => 'Sari', 'perangkat' => 'Laptop HP', 'teknisi' => 'Ilham', 'status' => 'Selesai', 'layanan' => 'Reguler', 'status_pembayaran' => 'Lunas', 'whatsapp' => '6281234567890'],
    ['nota' => '#5145', 'pelanggan' => 'Sari', 'perangkat' => 'Laptop HP', 'teknisi' => 'Ilham', 'status' => 'Selesai', 'layanan' => 'Reguler', 'status_pembayaran' => 'Belum Bayar', 'whatsapp' => '6281234567890'],
];
?>

  <!-- ================= KONTEN UTAMA ================= -->
    <div class="flex items-center gap-2.5 mb-6" id="filterWaktuGroup">
      <button type="button" class="px-6 py-2.5 rounded-full bg-tosca-active text-white-light text-xs font-semibold transition">Hari ini</button>
      <button type="button" class="px-6 py-2.5 rounded-full bg-white-light text-gray-600 border border-gray-200 text-xs font-medium hover:bg-gray-50 transition">7 Hari</button>
      <button type="button" class="px-6 py-2.5 rounded-full bg-white-light text-gray-600 border border-gray-200 text-xs font-medium hover:bg-gray-50 transition">30 Hari</button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start">
      <div class="xl:col-span-3 space-y-6">
        
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
          <div class="lg:col-span-3 bg-white-light rounded-3xl p-6 border border-gray-200 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
              <h2 class="text-md font-semibold text-blue-darker tracking-tight">Servis Bulan Ini</h2>
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
          <div class="lg:col-span-2 bg-white-light rounded-3xl p-6 border border-gray-200 flex flex-col justify-top">
            <h2 class="text-md font-semibold text-blue-darker tracking-tight mb-7">Beban Teknisi</h2>
            <div class="space-y-6">
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

        <!-- TABEL SERVIS -->
        <div class="bg-white-light rounded-3xl p-6 border border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h2 class="text-md font-semibold text-blue-darker tracking-tight">Servis Selesai</h2>
            <div class="relative w-full sm:w-60">
              <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              </span>
              <input type="text" id="searchPelanggan" placeholder="Cari Pelanggan" class="w-full pl-10 pr-4 py-2 bg-white-light border border-gray-400 rounded-full text-sm text-gray-600 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-600 transition">
            </div>
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
                  <th class="py-4 px-3 text-center">Hubungi</th>
                </tr>
              </thead>
              <tbody id="tableBody" class="divide-y divide-gray-200 text-blue-darker font-medium">
                <?php foreach ($daftar_servis_selesai as $item): ?>
                  <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="py-4 px-3 font-bold text-blue-darker"><?= e($item['nota']) ?></td>
                    <td class="py-4 px-3"><?= e($item['pelanggan']) ?></td>
                    <td class="py-4 px-3 text-gray-500"><?= e($item['perangkat']) ?></td>
                    <td class="py-4 px-3 text-gray-500"><?= e($item['teknisi']) ?></td>
                    <td class="py-4 px-3 text-center">
                      <!-- Badge Selesai warna pastel sesuai desain -->
                      <span><?= badgeStatus('selesai') ?></span>
                    </td>
                    <td class="py-4 px-3 text-center">
                    <?= badgeStatus('express') ?>
                    </td>
                    <td class="py-4 px-3 text-center">
                    <?= badgeStatus('lunas') ?>
                    </td>
                    <td class="py-4 px-3 text-center">
                      <a href="https://wa.me/<?= e($item['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-tosca text-white-light hover:bg-tosca-hover transition-colors">
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
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- KOLOM KANAN (Telah diperbaiki strukturnya) -->
      <div class="space-y-6">
        
        <!-- KARTU REKAP HASIL PEKERJAAN -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200">
          
          <h3 class="text-[16px] font-semibold text-blue-darker tracking-tight mb-5">Hasil Pekerjaan</h3>
          <div class="space-y-4 text-[14px]">
            <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Selesai :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['selesai']) ?></span></div>
            <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Cancel :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['cancel']) ?></span></div>
            <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Can't Repair :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['cant_repair']) ?></span></div>
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
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200">
          <h3 class="text-[16px] font-semibold text-blue-darker tracking-tight">Laporan Bulanan</h3>
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

<!-- JS Data Passing -->
<script>
  window.grafikServisData = <?= json_encode($data_grafik_servis) ?>;
</script>
<script src="../assets/js/dashboard_admin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>