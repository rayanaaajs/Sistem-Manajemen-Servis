<?php
// Panggil koneksi database
require '../includes/koneksi.php';

$token = $_POST['token'] ?? '';
$pass1 = $_POST['new_password'] ?? '';
$pass2 = $_POST['confirm_password'] ?? '';

if (empty($token)) {
    echo "<script>alert('Token tidak valid!'); window.location.href='login.php';</script>";
    exit;
}

if ($pass1 !== $pass2) {
    echo "<script>alert('Password baru dan konfirmasi tidak cocok!'); window.history.back();</script>";
    exit;
}

try {
    // 1. Cari karyawan yang memiliki token ini di Supabase
    $stmt = $pdo->prepare("SELECT id_karyawan, token_expired FROM karyawan WHERE token_reset_password = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Cek apakah token sudah expired (lewat 30 menit)
        date_default_timezone_set('Asia/Jakarta');
        $waktu_sekarang = time();
        $waktu_expired = strtotime($user['token_expired']);

        if ($waktu_sekarang > $waktu_expired) {
            echo "<script>alert('Link reset password sudah kadaluarsa! Silakan minta link baru.'); window.location.href='lupa_password.php';</script>";
            exit;
        }

        // 3. Enkripsi password baru (Wajib untuk keamanan database)
        $password_hashed = password_hash($pass1, PASSWORD_DEFAULT);

        // 4. Update password di Supabase, lalu HANGUSKAN tokennya (jadikan NULL)
        $updateStmt = $pdo->prepare("UPDATE karyawan SET password = :password, token_reset_password = NULL, token_expired = NULL WHERE id_karyawan = :id");
        $updateStmt->execute([
            'password' => $password_hashed,
            'id' => $user['id_karyawan']
        ]);

        echo "<script>alert('Berhasil! Password telah diubah. Silakan login.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Token tidak ditemukan atau sudah pernah digunakan!'); window.location.href='login.php';</script>";
    }

} catch (PDOException $e) {
    echo "<script>alert('Terjadi kesalahan pada database!'); window.history.back();</script>";
}
?>