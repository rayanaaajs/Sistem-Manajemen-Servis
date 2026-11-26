<?php
// ==============================================================================
// auth/proses_login.php
// Menerima POST dari auth/login.php. Selalu berakhir dengan redirect:
//   - gagal  -> balik ke login.php dengan pesan di $_SESSION['auth_error']
//   - sukses -> ke dashboard sesuai role
// ==============================================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/koneksi.php'; // ← GANTI sesuai file koneksi PDO-mu (harus menghasilkan $pdo)

const MAKS_GAGAL_PER_IP   = 5;    // 5x salah dari satu IP dalam 15 menit -> diblok
const MAKS_GAGAL_PER_USER = 20;   // lebih longgar per username (supaya akun tidak mudah "dikunci" orang iseng)

// Hash bcrypt valid, dipakai supaya waktu proses sama walau username tidak ada
// (mencegah orang menebak username lewat selisih waktu respons)
const HASH_PALSU = '$2y$10$.vGA1O9wmRjrwAVXD98HNOgsNpDczlqm3Jq7KnEd1rVAGv3Fykk1a';

function kembali_ke_login(string $pesan): void
{
    $_SESSION['auth_error'] = $pesan;
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 1) CSRF (dicek manual di sini supaya user dapat pesan ramah, bukan halaman "die")
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || $token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    kembali_ke_login('Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.');
}

// 2) Ambil & validasi input
$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';   // jangan percaya X-Forwarded-For kecuali di belakang proxy tepercaya

if ($username === '' || $password === '' || mb_strlen($username) > 50 || strlen($password) > 255) {
    kembali_ke_login('Username atau password salah.');
}

try {
    // 3) Batas percobaan gagal (15 menit terakhir)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FILTER (WHERE ip = :ip)       AS dari_ip,
               COUNT(*) FILTER (WHERE username = :u)  AS untuk_user
        FROM percobaan_login
        WHERE waktu > NOW() - INTERVAL '15 minutes'
    ");
    $stmt->execute(['ip' => $ip, 'u' => $username]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);

    if ((int) $c['dari_ip'] >= MAKS_GAGAL_PER_IP || (int) $c['untuk_user'] >= MAKS_GAGAL_PER_USER) {
        kembali_ke_login('Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.');
    }

    // 4) Ambil user (prepared statement -> kebal SQL injection)
    $stmt = $pdo->prepare('
        SELECT id_karyawan, nama_karyawan, password, peran
        FROM karyawan
        WHERE username = :u
        LIMIT 1
    ');
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5) Verifikasi password (selalu dijalankan, walau user tidak ada)
    $hash = $user['password'] ?? HASH_PALSU;
    $role = $user ? strtolower((string) $user['peran']) : '';
    $ok   = password_verify($password, $hash)
            && $user
            && in_array($role, ['admin', 'teknisi'], true);

    if (!$ok) {
        // Catat percobaan gagal + pesan umum (jangan bedakan "user tidak ada" vs "password salah")
        $pdo->prepare('INSERT INTO percobaan_login (username, ip) VALUES (:u, :ip)')
            ->execute(['u' => mb_substr($username, 0, 50), 'ip' => $ip]);
        $pdo->exec("DELETE FROM percobaan_login WHERE waktu < NOW() - INTERVAL '1 day'");

        kembali_ke_login('Username atau password salah.');
    }

    // 6) Sukses
    // Upgrade hash otomatis kalau algoritma/cost PHP berubah
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE karyawan SET password = :h WHERE id_karyawan = :id')
            ->execute(['h' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id_karyawan']]);
    }

    // Bersihkan catatan gagal milik user+IP ini
    $pdo->prepare('DELETE FROM percobaan_login WHERE username = :u AND ip = :ip')
        ->execute(['u' => $username, 'ip' => $ip]);

    // Session baru (mencegah session fixation)
    session_regenerate_id(true);
    $_SESSION['user_id']        = (int) $user['id_karyawan'];   // nama key sama dengan guard di auth.php
    $_SESSION['nama']           = $user['nama_karyawan'];
    $_SESSION['role']           = $role;                        // 'admin' | 'teknisi'
    $_SESSION['aktif_terakhir'] = time();
    $_SESSION['csrf_token']     = bin2hex(random_bytes(32));    // token baru setelah login
    unset($_SESSION['auth_error'], $_SESSION['auth_success']);

    // Tujuan redirect hardcode (bukan dari input user -> aman dari open redirect)
    header('Location: ' . ($role === 'admin' ? '../admin/dashboard.php' : '../teknisi/dashboard.php'));
    exit;

} catch (PDOException $e) {
    error_log('proses_login: ' . $e->getMessage());   // detail cuma di log server
    kembali_ke_login('Terjadi kesalahan pada server. Silakan coba lagi.');
}