<?php
// ==============================================================================
// 1. INISIALISASI SESSION, HELPER, DAN KEAMANAN
// ==============================================================================
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../includes/helpers.php';

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
// 2. DATA DUMMY / DEMO
// ==============================================================================
$tanggal_hari_ini = "Sabtu, 3 Oktober 2026";

$stat_selesai = ['total' => 82];
$stat_servis_masuk = ['total' => 120];
$stat_dikerjakan = ['total' => 12];
$stat_omzet = ['total' => 'Rp 8,9 jt'];

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
    ['nama' => 'Ilham', 'unit' => 8],
    ['nama' => 'Bagas', 'unit' => 6],
    ['nama' => 'Dimas', 'unit' => 3],
];

// --- DATA LAPORAN BULANAN (DINAMIS) ---
$bulan_laporan = "Oktober 2026";

// 1. Selesai (Otomatis samakan dengan metrik di atas)
$selesai_text = $stat_selesai['total'] . ' / ' . $stat_servis_masuk['total'];
$persen_selesai = ($stat_servis_masuk['total'] > 0) 
    ? min(round(($stat_selesai['total'] / $stat_servis_masuk['total']) * 100), 100) 
    : 0;

// 2. Sparepart (Merah jika >= 50)
$total_sparepart = 50; // Coba ubah ke 50 untuk tes warna merah
$max_sparepart = 50;   // Batas maksimal sparepart
$persen_sparepart = min(round(($total_sparepart / $max_sparepart) * 100), 100);
// Menggunakan warna semantic dari input.css: danger (#C2402A) atau warning (#F9C25C)
$warna_sparepart_class = ($total_sparepart >= 50) ? 'bg-danger' : 'bg-warning';

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
<?php
$page_title = 'Halo, ' . htmlspecialchars((string)($_SESSION['user']['nama'] ?? 'Admin'), ENT_QUOTES, 'UTF-8');
$active_menu = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="flex items-center gap-2.5 mb-6" id="filterWaktuGroup">
      <button type="button" class="filter-btn px-5 py-1.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer">Hari ini</button>
      <button type="button" class="filter-btn px-5 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer">7 Hari</button>
      <button type="button" class="filter-btn px-5 py-1.5 rounded-full bg-white-light text-white-darker border border-white-active text-xs font-medium shadow-2xs hover:bg-white-light-hover transition cursor-pointer">30 Hari</button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start">
      <div class="xl:col-span-3 space-y-6">
        
        <!-- 4 Metrik -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-orange text-white-light rounded-2xl p-6 shadow-sm flex flex-col justify-center">
            <p class="text-xs font-medium text-white-light/90">Selesai</p>
            <h3 class="text-4xl font-extrabold mt-1 tracking-tight"><?= e($stat_selesai['total']) ?></h3>
          </div>
          <div class="bg-tosca-dark text-white-light rounded-2xl p-6 shadow-sm flex flex-col justify-center">
            <p class="text-xs font-medium text-tosca-light">Servis Masuk</p>
            <h3 class="text-4xl font-extrabold mt-1 tracking-tight"><?= e($stat_servis_masuk['total']) ?></h3>
          </div>
          <div class="bg-tosca-dark text-white-light rounded-2xl p-6 shadow-sm flex flex-col justify-center">
            <p class="text-xs font-medium text-tosca-light">Dikerjakan</p>
            <h3 class="text-4xl font-extrabold mt-1 tracking-tight"><?= e($stat_dikerjakan['total']) ?></h3>
          </div>
          <div class="bg-tosca-dark text-white-light rounded-2xl p-6 shadow-sm flex flex-col justify-center">
            <p class="text-xs font-medium text-tosca-light">Omzet</p>
            <h3 class="text-3xl font-extrabold mt-1 tracking-tight"><?= e($stat_omzet['total']) ?></h3>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
          
          <!-- GRAFIK SERVIS (CHART.JS) -->
          <div class="lg:col-span-3 bg-white-light rounded-2xl p-6 shadow-sm border border-white-hover/60 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
              <h2 class="text-sm font-bold text-blue-darker tracking-tight">Servis Bulan Ini</h2>
              <div class="flex items-center gap-4 text-xs font-medium text-white-dark-hover">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange"></span><span>Masuk</span></div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-tosca-dark"></span><span>Selesai</span></div>
              </div>
            </div>
            <div class="relative w-full h-48 mt-2">
              <canvas id="servisChart"></canvas>
            </div>
          </div>

          <!-- BEBAN TEKNISI -->
          <div class="lg:col-span-2 bg-white-light rounded-2xl p-6 shadow-sm border border-white-hover/60 flex flex-col justify-between">
            <h2 class="text-sm font-bold text-blue-darker tracking-tight mb-4">Beban Teknisi</h2>
            <div class="space-y-4">
              <?php foreach ($beban_teknisi as $bt): 
                $persen = min(($bt['unit'] / $max_beban_teknisi) * 100, 100);
                
                // Set kelas warna Tailwind dari input.css
                $warna_bar_class = 'bg-success'; // Hijau aman (#1F7A45)
                if ($bt['unit'] >= 8) {
                    $warna_bar_class = 'bg-danger'; // Merah penuh (#C2402A)
                } elseif ($bt['unit'] >= 5) {
                    $warna_bar_class = 'bg-warning'; // Oranye mendekati penuh (#F26B21)
                } elseif ($bt['unit'] >= 3) {
                    $warna_bar_class = 'bg-success'; // Kuning sedang (#F9C25C)
                }
              ?>
                <div>
                  <div class="flex justify-between items-center text-xs font-semibold mb-1.5">
                    <span class="text-blue-darker"><?= e($bt['nama']) ?></span>
                    <span class="text-white-dark-hover font-medium"><?= e($bt['unit']) ?> unit</span>
                  </div>
                  <div class="w-full h-3 bg-white-hover rounded-full overflow-hidden">
                    <div class="h-full <?= $warna_bar_class ?> rounded-full transition-all duration-500" 
                        style="width: <?= $persen ?>%;"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="h-2"></div>
          </div>
        </div>

        <!-- TABEL SERVIS -->
        <div class="bg-white-light rounded-2xl p-6 shadow-sm border border-white-hover/60">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <h2 class="text-sm font-bold text-blue-darker tracking-tight">Servis Selesai</h2>
            <div class="relative w-full sm:w-60">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-white-dark-hover">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              </span>
              <input type="text" id="searchPelanggan" placeholder="Cari Pelanggan" class="w-full pl-9 pr-3 py-1 bg-white-light border border-white-active rounded-full text-xs text-white-darker placeholder-white-dark-hover focus:outline-none focus:ring-1 focus:ring-tosca-dark transition">
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="border-b border-white-hover text-white-dark-hover font-semibold text-[11px]">
                  <th class="py-3 px-3">Nota</th>
                  <th class="py-3 px-3">Pelanggan</th>
                  <th class="py-3 px-3">Perangkat</th>
                  <th class="py-3 px-3">Teknisi</th>
                  <th class="py-3 px-3 text-center">Status</th>
                  <th class="py-3 px-3 text-center">Layanan</th>
                  <th class="py-3 px-3 text-center">Status Pembayaran</th>
                  <th class="py-3 px-3 text-center">Hubungi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white-hover/50 text-white-darker font-medium" id="tableBodyServis">
                <?php foreach ($daftar_servis_selesai as $item): ?>
                  <tr class="hover:bg-warning-light/40 transition-colors">
                    <td class="py-3 px-3 font-bold text-blue-darker"><?= e($item['nota']) ?></td>
                    <td class="py-3 px-3"><?= e($item['pelanggan']) ?></td>
                    <td class="py-3 px-3 text-white-dark-active"><?= e($item['perangkat']) ?></td>
                    <td class="py-3 px-3"><?= e($item['teknisi']) ?></td>
                    <td class="py-3 px-3 text-center"><span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-semibold bg-success-light text-success-dark"><?= e($item['status']) ?></span></td>
                    <td class="py-3 px-3 text-center">
                      <?php if ($item['layanan'] === 'Express'): ?>
                        <span class="font-bold text-danger text-xs">Express</span>
                      <?php else: ?>
                        <span class="font-semibold text-blue-darker text-xs">Reguler</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <?php if ($item['status_pembayaran'] === 'Lunas'): ?>
                        <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-semibold bg-success-light text-success-dark">Lunas</span>
                      <?php else: ?>
                        <span class="inline-block px-3 py-0.5 rounded-full text-[11px] font-semibold bg-blue-light text-blue-hover">Belum Bayar</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-3 text-center">
                      <a href="https://wa.me/<?= e($item['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" title="Hubungi Pelanggan via WhatsApp" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-success text-white-light hover:bg-success-hover shadow-xs hover:shadow-md transition-all">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.076-2.023-.473-1.613-.665-2.651-2.3-2.731-2.408-.079-.109-.648-.864-.648-1.65 0-.786.411-1.173.556-1.332.144-.159.317-.199.423-.199.105 0 .211.002.304.007.098.005.231-.037.36.275.144.348.491 1.2.535 1.288.044.088.073.19.014.305-.059.115-.088.19-.176.293-.088.104-.185.232-.265.311-.088.088-.18.184-.078.36.103.175.457.755.98 1.222.673.602 1.242.788 1.417.876.175.088.278.074.382-.044.103-.118.441-.515.558-.692.118-.176.236-.147.397-.088.162.059 1.026.484 1.202.572.176.088.293.132.337.206.044.074.044.428-.1 1.833z"/></svg>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- KOLOM KANAN -->
      <div class="space-y-6">
        <div class="bg-white-light rounded-2xl p-6 shadow-sm border border-white-hover/60">
          <h3 class="text-sm font-bold text-blue-darker tracking-tight mb-3">Hasil Pekerjaan</h3>
          <div class="space-y-2 text-xs">
            <div class="flex justify-between items-center text-white-darker"><span class="font-medium">Selesai :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['selesai']) ?></span></div>
            <div class="flex justify-between items-center text-white-darker"><span class="font-medium">Cancel :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['cancel']) ?></span></div>
            <div class="flex justify-between items-center text-white-darker"><span class="font-medium">Can't Repair :</span><span class="font-bold text-blue-darker"><?= e($hasil_pekerjaan['cant_repair']) ?></span></div>
          </div>
          <div class="my-5 border-t border-white-hover"></div>
          <h3 class="text-sm font-bold text-blue-darker tracking-tight mb-3">Jumlah Pekerjaan Teknisi</h3>
          <div class="space-y-2 text-xs">
            <?php foreach ($pekerjaan_teknisi as $nama_tek => $jml): ?>
              <div class="flex justify-between items-center text-white-darker"><span class="font-medium"><?= e($nama_tek) ?> :</span><span class="font-bold text-blue-darker"><?= e($jml) ?></span></div>
            <?php endforeach; ?>
          </div>
          <div class="my-5 border-t border-white-hover"></div>
          <h3 class="text-sm font-bold text-blue-darker tracking-tight mb-3">Status</h3>
          <div class="space-y-2 text-xs">
            <div class="flex justify-between items-center text-white-darker"><span class="font-medium">Di Ambil User :</span><span class="font-bold text-blue-darker"><?= e($status_barang['di_ambil_user']) ?></span></div>
            <div class="flex justify-between items-center text-white-darker"><span class="font-medium">Barang Di Toko :</span><span class="font-bold text-blue-darker"><?= e($status_barang['barang_di_toko']) ?></span></div>
          </div>
        </div>

        <!-- LAPORAN BULANAN (DINAMIS & OTOMATIS) -->
        <div class="bg-white-light rounded-2xl p-6 shadow-sm border border-white-hover/60">
          <h3 class="text-sm font-bold text-blue-darker tracking-tight">Laporan Bulanan</h3>
          <p class="text-[11px] text-white-dark-hover font-medium mb-4"><?= e($bulan_laporan) ?></p>
          
          <div class="space-y-4">
            <!-- Progress Selesai (Otomatis mengambil nilai metrik atas) -->
            <div>
              <div class="flex justify-between items-center text-xs font-semibold mb-1.5">
                <span class="text-white-dark-active">Selesai</span>
                <span class="text-blue-darker font-bold"><?= e($selesai_text) ?></span>
              </div>
              <div class="w-full h-2.5 bg-orange-light rounded-full overflow-hidden">
                <div class="h-full bg-orange rounded-full transition-all duration-500" 
                    style="width: <?= $persen_selesai ?>%;"></div>
              </div>
            </div>

            <!-- Progress Sparepart (Sistem warna otomatis: Merah jika >= 50) -->
            <div>
              <div class="flex justify-between items-center text-xs font-semibold mb-1.5">
                <span class="text-white-dark-active">Sparepart</span>
                <span class="text-blue-darker font-bold"><?= e($total_sparepart) ?> item</span>
              </div>
              <div class="w-full h-2.5 bg-warning-light rounded-full overflow-hidden">
                <div class="h-full <?= $warna_sparepart_class ?> rounded-full transition-all duration-500" 
                    style="width: <?= $persen_sparepart ?>%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div> <!-- Penutup <div class="space-y-6"> dari header.php -->
</main> <!-- Penutup <main> dari header.php -->

<!-- JS Data Passing -->
<script>
  window.grafikServisData = <?= json_encode($data_grafik_servis) ?>;
</script>
<script src="../assets/js/dashboard_admin.js"></script>

</body>
</html>
