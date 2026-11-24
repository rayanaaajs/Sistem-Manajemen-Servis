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

$lihatDetail = array_key_exists('nota', $_GET);
$page_title = $lihatDetail ? 'Detail Servis' : 'Dashboard Admin';
$active_menu = 'Dashboard';
$search_placeholder = 'Cari antrean menunggu persetujuan...';

if (empty($_SESSION['csrf_persetujuan'])) {
    $_SESSION['csrf_persetujuan'] = bin2hex(random_bytes(32));
}

// Simpan hasil verifikasi keputusan di dashboard.php melalui PRG (POST-Redirect-GET).
// Tidak perlu lagi proses_persetujuan.php.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formNota = trim((string) ($_POST['nota'] ?? ''));
    $returnToDetail = ($_POST['return_to'] ?? '') === 'detail';
    $tujuan = 'dashboard.php' . ($returnToDetail && $formNota !== '' && strlen($formNota) <= 10
        ? '?nota=' . rawurlencode($formNota)
        : '');

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

$flashDashboard = $_SESSION['flash_dashboard'] ?? null;
unset($_SESSION['flash_dashboard']);

$zona = new DateTimeZone('Asia/Jakarta');
$sekarang = new DateTimeImmutable('now', $zona);

// Di URL dashboard.php?nota=..., data detail diambil secara khusus.
// Query rekap/dashboard tidak dijalankan pada halaman detail.
if ($lihatDetail) {
    $nota = trim((string) $_GET['nota']);
    $errorPage = '';
    $servis = null;
    $riwayat = [];
    $sparepart = [];
    $totalSparepart = 0.0;

    $rupiah = static fn($nilai): string => 'Rp ' . number_format((float) $nilai, 0, ',', '.');
    $tanggal = static fn($nilai): string => $nilai
        ? date('d M Y, H:i', strtotime((string) $nilai))
        : 'Belum ditentukan';
    $normalisasiWhatsApp = static function (?string $nomor): string {
        $angka = preg_replace('/\D+/', '', (string) $nomor);
        if ($angka === '') return '';
        if (str_starts_with($angka, '0')) return '62' . substr($angka, 1);
        if (str_starts_with($angka, '8')) return '62' . $angka;
        return $angka;
    };
    $urlWA = '';

    if ($nota === '' || strlen($nota) > 10) {
        $errorPage = 'Nomor nota tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare(<<<'SQL'
                SELECT s.no_nota, s.tanggal_masuk, s.jenis_barang,
                       s.merek_dan_spesifikasi, s.kelengkapan_barang,
                       s.kondisi_awal_unit, s.keluhan_kerusakan,
                       s.catatan_cek_fisik, s.persetujuan_bongkar,
                       s.persetujuan_pelanggan, s.status_pengerjaan,
                       s.jenis_layanan, s.estimasi_pengambilan,
                       s.biaya_jasa, s.biaya_urgent,
                       p.nama_pelanggan, p.no_hp, p.alamat,
                       COALESCE(k.nama_karyawan, 'Belum ditugaskan') AS nama_teknisi,
                       COALESCE(v.status_pembayaran, 'Belum Lunas') AS status_pembayaran,
                       COALESCE(v.status_pengambilan, 'Belum Diambil') AS status_pengambilan
                FROM public.servis AS s
                JOIN public.pelanggan AS p ON p.id_pelanggan = s.id_pelanggan
                LEFT JOIN public.karyawan AS k ON k.id_karyawan = s.id_karyawan
                LEFT JOIN public.validasi AS v ON v.no_nota = s.no_nota
                WHERE s.no_nota = :nota
                LIMIT 1
            SQL);
            $stmt->execute(['nota' => $nota]);
            $servis = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($servis === null) {
                $errorPage = 'Servis tidak ditemukan.';
            } else {
                $stmt = $pdo->prepare(<<<'SQL'
                    SELECT t.status_pengerjaan, t.keterangan, t.waktu_update,
                           COALESCE(k.nama_karyawan, 'Sistem') AS nama_karyawan
                    FROM public.tracking_servis AS t
                    LEFT JOIN public.karyawan AS k ON k.id_karyawan = t.id_karyawan
                    WHERE t.no_nota = :nota
                    ORDER BY t.waktu_update ASC, t.id_tracking ASC
                SQL);
                $stmt->execute(['nota' => $nota]);
                $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare(<<<'SQL'
                    SELECT sp.nama_sparepart, ds.jumlah_dipakai, ds.subtotal_harga
                    FROM public.detail_servis AS ds
                    JOIN public.sparepart AS sp ON sp.id_sparepart = ds.id_sparepart
                    WHERE ds.no_nota = :nota
                    ORDER BY sp.nama_sparepart ASC
                SQL);
                $stmt->execute(['nota' => $nota]);
                $sparepart = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sparepart as $item) {
                    $totalSparepart += (float) $item['subtotal_harga'];
                }

                $nomorWA = $normalisasiWhatsApp((string) $servis['no_hp']);
                $pesanWA = 'Halo ' . $servis['nama_pelanggan']
                    . ', kami dari SIPASTI menghubungi terkait servis nota ' . $servis['no_nota'] . '.';
                $urlWA = $nomorWA !== ''
                    ? 'https://wa.me/' . $nomorWA . '?text=' . rawurlencode($pesanWA)
                    : '';
            }
        } catch (PDOException $e) {
            error_log('admin/dashboard_data.php/detail: ' . $e->getMessage());
            $errorPage = 'Gagal mengambil data servis.';
        }
    }
    return;
}

