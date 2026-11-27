<?php

/**
 * Controller dan sumber data Dashboard Admin SIPASTI.
 * Semua koneksi, query, verifikasi, serta perubahan persetujuan berada di sini.
 * Hanya dipanggil dari dashboard.php (bukan URL publik terpisah).
 */
if (!defined('SIPASTI_ADMIN_DASHBOARD')) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (strtolower((string) ($_SESSION['role'] ?? '')) !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk admin.');
}

$page_title = 'Dashboard Admin';
$active_menu = 'Dashboard';
$search_placeholder = 'Cari antrean menunggu persetujuan...';

$zona = new DateTimeZone('Asia/Jakarta');
$sekarang = new DateTimeImmutable('now', $zona);
$hariIni = $sekarang->setTime(0, 0);
$besok = $hariIni->modify('+1 day');
$pilihanRentang = ['today' => 1, '7d' => 7, '30d' => 30];
$rentang = is_string($_GET['range'] ?? null) ? $_GET['range'] : 'today';
$errorRentang = '';
$tanggalMulaiCustom = $hariIni->format('Y-m-d');
$tanggalAkhirCustom = $tanggalMulaiCustom;
$parseTanggal = static function ($nilai) use ($zona): ?DateTimeImmutable {
    if (!is_string($nilai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $nilai)) return null;
    $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d', $nilai, $zona);
    return $tanggal && $tanggal->format('Y-m-d') === $nilai ? $tanggal : null;
};
if ($rentang === 'custom') {
    $mulai = $parseTanggal($_GET['start_date'] ?? null);
    $sampai = $parseTanggal($_GET['end_date'] ?? null);
    if (!$mulai || !$sampai || $mulai > $sampai) {
        $errorRentang = 'Rentang tanggal tidak valid. Pilih tanggal mulai dan akhir yang sesuai.';
        $rentang = 'today';
    } else {
        $tanggalMulaiCustom = $mulai->format('Y-m-d');
        $tanggalAkhirCustom = $sampai->format('Y-m-d');
    }
} elseif (!isset($pilihanRentang[$rentang])) {
    $rentang = 'today';
}
if ($rentang === 'custom') {
    $awalRentang = $mulai;
    $akhirRentang = $sampai->modify('+1 day');
    $jumlahHari = (int) $awalRentang->diff($akhirRentang)->days;
} else {
    $jumlahHari = $pilihanRentang[$rentang];
    $akhirRentang = $besok;
    $awalRentang = $akhirRentang->modify("-{$jumlahHari} days");
}
$awalRentangLalu = $awalRentang->modify("-{$jumlahHari} days");
$fmtTimestamp = static fn(DateTimeImmutable $date): string => $date->format('Y-m-d H:i:s');
$awal = $fmtTimestamp($awalRentang);
$akhir = $fmtTimestamp($akhirRentang);
$awalLalu = $fmtTimestamp($awalRentangLalu);
$parameterRentang = ['range' => $rentang];
if ($rentang === 'custom') {
    $parameterRentang += ['start_date' => $tanggalMulaiCustom, 'end_date' => $tanggalAkhirCustom];
}
$urlDashboard = 'dashboard.php?' . http_build_query($parameterRentang);
$urlDetailDashboard = static fn(string $nota): string => 'detail_antrean.php?' . http_build_query(
    ['nota' => $nota, 'from' => 'dashboard'] + $parameterRentang
);

if (empty($_SESSION['csrf_persetujuan'])) {
    $_SESSION['csrf_persetujuan'] = bin2hex(random_bytes(32));
}

