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

$page_title = 'Tambah Sparepart';
$active_menu = 'sparepart';

$pesan_error = '';
$formNama = '';
$formStok = '';
$formHarga = '';


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
  $formNama = is_string($_POST['nama'] ?? null) ? $_POST['nama'] : '';
  $formStok = is_string($_POST['stok'] ?? null) ? $_POST['stok'] : '';
  $formHarga = is_string($_POST['harga'] ?? null) ? $_POST['harga'] : '';

  try {
    $token = is_string($_POST['csrf_sparepart'] ?? null) ? $_POST['csrf_sparepart'] : '';
    if ($token === '' || !hash_equals((string) $_SESSION['csrf_sparepart'], $token)) {
      throw new InvalidArgumentException('Sesi formulir tidak valid. Muat ulang halaman.');
    }

    [$nama, $stok, $harga] = validasiInputSparepart($_POST);

    $stmt = $pdo->prepare("
            INSERT INTO public.sparepart (nama_sparepart, stok_tersedia, harga_satuan)
            VALUES (:nama, :stok, :harga)
        ");
    $stmt->bindValue(':nama', $nama, PDO::PARAM_STR);
    $stmt->bindValue(':stok', $stok, PDO::PARAM_INT);
    $stmt->bindValue(':harga', $harga, PDO::PARAM_STR);
    $stmt->execute();

    $_SESSION['flash_sparepart'] = ['tipe' => 'success', 'pesan' => 'Sparepart berhasil ditambahkan.'];
    header('Location: index.php');
    exit;
  } catch (InvalidArgumentException $e) {
    $pesan_error = $e->getMessage();
  } catch (PDOException $e) {
    error_log('sparepart/tambah.php: ' . $e->getMessage());
    $pesan_error = 'Gagal menambahkan sparepart. Periksa data dan coba lagi.';
  }
}

$batasMenipis = defined('BATAS_MENIPIS') ? max(0, (int) BATAS_MENIPIS) : 5;
$stokTampilan = ctype_digit($formStok) ? (int) $formStok : 0;
$statusPreview = $stokTampilan <= 0 ? 'Habis'
  : ($stokTampilan <= $batasMenipis ? 'Menipis' : 'Aman');

require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($pesan_error !== ''): ?>
  <div role="alert" class="mb-5 rounded-2xl border border-danger bg-danger-light px-5 py-4 text-sm font-medium text-danger-dark">
    <?= e($pesan_error) ?>
  </div>
<?php endif; ?>

<!-- FORM TAMBAH SPAREPART SESUAI DESAIN FIGMA -->
<div class="max-w-xl">
  <form action="tambah.php" method="POST" class="bg-white-light rounded-3xl p-7 lg:p-8 shadow-sm border border-white-hover/60">
    <input type="hidden" name="csrf_sparepart" value="<?= e($_SESSION['csrf_sparepart']) ?>">
    <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Data Sparepart</h2>

    <div class="space-y-5 lg:space-y-6">
      <!-- Nama Sparepart -->
      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Nama Sparepart</label>
        <input type="text"
          name="nama"
          value="<?= e($formNama) ?>"
          placeholder="Masukkan Nama"
          class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
      </div>

      <!-- Stok (Border Navy / Blue-darker sesuai screenshot Figma) -->
      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Stok</label>
        <input type="text"
          name="stok"
          id="stokSparepartInput"
          value="<?= e($formStok) ?>"
          inputmode="numeric"
          placeholder="Masukkan Stok"
          class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
      </div>

      <!-- Harga -->
      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Harga</label>
        <input type="text"
          name="harga"
          value="<?= e($formHarga) ?>"
          placeholder="Masukkan Harga Satuan"
          class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
      </div>

      <!-- Status -->
      <div>
        <label class="block text-sm font-bold text-blue-darker mb-2">Status</label>
        <div class="relative w-40 sm:w-48">
          <select name="status" id="statusSparepartPreview" disabled
            class="w-full px-4 py-3 rounded-2xl border border-white-active bg-success-light text-sm font-semibold text-success-dark text-center appearance-none focus:outline-none transition cursor-pointer">
            <option value="Aman" <?= $statusPreview === 'Aman' ? 'selected' : '' ?>>Aman</option>
            <option value="Menipis" <?= $statusPreview === 'Menipis' ? 'selected' : '' ?>>Menipis</option>
            <option value="Habis" <?= $statusPreview === 'Habis' ? 'selected' : '' ?>>Habis</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tombol Aksi Bawah: Batal & Tambah Sparepart -->
    <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
      <a href="index.php"
        class="px-8 py-2.5 rounded-full border border-blue-darker text-blue-darker hover:bg-white-hover text-sm font-bold transition text-center cursor-pointer">
        Batal
      </a>
      <button type="submit"
        class="px-8 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-bold transition cursor-pointer">
        Tambah Sparepart
      </button>
    </div>
  </form>
</div>


<script>
  document.addEventListener('DOMContentLoaded', () => {
    const stokInput = document.getElementById('stokSparepartInput');
    const statusPreview = document.getElementById('statusSparepartPreview');
    if (!stokInput || !statusPreview) return;

    const batasMenipis = <?= (int) $batasMenipis ?>;
    const palette = {
      Aman: ['bg-success-light', 'text-success-dark'],
      Menipis: ['bg-warning-light', 'text-warning-dark'],
      Habis: ['bg-danger-light', 'text-danger-dark']
    };
    const allColors = Object.values(palette).flat();

    function updateStatus() {
      stokInput.value = stokInput.value.replace(/\D/g, '');
      const stok = Number(stokInput.value || 0);
      const status = stok <= 0 ? 'Habis' : (stok <= batasMenipis ? 'Menipis' : 'Aman');
      statusPreview.value = status;
      statusPreview.classList.remove(...allColors);
      statusPreview.classList.add(...palette[status]);
    }

    stokInput.addEventListener('input', updateStatus);
    updateStatus();
  });
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>