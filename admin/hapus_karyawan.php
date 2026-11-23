<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$id_karyawan = $_GET['id'] ?? 0;

if (!$id_karyawan) {
    echo "<script>alert('ID Karyawan tidak valid!'); window.location.href='karyawan.php';</script>";
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute(['id' => $id_karyawan]);

    echo "<script>alert('Data karyawan berhasil dihapus permanen!'); window.location.href='karyawan.php';</script>";
} catch (PDOException $e) {
    echo "<script>alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "'); window.location.href='karyawan.php';</script>";
}
?>