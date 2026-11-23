<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

$no_nota = trim($_GET['nota'] ?? $_POST['no_nota'] ?? '');

if ($no_nota === '') {
    echo "<script>alert('Nomor nota tidak ditemukan di URL!'); window.location.href='antrean.php';</script>";
    exit;
}

function jsAlertBackEdit($pesan) {
    $jsonPesan = json_encode((string)$pesan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    echo "<script>alert($jsonPesan); window.history.back();</script>";
    exit;
}

// ==============================================================================
// 1. PROSES SIMPAN PERUBAHAN DATA ANTREAN + VALIDASI + SINKRONISASI TABEL VALIDASI
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan       = (int)($_POST['id_pelanggan'] ?? 0);
    $nama_pelanggan     = trim($_POST['pelanggan'] ?? '');
    $no_hp              = preg_replace('/[\s\-\(\)\+]/', '', trim($_POST['nomor'] ?? ''));
    $alamat             = trim($_POST['alamat'] ?? '');

    $jenis_barang       = trim($_POST['jenis_barang'] ?? '');
    $merek_spesifikasi  = trim($_POST['merek_spesifikasi'] ?? '');
    $kelengkapan        = trim($_POST['kelengkapan'] ?? '');
    $kondisi_unit       = trim($_POST['kondisi_unit'] ?? 'Hidup');
    $password_perangkat = trim($_POST['password_perangkat'] ?? '');

    $biaya_jasa_raw     = trim($_POST['biaya_jasa'] ?? '0');
    $biaya_jasa         = (int)preg_replace('/[^0-9\-]/', '', $biaya_jasa_raw);

    $keluhan            = trim($_POST['keluhan'] ?? '');
    $catatan_cek_fisik  = trim($_POST['catatan_cek_fisik'] ?? '');
    $id_karyawan        = (int)($_POST['id_karyawan'] ?? 0);
    $tgl_masuk_input    = trim($_POST['tgl_masuk'] ?? '');
    $tgl_estimasi_input = trim($_POST['tgl_estimasi'] ?? '');
    $estimasi_lama      = trim($_POST['estimasi_lama'] ?? '');

    $jenis_layanan      = trim($_POST['jenis_layanan'] ?? 'Reguler');
    $persetujuan_raw    = trim($_POST['persetujuan_bongkar'] ?? 'setuju');

    $status_pembayaran  = trim($_POST['status_pembayaran'] ?? 'Belum Lunas');
    $status_pengambilan = trim($_POST['status_pengambilan'] ?? 'Belum Diambil');

    // --- VALIDASI INPUT SERVER-SIDE ---
    if ($nama_pelanggan === '' || mb_strlen($nama_pelanggan) < 2) {
        jsAlertBackEdit('Nama pelanggan wajib diisi (minimal 2 karakter)!');
    }
    if (!preg_match('/^(08|62)[0-9]{8,13}$/', $no_hp)) {
        jsAlertBackEdit('Nomor WhatsApp tidak valid! Gunakan awalan 08 atau 62 (10–15 digit angka).');
    }
    if ($alamat === '') {
        jsAlertBackEdit('Alamat pelanggan wajib diisi!');
    }
    if ($jenis_barang === '' || $merek_spesifikasi === '') {
        jsAlertBackEdit('Jenis barang serta merek & spesifikasi wajib diisi!');
    }
    if (!in_array($kondisi_unit, ['Hidup', 'Mati', 'Blank'], true)) {
        jsAlertBackEdit('Pilihan kondisi awal unit tidak valid!');
    }
    if ($biaya_jasa < 0) {
        jsAlertBackEdit('Biaya jasa servis tidak boleh kurang dari 0!');
    }
    if ($keluhan === '' || $id_karyawan <= 0) {
        jsAlertBackEdit('Keluhan kerusakan dan teknisi penanggung jawab wajib diisi!');
    }
    if (!strtotime($tgl_masuk_input) || !strtotime($tgl_estimasi_input)) {
        jsAlertBackEdit('Format tanggal masuk maupun estimasi selesai tidak valid!');
    }
    $tglMasukYmd    = date('Y-m-d', strtotime($tgl_masuk_input));
    $tglEstimasiYmd = date('Y-m-d', strtotime($tgl_estimasi_input));
    if ($tglEstimasiYmd < $tglMasukYmd) {
        jsAlertBackEdit('Tanggal Estimasi Selesai tidak boleh lebih awal dari tanggal Servis Masuk!');
    }
    if (!in_array($jenis_layanan, ['Reguler', 'Express'], true)) {
        jsAlertBackEdit('Jenis layanan harus Reguler atau Express!');
    }
    if (!in_array($persetujuan_raw, ['setuju', 'tidak_setuju'], true)) {
        jsAlertBackEdit('Pilihan persetujuan bongkar tidak valid!');
    }
    if (!in_array($status_pembayaran, ['Belum Lunas', 'Lunas'], true)) {
        jsAlertBackEdit('Status pembayaran tidak valid!');
    }
    if (!in_array($status_pengambilan, ['Belum Diambil', 'Sudah Diambil'], true)) {
        jsAlertBackEdit('Status pengambilan tidak valid!');
    }

    $biaya_urgent        = ($jenis_layanan === 'Express') ? 50000 : 0;
    $persetujuan_bongkar = ($persetujuan_raw === 'tidak_setuju') ? 'Tidak' : 'Ya';

    // Default nilai database sesuai insert di tambah_antrean.php
    $db_status_pembayaran  = ($status_pembayaran === 'Lunas') ? 'Lunas' : 'Belum Dibayar';
    $db_status_pengambilan = ($status_pengambilan === 'Sudah Diambil') ? 'Sudah Diambil' : 'Belum Diambil';

    try {
        // 1) Samakan nilai persetujuan_bongkar dengan constraint CHECK di Supabase
        $stmtCheck = $pdo->query("
            SELECT pg_get_constraintdef(oid) 
            FROM pg_constraint 
            WHERE conname = 'servis_persetujuan_barang_check' 
            LIMIT 1
        ");
        $checkDef = (string)$stmtCheck->fetchColumn();
        if ($checkDef !== '' && preg_match_all("/'([^']+)'/", $checkDef, $matches) && !empty($matches[1])) {
            $allowedVals = $matches[1];
            if ($persetujuan_raw === 'tidak_setuju') {
                foreach ($allowedVals as $val) {
                    if (stripos($val, 'tidak') !== false || stripos($val, 'tolak') !== false || strtolower($val) === 'no') {
                        $persetujuan_bongkar = $val;
                        break;
                    }
                }
            } else {
                foreach ($allowedVals as $val) {
                    if (stripos($val, 'tidak') === false && stripos($val, 'tolak') === false && strtolower($val) !== 'no') {
                        $persetujuan_bongkar = $val;
                        break;
                    }
                }
            }
        }

        // 2) Cek constraint tabel validasi agar nilai status_pembayaran & status_pengambilan 100% sesuai DB
        $stmtValConstraints = $pdo->query("
            SELECT conname, pg_get_constraintdef(oid) AS def
            FROM pg_constraint
            WHERE conrelid = 'validasi'::regclass AND contype = 'c'
        ");
        $valConstraints = $stmtValConstraints->fetchAll(PDO::FETCH_ASSOC);
        foreach ($valConstraints as $vc) {
            $def = $vc['def'] ?? '';
            if (stripos($def, 'status_pembayaran') !== false && preg_match_all("/'([^']+)'/", $def, $mPay) && !empty($mPay[1])) {
                if ($status_pembayaran === 'Lunas') {
                    foreach ($mPay[1] as $opt) {
                        if (strtolower(trim($opt)) === 'lunas') {
                            $db_status_pembayaran = $opt;
                            break;
                        }
                    }
                } else {
                    foreach ($mPay[1] as $opt) {
                        if (strtolower(trim($opt)) !== 'lunas') {
                            $db_status_pembayaran = $opt;
                            break;
                        }
                    }
                }
            }
            if (stripos($def, 'status_pengambilan') !== false && preg_match_all("/'([^']+)'/", $def, $mTake) && !empty($mTake[1])) {
                if ($status_pengambilan === 'Sudah Diambil') {
                    foreach ($mTake[1] as $opt) {
                        if (stripos($opt, 'sudah') !== false) {
                            $db_status_pengambilan = $opt;
                            break;
                        }
                    }
                } else {
                    foreach ($mTake[1] as $opt) {
                        if (stripos($opt, 'belum') !== false) {
                            $db_status_pengambilan = $opt;
                            break;
                        }
                    }
                }
            }
        }

        $pdo->beginTransaction();

        // Update data pelanggan
        if ($id_pelanggan > 0) {
            $stmtPel = $pdo->prepare("
                UPDATE pelanggan 
                SET nama_pelanggan = :nama, no_hp = :no_hp, alamat = :alamat
                WHERE id_pelanggan = :id
            ");
            $stmtPel->execute([
                'nama'   => $nama_pelanggan,
                'no_hp'  => $no_hp,
                'alamat' => $alamat,
                'id'     => $id_pelanggan
            ]);
        }

        // Ambil data lama (jam masuk & status_pengerjaan saat ini dari teknisi)
        $stmtOld = $pdo->prepare("SELECT tanggal_masuk, status_pengerjaan FROM servis WHERE no_nota = :nota");
        $stmtOld->execute(['nota' => $no_nota]);
        $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);
        $status_saat_ini = $oldData['status_pengerjaan'] ?? 'Menunggu';

        $jamMasuk = !empty($oldData['tanggal_masuk']) ? date('H:i:s', strtotime($oldData['tanggal_masuk'])) : date('H:i:s');
        $tanggal_masuk_baru = $tglMasukYmd . ' ' . $jamMasuk;
        $estimasi_baru      = $tglEstimasiYmd . ' ' . $jamMasuk;

        // Update data servis (tanpa mengubah status_pengerjaan milik teknisi)
        $stmtUpd = $pdo->prepare("
            UPDATE servis SET
                tanggal_masuk         = :tanggal_masuk,
                jenis_barang          = :jenis_barang,
                merek_dan_spesifikasi = :merek_spesifikasi,
                kelengkapan_barang    = :kelengkapan,
                kondisi_awal_unit     = :kondisi_unit,
                password_perangkat    = :password_perangkat,
                keluhan_kerusakan     = :keluhan,
                catatan_cek_fisik     = :catatan_cek_fisik,
                persetujuan_bongkar   = :persetujuan_bongkar,
                biaya_jasa            = :biaya_jasa,
                id_karyawan           = :id_karyawan,
                estimasi_pengambilan  = :estimasi_pengambilan,
                jenis_layanan         = :jenis_layanan,
                biaya_urgent          = :biaya_urgent
            WHERE no_nota = :no_nota
        ");
        $stmtUpd->execute([
            'tanggal_masuk'        => $tanggal_masuk_baru,
            'jenis_barang'         => $jenis_barang,
            'merek_spesifikasi'    => $merek_spesifikasi,
            'kelengkapan'          => $kelengkapan,
            'kondisi_unit'         => $kondisi_unit,
            'password_perangkat'   => $password_perangkat,
            'keluhan'              => $keluhan,
            'catatan_cek_fisik'    => $catatan_cek_fisik,
            'persetujuan_bongkar'  => $persetujuan_bongkar,
            'biaya_jasa'           => $biaya_jasa,
            'id_karyawan'          => $id_karyawan,
            'estimasi_pengambilan' => $estimasi_baru,
            'jenis_layanan'        => $jenis_layanan,
            'biaya_urgent'         => $biaya_urgent,
            'no_nota'              => $no_nota
        ]);

        // Sinkronisasi ke tabel validasi (status pembayaran & pengambilan)
        $cekVal = $pdo->prepare("SELECT no_nota FROM validasi WHERE no_nota = :nota LIMIT 1");
        $cekVal->execute(['nota' => $no_nota]);

        if ($cekVal->fetchColumn()) {
            $stmtVal = $pdo->prepare("
                UPDATE validasi
                SET status_pembayaran  = :bayar,
                    status_pengambilan = :ambil
                WHERE no_nota = :nota
            ");
            $stmtVal->execute([
                'bayar' => $db_status_pembayaran,
                'ambil' => $db_status_pengambilan,
                'nota'  => $no_nota
            ]);
        } else {
            $stmtValIns = $pdo->prepare("
                INSERT INTO validasi (no_nota, status_pembayaran, status_pengambilan)
                VALUES (:nota, :bayar, :ambil)
            ");
            $stmtValIns->execute([
                'nota'  => $no_nota,
                'bayar' => $db_status_pembayaran,
                'ambil' => $db_status_pengambilan
            ]);
        }

        // Catat ke tracking_servis jika estimasi waktu diperpanjang
        $aktor_id = (int)($_SESSION['user_id'] ?? $id_karyawan);
        if ($estimasi_lama !== '' && $tglEstimasiYmd > $estimasi_lama) {
            $stmtTrack = $pdo->prepare("
                INSERT INTO tracking_servis (no_nota, keterangan, waktu_update, status_pengerjaan, id_karyawan)
                VALUES (:no_nota, :keterangan, NOW(), :status, :id_karyawan)
            ");
            $stmtTrack->execute([
                'no_nota'     => $no_nota,
                'keterangan'  => 'Estimasi waktu pengerjaan diperpanjang menjadi ' . date('d M Y', strtotime($estimasi_baru)) . '.',
                'status'      => $status_saat_ini,
                'id_karyawan' => $aktor_id
            ]);
        }

        $pdo->commit();

        echo "<script>alert('Perubahan data nota " . addslashes($no_nota) . " berhasil disimpan!'); window.location.href='antrean.php';</script>";
        exit;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>alert('Gagal memperbarui data: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
}

// ==============================================================================
// 2. AMBIL DATA ANTREAN, SPAREPART OTOMATIS, & TEKNISI AKTIF
// ==============================================================================
$data = null;
$total_sparepart = 0;
$daftar_sparepart_terpakai = [];
$teknisi_aktif_list = [];

try {
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            p.nama_pelanggan,
            p.no_hp,
            p.alamat,
            v.status_pembayaran,
            v.status_pengambilan
        FROM servis s
        LEFT JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
        LEFT JOIN validasi v ON s.no_nota = v.no_nota
        WHERE s.no_nota = :nota
        LIMIT 1
    ");
    $stmt->execute(['nota' => $no_nota]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo "<script>alert('Data nota tidak ditemukan di database!'); window.location.href='antrean.php';</script>";
        exit;
    }

    $stmtSp = $pdo->prepare("
        SELECT ds.*, sp.*
        FROM detail_servis ds
        JOIN sparepart sp ON ds.id_sparepart = sp.id_sparepart
        WHERE ds.no_nota = :nota
    ");
    $stmtSp->execute(['nota' => $no_nota]);
    $rawSp = $stmtSp->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rawSp as $sp) {
        $qty   = (int)($sp['jumlah_dipakai'] ?? $sp['jumlah'] ?? $sp['qty'] ?? 1);
        $harga = (int)($sp['harga_satuan'] ?? $sp['harga_jual'] ?? $sp['harga'] ?? 0);
        $sub   = (int)($sp['subtotal_harga'] ?? $sp['subtotal'] ?? ($qty * $harga));
        $namaSp = $sp['nama_sparepart'] ?? $sp['nama_barang'] ?? 'Sparepart';

        $daftar_sparepart_terpakai[] = [
            'nama_sparepart' => $namaSp,
            'jumlah'         => $qty,
            'subtotal'       => $sub
        ];
        $total_sparepart += $sub;
    }

    $stmtTek = $pdo->prepare("
        SELECT id_karyawan, nama_karyawan 
        FROM karyawan 
        WHERE (LOWER(peran) = 'teknisi' AND LOWER(COALESCE(status, 'aktif')) = 'aktif')
           OR id_karyawan = :current_id
        ORDER BY nama_karyawan ASC
    ");
    $stmtTek->execute(['current_id' => (int)($data['id_karyawan'] ?? 0)]);
    $teknisi_aktif_list = $stmtTek->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    die("Error memuat halaman Edit Antrean: " . htmlspecialchars($e->getMessage()));
}

$kondisi_val        = $data['kondisi_awal_unit'] ?: 'Hidup';
$layanan_val        = $data['jenis_layanan'] ?: 'Reguler';
$status_val         = $data['status_pengerjaan'] ?: 'Menunggu';
$status_bayar_val   = (strtolower(trim((string)($data['status_pembayaran'] ?? ''))) === 'lunas') ? 'Lunas' : 'Belum Lunas';
$status_ambil_val   = (strtolower(trim((string)($data['status_pengambilan'] ?? ''))) === 'sudah diambil') ? 'Sudah Diambil' : 'Belum Diambil';
$persetujuan_val    = (stripos((string)($data['persetujuan_bongkar'] ?? ''), 'tidak') !== false) ? 'tidak_setuju' : 'setuju';
$biaya_jasa_val     = (int)($data['biaya_jasa'] ?? 0);
$biaya_exp_val      = ($layanan_val === 'Express') ? 50000 : 0;
$total_biaya_val    = $biaya_jasa_val + $biaya_exp_val + $total_sparepart;

$tgl_masuk_val    = !empty($data['tanggal_masuk']) ? date('Y-m-d', strtotime($data['tanggal_masuk'])) : date('Y-m-d');
$tgl_estimasi_val = !empty($data['estimasi_pengambilan']) ? date('Y-m-d', strtotime($data['estimasi_pengambilan'])) : date('Y-m-d', strtotime($tgl_masuk_val . ' +3 days'));

$page_title = 'Edit Antrean';
$active_menu = 'antrean';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <!-- HEADER INFO NOTA (STATUS PENGERJAAN READ-ONLY DARI TEKNISI) -->
      <div class="mb-4 flex flex-wrap items-center justify-between gap-2 bg-white-light px-6 py-4 rounded-2xl border border-white-hover/60">
        <div class="flex items-center gap-3">
          <span class="text-xs font-semibold text-white-dark-hover uppercase tracking-wider">Nomor Nota</span>
          <span class="px-3.5 py-1 rounded-full bg-tosca-light text-tosca-dark text-sm font-bold"><?= e($data['no_nota']) ?></span>
        </div>
        <div class="flex items-center gap-2 text-xs font-medium text-white-dark-hover">
          <span>Status Pengerjaan (Teknisi):</span>
          <?= badgeStatus(convertStatusAdmin($status_val)) ?>
        </div>
      </div>

      <!-- FORM 3 KOLOM -->
      <form
        action="edit_antrean.php?nota=<?= urlencode($data['no_nota']) ?>"
        method="POST"
        id="formEditAntrean"
        class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start"
      >
        <input type="hidden" name="no_nota" id="hiddenNoNota" value="<?= e($data['no_nota']) ?>" />
        <input type="hidden" name="id_pelanggan" value="<?= (int)($data['id_pelanggan'] ?? 0) ?>" />
        <input type="hidden" name="estimasi_lama" value="<?= e($tgl_estimasi_val) ?>" />

        <!-- KOLOM 1: DATA PELANGGAN -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
              Data Pelanggan
            </h2>

            <div class="space-y-5 lg:space-y-6">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pelanggan</label>
                <input
                  type="text"
                  name="pelanggan"
                  id="inputPelangganEdit"
                  value="<?= e($data['nama_pelanggan'] ?? '') ?>"
                  required
                  minlength="2"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
                <input
                  type="tel"
                  name="nomor"
                  id="inputNomorEdit"
                  value="<?= e($data['no_hp'] ?? '') ?>"
                  required
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
                <input
                  type="text"
                  name="alamat"
                  id="inputAlamatEdit"
                  value="<?= e($data['alamat'] ?? '') ?>"
                  required
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- KOLOM 2: DATA PERANGKAT + RINCIAN BIAYA DI BAWAHNYA -->
        <div class="flex flex-col gap-4">
          <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
            <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
              Data Perangkat
            </h2>

            <div class="space-y-5 lg:space-y-6">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Barang</label>
                <input
                  type="text"
                  name="jenis_barang"
                  id="inputJenisBarangEdit"
                  value="<?= e($data['jenis_barang'] ?? '') ?>"
                  required
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Merek dan Spesifikasi</label>
                <input
                  type="text"
                  name="merek_spesifikasi"
                  id="inputMerekEdit"
                  value="<?= e($data['merek_dan_spesifikasi'] ?? '') ?>"
                  required
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Kelengkapan</label>
                <input
                  type="text"
                  name="kelengkapan"
                  value="<?= e($data['kelengkapan_barang'] ?? '') ?>"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Kondisi Awal Unit</label>
                <div class="grid grid-cols-3 gap-2.5" id="groupKondisiUnit">
                  <?php foreach (['Hidup', 'Mati', 'Blank'] as $optKondisi): ?>
                    <?php $isAktif = (strcasecmp($kondisi_val, $optKondisi) === 0); ?>
                    <button
                      type="button"
                      data-val="<?= $optKondisi ?>"
                      class="kondisi-btn py-3 px-4 rounded-2xl text-sm transition text-center cursor-pointer <?= $isAktif ? 'bg-tosca-dark text-white-light font-semibold shadow-xs' : 'border border-white-active bg-white-light text-blue-darker hover:bg-white-hover font-medium' ?>"
                    >
                      <?= $optKondisi ?>
                    </button>
                  <?php endforeach; ?>
                </div>
                <input type="hidden" name="kondisi_unit" id="inputKondisiUnit" value="<?= e($kondisi_val) ?>" />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password Perangkat</label>
                <div class="relative">
                  <input
                    type="password"
                    id="passwordPerangkat"
                    name="password_perangkat"
                    value="<?= e($data['password_perangkat'] ?? '') ?>"
                    class="w-full pl-4 pr-11 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                  />
                  <button
                    type="button"
                    id="togglePasswordBtn"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-white-dark-hover hover:text-blue-darker cursor-pointer"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- KARTU RINCIAN BIAYA DI BAWAH DATA PERANGKAT -->
          <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60">
            <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-5">
              Rincian Biaya
            </h2>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Biaya Jasa Servis (Rp)</label>
                <input
                  type="number"
                  min="0"
                  step="1000"
                  name="biaya_jasa"
                  id="inputBiayaJasa"
                  value="<?= $biaya_jasa_val ?>"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-semibold text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div class="rounded-2xl bg-white-light-active border border-white-hover p-4 space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-white-dark-hover font-medium">Biaya Layanan Express</span>
                  <span id="displayBiayaExpress" class="font-semibold text-blue-darker">
                    Rp <?= number_format($biaya_exp_val, 0, ',', '.') ?>
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-white-dark-hover font-medium">
                    Sparepart Terpakai (<?= count($daftar_sparepart_terpakai) ?> item)
                  </span>
                  <span id="displayBiayaSparepart" data-sparepart="<?= $total_sparepart ?>" class="font-semibold text-blue-darker">
                    Rp <?= number_format($total_sparepart, 0, ',', '.') ?>
                  </span>
                </div>

                <?php if (!empty($daftar_sparepart_terpakai)): ?>
                  <div class="pt-1 pl-2 border-l-2 border-tosca-light-active space-y-1 text-xs text-white-dark-hover">
                    <?php foreach ($daftar_sparepart_terpakai as $sp): ?>
                      <div class="flex justify-between">
                        <span><?= e($sp['nama_sparepart']) ?> (x<?= (int)$sp['jumlah'] ?>)</span>
                        <span>Rp <?= number_format((int)$sp['subtotal'], 0, ',', '.') ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="border-t border-white-hover pt-2.5 flex items-center justify-between">
                  <span class="font-bold text-blue-darker">Total Biaya</span>
                  <span id="displayTotalBiaya" class="text-base font-bold text-orange">
                    Rp <?= number_format($total_biaya_val, 0, ',', '.') ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- KOLOM 3: KELUHAN DAN PENANGANAN -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60 flex flex-col justify-between">
          <div class="space-y-5 lg:space-y-6">
            <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
              Keluhan dan Penanganan
            </h2>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Keluhan Kerusakan</label>
              <input
                type="text"
                name="keluhan"
                id="inputKeluhanEdit"
                value="<?= e($data['keluhan_kerusakan'] ?? '') ?>"
                required
                class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
              />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Catatan Cek Fisik</label>
              <input
                type="text"
                name="catatan_cek_fisik"
                value="<?= e($data['catatan_cek_fisik'] ?? '') ?>"
                class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
              />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Teknisi Aktif</label>
              <div class="relative">
                <select
                  name="id_karyawan"
                  id="selectTeknisiAktif"
                  required
                  class="w-full pl-9 pr-8 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                >
                  <?php foreach ($teknisi_aktif_list as $tek): ?>
                    <option
                      value="<?= (int)$tek['id_karyawan'] ?>"
                      data-nama="<?= htmlspecialchars($tek['nama_karyawan']) ?>"
                      <?= ((int)$tek['id_karyawan'] === (int)($data['id_karyawan'] ?? 0)) ? 'selected' : '' ?>
                    >
                      <?= htmlspecialchars($tek['nama_karyawan']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <div
                    id="teknisiAvatarInitial"
                    class="w-5 h-5 rounded-full bg-orange-light-active text-orange text-[9px] font-bold flex items-center justify-center uppercase"
                  >
                    TK
                  </div>
                </div>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Servis Masuk</label>
                <input
                  type="date"
                  name="tgl_masuk"
                  id="inputTglMasuk"
                  value="<?= e($tgl_masuk_val) ?>"
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Estimasi Selesai</label>
                <input
                  type="date"
                  name="tgl_estimasi"
                  id="inputTglEstimasi"
                  value="<?= e($tgl_estimasi_val) ?>"
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Layanan</label>
              <div class="grid grid-cols-2 gap-3" id="groupLayanan">
                <?php foreach (['Reguler', 'Express'] as $optLayanan): ?>
                  <?php $isLayAktif = (strcasecmp($layanan_val, $optLayanan) === 0); ?>
                  <button
                    type="button"
                    data-val="<?= $optLayanan ?>"
                    class="layanan-btn py-3 px-4 rounded-2xl text-sm transition text-center cursor-pointer <?= $isLayAktif ? 'bg-tosca-dark text-white-light font-semibold shadow-xs' : 'border border-white-active bg-white-light text-blue-darker hover:bg-white-hover font-medium' ?>"
                  >
                    <?= $optLayanan ?>
                  </button>
                <?php endforeach; ?>
              </div>
              <input type="hidden" name="jenis_layanan" id="inputJenisLayanan" value="<?= e($layanan_val) ?>" />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Persetujuan Bongkar</label>
              <div class="grid grid-cols-2 gap-3" id="groupPersetujuan">
                <label
                  class="persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border bg-white-light cursor-pointer transition select-none <?= $persetujuan_val === 'setuju' ? 'border-blue-darker/60' : 'border-white-active hover:bg-white-hover' ?>"
                >
                  <input type="radio" name="persetujuan_bongkar" value="setuju" <?= $persetujuan_val === 'setuju' ? 'checked' : '' ?> class="sr-only" />
                  <div class="check-box-icon w-5 h-5 rounded-md flex items-center justify-center text-xs font-bold transition <?= $persetujuan_val === 'setuju' ? 'bg-tosca-dark text-white-light' : 'border border-white-active bg-white-light text-transparent' ?>">
                    ✓
                  </div>
                  <span class="text-sm text-blue-darker <?= $persetujuan_val === 'setuju' ? 'font-semibold' : 'font-medium' ?>">Setuju Dibongkar</span>
                </label>

                <label
                  class="persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border bg-white-light cursor-pointer transition select-none <?= $persetujuan_val === 'tidak_setuju' ? 'border-blue-darker/60' : 'border-white-active hover:bg-white-hover' ?>"
                >
                  <input type="radio" name="persetujuan_bongkar" value="tidak_setuju" <?= $persetujuan_val === 'tidak_setuju' ? 'checked' : '' ?> class="sr-only" />
                  <div class="check-box-icon w-5 h-5 rounded-md flex items-center justify-center text-xs font-bold transition <?= $persetujuan_val === 'tidak_setuju' ? 'bg-tosca-dark text-white-light' : 'border border-white-active bg-white-light text-transparent' ?>">
                    ✓
                  </div>
                  <span class="text-sm text-blue-darker <?= $persetujuan_val === 'tidak_setuju' ? 'font-semibold' : 'font-medium' ?>">Tidak Setuju</span>
                </label>
              </div>
            </div>

            <!-- DROPDOWN MANUAL PEMBAYARAN & PENGAMBILAN -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-blue-darker mb-1.5">Status Pembayaran</label>
                <div class="relative">
                  <select
                    name="status_pembayaran"
                    id="selectStatusPembayaran"
                    class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                  >
                    <option value="Belum Lunas" <?= $status_bayar_val === 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                    <option value="Lunas" <?= $status_bayar_val === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-blue-darker mb-1.5">Status Pengambilan</label>
                <div class="relative">
                  <select
                    name="status_pengambilan"
                    id="selectStatusPengambilan"
                    class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                  >
                    <option value="Belum Diambil" <?= $status_ambil_val === 'Belum Diambil' ? 'selected' : '' ?>>Belum Diambil</option>
                    <option value="Sudah Diambil" <?= $status_ambil_val === 'Sudah Diambil' ? 'selected' : '' ?>>Sudah Diambil</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-10 pt-4 flex items-center justify-end gap-3.5">
            <a
              href="antrean.php"
              class="px-8 py-3 rounded-full border border-tosca-dark hover:bg-tosca-dark-hover text-tosca-dark hover:text-white-light text-sm font-semibold transition text-center cursor-pointer"
            >
              Batal
            </a>
            <button
              type="button"
              id="btnBukaModalSimpan"
              class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer"
            >
              Simpan Perubahan
            </button>
          </div>
        </div>
      </form>

      <!-- ==============================================================================
           MODAL KONFIRMASI SIMPAN PERUBAHAN
           ============================================================================== -->
      <div id="modalKonfirmasiSimpan" onclick="if(event.target === this) tutupModalSimpan()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-md rounded-3xl p-6 sm:p-7 shadow-2xl border border-gray-200">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-orange-light mb-4">
            <svg class="h-7 w-7 text-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <h3 class="text-xl font-bold text-blue-darker text-center mb-1.5">Simpan Perubahan?</h3>
          <p class="text-xs text-white-dark-hover text-center mb-5 leading-relaxed">
            Apakah Anda yakin ingin menyimpan perubahan pada nota <strong id="previewNotaSimpan" class="text-blue-darker"></strong>?
          </p>

          <div class="rounded-2xl bg-white-light-active border border-white-hover p-4 space-y-2.5 text-xs mb-6">
            <div class="flex justify-between gap-3">
              <span class="text-white-dark-hover font-medium">Status Pembayaran</span>
              <span id="previewBayarSimpan" class="font-bold text-blue-darker text-right">-</span>
            </div>
            <div class="flex justify-between gap-3 border-t border-white-hover pt-2.5">
              <span class="text-white-dark-hover font-medium">Status Pengambilan</span>
              <span id="previewAmbilSimpan" class="font-bold text-blue-darker text-right">-</span>
            </div>
            <div class="flex justify-between gap-3 border-t border-white-hover pt-2.5">
              <span class="text-white-dark-hover font-medium">Jenis Layanan</span>
              <span id="previewLayananSimpan" class="font-bold text-blue-darker text-right">-</span>
            </div>
            <div class="flex justify-between gap-3 border-t border-white-hover pt-2.5">
              <span class="text-white-dark-hover font-medium">Total Biaya</span>
              <span id="previewTotalSimpan" class="font-bold text-orange text-right">-</span>
            </div>
          </div>

          <div class="flex items-center justify-center gap-3">
            <button
              type="button"
              onclick="tutupModalSimpan()"
              class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer"
            >
              Periksa Lagi
            </button>
            <button
              type="button"
              id="btnConfirmSimpan"
              class="w-full rounded-full bg-orange hover:bg-orange-hover py-3 text-sm font-semibold text-white-light transition cursor-pointer"
            >
              Ya, Simpan
            </button>
          </div>
        </div>
      </div>

      <script>
        function tutupModalSimpan() {
          const modal = document.getElementById("modalKonfirmasiSimpan");
          if (modal) modal.style.display = "none";
        }

        document.addEventListener("DOMContentLoaded", function () {
          function formatRupiah(angka) {
            return "Rp " + (Number(angka) || 0).toLocaleString("id-ID");
          }

          const formEdit            = document.getElementById("formEditAntrean");
          const inputPelangganEdit  = document.getElementById("inputPelangganEdit");
          const inputNomorEdit      = document.getElementById("inputNomorEdit");
          const inputAlamatEdit     = document.getElementById("inputAlamatEdit");
          const inputJenisBarang    = document.getElementById("inputJenisBarangEdit");
          const inputMerek          = document.getElementById("inputMerekEdit");
          const inputKeluhan        = document.getElementById("inputKeluhanEdit");
          const inputTglMasuk       = document.getElementById("inputTglMasuk");
          const inputTglEstimasi    = document.getElementById("inputTglEstimasi");
          const inputBiayaJasa      = document.getElementById("inputBiayaJasa");
          const inputLayanan        = document.getElementById("inputJenisLayanan");
          const displayBiayaExpress = document.getElementById("displayBiayaExpress");
          const displaySparepart    = document.getElementById("displayBiayaSparepart");
          const displayTotalBiaya   = document.getElementById("displayTotalBiaya");

          const btnBukaModalSimpan  = document.getElementById("btnBukaModalSimpan");
          const btnConfirmSimpan    = document.getElementById("btnConfirmSimpan");
          const modalSimpan         = document.getElementById("modalKonfirmasiSimpan");

          function validasiFormEdit() {
            if (!inputPelangganEdit || inputPelangganEdit.value.trim().length < 2) {
              alert("Nama pelanggan wajib diisi (minimal 2 karakter)!");
              inputPelangganEdit?.focus();
              return false;
            }
            const hpClean = (inputNomorEdit?.value || "").replace(/[\s\-\(\)\+]/g, "").trim();
            if (!/^(08|62)[0-9]{8,13}$/.test(hpClean)) {
              alert("Nomor WhatsApp tidak valid! Gunakan awalan 08 atau 62 (10–15 digit angka).");
              inputNomorEdit?.focus();
              return false;
            }
            if (!inputAlamatEdit || inputAlamatEdit.value.trim() === "") {
              alert("Alamat pelanggan wajib diisi!");
              inputAlamatEdit?.focus();
              return false;
            }
            if (!inputJenisBarang || inputJenisBarang.value.trim() === "") {
              alert("Jenis barang wajib diisi!");
              inputJenisBarang?.focus();
              return false;
            }
            if (!inputMerek || inputMerek.value.trim() === "") {
              alert("Merek dan spesifikasi wajib diisi!");
              inputMerek?.focus();
              return false;
            }
            if ((parseInt(inputBiayaJasa?.value || "0", 10) || 0) < 0) {
              alert("Biaya jasa servis tidak boleh kurang dari 0!");
              inputBiayaJasa?.focus();
              return false;
            }
            if (!inputKeluhan || inputKeluhan.value.trim() === "") {
              alert("Keluhan kerusakan wajib diisi!");
              inputKeluhan?.focus();
              return false;
            }
            if (!inputTglMasuk?.value || !inputTglEstimasi?.value) {
              alert("Tanggal Servis Masuk dan Estimasi Selesai wajib diisi!");
              return false;
            }
            if (inputTglEstimasi.value < inputTglMasuk.value) {
              alert("Tanggal Estimasi Selesai tidak boleh lebih awal dari tanggal Servis Masuk!");
              inputTglEstimasi.focus();
              return false;
            }
            return true;
          }

          // Klik Simpan Perubahan -> Validasi dulu -> Munculkan Modal Konfirmasi
          if (btnBukaModalSimpan && modalSimpan) {
            btnBukaModalSimpan.addEventListener("click", function () {
              if (!validasiFormEdit()) return;

              const selectBayar  = document.getElementById("selectStatusPembayaran");
              const selectAmbil  = document.getElementById("selectStatusPengambilan");
              const noNotaVal    = document.getElementById("hiddenNoNota")?.value || "-";
              const layananText  = inputLayanan?.value || "Reguler";

              const labelBayar = selectBayar ? selectBayar.options[selectBayar.selectedIndex].text.trim() : "-";
              const labelAmbil = selectAmbil ? selectAmbil.options[selectAmbil.selectedIndex].text.trim() : "-";
              const totalText  = displayTotalBiaya ? displayTotalBiaya.textContent.trim() : "Rp 0";

              document.getElementById("previewNotaSimpan").textContent    = noNotaVal;
              document.getElementById("previewBayarSimpan").textContent   = labelBayar;
              document.getElementById("previewAmbilSimpan").textContent   = labelAmbil;
              document.getElementById("previewLayananSimpan").textContent = layananText;
              document.getElementById("previewTotalSimpan").textContent   = totalText;

              modalSimpan.style.display = "flex";
            });
          }

          if (btnConfirmSimpan && formEdit) {
            btnConfirmSimpan.addEventListener("click", function () {
              btnConfirmSimpan.disabled = true;
              btnConfirmSimpan.textContent = "Menyimpan...";
              formEdit.submit();
            });
          }

          function hitungTotalBiaya() {
            const jasa      = parseInt(inputBiayaJasa?.value || "0", 10) || 0;
            const layanan   = inputLayanan?.value || "Reguler";
            const express   = (layanan === "Express") ? 50000 : 0;
            const sparepart = parseInt(displaySparepart?.getAttribute("data-sparepart") || "0", 10) || 0;

            if (displayBiayaExpress) displayBiayaExpress.textContent = formatRupiah(express);
            if (displayTotalBiaya) displayTotalBiaya.textContent = formatRupiah(jasa + express + sparepart);
          }

          if (inputBiayaJasa) {
            inputBiayaJasa.addEventListener("input", hitungTotalBiaya);
          }

          const togglePasswordBtn = document.getElementById("togglePasswordBtn");
          const passwordInput     = document.getElementById("passwordPerangkat");
          if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener("click", function () {
              passwordInput.type = passwordInput.type === "password" ? "text" : "password";
            });
          }

          const kondisiBtns  = document.querySelectorAll("#groupKondisiUnit .kondisi-btn");
          const inputKondisi = document.getElementById("inputKondisiUnit");
          kondisiBtns.forEach(btn => {
            btn.addEventListener("click", function () {
              kondisiBtns.forEach(b => {
                b.className = "kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer";
              });
              this.className = "kondisi-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition text-center cursor-pointer";
              if (inputKondisi) inputKondisi.value = this.getAttribute("data-val");
            });
          });

          const selectTeknisi = document.getElementById("selectTeknisiAktif");
          const teknisiAvatar = document.getElementById("teknisiAvatarInitial");
          function updateAvatar() {
            if (!selectTeknisi || !teknisiAvatar) return;
            const opt = selectTeknisi.options[selectTeknisi.selectedIndex];
            const nama = opt ? (opt.getAttribute("data-nama") || opt.textContent).trim() : "TK";
            const parts = nama.split(/\s+/);
            let initials = parts[0] ? parts[0].substring(0, 2) : "TK";
            if (parts.length > 1 && parts[1]) initials = parts[0][0] + parts[1][0];
            teknisiAvatar.textContent = initials.toUpperCase();
          }
          if (selectTeknisi) {
            selectTeknisi.addEventListener("change", updateAvatar);
            updateAvatar();
          }

          const layananBtns = document.querySelectorAll("#groupLayanan .layanan-btn");
          layananBtns.forEach(btn => {
            btn.addEventListener("click", function () {
              layananBtns.forEach(b => {
                b.className = "layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer";
              });
              this.className = "layanan-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition text-center cursor-pointer";
              if (inputLayanan) inputLayanan.value = this.getAttribute("data-val");
              hitungTotalBiaya();
            });
          });

          const persetujuanCards = document.querySelectorAll("#groupPersetujuan .persetujuan-card");
          persetujuanCards.forEach(card => {
            card.addEventListener("click", function () {
              persetujuanCards.forEach(c => {
                c.className = "persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light cursor-pointer hover:bg-white-hover transition select-none";
                const icon = c.querySelector(".check-box-icon");
                if (icon) icon.className = "check-box-icon w-5 h-5 rounded-md border border-white-active bg-white-light flex items-center justify-center text-xs font-bold text-transparent transition";
                const span = c.querySelector("span");
                if (span) span.className = "text-sm font-medium text-blue-darker";
                const radio = c.querySelector('input[type="radio"]');
                if (radio) radio.checked = false;
              });

              this.className = "persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-blue-darker/60 bg-white-light cursor-pointer transition select-none";
              const activeIcon = this.querySelector(".check-box-icon");
              if (activeIcon) activeIcon.className = "check-box-icon w-5 h-5 rounded-md bg-tosca-dark text-white-light flex items-center justify-center text-xs font-bold transition";
              const activeSpan = this.querySelector("span");
              if (activeSpan) activeSpan.className = "text-sm font-semibold text-blue-darker";
              const currentRadio = this.querySelector('input[type="radio"]');
              if (currentRadio) currentRadio.checked = true;
            });
          });
        });
      </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>