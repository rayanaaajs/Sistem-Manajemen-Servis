<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$no_nota = trim($_GET['nota'] ?? '');

if (empty($no_nota)) {
    echo "<script>alert('Nomor nota tidak valid!'); window.location.href='antrean.php';</script>";
    exit;
}

try {
    $pdo->beginTransaction();

    // Hapus data relasi terlebih dahulu agar tidak terblokir Foreign Key
    $stmtTracking = $pdo->prepare("DELETE FROM tracking_servis WHERE no_nota = :nota");
    $stmtTracking->execute(['nota' => $no_nota]);

    $stmtDetail = $pdo->prepare("DELETE FROM detail_servis WHERE no_nota = :nota");
    $stmtDetail->execute(['nota' => $no_nota]);

    $stmtValidasi = $pdo->prepare("DELETE FROM validasi WHERE no_nota = :nota");
    $stmtValidasi->execute(['nota' => $no_nota]);

    // Hapus data utama dari tabel servis (data pelanggan di tabel pelanggan tetap aman)
    $stmtServis = $pdo->prepare("DELETE FROM servis WHERE no_nota = :nota");
    $stmtServis->execute(['nota' => $no_nota]);

    $pdo->commit();

    echo "<script>alert('Data antrean nota " . addslashes($no_nota) . " berhasil dihapus permanen!'); window.location.href='antrean.php';</script>";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<script>alert('Gagal menghapus antrean: " . addslashes($e->getMessage()) . "'); window.location.href='antrean.php';</script>";
}
?>