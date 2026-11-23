
<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

// Hanya menerima request POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /teknisi/dashboard.php');
    exit;
}

// ========================================
// AMBIL DAN VALIDASI INPUT
// ========================================

$nota = trim((string) ($_POST['nota'] ?? ''));
$idKaryawan = (int) ($_SESSION['user_id'] ?? 0);

$idDetail = filter_var(
    $_POST['id_detail'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$tokenForm = (string) ($_POST['csrf_edit_servis'] ?? '');
$tokenSesi = (string) ($_SESSION['csrf_edit_servis'] ?? '');

// ========================================
// HELPER REDIRECT
// ========================================

function redirectEditServis(
    string $nota,
    string $tipe,
    string $pesan
): never {
    $_SESSION['flash_servis'] = [
        'tipe' => $tipe,
        'pesan' => $pesan
    ];

    header(
        'Location: /teknisi/edit_servis.php?nota=' .
        urlencode($nota)
    );
    exit;
}

// ========================================
// VALIDASI AWAL
// ========================================

if ($nota === '' || strlen($nota) > 10 || $idKaryawan <= 0) {
    redirectEditServis(
        $nota,
        'error',
        'Nomor nota atau sesi login tidak valid.'
    );
}

if (
    $tokenForm === '' ||
    $tokenSesi === '' ||
    !hash_equals($tokenSesi, $tokenForm)
) {
    redirectEditServis(
        $nota,
        'error',
        'Token keamanan tidak valid. Muat ulang halaman.'
    );
}

if ($idDetail === false) {
    redirectEditServis(
        $nota,
        'error',
        'ID pemakaian sparepart tidak valid.'
    );
}

// ========================================
// PROSES HAPUS SPAREPART
// ========================================

try {
    $pdo->beginTransaction();

    // 1. Pastikan servis milik teknisi yang login.
    // FOR UPDATE mencegah perubahan bersamaan.
    $stmtServis = $pdo->prepare("
        SELECT status_pengerjaan
        FROM servis
        WHERE no_nota = :nota
          AND id_karyawan = :karyawan
        FOR UPDATE
    ");

    $stmtServis->execute([
        'nota' => $nota,
        'karyawan' => $idKaryawan
    ]);

    $statusServis = $stmtServis->fetchColumn();

    if ($statusServis === false) {
        throw new DomainException(
            'Servis tidak ditemukan atau bukan tugas Anda.'
        );
    }

    // 2. Jangan izinkan perubahan pada servis final.
    if (
        in_array(
            $statusServis,
            ['Selesai', 'Dibatalkan'],
            true
        )
    ) {
        throw new DomainException(
            'Sparepart pada servis yang sudah selesai atau dibatalkan tidak dapat dihapus.'
        );
    }

    // 3. Ambil detail pemakaian berdasarkan id_detail.
    $stmtDetail = $pdo->prepare("
        SELECT
            id_sparepart,
            jumlah_dipakai
        FROM detail_servis
        WHERE id_detail = :id_detail
          AND no_nota = :nota
        FOR UPDATE
    ");

    $stmtDetail->execute([
        'id_detail' => $idDetail,
        'nota' => $nota
    ]);

    $detail = $stmtDetail->fetch(PDO::FETCH_ASSOC);

    if (!$detail) {
        throw new DomainException(
            'Data pemakaian sparepart tidak ditemukan.'
        );
    }

    $idSparepart = (int) $detail['id_sparepart'];
    $jumlahDipakai = (int) $detail['jumlah_dipakai'];

    // 4. Kembalikan stok sparepart.
    $stmtStok = $pdo->prepare("
        UPDATE sparepart
        SET stok_tersedia = stok_tersedia + :jumlah
        WHERE id_sparepart = :id_sparepart
    ");

    $stmtStok->execute([
        'jumlah' => $jumlahDipakai,
        'id_sparepart' => $idSparepart
    ]);

    if ($stmtStok->rowCount() !== 1) {
        throw new RuntimeException(
            'Gagal mengembalikan stok sparepart.'
        );
    }

    // 5. Hapus hanya detail pemakaian yang dipilih.
    $stmtHapus = $pdo->prepare("
        DELETE FROM detail_servis
        WHERE id_detail = :id_detail
          AND no_nota = :nota
    ");

    $stmtHapus->execute([
        'id_detail' => $idDetail,
        'nota' => $nota
    ]);

    if ($stmtHapus->rowCount() !== 1) {
        throw new RuntimeException(
            'Gagal menghapus pemakaian sparepart.'
        );
    }

    // 6. Simpan kedua perubahan sekaligus.
    $pdo->commit();

    redirectEditServis(
        $nota,
        'success',
        'Sparepart berhasil dihapus dan stok telah dikembalikan.'
    );

} catch (DomainException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    redirectEditServis(
        $nota,
        'error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'hapus_pakai.php: ' . $e->getMessage()
    );

    redirectEditServis(
        $nota,
        'error',
        'Terjadi kesalahan saat menghapus sparepart.'
    );
}
