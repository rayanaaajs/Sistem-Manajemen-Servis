<?php
require_once __DIR__ . '/../includes/auth.php';   
$page_title = 'Edit Sparepart';
$active_menu = 'sparepart';
// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

// ==============================================================================
// STATUS SPAREPART: OTOMATIS DARI STOK (ADMIN TIDAK BISA UBAH MANUAL)
// ==============================================================================
// Stok 0           => Habis
// Stok 1 - 10      => Menipis   (ubah BATAS_MENIPIS kalau mau batas lain)
// Stok di atas 10  => Aman
const BATAS_MENIPIS = 5;

function hitungStatusSparepart($stok) {
    $stok = (int) $stok;
    if ($stok <= 0) return 'Habis';
    if ($stok <= BATAS_MENIPIS) return 'Menipis';
    return 'Aman';
}

function getBadgeSparepartClass($status) {
    switch ($status) {
        case 'Aman':    return 'bg-success-light text-success-dark';
        case 'Menipis': return 'bg-warning-light text-warning-dark';
        case 'Habis':   return 'bg-danger-light text-danger-dark';
        default:        return 'bg-white-hover text-white-darker';
    }
}

// Data dummy sparepart yang lagi diedit
$sparepart = [
    'nama'  => 'RAM FR CUB',
    'stok'  => 60,
    'harga' => 'Rp 30.000.000',
];

$status_sparepart = hitungStatusSparepart($sparepart['stok']);
$badge_class      = getBadgeSparepartClass($status_sparepart);
?>

      <!-- FORM EDIT SPAREPART SESUAI DESAIN FIGMA (2 KOLOM: FORM DATA & RINGKASAN) -->
      <form action="sparepart.php" method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">

        <!-- ==========================================
             KARTU KIRI: DATA SPAREPART
             ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Sparepart</h2>

            <div class="space-y-5 lg:space-y-6 max-w-xl">
              <!-- Nama Sparepart -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama Sparepart</label>
                <input type="text"
                       name="nama"
                       placeholder="Masukkan Nama"
                       value="<?= htmlspecialchars($sparepart['nama']) ?>"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Stok (Border Navy / Blue-darker sesuai screenshot Figma) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Stok</label>
                <input type="text"
                       inputmode="numeric"
                       name="stok"
                       id="stokSparepartInput"
                       placeholder="Masukkan Stok"
                       value="<?= (int) $sparepart['stok'] ?>"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Harga -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Harga</label>
                <input type="text"
                       name="harga"
                       placeholder="Masukkan Harga Satuan"
                       value="<?= htmlspecialchars($sparepart['harga']) ?>"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Status (READ-ONLY, otomatis dari stok) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Status</label>
                <span id="statusSparepartBadge"
                      class="inline-block w-40 sm:w-48 px-4 py-3 rounded-2xl text-sm font-semibold text-center transition-all <?= $badge_class ?>">
                  <?= htmlspecialchars($status_sparepart) ?>
                </span>
                <p class="text-[11px] text-white-dark-hover mt-2 font-medium">
                  Status otomatis mengikuti stok dan tidak bisa diubah manual.
                </p>
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Simpan Perubahan -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a href="index.php"
               class="px-8 py-2.5 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-light text-sm font-semibold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </div>

        <!-- ==========================================
             KARTU KANAN: RINGKASAN SPAREPART
             ========================================== -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Ringkasan Sparepart</h2>

            <!-- Banner Nama Sparepart Oranye -->
            <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
              <p class="text-xs text-orange-dark font-medium mb-1">Nama</p>
              <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight"><?= htmlspecialchars($sparepart['nama']) ?></h3>
              <p class="text-[11px] text-orange-dark/80 mt-2 font-medium">Dibuat otomatis saat disimpan</p>
            </div>

            <!-- Daftar Informasi Ringkasan -->
            <div class="text-xs font-medium text-blue-darker">
              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Status</span>
                  <span id="ringkasanStatusSparepart"
                        class="inline-block w-28 py-2 text-center rounded-full text-xs font-semibold transition-all <?= $badge_class ?>">
                    <?= htmlspecialchars($status_sparepart) ?>
                  </span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Harga</span>
                <span class="font-bold text-blue-darker text-sm"><?= htmlspecialchars($sparepart['harga']) ?></span>
              </div>

              <div class="flex justify-between items-center py-3.5">
                <span class="text-blue-darker/70">Stok</span>
                <span id="ringkasanStokSparepart" class="font-bold text-blue-darker text-sm"><?= (int) $sparepart['stok'] ?></span>
              </div>
            </div>
          </div>

          <!-- Banner Biru Muda: Setelah disimpan -->
          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Setelah disimpan</h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Perubahan akan di simpan</li>
              <li>Dapat di edit lagi</li>
            </ul>
          </div>
        </div>

      </form>

<!-- ==============================================================================
     JS: STATUS OTOMATIS BERUBAH LIVE SAAT STOK DIKETIK
     (Tampilan doang. Pas disimpan, status tetap WAJIB dihitung ulang di server
      pakai hitungStatusSparepart(), jangan percaya data dari browser.)
============================================================================== -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const BATAS_MENIPIS = <?= BATAS_MENIPIS ?>;

    const stokInput    = document.getElementById('stokSparepartInput');
    const badgeForm    = document.getElementById('statusSparepartBadge');
    const badgeSummary = document.getElementById('ringkasanStatusSparepart');
    const stokSummary  = document.getElementById('ringkasanStokSparepart');

    const STYLE = {
      'Aman':    'bg-success-light text-success-dark',
      'Menipis': 'bg-warning-light text-warning-dark',
      'Habis':   'bg-danger-light text-danger-dark'
    };
    const ALL_STYLE_CLASSES = Object.values(STYLE).join(' ').split(' ');

    function hitungStatus(stok) {
      if (stok <= 0) return 'Habis';
      if (stok <= BATAS_MENIPIS) return 'Menipis';
      return 'Aman';
    }

    function applyStatus(el, status) {
      el.classList.remove(...ALL_STYLE_CLASSES);
      el.classList.add(...STYLE[status].split(' '));
      el.textContent = status;
    }

    function render() {
      // Hanya angka yang boleh masuk ke input stok
      stokInput.value = stokInput.value.replace(/\D/g, '');
      const stok = parseInt(stokInput.value, 10) || 0;
      const status = hitungStatus(stok);

      applyStatus(badgeForm, status);
      applyStatus(badgeSummary, status);
      stokSummary.textContent = stok;
    }

    stokInput.addEventListener('input', render);
  });
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>