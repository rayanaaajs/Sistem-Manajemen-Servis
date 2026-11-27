<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

function jsAlertRedirect($pesan, $tujuan = null) {
    $jsonPesan = json_encode(
        (string)$pesan,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE
    );
    if ($tujuan !== null) {
        $jsonTujuan = json_encode($tujuan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        echo "<script>alert($jsonPesan); window.location.href = $jsonTujuan;</script>";
    } else {
        echo "<script>alert($jsonPesan); window.history.back();</script>";
    }
    exit;
}

// ==============================================================================
// 1. ENDPOINT AJAX: SIMPAN PELANGGAN BARU DARI MODAL KONFIRMASI (TANPA RELOAD)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax_action'] ?? '') === 'tambah_pelanggan') {
    header('Content-Type: application/json; charset=utf-8');

    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $no_hp          = trim($_POST['no_hp'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');

    if ($nama_pelanggan === '' || $no_hp === '' || $alamat === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Nama pelanggan, nomor WhatsApp, dan alamat wajib diisi!'
        ]);
        exit;
    }

    try {
        $cekStmt = $pdo->prepare("
            SELECT id_pelanggan, nama_pelanggan, no_hp, alamat 
            FROM pelanggan 
            WHERE LOWER(TRIM(nama_pelanggan)) = LOWER(:nama)
               OR TRIM(no_hp) = :no_hp
            LIMIT 1
        ");
        $cekStmt->execute([
            'nama'  => $nama_pelanggan,
            'no_hp' => $no_hp
        ]);
        $existing = $cekStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo json_encode([
                'success'        => true,
                'message'        => 'Data pelanggan sudah terdaftar atas nama ' . $existing['nama_pelanggan'] . '.',
                'id_pelanggan'   => (int)$existing['id_pelanggan'],
                'nama_pelanggan' => $existing['nama_pelanggan'],
                'no_hp'          => $existing['no_hp'],
                'alamat'         => $existing['alamat']
            ]);
            exit;
        }

        $insertStmt = $pdo->prepare("
            INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat)
            VALUES (:nama, :no_hp, :alamat)
            RETURNING id_pelanggan
        ");
        $insertStmt->execute([
            'nama'   => $nama_pelanggan,
            'no_hp'  => $no_hp,
            'alamat' => $alamat
        ]);
        $newId = $insertStmt->fetchColumn();

        echo json_encode([
            'success'        => true,
            'message'        => 'Data pelanggan baru berhasil disimpan!',
            'id_pelanggan'   => (int)$newId,
            'nama_pelanggan' => $nama_pelanggan,
            'no_hp'          => $no_hp,
            'alamat'         => $alamat
        ]);
        exit;
    } catch (Exception $e) {
        error_log('[tambah_antrean.php - AJAX] ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan pelanggan: ' . $e->getMessage()
        ]);
        exit;
    }
}

