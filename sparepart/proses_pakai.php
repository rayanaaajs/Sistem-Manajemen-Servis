<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /teknisi/dashboard.php');
    exit;
}

$nota = trim((string) ($_POST['nota'] ?? ''));
$idKaryawan = (int) ($_SESSION['user_id'] ?? 0);
$idSparepart = filter_var($_POST['id_sparepart'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$jumlah = filter_var($_POST['jumlah'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$tokenForm = (string) ($_POST['csrf_pakai_sparepart'] ?? '');
$tokenSesi = (string) ($_SESSION['csrf_pakai_sparepart'] ?? '');

function redirectPakai(string $nota, string $pesan): never
{
    $_SESSION['flash_pakai'] = ['tipe' => 'error', 'pesan' => $pesan];
    header('Location: /sparepart/pakai.php?nota=' . urlencode($nota));
    exit;
}

if ($nota === '' || strlen($nota) > 10 || $idKaryawan <= 0) {
    redirectPakai($nota, 'Nomor nota atau sesi login tidak valid.');
}
if ($tokenForm === '' || $tokenSesi === '' || !hash_equals($tokenSesi, $tokenForm)) {
    redirectPakai($nota, 'Token keamanan tidak valid. Muat ulang halaman dan coba lagi.');
}
if ($idSparepart === false || $jumlah === false) {
    redirectPakai($nota, 'Sparepart atau jumlah pemakaian tidak valid.');
}

try {
    $pdo->beginTransaction();

    // Kunci servis dan sparepart untuk mencegah penambahan pada status akhir / stok minus.
    $cekServis = $pdo->prepare("
        SELECT status_pengerjaan FROM servis
        WHERE no_nota = :nota AND id_karyawan = :karyawan
        FOR UPDATE
    ");
    $cekServis->execute(['nota' => $nota, 'karyawan' => $idKaryawan]);
    $status = $cekServis->fetchColumn();
    if ($status === false) {
        throw new DomainException('Servis tidak ditemukan atau bukan tugas Anda.');
    }
    if (in_array($status, ['Selesai', 'Dibatalkan'], true)) {
        throw new DomainException('Servis sudah selesai atau dibatalkan. Sparepart tidak bisa ditambahkan.');
    }

    $cekSparepart = $pdo->prepare("
        SELECT stok_tersedia, harga_satuan FROM sparepart
        WHERE id_sparepart = :id FOR UPDATE
    ");
    $cekSparepart->execute(['id' => $idSparepart]);
    $sparepart = $cekSparepart->fetch(PDO::FETCH_ASSOC);
    if (!$sparepart) {
        throw new DomainException('Sparepart tidak ditemukan.');
    }
    if ($jumlah > (int) $sparepart['stok_tersedia']) {
        throw new DomainException('Stok tidak cukup. Stok tersedia: ' . $sparepart['stok_tersedia'] . ' unit.');
    }

    // Harga dihitung dari server, bukan dari atribut data-harga di browser.
    $hargaSatuan = (string) $sparepart['harga_satuan'];
    $kurangi = $pdo->prepare("
        UPDATE sparepart
        SET stok_tersedia = stok_tersedia - :jumlah
        WHERE id_sparepart = :id AND stok_tersedia >= :jumlah_cek
    ");
    $kurangi->execute(['jumlah' => $jumlah, 'id' => $idSparepart, 'jumlah_cek' => $jumlah]);
    if ($kurangi->rowCount() !== 1) {
        throw new DomainException('Stok berubah. Silakan muat ulang halaman.');
    }

    $simpan = $pdo->prepare("
        INSERT INTO detail_servis
            (no_nota, id_sparepart, jumlah_dipakai, harga_satuan, subtotal_harga)
        VALUES
            (:nota, :id, :jumlah, :harga, CAST(:harga_total AS numeric) * :jumlah_total)
    ");
    $simpan->execute([
        'nota' => $nota,
        'id' => $idSparepart,
        'jumlah' => $jumlah,
        'harga' => $hargaSatuan,
        'harga_total' => $hargaSatuan,
        'jumlah_total' => $jumlah,
    ]);

    $pdo->commit();
    $_SESSION['flash_servis'] = ['tipe' => 'success', 'pesan' => 'Sparepart berhasil ditambahkan. Stok otomatis berkurang.'];
    header('Location: /teknisi/edit_servis.php?nota=' . urlencode($nota));
    exit;
} catch (DomainException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    redirectPakai($nota, $e->getMessage());
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('proses_pakai.php: ' . $e->getMessage());
    redirectPakai($nota, 'Gagal menyimpan penggunaan sparepart. Silakan coba lagi.');
}