// Simpan hasil verifikasi keputusan di dashboard.php melalui PRG (POST-Redirect-GET).
// Tidak perlu lagi proses_persetujuan.php.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formNota = trim((string) ($_POST['nota'] ?? ''));
    $returnToDetail = ($_POST['return_to'] ?? '') === 'detail';
    $tujuan = $returnToDetail && $formNota !== '' && strlen($formNota) <= 10
        ? $urlDetailDashboard($formNota) : $urlDashboard;

    $kembali = static function (string $tipe, string $pesan) use ($tujuan): void {
        $_SESSION['flash_dashboard'] = ['tipe' => $tipe, 'pesan' => $pesan];
        header('Location: ' . $tujuan, true, 303);
        exit;
    };

    $tokenForm = (string) ($_POST['csrf_persetujuan'] ?? '');
    $tokenSesi = (string) ($_SESSION['csrf_persetujuan'] ?? '');
    if ($tokenForm === '' || $tokenSesi === '' || !hash_equals($tokenSesi, $tokenForm)) {
        $kembali('error', 'Sesi formulir tidak valid. Muat ulang halaman.');
    }
    $keputusan = (string) ($_POST['keputusan'] ?? '');
    if ($formNota === '' || strlen($formNota) > 10 || !in_array($keputusan, ['Ya', 'Tidak'], true)) {
        $kembali('error', 'Nomor nota atau keputusan tidak valid.');
    }

    try {
        $pdo->beginTransaction();
        $pdo->query('SELECT pg_advisory_xact_lock(731503, 1)')->closeCursor();

        // Cegah dua admin mencatat keputusan atas nota yang sama secara bersamaan.
        $cek = $pdo->prepare(<<<'SQL'
            SELECT status_pengerjaan, persetujuan_pelanggan
            FROM public.servis
            WHERE no_nota = :nota
            FOR UPDATE
        SQL);
        $cek->execute(['nota' => $formNota]);
        $data = $cek->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            throw new RuntimeException('Servis tidak ditemukan.');
        }
        if ($data['status_pengerjaan'] !== 'Menunggu Persetujuan') {
            throw new RuntimeException('Servis sudah tidak berada dalam tahap menunggu persetujuan.');
        }
        if ($data['persetujuan_pelanggan'] !== null && trim((string) $data['persetujuan_pelanggan']) !== '') {
            throw new RuntimeException('Keputusan pelanggan sudah dicatat sebelumnya.');
        }

        $ubah = $pdo->prepare(<<<'SQL'
            UPDATE public.servis
            SET persetujuan_pelanggan = :keputusan
            WHERE no_nota = :nota
        SQL);
        $ubah->execute(['keputusan' => $keputusan, 'nota' => $formNota]);
        $pdo->commit();
        $kembali('success', "Persetujuan pelanggan untuk nota {$formNota} berhasil dicatat: {$keputusan}.");
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $kembali('error', $e->getMessage());
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('admin/dashboard_data.php: ' . $e->getMessage());
        $kembali('error', 'Keputusan gagal disimpan. Periksa koneksi dan coba lagi.');
    }
}

// Dukungan tautan detail lama: gunakan satu halaman detail saja.
if (array_key_exists('nota', $_GET)) {
    $notaLama = is_string($_GET['nota']) ? trim($_GET['nota']) : '';
    header('Location: ' . $urlDetailDashboard($notaLama), true, 302);
    exit;
}

$flashDashboard = $_SESSION['flash_dashboard'] ?? null;
unset($_SESSION['flash_dashboard']);

// Format tanggal Indonesia; input dan parameter SQL tetap memakai ISO Y-m-d.
$formatTanggalDashboard = static function (DateTimeImmutable $tanggal, bool $tahun = true): string {
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return $tanggal->format('j') . ' ' . $bulan[(int) $tanggal->format('n')]
        . ($tahun ? ' ' . $tanggal->format('Y') : '');
};
$formatRentangDashboard = static function (DateTimeImmutable $dari, DateTimeImmutable $ke, bool $tahun = true) use ($formatTanggalDashboard): string {
    if ($dari->format('Y-m-d') === $ke->format('Y-m-d')) {
        return $formatTanggalDashboard($dari, $tahun);
    }
    if ($dari->format('Y-m') === $ke->format('Y-m')) {
        return $dari->format('j') . '–' . $formatTanggalDashboard($ke, $tahun);
    }
    if ($dari->format('Y') === $ke->format('Y')) {
        return $formatTanggalDashboard($dari, false) . ' – ' . $formatTanggalDashboard($ke, $tahun);
    }
    // Tahun tetap ditampilkan pada kedua batas jika melewati pergantian tahun.
    return $formatTanggalDashboard($dari) . ' – ' . $formatTanggalDashboard($ke);
};

