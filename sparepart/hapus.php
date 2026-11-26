<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (strtolower((string) ($_SESSION['role'] ?? '')) !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk admin.');
}

if (empty($_SESSION['csrf_sparepart'])) {
    $_SESSION['csrf_sparepart'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode permintaan tidak diizinkan.');
}

$token = is_string($_POST['csrf_sparepart'] ?? null) ? $_POST['csrf_sparepart'] : '';
if ($token === '' || !hash_equals((string) $_SESSION['csrf_sparepart'], $token)) {
    $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'Sesi formulir tidak valid. Muat ulang halaman.'];
    header('Location: index.php');
    exit;
}

$idRaw = $_POST['id_sparepart'] ?? null;
$idSparepart = is_scalar($idRaw)
    ? filter_var($idRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    : false;

if ($idSparepart === false) {
    $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => 'ID sparepart tidak valid.'];
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock baris untuk memastikan penghapusan tidak berbalapan dengan pemakaian sparepart.
    $stmt = $pdo->prepare("
        SELECT id_sparepart FROM public.sparepart
        WHERE id_sparepart = :id FOR UPDATE
    ");
    $stmt->bindValue(':id', (int) $idSparepart, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetchColumn()) {
        throw new RuntimeException('Sparepart tidak ditemukan atau sudah dihapus.');
    }

    // Sparepart yang dipakai servis tidak boleh dihapus agar riwayat tetap utuh.
    $cek = $pdo->prepare("
        SELECT 1 FROM public.detail_servis
        WHERE id_sparepart = :id LIMIT 1
    ");
    $cek->bindValue(':id', (int) $idSparepart, PDO::PARAM_INT);
    $cek->execute();
    if ($cek->fetchColumn()) {
        throw new RuntimeException('Sparepart sudah digunakan dalam servis dan tidak dapat dihapus.');
    }

    $hapus = $pdo->prepare("
        DELETE FROM public.sparepart WHERE id_sparepart = :id
    ");
    $hapus->bindValue(':id', (int) $idSparepart, PDO::PARAM_INT);
    $hapus->execute();

    $pdo->commit();
    $_SESSION['flash_sparepart'] = ['tipe' => 'success', 'pesan' => 'Sparepart berhasil dihapus.'];
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => $e->getMessage()];
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('sparepart/hapus.php: ' . $e->getMessage());
    $pesan = $e->getCode() === '23503'
        ? 'Sparepart masih digunakan oleh data lain dan tidak bisa dihapus.'
        : 'Gagal menghapus sparepart. Silakan coba lagi.';
    $_SESSION['flash_sparepart'] = ['tipe' => 'error', 'pesan' => $pesan];
}

header('Location: index.php');
exit;
