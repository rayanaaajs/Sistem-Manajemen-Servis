<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (strtolower((string) ($_SESSION['role'] ?? '')) !== 'admin') {
  http_response_code(403);
  exit('Akses hanya untuk admin.');
}

if (empty($_SESSION['csrf_sparepart'])) {
  $_SESSION['csrf_sparepart'] = bin2hex(random_bytes(32));
}

$page_title = 'Edit Sparepart';
$active_menu = 'sparepart';

$idRaw = $_GET['id'] ?? null;
$idSparepart = is_scalar($idRaw)
  ? filter_var($idRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
  : false;

if ($idSparepart === false) {
  $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'ID sparepart tidak valid.'];
  header('Location: index.php');
  exit;
}

try {
  $stmt = $pdo->prepare("
        SELECT id_sparepart, nama_sparepart, stok_tersedia, harga_satuan
        FROM public.sparepart
        WHERE id_sparepart = :id
        LIMIT 1
    ");
  $stmt->bindValue(':id', (int) $idSparepart, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'Sparepart tidak ditemukan.'];
    header('Location: index.php');
    exit;
  }
} catch (PDOException $e) {
  error_log('sparepart/edit.php (load): ' . $e->getMessage());
  $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'Gagal memuat sparepart.'];
  header('Location: index.php');
  exit;
}

$pesan_error = '';
$nama = (string) $row['nama_sparepart'];
$stok = (int) $row['stok_tersedia'];
$hargaTersimpan = 'Rp ' . number_format((float) $row['harga_satuan'], 0, ',', '.');
$harga = $hargaTersimpan;


