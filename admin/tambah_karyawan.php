<?php
require_once __DIR__ . '/../includes/auth.php';   
require_once __DIR__ . '/../includes/koneksi.php'; // Panggil koneksi database

$page_title = 'Tambah Karyawan';
$active_menu = 'karyawan';

// LOGIKA SIMPAN DATA KE SUPABASE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $peran    = 'Teknisi'; // Peran default

    if (empty($nama) || empty($username) || empty($password) || empty($email)) {
        echo "<script>alert('Semua bidang formulir wajib diisi!'); window.history.back();</script>";
        exit;
    }

    try {
        // 1. Cek apakah username atau email sudah pernah digunakan
        $checkStmt = $pdo->prepare("SELECT id_karyawan FROM karyawan WHERE username = :username OR email = :email LIMIT 1");
        $checkStmt->execute(['username' => $username, 'email' => $email]);

        if ($checkStmt->fetch()) {
            echo "<script>alert('Username atau Email sudah terdaftar! Gunakan data lain.'); window.history.back();</script>";
            exit;
        }

        // 2. Hash password demi keamanan akun
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert ke database Supabase
        $stmt = $pdo->prepare("INSERT INTO karyawan (nama_karyawan, username, password, peran, email) VALUES (:nama, :username, :password, :peran, :email)");
        $stmt->execute([
            'nama'     => $nama,
            'username' => $username,
            'password' => $hashed_password,
            'peran'    => $peran,
            'email'    => $email
        ]);

        echo "<script>alert('Karyawan berhasil ditambahkan!'); window.location.href='karyawan.php';</script>";
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('Gagal menyimpan data: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
}

// Panggil header & helper
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- FORM 2 KOLOM (KIRI: FORM DATA TEKNISI, KANAN: RINGKASAN TEKNISI) -->
      <form action="tambah_karyawan.php" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
        <!-- ==========================================
           KARTU KIRI: DATA TEKNISI (2 KOLOM GRID)
           ========================================== -->
        <div class="lg:col-span-2 bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              Data Teknisi
            </h2>

            <!-- Grid Input Form -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
              <!-- Nama -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nama</label>
                <input
                  type="text"
                  name="nama"
                  required
                  placeholder="Masukkan Nama"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <!-- Username -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Username</label>
                <input
                  type="text"
                  name="username"
                  required
                  placeholder="Buat Username"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <!-- Input Alamat Dihapus agar sesuai database -->

              <!-- Password -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password</label>
                <div class="relative">
                  <input
                    type="password"
                    id="passwordInput"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full pl-4 pr-11 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker tracking-wider focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                  />
                  <button
                    type="button"
                    id="togglePasswordBtn"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white-dark-hover hover:text-blue-darker cursor-pointer"
                  >
                    <!-- Eye Icon -->
                    <svg
                      id="eyeIcon"
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Input Nomor Whatsapp Dihapus agar sesuai database -->

              <!-- Pilih Status Teknisi -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Status Teknisi</label>
                <div class="relative">
                  <select
                    name="status_teknisi"
                    class="w-full pl-4 pr-10 py-3.5 rounded-2xl border border-white-active bg-success-light/60 text-sm font-semibold text-success-dark appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                  >
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-success-dark">
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Email Teknisi -->
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Email Teknisi</label>
                <input
                  type="email"
                  name="email"
                  required
                  placeholder="Example@gmail.com"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Bawah: Batal & Tambah Karyawan -->
          <div class="mt-12 pt-6 flex items-center justify-end gap-3.5">
            <a
              href="karyawan.php"
              class="px-8 py-3 rounded-full border border-tosca-dark hover:bg-tosca-dark-hover text-tosca-dark hover:text-white-light text-sm font-semibold transition text-center cursor-pointer"
            >
              Batal
            </a>
            <button
              type="submit"
              class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer"
            >
              Tambah Karyawan
            </button>
          </div>
        </div>

        <!-- ==========================================
           KARTU KANAN: RINGKASAN TEKNISI
           ========================================== -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-gray-200 flex flex-col justify-between">
          <div>
            <h2 class="text-xl font-bold text-blue-darker tracking-tight mb-6">
              Ringkasan Teknisi
            </h2>

            <!-- State Kosong: Belum ada data -->
            <div class="flex flex-col items-center justify-center text-center py-12">
              <div class="w-16 h-16 rounded-full bg-white-hover/60 flex items-center justify-center mb-4">
                <svg
                  class="w-8 h-8 text-white-dark"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
              <p class="text-sm font-semibold text-white-dark-hover mb-1">
                Belum ada data
              </p>
              <p class="text-xs text-white-dark-hover/80">
                Ringkasan akan muncul setelah karyawan ditambahkan
              </p>
            </div>

            <!-- Daftar Informasi Ringkasan -->
            <div class="space-y-4 text-xs font-medium text-white-darker mt-4">
              <div class="flex justify-between items-center py-1">
                <span>Tanggal Akun Dibuat</span>
                <span class="font-bold text-white-dark-hover text-sm"><?= date('d M Y') ?></span>
              </div>

              <div class="flex justify-between items-center py-1 border-t border-white-hover/60 pt-3">
                <span>Status awal</span>
                <span class="font-bold text-success-dark text-sm">Aktif</span>
              </div>

              <div class="flex justify-between items-center py-1 border-t border-white-hover/60 pt-3">
                <span>Peran</span>
                <span class="font-bold text-blue-darker text-sm">Teknisi</span>
              </div>
            </div>
          </div>

          <!-- Banner Biru Muda: Setelah disimpan -->
          <div class="mt-8 p-5 rounded-2xl bg-lightblue-light text-lightblue-darker text-xs font-medium">
            <h4 class="font-bold text-lightblue-darker mb-2 text-xs">
              Setelah disimpan
            </h4>
            <ul class="space-y-1.5 text-[11px] text-lightblue-dark font-medium list-disc list-inside">
              <li>Akun bisa di loginkan</li>
              <li>Dapat di edit lagi</li>
            </ul>
          </div>
        </div>
      </form>

    <!-- JAVASCRIPT: Toggle Lihat Password -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const togglePasswordBtn = document.getElementById("togglePasswordBtn");
        const passwordInput = document.getElementById("passwordInput");
        if (togglePasswordBtn && passwordInput) {
          togglePasswordBtn.addEventListener("click", function () {
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
          });
        }
      });
    </script>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>