// ==============================================================================
// 2. PROSES SIMPAN ANTREAN SERVIS BARU KE DATABASE
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
    $id_pelanggan       = trim($_POST['id_pelanggan'] ?? '');
    $nama_pelanggan     = trim($_POST['pelanggan'] ?? '');
    $no_hp              = trim($_POST['nomor'] ?? '');
    $alamat             = trim($_POST['alamat'] ?? '');

    $jenis_barang       = trim($_POST['jenis_barang'] ?? '');
    $merek_spesifikasi  = trim($_POST['merek_spesifikasi'] ?? '');
    $kelengkapan        = trim($_POST['kelengkapan'] ?? '');
    $kondisi_unit       = trim($_POST['kondisi_unit'] ?? '');
    $password_perangkat = trim($_POST['password_perangkat'] ?? '');

    $keluhan            = trim($_POST['keluhan'] ?? '');
    $catatan_awal       = trim($_POST['catatan_awal'] ?? '');
    $id_karyawan        = (int)($_POST['id_karyawan'] ?? 0);
    $tgl_masuk_input    = trim($_POST['tgl_masuk'] ?? '');
    $jenis_layanan      = trim($_POST['jenis_layanan'] ?? '');
    $persetujuan_raw    = trim($_POST['persetujuan_bongkar'] ?? '');

    if ($nama_pelanggan === '' || $no_hp === '' || $alamat === '') {
        jsAlertRedirect('Data pelanggan (Nama, Nomor WA, Alamat) wajib diisi!');
    }
    if ($jenis_barang === '' || $merek_spesifikasi === '' || $kondisi_unit === '') {
        jsAlertRedirect('Jenis barang, merek & spesifikasi, serta kondisi awal unit wajib diisi!');
    }
    if ($keluhan === '' || $id_karyawan <= 0 || $tgl_masuk_input === '' || $jenis_layanan === '' || $persetujuan_raw === '') {
        jsAlertRedirect('Lengkapi keluhan, teknisi aktif, tanggal masuk, jenis layanan, dan persetujuan bongkar!');
    }

    // Nilai default sesuai check constraint servis_persetujuan_barang_check ('Ya' / 'Tidak')
    $persetujuan_bongkar = ($persetujuan_raw === 'tidak_setuju') ? 'Tidak' : 'Ya';
    $jenis_layanan_clean = ($jenis_layanan === 'Express') ? 'Express' : 'Reguler';

    try {
        // Baca definisi check constraint servis_persetujuan_barang_check agar 100% cocok dengan kapitalisasi di Supabase
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

        $pdo->beginTransaction();
        $pdo->query("SELECT pg_advisory_xact_lock(731503, 1)")->closeCursor();

        if ($id_pelanggan === '' || (int)$id_pelanggan <= 0) {
            $cekPel = $pdo->prepare("
                SELECT id_pelanggan FROM pelanggan 
                WHERE LOWER(TRIM(nama_pelanggan)) = LOWER(:nama) AND TRIM(no_hp) = :no_hp 
                LIMIT 1
            ");
            $cekPel->execute([
                'nama'  => $nama_pelanggan,
                'no_hp' => $no_hp
            ]);
            $foundId = $cekPel->fetchColumn();

            if ($foundId) {
                $id_pelanggan = (int)$foundId;
            } else {
                $insPel = $pdo->prepare("
                    INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat)
                    VALUES (:nama, :no_hp, :alamat)
                    RETURNING id_pelanggan
                ");
                $insPel->execute([
                    'nama'   => $nama_pelanggan,
                    'no_hp'  => $no_hp,
                    'alamat' => $alamat
                ]);
                $id_pelanggan = (int)$insPel->fetchColumn();
            }
        } else {
            $id_pelanggan = (int)$id_pelanggan;
        }

        // Generate Nomor Nota Permanen: SC-0001, SC-0002, dst.
        $stmtMaxNota = $pdo->query("
            SELECT COALESCE(MAX(CAST(NULLIF(regexp_replace(no_nota, '[^0-9]', '', 'g'), '') AS INTEGER)), 0)
            FROM servis
        ");
        $nextNumber = ((int)$stmtMaxNota->fetchColumn()) + 1;
        $no_nota = 'SC-' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);

        // Waktu masuk & estimasi default +3 hari pengerjaan
        $validDate = strtotime($tgl_masuk_input) ? date('Y-m-d', strtotime($tgl_masuk_input)) : date('Y-m-d');
        $tanggal_masuk = $validDate . ' ' . date('H:i:s');
        $estimasi_pengambilan = date('Y-m-d H:i:s', strtotime($tanggal_masuk . ' +3 days'));

        // Harga default Express = 50.000, Reguler = 0
        $biaya_urgent = ($jenis_layanan_clean === 'Express') ? 50000 : 0;
        $biaya_jasa   = 0;
        $status_awal  = 'Menunggu';

        // 1. Insert ke tabel servis
        $stmtServis = $pdo->prepare("
            INSERT INTO servis (
                no_nota,
                tanggal_masuk,
                jenis_barang,
                merek_dan_spesifikasi,
                kelengkapan_barang,
                kondisi_awal_unit,
                password_perangkat,
                keluhan_kerusakan,
                catatan_cek_fisik,
                persetujuan_bongkar,
                biaya_jasa,
                status_pengerjaan,
                id_pelanggan,
                id_karyawan,
                estimasi_pengambilan,
                jenis_layanan,
                biaya_urgent
            ) VALUES (
                :no_nota,
                :tanggal_masuk,
                :jenis_barang,
                :merek_spesifikasi,
                :kelengkapan,
                :kondisi_unit,
                :password_perangkat,
                :keluhan,
                :catatan_cek_fisik,
                :persetujuan_bongkar,
                :biaya_jasa,
                :status_pengerjaan,
                :id_pelanggan,
                :id_karyawan,
                :estimasi_pengambilan,
                :jenis_layanan,
                :biaya_urgent
            )
        ");
        $stmtServis->execute([
            'no_nota'              => $no_nota,
            'tanggal_masuk'        => $tanggal_masuk,
            'jenis_barang'         => $jenis_barang,
            'merek_spesifikasi'    => $merek_spesifikasi,
            'kelengkapan'          => $kelengkapan,
            'kondisi_unit'         => $kondisi_unit,
            'password_perangkat'   => $password_perangkat,
            'keluhan'              => $keluhan,
            'catatan_cek_fisik'    => $catatan_awal,
            'persetujuan_bongkar'  => $persetujuan_bongkar,
            'biaya_jasa'           => $biaya_jasa,
            'status_pengerjaan'    => $status_awal,
            'id_pelanggan'         => $id_pelanggan,
            'id_karyawan'          => $id_karyawan,
            'estimasi_pengambilan' => $estimasi_pengambilan,
            'jenis_layanan'        => $jenis_layanan_clean,
            'biaya_urgent'         => $biaya_urgent
        ]);

        // 2. Insert ke tabel validasi
        $stmtValidasi = $pdo->prepare("
            INSERT INTO validasi (no_nota, status_pembayaran, status_pengambilan)
            VALUES (:no_nota, 'Belum Dibayar', 'Belum Diambil')
        ");
        $stmtValidasi->execute(['no_nota' => $no_nota]);

        // 3. Insert riwayat pertama ke tabel tracking_servis
        $stmtTracking = $pdo->prepare("
            INSERT INTO tracking_servis (no_nota, keterangan, waktu_update, status_pengerjaan, id_karyawan)
            VALUES (:no_nota, :keterangan, :waktu_update, :status_pengerjaan, :id_karyawan)
        ");
        $stmtTracking->execute([
            'no_nota'           => $no_nota,
            'keterangan'        => 'Unit diterima oleh admin dan masuk antrean servis (' . $jenis_layanan_clean . ').',
            'waktu_update'      => $tanggal_masuk,
            'status_pengerjaan' => $status_awal,
            'id_karyawan'       => $id_karyawan
        ]);

        $pdo->commit();

        jsAlertRedirect('Antrean berhasil ditambahkan! Nomor Nota: ' . $no_nota, 'antrean.php');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[tambah_antrean.php - INSERT] ' . $e->getMessage());
        jsAlertRedirect('Gagal menyimpan antrean: ' . $e->getMessage());
    }
}

// ==============================================================================
// 3. AMBIL DATA PELANGGAN & TEKNISI AKTIF DARI DATABASE
// ==============================================================================
$pelanggan_list = [];
$teknisi_aktif_list = [];

try {
    $stmtPel = $pdo->query("SELECT id_pelanggan, nama_pelanggan, no_hp, alamat FROM pelanggan ORDER BY nama_pelanggan ASC");
    $pelanggan_list = $stmtPel->fetchAll(PDO::FETCH_ASSOC);

    $stmtTek = $pdo->query("
        SELECT k.id_karyawan, k.nama_karyawan,
               CASE WHEN LOWER(k.peran) = 'teknisi' AND LOWER(COALESCE(k.status, 'aktif')) = 'aktif' THEN 1 ELSE 0 END AS aktif,
               COALESCE(b.aktif_total, 0) AS aktif_total,
               COALESCE(b.sedang_dikerjakan, 0) AS sedang_dikerjakan,
               COALESCE(b.express_siap, 0) AS express_siap
        FROM karyawan k
        LEFT JOIN (
            SELECT s.id_karyawan,
                   COUNT(*) FILTER (WHERE s.status_pengerjaan NOT IN ('Selesai', 'Dibatalkan')) AS aktif_total,
                   COUNT(*) FILTER (WHERE s.status_pengerjaan = 'Dikerjakan') AS sedang_dikerjakan,
                   COUNT(*) FILTER (WHERE s.jenis_layanan = 'Express' AND (
                       s.status_pengerjaan IN ('Menunggu', 'Diterima', 'Dikerjakan')
                       OR (s.status_pengerjaan = 'Menunggu Persetujuan' AND s.persetujuan_pelanggan IN ('Ya', 'Tidak'))
                   )) AS express_siap
            FROM servis s
            WHERE s.id_karyawan IS NOT NULL
            GROUP BY s.id_karyawan
        ) b ON b.id_karyawan = k.id_karyawan
        WHERE LOWER(k.peran) = 'teknisi' AND LOWER(COALESCE(k.status, 'aktif')) = 'aktif'
        ORDER BY aktif DESC, sedang_dikerjakan ASC, express_siap ASC, aktif_total ASC, k.nama_karyawan ASC, k.id_karyawan ASC
    ");
    $teknisi_aktif_list = $stmtTek->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[tambah_antrean.php - SELECT] ' . $e->getMessage());
    $msgLoad = json_encode('Gagal memuat data referensi: ' . $e->getMessage(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    echo "<script>alert($msgLoad);</script>";
}

$page_title = 'Tambah Antrean';
$active_menu = 'antrean';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

      <style>
        #passwordPerangkat::-ms-reveal,
        #passwordPerangkat::-ms-clear {
          display: none;
        }
      </style>

      <!-- FORM 3 KOLOM -->
      <form
        action="tambah_antrean.php"
        method="POST"
        autocomplete="off"
        id="formTambahAntrean"
        class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch"
      >
        <input type="text" name="fake_user" style="display:none" tabindex="-1" autocomplete="username" />
        <input type="password" name="fake_pass" style="display:none" tabindex="-1" autocomplete="new-password" />

        <!-- KOLOM 1: DATA PELANGGAN -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between gap-2 mb-6">
              <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight">
                Data Pelanggan
              </h2>
              <span id="badgePelangganTerdaftar" class="hidden px-3 py-1 rounded-full bg-success-light text-success-dark text-[11px] font-semibold">
                Pelanggan Terdaftar
              </span>
            </div>

            <input type="hidden" name="id_pelanggan" id="idPelangganInput" value="" />

            <div class="space-y-5 lg:space-y-6">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pelanggan</label>
                <input
                  type="text"
                  name="pelanggan"
                  id="inputNamaPelanggan"
                  value=""
                  autocomplete="off"
                  required
                  placeholder="Masukkan nama pelanggan"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Nomor</label>
                <input
                  type="tel"
                  name="nomor"
                  id="inputNomorPelanggan"
                  value=""
                  autocomplete="off"
                  required
                  placeholder="Masukkan Nomor Whatsapp"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Alamat</label>
                <input
                  type="text"
                  name="alamat"
                  id="inputAlamatPelanggan"
                  value=""
                  autocomplete="off"
                  required
                  placeholder="Masukkan Alamat Pelanggan"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>
            </div>
          </div>

          <div id="wrapperBtnTambahPelanggan" class="hidden mt-10 pt-4 flex flex-col items-center gap-2">
            <button
              type="button"
              id="btnBukaModalPelanggan"
              class="w-full sm:w-auto px-10 py-3.5 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer"
            >
              Tambah Pelanggan
            </button>
          </div>
        </div>

        <!-- KOLOM 2: DATA PERANGKAT -->
        <div class="bg-white-light rounded-3xl p-7 lg:p-8 border border-white-hover/60 flex flex-col justify-between">
          <div>
            <h2 class="text-lg lg:text-xl font-bold text-blue-darker tracking-tight mb-6">
              Data Perangkat
            </h2>

            <div class="space-y-5 lg:space-y-6">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Jenis Barang</label>
                <input
                  type="text"
                  name="jenis_barang"
                  value=""
                  autocomplete="off"
                  required
                  placeholder="Masukkan jenis barang"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Merek dan Spesifikasi</label>
                <input
                  type="text"
                  name="merek_spesifikasi"
                  value=""
                  autocomplete="off"
                  required
                  placeholder="Masukkan merek dan spesifikasi"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Kelengkapan</label>
                <input
                  type="text"
                  name="kelengkapan"
                  value=""
                  autocomplete="off"
                  placeholder="Masukkan kelengkapan barang"
                  class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Kondisi Awal Unit</label>
                <div class="grid grid-cols-3 gap-2.5" id="groupKondisiUnit">
                  <button
                    type="button"
                    data-val="Hidup"
                    class="kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer"
                  >
                    Hidup
                  </button>
                  <button
                    type="button"
                    data-val="Mati"
                    class="kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer"
                  >
                    Mati
                  </button>
                  <button
                    type="button"
                    data-val="Blank"
                    class="kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer"
                  >
                    Blank
                  </button>
                </div>
                <input
                  type="hidden"
                  name="kondisi_unit"
                  id="inputKondisiUnit"
                  value=""
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Password Perangkat</label>
                <div class="relative">
                  <input
                    type="password"
                    id="passwordPerangkat"
                    name="password_perangkat"
                    value=""
                    autocomplete="new-password"
                    placeholder="Masukkan password perangkat (opsional)"
                    class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
                  />
                  <button
                    type="button"
                    id="togglePasswordPerangkatBtn"
                    aria-label="Lihat atau sembunyikan password"
                    style="position: absolute; top: 0; right: 0; bottom: 0; z-index: 20;"
                    class="px-4 flex items-center justify-center text-gray-500 hover:text-blue-darker transition cursor-pointer"
                  >
                    <svg id="iconEyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="iconEyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
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
                value=""
                autocomplete="off"
                required
                placeholder="Masukkan keluhan kerusakan"
                class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
              />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Catatan Awal</label>
              <input
                type="text"
                name="catatan_awal"
                value=""
                autocomplete="off"
                placeholder="Kondisi barang sebelum di servis"
                class="w-full px-4 py-3.5 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker placeholder-white-dark-hover focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Pilih Teknisi Aktif</label>
                <div class="relative">
                  <select
                    name="id_karyawan"
                    id="selectTeknisiAktif"
                    required
                    class="w-full pl-9 pr-8 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker appearance-none focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                  >
                    <option value="" selected>Pilih Teknisi</option>
                    <?php foreach ($teknisi_aktif_list as $idxTek => $tek): ?>
                      <option value="<?= (int)$tek['id_karyawan'] ?>" data-nama="<?= htmlspecialchars($tek['nama_karyawan']) ?>">
                        <?= htmlspecialchars($tek['nama_karyawan']) ?> (<?= (int)$tek['aktif_total'] ?> aktif, <?= (int)$tek['express_siap'] ?> Express, <?= (int)$tek['sedang_dikerjakan'] ?> dikerjakan)<?= $idxTek === 0 && (int)$tek['aktif'] === 1 ? ' - Rekomendasi' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <div
                      id="teknisiAvatarInitial"
                      class="w-5 h-5 rounded-full bg-orange-light-active text-orange text-[9px] font-bold flex items-center justify-center uppercase"
                    >
                      ?
                    </div>
                  </div>
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-white-dark-hover">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-bold text-blue-darker mb-2">Servis Masuk</label>
                <input
                  type="date"
                  name="tgl_masuk"
                  value=""
                  required
                  class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition cursor-pointer"
                />
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-bold text-blue-darker">Jenis Layanan</label>
                <span id="infoBiayaLayanan" class="text-[11px] font-semibold text-white-dark-hover">
                  Pilih layanan
                </span>
              </div>
              <div class="grid grid-cols-2 gap-3" id="groupLayanan">
                <button
                  type="button"
                  data-val="Reguler"
                  class="layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer"
                >
                  Reguler
                </button>
                <button
                  type="button"
                  data-val="Express"
                  class="layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer"
                >
                  Express
                </button>
              </div>
              <input
                type="hidden"
                name="jenis_layanan"
                id="inputJenisLayanan"
                value=""
              />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-2">Persetujuan Bongkar</label>
              <div class="grid grid-cols-2 gap-3" id="groupPersetujuan">
                <label
                  class="persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light cursor-pointer hover:bg-white-hover transition select-none"
                >
                  <input
                    type="radio"
                    name="persetujuan_bongkar"
                    value="setuju"
                    class="sr-only"
                  />
                  <div
                    class="check-box-icon w-5 h-5 rounded-md border border-white-active bg-white-light flex items-center justify-center text-xs font-bold text-transparent transition"
                  >
                    ✓
                  </div>
                  <span class="text-sm font-medium text-blue-darker">Setuju Dibongkar</span>
                </label>
                <label
                  class="persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light cursor-pointer hover:bg-white-hover transition select-none"
                >
                  <input
                    type="radio"
                    name="persetujuan_bongkar"
                    value="tidak_setuju"
                    class="sr-only"
                  />
                  <div
                    class="check-box-icon w-5 h-5 rounded-md border border-white-active bg-white-light flex items-center justify-center text-xs font-bold text-transparent transition"
                  >
                    ✓
                  </div>
                  <span class="text-sm font-medium text-blue-darker">Tidak Setuju</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="relative">
                <select
                  disabled
                  class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-white-darker appearance-none cursor-not-allowed"
                >
                  <option value="Belum Dibayar" selected>Belum Dibayar</option>
                </select>
              </div>

              <div class="relative">
                <select
                  disabled
                  class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light-active text-sm font-medium text-white-darker appearance-none cursor-not-allowed"
                >
                  <option value="Belum Diambil" selected>Belum Diambil</option>
                </select>
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
              type="submit"
              class="px-8 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-sm font-semibold transition cursor-pointer"
            >
              Tambah Antrean
            </button>
          </div>
        </div>
      </form>

      <!-- MODAL KONFIRMASI TAMBAH PELANGGAN -->
      <div id="modalKonfirmasiPelanggan" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-md rounded-3xl p-6 sm:p-7 shadow-2xl border border-gray-200">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-orange-light mb-4">
            <svg class="h-7 w-7 text-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>

          <h3 class="text-xl font-bold text-blue-darker text-center mb-1.5">Konfirmasi Data Pelanggan</h3>
          <p class="text-xs text-white-dark-hover text-center mb-5 leading-relaxed">
            Pastikan data pelanggan berikut sudah benar sebelum disimpan.
          </p>

          <div class="rounded-2xl bg-white-light-active border border-white-hover p-4 space-y-2.5 text-xs mb-6">
            <div class="flex justify-between gap-3">
              <span class="text-white-dark-hover font-medium">Nama Pelanggan</span>
              <span id="previewNamaPelanggan" class="font-bold text-blue-darker text-right">-</span>
            </div>
            <div class="flex justify-between gap-3 border-t border-white-hover pt-2.5">
              <span class="text-white-dark-hover font-medium">Nomor WhatsApp</span>
              <span id="previewNomorPelanggan" class="font-bold text-blue-darker text-right">-</span>
            </div>
            <div class="flex justify-between gap-3 border-t border-white-hover pt-2.5">
              <span class="text-white-dark-hover font-medium shrink-0">Alamat</span>
              <span id="previewAlamatPelanggan" class="font-bold text-blue-darker text-right">-</span>
            </div>
          </div>

          <div class="flex items-center justify-center gap-3">
            <button
              type="button"
              id="btnBatalModalPelanggan"
              class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer"
            >
              Periksa Lagi
            </button>
            <button
              type="button"
              id="btnSimpanPelangganAjax"
              class="w-full rounded-full bg-orange hover:bg-orange-hover py-3 text-sm font-semibold text-white-light transition cursor-pointer"
            >
              Ya, Simpan
            </button>
          </div>
        </div>
      </div>

      <!-- JAVASCRIPT INTERAKTIF -->
      <script>
        document.addEventListener("DOMContentLoaded", function () {
          let daftarPelanggan = <?= json_encode($pelanggan_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?>;

          const formTambah          = document.getElementById("formTambahAntrean");
          const idPelangganInput    = document.getElementById("idPelangganInput");
          const namaInput           = document.getElementById("inputNamaPelanggan");
          const nomorInput          = document.getElementById("inputNomorPelanggan");
          const alamatInput         = document.getElementById("inputAlamatPelanggan");
          const badgeTerdaftar      = document.getElementById("badgePelangganTerdaftar");
          const wrapperBtnPelanggan = document.getElementById("wrapperBtnTambahPelanggan");

          let isAutoFilled = false;

          if (formTambah) {
            formTambah.reset();
            idPelangganInput.value = "";
          }

          function updateTombolTambahPelanggan() {
            const nama   = namaInput.value.trim();
            const nomor  = nomorInput.value.trim();
            const alamat = alamatInput.value.trim();
            const sudahAdaDiDb = idPelangganInput.value !== "";

            if (!sudahAdaDiDb && nama !== "" && nomor !== "" && alamat !== "") {
              wrapperBtnPelanggan.classList.remove("hidden");
            } else {
              wrapperBtnPelanggan.classList.add("hidden");
            }
          }

          if (namaInput) {
            namaInput.addEventListener("input", function () {
              const ketikanNama = this.value.trim().toLowerCase();

              const matchNama = ketikanNama !== ""
                ? daftarPelanggan.find(p => (p.nama_pelanggan || "").trim().toLowerCase() === ketikanNama)
                : null;

              if (matchNama) {
                idPelangganInput.value = matchNama.id_pelanggan;
                nomorInput.value       = matchNama.no_hp || "";
                alamatInput.value      = matchNama.alamat || "";
                isAutoFilled           = true;

                badgeTerdaftar.classList.remove("hidden");
                wrapperBtnPelanggan.classList.add("hidden");
              } else {
                if (isAutoFilled) {
                  nomorInput.value  = "";
                  alamatInput.value = "";
                  isAutoFilled      = false;
                }
                idPelangganInput.value = "";
                badgeTerdaftar.classList.add("hidden");
                updateTombolTambahPelanggan();
              }
            });
          }

          if (nomorInput) {
            nomorInput.addEventListener("input", function () {
              const ketikanNomor = this.value.trim();
              const matchNomor = ketikanNomor !== ""
                ? daftarPelanggan.find(p => (p.no_hp || "").trim() === ketikanNomor)
                : null;

              if (matchNomor) {
                idPelangganInput.value = matchNomor.id_pelanggan;
                if (namaInput.value.trim() === "") {
                  namaInput.value = matchNomor.nama_pelanggan || "";
                }
                alamatInput.value = matchNomor.alamat || "";
                isAutoFilled      = true;

                badgeTerdaftar.classList.remove("hidden");
                wrapperBtnPelanggan.classList.add("hidden");
              } else {
                if (idPelangganInput.value !== "") {
                  idPelangganInput.value = "";
                  badgeTerdaftar.classList.add("hidden");
                }
                isAutoFilled = false;
                updateTombolTambahPelanggan();
              }
            });
          }

          if (alamatInput) {
            alamatInput.addEventListener("input", function () {
              if (idPelangganInput.value !== "") {
                idPelangganInput.value = "";
                badgeTerdaftar.classList.add("hidden");
              }
              isAutoFilled = false;
              updateTombolTambahPelanggan();
            });
          }

          const btnBukaModalPelanggan  = document.getElementById("btnBukaModalPelanggan");
          const modalPelanggan         = document.getElementById("modalKonfirmasiPelanggan");
          const btnBatalModalPelanggan = document.getElementById("btnBatalModalPelanggan");
          const btnSimpanPelangganAjax = document.getElementById("btnSimpanPelangganAjax");

          if (btnBukaModalPelanggan) {
            btnBukaModalPelanggan.addEventListener("click", function () {
              const nama   = namaInput.value.trim();
              const nomor  = nomorInput.value.trim();
              const alamat = alamatInput.value.trim();

              if (!nama || !nomor || !alamat) {
                alert("Isi Nama Pelanggan, Nomor WhatsApp, dan Alamat terlebih dahulu!");
                return;
              }

              document.getElementById("previewNamaPelanggan").textContent   = nama;
              document.getElementById("previewNomorPelanggan").textContent  = nomor;
              document.getElementById("previewAlamatPelanggan").textContent = alamat;
              modalPelanggan.style.display = "flex";
            });
          }

          if (btnBatalModalPelanggan) {
            btnBatalModalPelanggan.addEventListener("click", function () {
              modalPelanggan.style.display = "none";
            });
          }

          if (btnSimpanPelangganAjax) {
            btnSimpanPelangganAjax.addEventListener("click", async function () {
              const nama   = namaInput.value.trim();
              const nomor  = nomorInput.value.trim();
              const alamat = alamatInput.value.trim();

              btnSimpanPelangganAjax.disabled = true;
              btnSimpanPelangganAjax.textContent = "Menyimpan...";

              try {
                const formData = new FormData();
                formData.append("ajax_action", "tambah_pelanggan");
                formData.append("nama_pelanggan", nama);
                formData.append("no_hp", nomor);
                formData.append("alamat", alamat);

                const response = await fetch("tambah_antrean.php", {
                  method: "POST",
                  body: formData
                });
                const result = await response.json();

                if (result.success) {
                  const sudahAda = daftarPelanggan.some(p => String(p.id_pelanggan) === String(result.id_pelanggan));
                  if (!sudahAda) {
                    daftarPelanggan.push({
                      id_pelanggan:   result.id_pelanggan,
                      nama_pelanggan: result.nama_pelanggan,
                      no_hp:          result.no_hp,
                      alamat:         result.alamat
                    });
                  }

                  idPelangganInput.value = result.id_pelanggan;
                  namaInput.value        = result.nama_pelanggan;
                  nomorInput.value       = result.no_hp;
                  alamatInput.value      = result.alamat;
                  isAutoFilled           = true;

                  badgeTerdaftar.classList.remove("hidden");
                  wrapperBtnPelanggan.classList.add("hidden");
                  modalPelanggan.style.display = "none";
                  alert(result.message);
                } else {
                  alert(result.message);
                }
              } catch (err) {
                alert("Terjadi kesalahan saat menyimpan data pelanggan.");
              } finally {
                btnSimpanPelangganAjax.disabled = false;
                btnSimpanPelangganAjax.textContent = "Ya, Simpan";
              }
            });
          }

          // Toggle Intip Password Perangkat
          const togglePasswordBtn = document.getElementById("togglePasswordPerangkatBtn");
          const passwordInput     = document.getElementById("passwordPerangkat");
          const iconEyeOpen       = document.getElementById("iconEyeOpen");
          const iconEyeClosed     = document.getElementById("iconEyeClosed");

          if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener("click", function (e) {
              e.preventDefault();
              const isHidden = passwordInput.type === "password";
              passwordInput.type = isHidden ? "text" : "password";

              if (iconEyeOpen && iconEyeClosed) {
                iconEyeOpen.classList.toggle("hidden", isHidden);
                iconEyeClosed.classList.toggle("hidden", !isHidden);
              }
            });
          }

          // Pilihan Kondisi Awal Unit
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

          // Avatar Inisial Teknisi
          const selectTeknisi = document.getElementById("selectTeknisiAktif");
          const teknisiAvatar = document.getElementById("teknisiAvatarInitial");
          if (selectTeknisi && teknisiAvatar) {
            selectTeknisi.addEventListener("change", function () {
              if (!this.value) {
                teknisiAvatar.textContent = "?";
                return;
              }
              const opt = this.options[this.selectedIndex];
              const nama = (opt.getAttribute("data-nama") || opt.textContent).trim();
              const parts = nama.split(/\s+/);
              let initials = parts[0] ? parts[0].substring(0, 2) : "TK";
              if (parts.length > 1 && parts[1]) {
                initials = parts[0][0] + parts[1][0];
              }
              teknisiAvatar.textContent = initials.toUpperCase();
            });
          }

          // Pilihan Jenis Layanan
          const layananBtns      = document.querySelectorAll("#groupLayanan .layanan-btn");
          const inputLayanan     = document.getElementById("inputJenisLayanan");
          const infoBiayaLayanan = document.getElementById("infoBiayaLayanan");
          layananBtns.forEach(btn => {
            btn.addEventListener("click", function () {
              layananBtns.forEach(b => {
                b.className = "layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer";
              });
              this.className = "layanan-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition text-center cursor-pointer";
              const val = this.getAttribute("data-val");
              if (inputLayanan) inputLayanan.value = val;

              if (infoBiayaLayanan) {
                if (val === "Express") {
                  infoBiayaLayanan.textContent = "Estimasi 3 hari • Biaya Express +Rp 50.000";
                  infoBiayaLayanan.className = "text-[11px] font-semibold text-danger-hover";
                } else {
                  infoBiayaLayanan.textContent = "Estimasi 3 hari • Reguler (Rp 0)";
                  infoBiayaLayanan.className = "text-[11px] font-semibold text-white-dark-hover";
                }
              }
            });
          });

          // Persetujuan Bongkar
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