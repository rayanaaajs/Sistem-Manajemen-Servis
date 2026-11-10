<?php
// ==============================================================================
// includes/auth.php  (versi lu + peningkatan)
// Guard clause: di-include di baris paling atas setiap halaman yang
// membutuhkan login (sebelum header.php mengeluarkan output apa pun),
// agar header('Location: ...') masih bisa dipanggil.
//
// Tambahan dari versi sebelumnya:
//  - session pakai cookie aman (lewat session.php)
//  - logout otomatis setelah 30 menit tidak aktif
//  - halaman di folder admin/ hanya untuk admin, teknisi/ hanya untuk teknisi
//  - Cache-Control: no-store (tombol Back setelah logout tidak menampilkan halaman lama)
// ==============================================================================
require_once __DIR__ . '/session.php';

header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// --- Idle timeout: 30 menit ---
$__batas_idle = 30 * 60;
if (time() - ($_SESSION['aktif_terakhir'] ?? 0) > $__batas_idle) {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['auth_error'] = 'Sesi berakhir karena tidak aktif. Silakan masuk lagi.';
    header('Location: ../auth/login.php');
    exit;
}
$_SESSION['aktif_terakhir'] = time();

// --- Pembatasan akses per folder ---
// admin/   -> role "admin"
// teknisi/ -> role "teknisi"
$__folder = basename(dirname($_SERVER['SCRIPT_FILENAME']));
if (in_array($__folder, ['admin', 'teknisi'], true) && ($_SESSION['role'] ?? '') !== $__folder) {
    http_response_code(403);
    exit('403 - Anda tidak punya akses ke halaman ini.');
}