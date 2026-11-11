<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$page_title = 'Edit Karyawan';
$active_menu = 'karyawan';

// 1. Tangkap ID Karyawan dari URL atau Form POST
$id_karyawan = $_GET['id'] ?? $_POST['id'] ?? 0;

if (!$id_karyawan) {
    echo "<script>alert('ID Karyawan tidak ditemukan!'); window.location.href='karyawan.php';</script>";
    exit;
}

// 2. PROSES UPDATE DATA SAAT FORM DISIMPAN (METHOD POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $status   = trim($_POST['status_karyawan'] ?? 'Aktif');

    if (empty($nama) || empty($username) || empty($email)) {
        echo "<script>alert('Nama, Username, dan Email wajib diisi!'); window.history.back();</script>";
        exit;
    }

    try {
        // Cek duplikasi Username / Email pada karyawan lain
        $checkStmt = $pdo->prepare("SELECT id_karyawan FROM karyawan WHERE (username = :username OR email = :email) AND id_karyawan != :id LIMIT 1");
        $checkStmt->execute(['username' => $username, 'email' => $email, 'id' => $id_karyawan]);

        if ($checkStmt->fetch()) {
            echo "<script>alert('Username atau Email sudah digunakan oleh karyawan lain!'); window.history.back();</script>";
            exit;
        }

        // Eksekusi Update ke Database Supabase
        $updateStmt = $pdo->prepare("UPDATE karyawan SET nama_karyawan = :nama, username = :username, email = :email, status = :status WHERE id_karyawan = :id");
        $updateStmt->execute([
            'nama'     => $nama,
            'username' => $username,
            'email'    => $email,
            'status'   => $status,
            'id'       => $id_karyawan
        ]);

        echo "<script>alert('Data karyawan berhasil diperbarui!'); window.location.href='karyawan.php';</script>";
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('Gagal memperbarui data: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
}

// 3. AMBIL DATA KARYAWAN TERBARU UNTUK DITAMPILKAN DI FORM
try {
    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute(['id' => $id_karyawan]);
    $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$karyawan) {
        echo "<script>alert('Data karyawan tidak ditemukan!'); window.location.href='karyawan.php';</script>";
        exit;
    }
} catch (PDOException $e) {
        echo "<script>alert('Terjadi kesalahan database!'); window.location.href='karyawan.php';</script>";
        exit;
}

$status_saat_ini = $karyawan['status'] ?? 'Aktif';

// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- FORM 2 KOLOM (KIRI: FORM DATA TEKNISI, KANAN: RINGKASAN TEKNISI) -->
      <form action="edit_karyawan.php?id=<?= $id_karyawan ?>" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
        <input type="hidden" name="id" value="<?= $id_karyawan ?>">

        <!-- ==========================================
            KARTU KIRI: DATA TEKNISI (2 KOLOM GRID)
            ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">

          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              <span>Data <?= htmlspecialchars($karyawan['peran']) ?></span> 
            </h2>

            <!-- Grid Input Form -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">

              <!-- Nama (Dinamis) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
                <input type="text"
                       name="nama"
                       required
                       value="<?= htmlspecialchars($karyawan['nama_karyawan']) ?>"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Username (Dinamis) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Username</label>
                <input type="text"
                       name="username"
                       required
                       value="<?= htmlspecialchars($karyawan['username']) ?>"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password</label>
                <input type="text"
                       name="password"
                       value="••••••••"
                       readonly
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker tracking-wider focus:outline-none transition cursor-default">
              </div>

              <!-- Pilih Status Teknisi (Dinamis Selected) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Status Karyawan</label>
                <div class="relative">
                  <select name="status_karyawan"
                          class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-success-light-active/60 bg-success-light text-sm font-semibold text-success-dark appearance-none focus:outline-none transition cursor-pointer">
                    <option value="Aktif" <?= (strtolower($status_saat_ini) === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="Nonaktif" <?= (strtolower($status_saat_ini) === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                  </select>
                  <div id="statusArrowIcon" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-success-dark">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Email (Dinamis) -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Email</label>
                <input type="email"
                       name="email"
                       required
                       value="<?= htmlspecialchars($karyawan['email']) ?>"
                       class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition">
              </div>

            </div>
          </div>

          <!-- BAGIAN UBAH PASSWORD (RESET VIA EMAIL) -->
          <div class="mt-8">
            <h3 class="text-sm font-bold text-blue-darker mb-3">Ubah Password</h3>
            <div class="flex flex-col sm:flex-row items-center gap-4 p-5 rounded-2xl bg-white-light-active border border-gray-200">
              <div class="w-14 h-14 rounded-full bg-orange-light-active flex items-center justify-center shrink-0">
                <svg class="w-7 h-7 text-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <div class="flex-1 text-center sm:text-left">
                <p class="text-sm font-bold text-blue-darker">Kirim Tautan Reset Password</p>
                <p class="text-xs text-blue-darker/70 mt-0.5">Kirimkan link reset ke email <span class="font-bold"><?= htmlspecialchars($karyawan['email']) ?></span> untuk perbarui kata sandi.</p>
              </div>
              
              <a href="../auth/proses_email.php?email=<?= urlencode($karyawan['email']) ?>&id=<?= $id_karyawan ?>"
                 onclick="return confirm('Yakin ingin mengirim link reset sandi ke <?= htmlspecialchars($karyawan['email']) ?>?')"
                 class="px-5 py-2.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap shrink-0 inline-block text-center">
                Kirim Link Reset Password
              </a>
            </div>
          </div>

          <div class="mt-8 pt-6 flex items-center justify-end gap-3.5">
            <a href="karyawan.php"
               class="px-8 py-3 rounded-full border border-tosca-dark text-tosca-dark hover:bg-tosca-dark-hover hover:text-white-hover text-sm font-bold transition text-center cursor-pointer">
              Batal
            </a>
            <button type="submit"
                    class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </div>

        <!-- ==========================================
             KARTU KANAN: RINGKASAN TEKNISI
             ========================================== -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">Ringkasan <?= htmlspecialchars($karyawan['peran']) ?></h2>

            <div class="bg-orange-light-active rounded-2xl p-6 mb-6">
              <p class="text-xs text-orange-dark font-medium mb-1">Username</p>
              <h3 class="text-3xl font-extrabold text-orange-dark tracking-tight"><?= htmlspecialchars($karyawan['username']) ?></h3>
            </div>

            <div class="text-xs font-medium text-blue-darker">
              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Status awal</span>
                <span id="ringkasanStatusBadge" class="inline-block px-6 py-1 rounded-full text-xs font-semibold transition-all">
                  <?= htmlspecialchars($status_saat_ini) ?>
                </span>
              </div>

              <div class="flex justify-between items-center py-3.5 border-b border-white-hover/60">
                <span class="text-blue-darker/70">Peran</span>
                <span class="font-bold text-blue-darker text-sm"><?= htmlspecialchars($karyawan['peran']) ?></span>
              </div>
            </div>
          </div>

          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">Setelah disimpan</h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Akun bisa di loginkan</li>
              <li>Dapat di edit lagi</li>
            </ul>
          </div>
        </div>

      </form>

    <!-- JAVASCRIPT: Status Dropdown/Badge Color Sync -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const statusSelect = document.querySelector('select[name="status_karyawan"]');
        const statusArrow = document.getElementById("statusArrowIcon");
        const ringkasanBadge = document.getElementById("ringkasanStatusBadge");

        if (statusSelect) {
          function updateStatusColor() {
            const isAktif = statusSelect.value === "Aktif";

            if (isAktif) {
              statusSelect.className = "w-full pl-4 pr-10 py-3.5 rounded-2xl border border-success-light-active/60 bg-success-light text-sm font-semibold text-success-dark appearance-none focus:outline-none transition cursor-pointer";
              if (statusArrow) {
                statusArrow.className = "absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-success-dark";
              }
            } else {
              statusSelect.className = "w-full pl-4 pr-10 py-3.5 rounded-2xl border border-white-active bg-white-hover text-sm font-semibold text-blue-darker appearance-none focus:outline-none transition cursor-pointer";
              if (statusArrow) {
                statusArrow.className = "absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-blue-darker";
              }
            }

            if (ringkasanBadge) {
              ringkasanBadge.textContent = statusSelect.value;
              if (isAktif) {
                ringkasanBadge.className = "inline-block px-6 py-1 rounded-full text-xs font-semibold bg-success-light text-success-dark transition-all";
              } else {
                ringkasanBadge.className = "inline-block px-6 py-1 rounded-full text-xs font-semibold bg-white-hover text-blue-darker transition-all";
              }
            }
          }

          statusSelect.addEventListener("change", updateStatusColor);
          updateStatusColor();
        }
      });
    </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>