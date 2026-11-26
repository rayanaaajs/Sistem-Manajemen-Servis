
<?php
require_once __DIR__ . '/session.php';
header('Cache-Control: no-store');

// Validasi sesi login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Idle timeout: 30 menit
$__batas_idle = 30 * 60;

if (time() - ($_SESSION['aktif_terakhir'] ?? 0) > $__batas_idle) {
    $_SESSION = [];
    session_regenerate_id(true);

    $_SESSION['auth_error'] =
        'Sesi berakhir karena tidak aktif. Silakan masuk lagi.';

    header('Location: ../auth/login.php');
    exit;
}

$_SESSION['aktif_terakhir'] = time();

// Identifikasi role dan halaman
$__role = strtolower((string) ($_SESSION['role'] ?? ''));
$__folder = basename(dirname($_SERVER['SCRIPT_FILENAME']));
$__file = basename($_SERVER['SCRIPT_FILENAME']);

// Halaman bersama yang dapat diakses admin dan teknisi
$__halaman_bersama = [
    'admin/riwayat.php'
];

$__halaman_aktif = $__folder . '/' . $__file;

$__akses_bersama =
    in_array($__halaman_aktif, $__halaman_bersama, true) &&
    in_array($__role, ['admin', 'teknisi'], true);

// Pembatasan akses berdasarkan folder
if (
    in_array($__folder, ['admin', 'teknisi'], true) &&
    $__role !== $__folder &&
    !$__akses_bersama
) {
    http_response_code(403);
    exit('403 - Anda tidak punya akses ke halaman ini.');
}
