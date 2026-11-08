<?php
// Tangkap data dari form reset_password.php
$token_dari_form = $_POST['token'] ?? '';
$password_baru = $_POST['new_password'] ?? '';
$konfirmasi_password = $_POST['confirm_password'] ?? '';

// LOGIKA 1: Cek apakah token terbawa dari URL/Form
if (empty($token_dari_form)) {
    echo "<script>alert('Token tidak valid atau tidak ditemukan!'); window.location.href='login.php';</script>";
    exit;
}

// LOGIKA 2: Cek apakah password dan konfirmasi sama
if ($password_baru !== $konfirmasi_password) {
    echo "<script>alert('Password baru dan konfirmasi tidak cocok!'); window.history.back();</script>";
    exit;
}

// LOGIKA 3: Simulasi Sukses (Menunggu Supabase dari temanmu)
// Nanti di sinilah temanmu menaruh query enkripsi password dan UPDATE ke Supabase
echo "<script>alert('Berhasil! Password telah diubah dan Link Token ( " . htmlspecialchars($token_dari_form) . " ) sekarang hangus.'); window.location.href='login.php';</script>";
?>