// Judul laporan dan chip memakai rentang yang sama dengan filter.
$labelPeriode = $formatRentangDashboard($awalRentang, $akhirRentang->modify('-1 day'));
$bulan_laporan = $labelPeriode;

$formatRupiah = static function (float $nilai): string {
    if ($nilai >= 1_000_000_000) return 'Rp ' . number_format($nilai / 1_000_000_000, 1, ',', '.') . ' M';
    if ($nilai >= 1_000_000) return 'Rp ' . number_format($nilai / 1_000_000, 1, ',', '.') . ' jt';
    if ($nilai >= 1_000) return 'Rp ' . number_format($nilai / 1_000, 1, ',', '.') . ' rb';
    return 'Rp ' . number_format($nilai, 0, ',', '.');
};

$indikatorPerubahan = static function (float $saatIni, float $sebelumnya): string {
    if ($sebelumnya <= 0) return $saatIni > 0 ? 'Baru' : '0%';
    $persen = (int) round((($saatIni - $sebelumnya) / $sebelumnya) * 100);
    return ($persen > 0 ? '+' : '') . $persen . '%';
};

$normalisasiWhatsApp = static function (?string $nomor): string {
    $angka = preg_replace('/\D+/', '', (string) $nomor);
    if ($angka === '') return '';
    if (str_starts_with($angka, '0')) return '62' . substr($angka, 1);
    if (str_starts_with($angka, '8')) return '62' . $angka;
    return $angka;
};

$tautanWhatsApp = static function (?string $nomor, string $pesan) use ($normalisasiWhatsApp): string {
    $angka = $normalisasiWhatsApp($nomor);
    return $angka === '' ? '' : 'https://wa.me/' . $angka . '?text=' . rawurlencode($pesan);
};

$stat_selesai = ['total' => 0, 'label_bawah' => 'Periode lalu: 0', 'badge' => '0%'];
$stat_servis_masuk = ['total' => 0, 'label_bawah' => 'Periode lalu: 0', 'badge' => '0%'];
$stat_dikerjakan = ['total' => 0, 'label_bawah' => 'Menunggu: 0', 'badge' => '0 Teknisi'];
$stat_omzet = ['total' => 'Rp 0', 'label_bawah' => 'Periode lalu: Rp 0', 'badge' => '0%'];
$data_grafik_servis = [];
$beban_teknisi = [];
$max_beban_teknisi = 10;
$selesai_text = '0 / 0';
$persen_selesai = 0;
$total_sparepart = 0;
$max_sparepart = 50;
$persen_sparepart = 0;
$hasil_pekerjaan = ['selesai' => 0, 'cancel' => 0, 'cant_repair' => '—'];
$pekerjaan_teknisi = [];
$status_barang = ['di_ambil_user' => 0, 'barang_di_toko' => 0];
$daftar_servis_selesai = [];
$daftar_menunggu_persetujuan = [];
$errorPage = '';

// Pagination servis selesai: 5 baris per halaman, pencarian dilakukan di database.
$selesai_cari = trim((string) ($_GET['selesai_q'] ?? ''));
$selesai_cari = substr($selesai_cari, 0, 100);
$halamanInput = filter_var($_GET['selesai_page'] ?? 1, FILTER_VALIDATE_INT);
$selesai_halaman = $halamanInput !== false && $halamanInput > 0 ? $halamanInput : 1;
$selesai_per_halaman = 5;
$selesai_total = 0;
$selesai_jumlah_halaman = 1;
$selesai_awal = 0;
$selesai_akhir = 0;

$urlHalamanSelesai = static function (int $halaman) use ($parameterRentang, $selesai_cari): string {
    return 'dashboard.php?' . http_build_query($parameterRentang + [
        'selesai_q' => $selesai_cari,
        'selesai_page' => max(1, $halaman),
    ]) . '#servis-selesai';
};

