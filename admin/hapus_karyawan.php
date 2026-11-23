<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$id_karyawan = (int)($_GET['id'] ?? 0);

if ($id_karyawan <= 0) {
    echo "<script>alert('ID Karyawan tidak valid!'); window.location.href='karyawan.php';</script>";
    exit;
}

$logged_in_id = (int)($_SESSION['id_karyawan'] ?? $_SESSION['user_id'] ?? 0);
if ($logged_in_id > 0 && $id_karyawan === $logged_in_id) {
    echo "<script>alert('Akun yang sedang digunakan login tidak dapat dihapus!'); window.location.href='karyawan.php';</script>";
    exit;
}

try {
    // Cek apakah karyawan masih terhubung dengan data servis / riwayat tracking
    $cekServis = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_karyawan = :id");
    $cekServis->execute(['id' => $id_karyawan]);

    $cekTracking = $pdo->prepare("SELECT COUNT(*) FROM tracking_servis WHERE id_karyawan = :id");
    $cekTracking->execute(['id' => $id_karyawan]);

    if ((int)$cekServis->fetchColumn() > 0 || (int)$cekTracking->fetchColumn() > 0) {
        echo "<script>alert('Karyawan tidak dapat dihapus karena masih memiliki riwayat penanganan servis. Ubah statusnya menjadi Nonaktif melalui menu Edit.'); window.location.href='karyawan.php';</script>";
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute(['id' => $id_karyawan]);

    echo "<script>alert('Data karyawan berhasil dihapus permanen!'); window.location.href='karyawan.php';</script>";
} catch (PDOException $e) {
    echo "<script>alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "'); window.location.href='karyawan.php';</script>";
}
?>