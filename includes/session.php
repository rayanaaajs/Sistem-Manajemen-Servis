<?php
// ==============================================================================
// includes/session.php
// Satu-satunya tempat session dimulai, supaya cookie-nya selalu aman.
// Di-require oleh: auth.php, header.php, auth/login.php, auth/proses_login.php, auth/logout.php
// ==============================================================================

function mulai_session_aman(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    ini_set('session.use_strict_mode', '1');   // tolak session ID buatan penyerang
    ini_set('session.use_only_cookies', '1');  // jangan terima session ID dari URL

    session_set_cookie_params([
        'lifetime' => 0,          // hilang saat browser ditutup
        'path'     => '/',
        'secure'   => $https,     // hanya lewat HTTPS (otomatis aktif di production)
        'httponly' => true,       // tidak bisa dibaca JavaScript
        'samesite' => 'Lax',      // cookie tidak ikut request lintas-situs
    ]);
    session_start();
}

function kirim_header_keamanan(): void
{
    if (headers_sent()) return;
    header('X-Frame-Options: DENY');                         // cegah clickjacking
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

mulai_session_aman();
kirim_header_keamanan();