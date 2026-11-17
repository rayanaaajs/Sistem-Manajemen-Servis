<?php
// Sesi harus dimulai sebelum header mengeluarkan output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Muat helper secara eksplisit sebelum view digunakan.
// header_pelanggan.php tetap menjadi sumber koneksi PDO dan layout header.
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header_pelanggan.php';
require_once __DIR__ . '/../includes/koneksi.php';

define('SIPASTI_LACAK_VIEW', true);

$data_servis = null;
$riwayat_list = [];
$pesan_error = '';
$nota_input = '';

$maks_percobaan = 5;
$durasi_kunci = 300; // 5 menit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota_input = strtoupper(trim((string) ($_POST['nota'] ?? '')));
    $pin_input = trim((string) ($_POST['pin_verifikasi'] ?? ''));

    $waktu_kunci = (int) ($_SESSION['lacak_kunci'] ?? 0);

    if ($waktu_kunci > time()) {
        $menit = (int) ceil(($waktu_kunci - time()) / 60);
        $pesan_error = "Terlalu banyak percobaan. Coba lagi dalam {$menit} menit.";
    } else {
        unset($_SESSION['lacak_kunci']);

        try {
            $servis = null;
            $valid = false;

            // Validasi format dilakukan sebelum query database.
            if (
                $nota_input !== ''
                && strlen($nota_input) <= 10
                && preg_match('/^\d{4}$/D', $pin_input)
            ) {
                // no_nota adalah primary key. Pencarian langsung dapat memakai index.
                $stmt = $pdo->prepare("
                    SELECT
                        s.no_nota AS nota,
                        p.nama_pelanggan AS pelanggan,
                        p.no_hp AS no_hp,
                        CONCAT_WS(' ', s.jenis_barang, s.merek_dan_spesifikasi) AS perangkat,
                        s.keluhan_kerusakan AS keluhan,
                        s.tanggal_masuk AS tanggal_masuk,
                        s.estimasi_pengambilan AS estimasi,
                        s.status_pengerjaan AS status,
                        s.jenis_layanan AS layanan
                    FROM public.servis s
                    JOIN public.pelanggan p
                        ON p.id_pelanggan = s.id_pelanggan
                    WHERE s.no_nota = :nota
                    LIMIT 1
                ");

                $stmt->execute(['nota' => $nota_input]);
                $servis = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($servis) {
                    // PIN adalah empat digit terakhir nomor HP pelanggan.
                    $digits = preg_replace('/\D/', '', (string) $servis['no_hp']);
                    $pin_database = strlen($digits) >= 4
                        ? substr($digits, -4)
                        : '';

                    $valid = $pin_database !== ''
                        && hash_equals($pin_database, $pin_input);
                }
            }

            if ($valid) {
                unset($servis['no_hp']);
                $data_servis = $servis;

                // Reset penghitung percobaan setelah verifikasi berhasil.
                unset($_SESSION['lacak_gagal'], $_SESSION['lacak_kunci']);

                // Riwayat hanya diambil setelah nota dan PIN berhasil diverifikasi.
                $stmt = $pdo->prepare("
                    SELECT
                        status_pengerjaan AS status,
                        keterangan AS deskripsi,
                        waktu_update AS waktu
                    FROM public.tracking_servis
                    WHERE no_nota = :nota
                    ORDER BY waktu_update ASC, id_tracking ASC
                ");

                $stmt->execute(['nota' => $servis['nota']]);
                $riwayat_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Pesan nota salah dan PIN salah dibuat sama untuk mencegah
                // nomor nota ditebak melalui perbedaan pesan.
                $jumlah_gagal = (int) ($_SESSION['lacak_gagal'] ?? 0) + 1;

                if ($jumlah_gagal >= $maks_percobaan) {
                    $_SESSION['lacak_gagal'] = 0;
                    $_SESSION['lacak_kunci'] = time() + $durasi_kunci;
                    $pesan_error = 'Terlalu banyak percobaan. Coba lagi dalam 5 menit.';
                } else {
                    $_SESSION['lacak_gagal'] = $jumlah_gagal;
                    $pesan_error = 'Nomor nota atau PIN tidak sesuai.';
                }
            }
        } catch (PDOException $e) {
            // Detail error hanya disimpan di log server.
            error_log('lacak_servis: ' . $e->getMessage());
            $pesan_error = 'Terjadi kesalahan koneksi database. Silakan coba lagi.';
        }
    }
}

// Semua markup halaman berada di file view.
require __DIR__ . '/lacak_view.php';