function validasiInputSparepart(array $post): array
{
  $nama = trim(is_string($post['nama'] ?? null) ? $post['nama'] : '');
  $stokRaw = trim(is_string($post['stok'] ?? null) ? $post['stok'] : '');
  $hargaRaw = trim(is_string($post['harga'] ?? null) ? $post['harga'] : '');

  if ($nama === '' || (function_exists('mb_strlen') ? mb_strlen($nama) : strlen($nama)) > 100) {
    throw new InvalidArgumentException('Nama sparepart wajib diisi (maksimal 100 karakter).');
  }

  if (!preg_match('/^\d{1,10}$/D', $stokRaw) || (float) $stokRaw > 2147483647) {
    throw new InvalidArgumentException('Stok harus berupa bilangan bulat dari 0 sampai 2147483647.');
  }

  // Terima 285000, 285.000, Rp 285.000, dan 285000,50.
  $hargaBersih = preg_replace('/^Rp\.?\s*/iu', '', $hargaRaw);
  $hargaBersih = preg_replace('/\s+/u', '', $hargaBersih ?? '');

  if (preg_match('/^\d{1,3}(?:\.\d{3})+(?:,\d{1,2})?$/D', $hargaBersih)) {
    $hargaBersih = str_replace('.', '', $hargaBersih);
    $hargaBersih = str_replace(',', '.', $hargaBersih);
  } elseif (preg_match('/^\d+(?:[,.]\d{1,2})?$/D', $hargaBersih)) {
    $hargaBersih = str_replace(',', '.', $hargaBersih);
  } else {
    throw new InvalidArgumentException('Harga tidak valid. Contoh: 285000 atau Rp 285.000.');
  }

  $bagianHarga = explode('.', $hargaBersih, 2);
  $angkaUtuh = ltrim($bagianHarga[0], '0');
  $angkaUtuh = $angkaUtuh === '' ? '0' : $angkaUtuh;
  if (strlen($angkaUtuh) > 10) {
    throw new InvalidArgumentException('Harga terlalu besar.');
  }
  $hargaNormal = $angkaUtuh . '.' . str_pad($bagianHarga[1] ?? '0', 2, '0');

  return [$nama, (int) $stokRaw, $hargaNormal];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = is_string($_POST['nama'] ?? null) ? $_POST['nama'] : '';
  $stok = is_string($_POST['stok'] ?? null) ? $_POST['stok'] : '';
  $harga = is_string($_POST['harga'] ?? null) ? $_POST['harga'] : '';

  try {
    $token = is_string($_POST['csrf_sparepart'] ?? null) ? $_POST['csrf_sparepart'] : '';
    if ($token === '' || !hash_equals((string) $_SESSION['csrf_sparepart'], $token)) {
      throw new InvalidArgumentException('Sesi formulir tidak valid. Muat ulang halaman.');
    }

    [$namaValid, $stokValid, $hargaValid] = validasiInputSparepart($_POST);

    $stmt = $pdo->prepare("
            UPDATE public.sparepart
            SET nama_sparepart = :nama,
                stok_tersedia = :stok,
                harga_satuan = :harga
            WHERE id_sparepart = :id
        ");
    $stmt->bindValue(':nama', $namaValid, PDO::PARAM_STR);
    $stmt->bindValue(':stok', $stokValid, PDO::PARAM_INT);
    $stmt->bindValue(':harga', $hargaValid, PDO::PARAM_STR);
    $stmt->bindValue(':id', (int) $idSparepart, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() < 1) {
      throw new RuntimeException('Sparepart tidak ditemukan atau sudah dihapus.');
    }

    $_SESSION['flash_sparepart'] = ['tipe' => 'success', 'pesan' => 'Sparepart berhasil diperbarui.'];
    header('Location: index.php');
    exit;
  } catch (InvalidArgumentException | RuntimeException $e) {
    $pesan_error = $e->getMessage();
  } catch (PDOException $e) {
    error_log('sparepart/edit.php (save): ' . $e->getMessage());
    $pesan_error = 'Gagal menyimpan sparepart. Periksa data dan coba lagi.';
  }
}

$batasMenipis = defined('BATAS_MENIPIS') ? max(0, (int) BATAS_MENIPIS) : 5;
$stokTampilan = is_numeric($stok) ? (int) $stok : 0;
$status_sparepart = $stokTampilan <= 0 ? 'Habis'
  : ($stokTampilan <= $batasMenipis ? 'Menipis' : 'Aman');

function getBadgeSparepartClass($status)
{
  switch ($status) {
    case 'Aman':
      return 'bg-success-light text-success-dark';
    case 'Menipis':
      return 'bg-warning-light text-warning-dark';
    case 'Habis':
      return 'bg-danger-light text-danger-dark';
    default:
      return 'bg-white-hover text-white-darker';
  }
}

$sparepart = [
  'nama' => $nama,
  'stok' => $stok,
  'harga' => $harga,
];
$badge_class = getBadgeSparepartClass($status_sparepart);

require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($pesan_error !== ''): ?>
  <div role="alert" class="mb-5 rounded-2xl border border-danger bg-danger-light px-5 py-4 text-sm font-medium text-danger-dark">
    <?= e($pesan_error) ?>
  </div>
<?php endif; ?>

<!-- FORM EDIT SPAREPART SESUAI DESAIN FIGMA (2 KOLOM: FORM DATA & RINGKASAN) -->
<form action="edit.php?id=<?= (int) $idSparepart ?>" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
  <input type="hidden" name="csrf_sparepart" value="<?= e($_SESSION['csrf_sparepart']) ?>">

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
            value="<?= e($sparepart['nama']) ?>"
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
            value="<?= e((string) $sparepart['stok']) ?>"
            class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
        </div>

        <!-- Harga -->
        <div>
          <label class="block text-sm font-bold text-blue-darker mb-2">Harga</label>
          <input type="text"
            name="harga"
            placeholder="Masukkan Harga Satuan"
            value="<?= e($sparepart['harga']) ?>"
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
  document.addEventListener("DOMContentLoaded", function() {
    const BATAS_MENIPIS = <?= (int) $batasMenipis ?>;

    const stokInput = document.getElementById('stokSparepartInput');
    const badgeForm = document.getElementById('statusSparepartBadge');
    const badgeSummary = document.getElementById('ringkasanStatusSparepart');
    const stokSummary = document.getElementById('ringkasanStokSparepart');

    const STYLE = {
      'Aman': 'bg-success-light text-success-dark',
      'Menipis': 'bg-warning-light text-warning-dark',
      'Habis': 'bg-danger-light text-danger-dark'
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