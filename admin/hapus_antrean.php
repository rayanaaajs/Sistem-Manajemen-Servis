<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (strtolower((string) ($_SESSION['role'] ?? '')) !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Penghapusan harus menggunakan POST.');
}

$dariRiwayat = ($_POST['from'] ?? '') === 'riwayat';
$urlKembali = $dariRiwayat ? 'riwayat.php' : 'antrean.php';
$jenisData = $dariRiwayat ? 'riwayat' : 'antrean';
$namaToken = $dariRiwayat ? 'csrf_hapus_riwayat' : 'csrf_hapus_antrean';
$token = $_POST[$namaToken] ?? '';
$tokenSession = $_SESSION[$namaToken] ?? '';

if (!is_string($token) || !is_string($tokenSession) || $tokenSession === '' || !hash_equals($tokenSession, $token)) {
    http_response_code(403);
    exit('Token CSRF tidak valid.');
}

$kembaliDenganPesan = static function (string $pesan) use ($urlKembali): void {
    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE;
    echo '<script>alert(' . json_encode($pesan, $flags) . '); window.location.href=' . json_encode($urlKembali, $flags) . ';</script>';
    exit;
};

$no_nota = is_string($_POST['nota'] ?? null) ? trim($_POST['nota']) : '';

if ($no_nota === '' || strlen($no_nota) > 10) {
    $kembaliDenganPesan('Nomor nota tidak valid!');
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

    if ($stmtServis->rowCount() !== 1) {
        $pdo->rollBack();
        $kembaliDenganPesan('Data nota tidak ditemukan.');
    }

    $pdo->commit();

    $kembaliDenganPesan("Data {$jenisData} nota {$no_nota} berhasil dihapus permanen!");
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('hapus_antrean.php: ' . $e->getMessage());
    $kembaliDenganPesan("Gagal menghapus {$jenisData}.");
}