$hariIni = $sekarang->setTime(0, 0);
$besok = $hariIni->modify('+1 day');
$pilihanRentang = ['today' => 1, '7d' => 7, '30d' => 30];
$rentang = (string) ($_GET['range'] ?? 'today');
if (!isset($pilihanRentang[$rentang])) {
    $rentang = 'today';
}

$jumlahHari = $pilihanRentang[$rentang];
$awalRentang = $besok->modify("-{$jumlahHari} days");
$awalRentangLalu = $awalRentang->modify("-{$jumlahHari} days");
$awalBulan = $hariIni->modify('first day of this month');
$awalBulanBerikut = $awalBulan->modify('first day of next month');

$fmtTimestamp = static fn(DateTimeImmutable $date): string => $date->format('Y-m-d H:i:s');
$awal = $fmtTimestamp($awalRentang);
$akhir = $fmtTimestamp($besok);
$awalLalu = $fmtTimestamp($awalRentangLalu);
$awalGrafik = $fmtTimestamp($awalBulan);
$akhirGrafik = $fmtTimestamp($awalBulanBerikut);

$bulanLaporan = $sekarang->format('F Y');
$namaBulan = [
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember',
];
$bulan_laporan = strtr($bulanLaporan, $namaBulan);

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

$urlHalamanSelesai = static function (int $halaman) use ($rentang, $selesai_cari): string {
    return 'dashboard.php?' . http_build_query([
        'range' => $rentang,
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
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Dikerjakan') AS dikerjakan,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Selesai') AS total_selesai,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Dibatalkan') AS total_batal,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Selesai' AND (v.tanggal_diambil IS NOT NULL OR v.status_pengambilan IN ('Sudah Diambil', 'Diambil'))) AS sudah_diambil,
            COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Selesai' AND v.tanggal_diambil IS NULL AND COALESCE(v.status_pengambilan, 'Belum Diambil') NOT IN ('Sudah Diambil', 'Diambil')) AS di_toko,
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
            SELECT no_nota, SUM(subtotal_harga) AS total_sparepart
            FROM public.detail_servis GROUP BY no_nota
        ) AS d ON d.no_nota = s.no_nota
    SQL);
    $stmt->execute([$awal, $akhir, $awalLalu, $awal, $awal, $akhir, $awalLalu, $awal]);
    $rekap = $stmt->fetch(PDO::FETCH_ASSOC);

    // Waktu status benar-benar menjadi Selesai berasal dari tracking_servis.
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            COUNT(DISTINCT no_nota) FILTER (WHERE waktu_update >= ? AND waktu_update < ?) AS selesai,
            COUNT(DISTINCT no_nota) FILTER (WHERE waktu_update >= ? AND waktu_update < ?) AS selesai_lalu
        FROM public.tracking_servis
        WHERE status_pengerjaan = 'Selesai'
    SQL);
    $stmt->execute([$awal, $akhir, $awalLalu, $awal]);
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
    $hasil_pekerjaan['selesai'] = (int) $rekap['total_selesai'];
    $hasil_pekerjaan['cancel'] = (int) $rekap['total_batal'];
    $status_barang['di_ambil_user'] = (int) $rekap['sudah_diambil'];
    $status_barang['barang_di_toko'] = (int) $rekap['di_toko'];
    $jumlahDikerjakan = (int) $rekap['dikerjakan'];
    $jumlahMenunggu = (int) $rekap['menunggu'];

    // Grafik lima kemungkinan minggu kalender dalam satu bulan (7 hari per segmen).
    $stmt = $pdo->prepare(<<<'SQL'
        SELECT minggu, SUM(masuk) AS masuk, SUM(selesai) AS selesai
        FROM (
            SELECT CEIL(EXTRACT(DAY FROM tanggal_masuk) / 7.0)::int AS minggu,
                   1 AS masuk, 0 AS selesai
            FROM public.servis
            WHERE tanggal_masuk >= ? AND tanggal_masuk < ?
            UNION ALL
            SELECT CEIL(EXTRACT(DAY FROM waktu_update) / 7.0)::int AS minggu,
                   0 AS masuk, 1 AS selesai
            FROM public.tracking_servis
            WHERE status_pengerjaan = 'Selesai'
              AND waktu_update >= ? AND waktu_update < ?
        ) AS sumber
        GROUP BY minggu
        ORDER BY minggu
    SQL);
    $stmt->execute([$awalGrafik, $akhirGrafik, $awalGrafik, $akhirGrafik]);
    $grafikDb = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $grafikDb[(int) $row['minggu']] = $row;
    }
    $jumlahMinggu = (int) ceil((int) $awalBulan->format('t') / 7);
    $totalMasukBulan = 0;
    $totalSelesaiBulan = 0;
    for ($i = 1; $i <= $jumlahMinggu; $i++) {
        $masuk = (int) ($grafikDb[$i]['masuk'] ?? 0);
        $selesai = (int) ($grafikDb[$i]['selesai'] ?? 0);
        $data_grafik_servis[] = ['minggu' => 'Mg ' . $i, 'masuk' => $masuk, 'selesai' => $selesai];
        $totalMasukBulan += $masuk;
        $totalSelesaiBulan += $selesai;
    }
    $selesai_text = $totalSelesaiBulan . ' / ' . $totalMasukBulan;
    $persen_selesai = $totalMasukBulan > 0
        ? min(100, round(($totalSelesaiBulan / $totalMasukBulan) * 100))
        : 0;

    // Semua teknisi (termasuk yang belum ditugaskan) tampil berdasarkan database.
    $stmt = $pdo->query(<<<'SQL'
        SELECT k.nama_karyawan AS nama,
               COUNT(s.no_nota) AS semua,
               COUNT(s.no_nota) FILTER (
                   WHERE s.status_pengerjaan IN ('Menunggu', 'Diterima', 'Menunggu Persetujuan', 'Dikerjakan')
               ) AS aktif
        FROM public.karyawan AS k
        LEFT JOIN public.servis AS s ON s.id_karyawan = k.id_karyawan
        WHERE k.peran = 'Teknisi'
        GROUP BY k.id_karyawan, k.nama_karyawan
        ORDER BY aktif DESC, k.nama_karyawan ASC
    SQL);
    $teknisi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($teknisi as $i => $row) {
        $nama = (string) $row['nama'];
        $aktif = (int) $row['aktif'];
        $beban_teknisi[] = [
            'nama' => $nama,
            'unit' => $aktif,
            'warna' => $i === 0 ? 'bg-orange' : 'bg-tosca',
        ];
        $pekerjaan_teknisi[$nama] = (int) $row['semua'];
        $max_beban_teknisi = max($max_beban_teknisi, $aktif);
    }
    $stat_dikerjakan = [
        'total' => $jumlahDikerjakan,
        'label_bawah' => 'Menunggu: ' . $jumlahMenunggu,
        'badge' => count($teknisi) . ' Teknisi',
    ];

    // Jumlah jenis sparepart (bukan jumlah seluruh stok fisik).
    $total_sparepart = (int) $pdo->query('SELECT COUNT(*) FROM public.sparepart')->fetchColumn();
    $max_sparepart = max(50, (int) (ceil($total_sparepart / 50) * 50));
    $persen_sparepart = $total_sparepart > 0
        ? min(100, (int) round(($total_sparepart / $max_sparepart) * 100))
        : 0;

    // Jumlah record dihitung untuk seluruh servis selesai, bukan hanya satu halaman.
    // Query pencarian menggunakan parameter terikat untuk mencegah SQL injection.
    $kondisiSelesai = " WHERE s.status_pengerjaan = 'Selesai'";
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
            . 'JOIN public.pelanggan AS p ON p.id_pelanggan = s.id_pelanggan'
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
               COALESCE(v.status_pembayaran, 'Belum Lunas') AS status_pembayaran
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