try {
    // Seluruh rekap + pendapatan dibaca sekali. Omzet adalah nilai nota
    // LUNAS dari servis yang masuk dalam rentang tanggal, bukan kas berdasarkan
    // tanggal bayar (karena belum tersedia kolom tanggal_pembayaran).
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            COUNT(*) FILTER (WHERE s.tanggal_masuk >= ? AND s.tanggal_masuk < ?) AS masuk,
            COUNT(*) FILTER (WHERE s.tanggal_masuk >= ? AND s.tanggal_masuk < ?) AS masuk_lalu,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Dikerjakan' AND s.tanggal_masuk >= ? AND s.tanggal_masuk < ?) AS dikerjakan,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Menunggu' AND s.tanggal_masuk >= ? AND s.tanggal_masuk < ?) AS menunggu,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Selesai' AND s.tanggal_masuk >= ? AND s.tanggal_masuk < ?) AS selesai_dari_masuk,
            COALESCE(SUM(d.jumlah_sparepart) FILTER (WHERE s.tanggal_masuk >= ? AND s.tanggal_masuk < ?), 0) AS jumlah_sparepart,
            COALESCE(SUM(CASE WHEN v.status_pembayaran = 'Lunas'
                               AND s.tanggal_masuk >= ? AND s.tanggal_masuk < ?
                              THEN COALESCE(s.biaya_jasa, 0)
                                   + COALESCE(s.biaya_urgent, 0)
                                   + COALESCE(d.total_sparepart, 0)
                              ELSE 0 END), 0) AS omzet,
            COALESCE(SUM(CASE WHEN v.status_pembayaran = 'Lunas'
                               AND s.tanggal_masuk >= ? AND s.tanggal_masuk < ?
                              THEN COALESCE(s.biaya_jasa, 0)
                                   + COALESCE(s.biaya_urgent, 0)
                                   + COALESCE(d.total_sparepart, 0)
                              ELSE 0 END), 0) AS omzet_lalu
        FROM public.servis AS s
        LEFT JOIN public.validasi AS v ON v.no_nota = s.no_nota
        LEFT JOIN (
            SELECT no_nota, SUM(subtotal_harga) AS total_sparepart, SUM(jumlah_dipakai) AS jumlah_sparepart
            FROM public.detail_servis GROUP BY no_nota
        ) AS d ON d.no_nota = s.no_nota
    SQL);
    $stmt->execute([
        $awal,
        $akhir,
        $awalLalu,
        $awal,
        $awal,
        $akhir,
        $awal,
        $akhir,
        $awal,
        $akhir,
        $awal,
        $akhir,
        $awal,
        $akhir,
        $awalLalu,
        $awal,
    ]);
    $rekap = $stmt->fetch(PDO::FETCH_ASSOC);

    // Hasil pekerjaan mengikuti waktu perubahan status, bukan tanggal masuk.
    // Status barang adalah kondisi terkini dari nota yang selesai dalam periode.
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            COUNT(DISTINCT t.no_nota) FILTER (WHERE t.status_pengerjaan = 'Selesai' AND t.waktu_update >= ? AND t.waktu_update < ?) AS selesai,
            COUNT(DISTINCT t.no_nota) FILTER (WHERE t.status_pengerjaan = 'Selesai' AND t.waktu_update >= ? AND t.waktu_update < ?) AS selesai_lalu,
            COUNT(DISTINCT t.no_nota) FILTER (WHERE t.status_pengerjaan = 'Dibatalkan' AND t.waktu_update >= ? AND t.waktu_update < ?) AS batal,
            COUNT(DISTINCT t.no_nota) FILTER (
                WHERE t.status_pengerjaan = 'Selesai' AND t.waktu_update >= ? AND t.waktu_update < ?
                  AND (v.tanggal_diambil IS NOT NULL OR v.status_pengambilan IN ('Sudah Diambil', 'Diambil'))
            ) AS sudah_diambil,
            COUNT(DISTINCT t.no_nota) FILTER (
                WHERE t.status_pengerjaan = 'Selesai' AND t.waktu_update >= ? AND t.waktu_update < ?
                  AND v.tanggal_diambil IS NULL AND COALESCE(v.status_pengambilan, 'Belum Diambil') NOT IN ('Sudah Diambil', 'Diambil')
            ) AS di_toko
        FROM public.tracking_servis AS t
        LEFT JOIN public.validasi AS v ON v.no_nota = t.no_nota
        WHERE t.status_pengerjaan IN ('Selesai', 'Dibatalkan')
          AND t.waktu_update >= ? AND t.waktu_update < ?
    SQL);
    $stmt->execute([
        $awal,
        $akhir,
        $awalLalu,
        $awal,
        $awal,
        $akhir,
        $awal,
        $akhir,
        $awal,
        $akhir,
        $awalLalu,
        $akhir,
    ]);
    $rekapSelesai = $stmt->fetch(PDO::FETCH_ASSOC);

    $jumlahMasuk = (int) $rekap['masuk'];
    $jumlahMasukLalu = (int) $rekap['masuk_lalu'];
    $jumlahSelesai = (int) $rekapSelesai['selesai'];
    $jumlahSelesaiLalu = (int) $rekapSelesai['selesai_lalu'];
    $omzet = (float) $rekap['omzet'];
    $omzetLalu = (float) $rekap['omzet_lalu'];

    $stat_selesai = [
        'total' => $jumlahSelesai,
        'label_bawah' => 'Periode lalu: ' . $jumlahSelesaiLalu,
        'badge' => $indikatorPerubahan($jumlahSelesai, $jumlahSelesaiLalu),
    ];
    $stat_servis_masuk = [
        'total' => $jumlahMasuk,
        'label_bawah' => 'Periode lalu: ' . $jumlahMasukLalu,
        'badge' => $indikatorPerubahan($jumlahMasuk, $jumlahMasukLalu),
    ];
    $stat_omzet = [
        'total' => $formatRupiah($omzet),
        'label_bawah' => 'Periode lalu: ' . $formatRupiah($omzetLalu),
        'badge' => $indikatorPerubahan($omzet, $omzetLalu),
    ];
    $hasil_pekerjaan['selesai'] = $jumlahSelesai;
    $hasil_pekerjaan['cancel'] = (int) $rekapSelesai['batal'];
    $status_barang['di_ambil_user'] = (int) $rekapSelesai['sudah_diambil'];
    $status_barang['barang_di_toko'] = (int) $rekapSelesai['di_toko'];
    $jumlahDikerjakan = (int) $rekap['dikerjakan'];
    $jumlahMenunggu = (int) $rekap['menunggu'];

    // Maksimal tujuh titik: harian untuk periode pendek, agregasi untuk periode panjang.
    // Seluruh data dalam interval tetap dijumlahkan oleh SQL, bukan dibuang.
    $langkah = max(1, (int) ceil($jumlahHari / 7));
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT FLOOR((hari - CAST(? AS date))::numeric / CAST(? AS integer))::int AS grup,
               SUM(masuk) AS masuk, SUM(selesai) AS selesai
        FROM (
            SELECT tanggal_masuk::date AS hari, 1 AS masuk, 0 AS selesai
            FROM public.servis WHERE tanggal_masuk >= ? AND tanggal_masuk < ?
            UNION ALL
            SELECT MIN(waktu_update)::date AS hari, 0 AS masuk, 1 AS selesai
            FROM public.tracking_servis
            WHERE status_pengerjaan = 'Selesai' AND waktu_update >= ? AND waktu_update < ?
            GROUP BY no_nota
        ) AS sumber
        GROUP BY grup ORDER BY grup
    SQL);
    $stmt->execute([$awalRentang->format('Y-m-d'), $langkah, $awal, $akhir, $awal, $akhir]);
    $grupGrafik = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $grupGrafik[(int) $row['grup']] = $row;
    }
    for ($i = 0; $i < (int) ceil($jumlahHari / $langkah); $i++) {
        $dari = $awalRentang->modify('+' . ($i * $langkah) . ' days');
        $ke = $awalRentang->modify('+' . min($jumlahHari - 1, ($i + 1) * $langkah - 1) . ' days');
        // Key minggu dipertahankan: label lengkap pada tooltip, label ringkas pada sumbu X.
        $data_grafik_servis[] = [
            'minggu' => $formatRentangDashboard($dari, $ke),
            'label_singkat' => $formatRentangDashboard($dari, $ke, $awalRentang->format('Y') !== $akhirRentang->modify('-1 day')->format('Y')),
            'masuk' => (int) ($grupGrafik[$i]['masuk'] ?? 0),
            'selesai' => (int) ($grupGrafik[$i]['selesai'] ?? 0),
        ];
    }

    // Progress: nota masuk dalam periode yang saat ini sudah selesai.
    // Pembilang dan penyebut menggunakan kelompok nota yang sama.
    $selesaiDariMasuk = (int) $rekap['selesai_dari_masuk'];
    $selesai_text = $selesaiDariMasuk . ' / ' . $jumlahMasuk;
    $persen_selesai = $jumlahMasuk > 0
        ? min(100, round(($selesaiDariMasuk / $jumlahMasuk) * 100))
        : 0;

    // Semua teknisi (termasuk yang belum ditugaskan) tampil berdasarkan database.
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT k.nama_karyawan AS nama,
               COUNT(s.no_nota) FILTER (WHERE s.tanggal_masuk >= ? AND s.tanggal_masuk < ?) AS semua,
               COUNT(s.no_nota) FILTER (
                   WHERE s.status_pengerjaan IN ('Menunggu', 'Diterima', 'Menunggu Persetujuan', 'Dikerjakan')
               ) AS aktif
        FROM public.karyawan AS k
        LEFT JOIN public.servis AS s ON s.id_karyawan = k.id_karyawan
        WHERE k.peran = 'Teknisi'
        GROUP BY k.id_karyawan, k.nama_karyawan
        ORDER BY aktif DESC, k.nama_karyawan ASC
    SQL);
    $stmt->execute([$awal, $akhir]);
    $teknisi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($teknisi as $i => $row) {
        $nama = (string) $row['nama'];
        $aktif = (int) $row['aktif'];
        $beban_teknisi[] = [
            'nama' => $nama,
            'unit' => $aktif,
            'warna' => $aktif > 5 ? 'bg-orange' : 'bg-tosca',
        ];
        $pekerjaan_teknisi[$nama] = (int) $row['semua'];
        $max_beban_teknisi = max($max_beban_teknisi, $aktif);
    }
    $stat_dikerjakan = [
        'total' => $jumlahDikerjakan,
        'label_bawah' => 'Menunggu: ' . $jumlahMenunggu,
        'badge' => count($teknisi) . ' Teknisi',
    ];

    // Pemakaian sparepart dari nota masuk pada periode terpilih.
    // detail_servis tidak mempunyai tanggal pemakaian.
    $total_sparepart = (int) $rekap['jumlah_sparepart'];
    $max_sparepart = max(50, (int) (ceil($total_sparepart / 50) * 50));
    $persen_sparepart = $total_sparepart > 0
        ? min(100, (int) round(($total_sparepart / $max_sparepart) * 100))
        : 0;

    // Jumlah record dihitung untuk seluruh servis selesai, bukan hanya satu halaman.
    // Query pencarian menggunakan parameter terikat untuk mencegah SQL injection.
    $kondisiSelesai = " WHERE s.status_pengerjaan = 'Selesai'
        AND NOT (
            COALESCE(v.status_pembayaran, '') = 'Lunas'
            AND COALESCE(v.status_pengambilan, '') = 'Sudah Diambil'
        )";
    $parameterCari = [];

    if ($selesai_cari !== '') {
        $kondisiSelesai .= " AND (p.nama_pelanggan ILIKE :nama_cari OR s.no_nota ILIKE :nota_cari)";
        $parameterCari = [
            'nama_cari' => '%' . $selesai_cari . '%',
            'nota_cari' => '%' . $selesai_cari . '%',
        ];
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM public.servis AS s '
            . 'JOIN public.pelanggan AS p ON p.id_pelanggan = s.id_pelanggan '
            . 'LEFT JOIN public.validasi AS v ON v.no_nota = s.no_nota'
            . $kondisiSelesai
    );
    $stmt->execute($parameterCari);
    $selesai_total = (int) $stmt->fetchColumn();
    $selesai_jumlah_halaman = max(1, (int) ceil($selesai_total / $selesai_per_halaman));
    $selesai_halaman = min($selesai_halaman, $selesai_jumlah_halaman);
    $offsetSelesai = ($selesai_halaman - 1) * $selesai_per_halaman;
    $selesai_awal = $selesai_total > 0 ? $offsetSelesai + 1 : 0;
    $selesai_akhir = min($offsetSelesai + $selesai_per_halaman, $selesai_total);

    $stmt = $pdo->prepare(<<<SQL
        SELECT s.no_nota AS nota,
               p.nama_pelanggan AS pelanggan,
               p.no_hp AS whatsapp,
               CONCAT_WS(' ', s.jenis_barang, s.merek_dan_spesifikasi) AS perangkat,
               COALESCE(k.nama_karyawan, 'Belum ditugaskan') AS teknisi,
               s.status_pengerjaan AS status,
               s.jenis_layanan AS layanan,
               COALESCE(v.status_pembayaran, 'Belum Lunas') AS status_pembayaran,
               COALESCE(v.status_pengambilan, 'Belum Diambil') AS status_pengambilan
        FROM public.servis AS s
        JOIN public.pelanggan AS p ON p.id_pelanggan = s.id_pelanggan
        LEFT JOIN public.karyawan AS k ON k.id_karyawan = s.id_karyawan
        LEFT JOIN public.validasi AS v ON v.no_nota = s.no_nota
        {$kondisiSelesai}
        ORDER BY s.tanggal_masuk DESC, s.no_nota DESC
        LIMIT :batas OFFSET :offset
    SQL);

    foreach ($parameterCari as $nama => $nilai) {
        $stmt->bindValue(':' . $nama, $nilai, PDO::PARAM_STR);
    }
    $stmt->bindValue(':batas', $selesai_per_halaman, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offsetSelesai, PDO::PARAM_INT);
    $stmt->execute();
    $daftar_servis_selesai = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Antrean persetujuan sengaja tidak difilter tanggal: setiap permintaan
    // yang belum diputuskan harus tetap terlihat oleh admin sampai ditangani.
    $stmt = $pdo->query(<<<'SQL'
        SELECT s.no_nota AS nota,
               p.nama_pelanggan AS pelanggan,
               p.no_hp AS whatsapp,
               CONCAT_WS(' ', s.jenis_barang, s.merek_dan_spesifikasi) AS perangkat,
               COALESCE(k.nama_karyawan, 'Belum ditugaskan') AS teknisi,
               s.persetujuan_pelanggan,
               COALESCE(NULLIF(BTRIM(t.keterangan), ''), 'Teknisi meminta persetujuan pelanggan.') AS keterangan,
               COALESCE(t.waktu_update, s.tanggal_masuk) AS waktu_permintaan
        FROM public.servis AS s
        JOIN public.pelanggan AS p ON p.id_pelanggan = s.id_pelanggan
        LEFT JOIN public.karyawan AS k ON k.id_karyawan = s.id_karyawan
        LEFT JOIN LATERAL (
            SELECT keterangan, waktu_update
            FROM public.tracking_servis
            WHERE no_nota = s.no_nota
              AND status_pengerjaan = 'Menunggu Persetujuan'
            ORDER BY waktu_update DESC, id_tracking DESC
            LIMIT 1
        ) AS t ON TRUE
        WHERE s.status_pengerjaan = 'Menunggu Persetujuan'
          AND NULLIF(BTRIM(s.persetujuan_pelanggan), '') IS NULL
        ORDER BY waktu_permintaan ASC, s.no_nota ASC
    SQL);
    $daftar_menunggu_persetujuan = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('admin/dashboard_data.php: ' . $e->getMessage());
    $errorPage = 'Sebagian data dashboard gagal dimuat. Periksa koneksi database lalu muat ulang.';
}
