<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

function jsAlertRedirectPelanggan($pesan, $url = 'pelanggan.php') {
    $jsonPesan = json_encode((string)$pesan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    $jsonUrl   = json_encode((string)$url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    echo "<script>alert($jsonPesan); window.location.href = $jsonUrl;</script>";
    exit;
}

function jsAlertBackPelanggan($pesan) {
    $jsonPesan = json_encode((string)$pesan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    echo "<script>alert($jsonPesan); window.history.back();</script>";
    exit;
}

// ==============================================================================
// 1. PROSES CRUD PELANGGAN KE SUPABASE (TAMBAH, EDIT, HAPUS)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = trim($_POST['aksi'] ?? '');

    // --- A. TAMBAH PELANGGAN BARU ---
    if ($aksi === 'tambah') {
        $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
        $no_hp          = preg_replace('/[\s\-\(\)\+]/', '', trim($_POST['no_hp'] ?? ''));
        $alamat         = trim($_POST['alamat'] ?? '');

        if ($nama_pelanggan === '' || mb_strlen($nama_pelanggan) < 2) {
            jsAlertBackPelanggan('Nama pelanggan wajib diisi (minimal 2 karakter)!');
        }
        if (!preg_match('/^(08|62)[0-9]{8,13}$/', $no_hp)) {
            jsAlertBackPelanggan('Nomor WhatsApp tidak valid! Gunakan awalan 08 atau 62 (10–15 digit angka).');
        }
        if ($alamat === '') {
            jsAlertBackPelanggan('Alamat pelanggan wajib diisi!');
        }

        try {
            // Cek duplikasi nomor HP agar tidak terjadi data ganda
            $stmtCek = $pdo->prepare("SELECT nama_pelanggan FROM pelanggan WHERE no_hp = :no_hp LIMIT 1");
            $stmtCek->execute(['no_hp' => $no_hp]);
            $existingNama = $stmtCek->fetchColumn();

            if ($existingNama !== false) {
                jsAlertBackPelanggan("Nomor HP $no_hp sudah terdaftar atas nama \"$existingNama\"!");
            }

            $stmtIns = $pdo->prepare("
                INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat)
                VALUES (:nama, :no_hp, :alamat)
            ");

            try {
                $stmtIns->execute([
                    'nama'   => $nama_pelanggan,
                    'no_hp'  => $no_hp,
                    'alamat' => $alamat
                ]);
            } catch (PDOException $eSeq) {
                // Auto-sinkron sequence PostgreSQL jika ID sebelumnya di-seed manual
                if ($eSeq->getCode() === '23505') {
                    $pdo->exec("
                        SELECT setval(
                            pg_get_serial_sequence('pelanggan', 'id_pelanggan'),
                            COALESCE((SELECT MAX(id_pelanggan) FROM pelanggan), 0) + 1,
                            false
                        )
                    ");
                    $stmtIns->execute([
                        'nama'   => $nama_pelanggan,
                        'no_hp'  => $no_hp,
                        'alamat' => $alamat
                    ]);
                } else {
                    throw $eSeq;
                }
            }

            jsAlertRedirectPelanggan("Pelanggan \"$nama_pelanggan\" berhasil ditambahkan!");
        } catch (PDOException $e) {
            jsAlertBackPelanggan('Gagal menambah pelanggan: ' . $e->getMessage());
        }
    }

    // --- B. EDIT DATA PELANGGAN ---
    if ($aksi === 'edit') {
        $id_pelanggan   = (int)($_POST['id_pelanggan'] ?? 0);
        $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
        $no_hp          = preg_replace('/[\s\-\(\)\+]/', '', trim($_POST['no_hp'] ?? ''));
        $alamat         = trim($_POST['alamat'] ?? '');

        if ($id_pelanggan <= 0) {
            jsAlertBackPelanggan('ID pelanggan tidak valid!');
        }
        if ($nama_pelanggan === '' || mb_strlen($nama_pelanggan) < 2) {
            jsAlertBackPelanggan('Nama pelanggan wajib diisi (minimal 2 karakter)!');
        }
        if (!preg_match('/^(08|62)[0-9]{8,13}$/', $no_hp)) {
            jsAlertBackPelanggan('Nomor WhatsApp tidak valid! Gunakan awalan 08 atau 62 (10–15 digit angka).');
        }
        if ($alamat === '') {
            jsAlertBackPelanggan('Alamat pelanggan wajib diisi!');
        }

        try {
            // Pastikan nomor HP tidak bentrok dengan pelanggan lain
            $stmtCek = $pdo->prepare("
                SELECT nama_pelanggan 
                FROM pelanggan 
                WHERE no_hp = :no_hp AND id_pelanggan <> :id 
                LIMIT 1
            ");
            $stmtCek->execute(['no_hp' => $no_hp, 'id' => $id_pelanggan]);
            $existingNama = $stmtCek->fetchColumn();

            if ($existingNama !== false) {
                jsAlertBackPelanggan("Nomor HP $no_hp sudah digunakan oleh pelanggan \"$existingNama\"!");
            }

            $stmtUpd = $pdo->prepare("
                UPDATE pelanggan
                SET nama_pelanggan = :nama,
                    no_hp          = :no_hp,
                    alamat         = :alamat
                WHERE id_pelanggan = :id
            ");
            $stmtUpd->execute([
                'nama'   => $nama_pelanggan,
                'no_hp'  => $no_hp,
                'alamat' => $alamat,
                'id'     => $id_pelanggan
            ]);

            jsAlertRedirectPelanggan("Data pelanggan \"$nama_pelanggan\" berhasil diperbarui!");
        } catch (PDOException $e) {
            jsAlertBackPelanggan('Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    // --- C. HAPUS DATA PELANGGAN ---
    if ($aksi === 'hapus') {
        $id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);

        if ($id_pelanggan <= 0) {
            jsAlertBackPelanggan('ID pelanggan tidak valid!');
        }

        try {
            // Cek apakah pelanggan masih terhubung dengan nota servis
            $stmtCekServis = $pdo->prepare("SELECT COUNT(*) FROM servis WHERE id_pelanggan = :id");
            $stmtCekServis->execute(['id' => $id_pelanggan]);
            $jumlahServis = (int)$stmtCekServis->fetchColumn();

            if ($jumlahServis > 0) {
                jsAlertRedirectPelanggan("Pelanggan tidak dapat dihapus karena masih memiliki $jumlahServis data nota servis!");
            }

            $stmtDel = $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = :id");
            $stmtDel->execute(['id' => $id_pelanggan]);

            jsAlertRedirectPelanggan('Data pelanggan berhasil dihapus!');
        } catch (PDOException $e) {
            jsAlertRedirectPelanggan('Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }
}

$page_title = 'Pelanggan';
$active_menu = 'pelanggan';
$search_placeholder = 'Cari nama pelanggan, nomor HP, atau alamat...';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/helpers.php';

/**
 * Helper format tanggal Indonesia (contoh: 23 Nov 2026)
 */
function formatTanggalPelanggan($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return '-';
    }
    $bulan = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $tgl = date('j', $timestamp);
    $bln = $bulan[(int)date('n', $timestamp)] ?? '';
    $thn = date('Y', $timestamp);
    return $tgl . ' ' . $bln . ' ' . $thn;
}

// ==============================================================================
// 2. AMBIL DATA PELANGGAN DARI SUPABASE + RINGKASAN SERVIS
// ==============================================================================
$daftar_pelanggan = [];

try {
    $sql = "
        SELECT 
            p.id_pelanggan,
            p.nama_pelanggan,
            p.no_hp,
            p.alamat,
            COUNT(s.no_nota) AS total_servis,
            MAX(s.tanggal_masuk) AS servis_terakhir,
            STRING_AGG(s.no_nota, ', ' ORDER BY s.no_nota DESC) AS daftar_nota
        FROM pelanggan p
        LEFT JOIN servis s ON p.id_pelanggan = s.id_pelanggan
        GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_hp, p.alamat
        ORDER BY p.id_pelanggan DESC
    ";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $nama = trim((string)($row['nama_pelanggan'] ?? 'Pelanggan Umum'));
        $parts = preg_split('/\s+/', $nama);
        $inisial = strtoupper(substr($parts[0] ?? 'PL', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : substr($parts[0] ?? 'PL', 1, 1)));
        $totalServis = (int)($row['total_servis'] ?? 0);

        $daftar_pelanggan[] = [
            'id'              => (int)$row['id_pelanggan'],
            'nama'            => $nama,
            'inisial'         => $inisial !== '' ? $inisial : 'PL',
            'no_hp'           => $row['no_hp'] ?? '-',
            'alamat'          => $row['alamat'] ?? '-',
            'total_servis'    => $totalServis,
            'servis_terakhir' => formatTanggalPelanggan($row['servis_terakhir'] ?? null),
            'daftar_nota'     => $row['daftar_nota'] ?? '-',
            'kategori'        => $totalServis > 0 ? 'pernah_servis' : 'baru'
        ];
    }
} catch (PDOException $e) {
    echo "<script>alert('Gagal mengambil data pelanggan: " . addslashes($e->getMessage()) . "');</script>";
}

$total_pelanggan = count($daftar_pelanggan);
?>

      <!-- FILTER KATEGORI & TOMBOL + PELANGGAN BARU -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2" id="filterPelangganGroup">
          <button type="button" class="filter-pelanggan-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap" data-filter="all">
            Semua <?= $total_pelanggan ?>
          </button>
          <button type="button" class="filter-pelanggan-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="pernah_servis">
            Pernah Servis
          </button>
          <button type="button" class="filter-pelanggan-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="baru">
            Belum Servis
          </button>
        </div>

        <!-- Tombol + Pelanggan Baru (Orange) -->
        <button type="button" onclick="bukaModalTambahPelanggan()" class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto sm:px-10 py-3 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold transition-all whitespace-nowrap cursor-pointer">
          <span class="text-sm font-bold">+</span>
          <span>Pelanggan Baru</span>
        </button>
      </div>

      <!-- ==============================================================================
           KARTU TABEL DAFTAR PELANGGAN
           ============================================================================== -->
      <div class="bg-white-light rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200">
        <!-- Header Tabel & Info Jumlah -->
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
          <h2 class="text-lg font-bold text-blue-darker tracking-tight">Daftar Pelanggan</h2>
          <span id="headerCountPelanggan" class="text-sm text-white-dark-hover font-medium"><?= $total_pelanggan ?> pelanggan</span>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
          <table class="w-full min-w-160 whitespace-nowrap text-left border-collapse text-sm" id="tablePelanggan">
            <thead>
              <tr class="border-b border-white-hover text-white-darker font-bold text-[13px] tracking-wider">
                <th class="py-4 px-5">Nama Pelanggan</th>
                <th class="py-4 px-5">Nomor WhatsApp</th>
                <th class="py-4 px-5">Alamat</th>
                <th class="py-4 px-5 text-center">Total Servis</th>
                <th class="hidden lg:table-cell py-4 px-5 text-center">Servis Terakhir</th>
                <th class="py-4 px-5 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="tableBodyPelanggan" class="divide-y divide-white-hover text-white-darker font-medium">
              <?php foreach ($daftar_pelanggan as $item): ?>
                <?php
                  $search_blob = strtolower($item['nama'] . ' ' . $item['no_hp'] . ' ' . $item['alamat'] . ' ' . $item['daftar_nota']);
                ?>
                <tr class="hover:bg-warning-light/40 transition-colors row-pelanggan"
                    data-kategori="<?= e($item['kategori']) ?>"
                    data-search="<?= e($search_blob) ?>">

                  <!-- Nama Pelanggan + Inisial -->
                  <td class="py-4 px-5">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-full bg-tosca-light text-tosca-dark font-bold text-xs flex items-center justify-center shrink-0">
                        <?= e($item['inisial']) ?>
                      </div>
                      <span class="font-semibold text-blue-darker"><?= e($item['nama']) ?></span>
                    </div>
                  </td>

                  <!-- Nomor WhatsApp -->
                  <td class="py-4 px-5 font-medium text-blue-darker"><?= e($item['no_hp']) ?></td>

                  <!-- Alamat -->
                  <td class="py-4 px-5 max-w-64 truncate" title="<?= e($item['alamat']) ?>">
                    <?= e($item['alamat']) ?>
                  </td>

                  <!-- Total Servis -->
                  <td class="py-4 px-5 text-center">
                    <?php if ($item['total_servis'] > 0): ?>
                      <span style="display: inline-block; min-width: 6.5rem; padding: 0.45rem 1rem;"
                            class="rounded-full text-xs font-semibold bg-success-light text-success-dark">
                        <?= $item['total_servis'] ?> Servis
                      </span>
                    <?php else: ?>
                      <span style="display: inline-block; min-width: 6.5rem; padding: 0.45rem 1rem;"
                            class="rounded-full text-xs font-semibold bg-blue-light text-blue-hover">
                        Baru
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- Servis Terakhir -->
                  <td class="hidden lg:table-cell py-4 px-5 text-center text-white-dark-active">
                    <?= e($item['servis_terakhir']) ?>
                  </td>

                  <!-- Aksi -->
                  <td class="py-4 px-5 text-center">
                    <div class="inline-flex items-center gap-2">
                      <button type="button"
                              onclick="bukaModalEditPelanggan(<?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)"
                              class="px-3.5 py-2 rounded-full border border-tosca-darker text-tosca-darker hover:bg-tosca-darker hover:text-white-light text-xs font-semibold transition cursor-pointer">
                        Edit
                      </button>
                      <button type="button"
                              onclick="bukaModalDetailPelanggan(<?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)"
                              class="px-3.5 py-2 rounded-full bg-tosca-dark hover:bg-tosca-dark-hover text-white-light text-xs font-semibold transition cursor-pointer">
                        Detail
                      </button>
                      <button type="button"
                              onclick="bukaModalHapusPelanggan(<?= (int)$item['id'] ?>, <?= htmlspecialchars(json_encode($item['nama']), ENT_QUOTES) ?>)"
                              class="px-3.5 py-2 rounded-full border border-danger text-danger hover:bg-danger hover:text-white-light text-xs font-semibold transition cursor-pointer">
                        Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>

              <!-- Baris Kosong -->
              <tr id="emptyPelangganRow" style="<?= $total_pelanggan === 0 ? '' : 'display: none;' ?>">
                <td colspan="6" class="py-10 text-center text-sm text-white-dark-hover">
                  Belum ada data pelanggan yang sesuai.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION CONTROLS -->
        <div id="paginationPelangganContainer" class="mt-6 pt-4 border-t border-white-hover flex flex-col sm:flex-row items-center justify-between gap-3">
          <p id="paginationPelangganInfo" class="text-xs text-white-dark-hover font-medium"></p>
          <div id="paginationPelangganButtons" class="flex items-center gap-1.5 flex-wrap"></div>
        </div>
      </div>

      <!-- ==============================================================================
           MODAL 1: TAMBAH PELANGGAN BARU
           ============================================================================== -->
      <div id="modalTambahPelanggan" onclick="if(event.target === this) tutupModalTambahPelanggan()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-md rounded-3xl p-6 sm:p-7 shadow-2xl border border-gray-200">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-blue-darker">Tambah Pelanggan Baru</h3>
            <button type="button" onclick="tutupModalTambahPelanggan()" class="text-white-dark-hover hover:text-blue-darker text-xl font-bold cursor-pointer">×</button>
          </div>

          <form action="pelanggan.php" method="POST" class="space-y-4">
            <input type="hidden" name="aksi" value="tambah" />

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-1.5">Nama Pelanggan</label>
              <input type="text" name="nama_pelanggan" required minlength="2" placeholder="Masukkan nama lengkap pelanggan"
                     class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition" />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-1.5">Nomor WhatsApp</label>
              <input type="tel" name="no_hp" required placeholder="Contoh: 081234567890"
                     class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition" />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-1.5">Alamat</label>
              <textarea name="alamat" rows="3" required placeholder="Masukkan alamat lengkap pelanggan"
                        class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
              <button type="button" onclick="tutupModalTambahPelanggan()"
                      class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer">
                Batal
              </button>
              <button type="submit"
                      class="w-full rounded-full bg-orange hover:bg-orange-hover py-3 text-sm font-semibold text-white-light transition cursor-pointer">
                Simpan Pelanggan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ==============================================================================
           MODAL 2: EDIT DATA PELANGGAN
           ============================================================================== -->
      <div id="modalEditPelanggan" onclick="if(event.target === this) tutupModalEditPelanggan()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-md rounded-3xl p-6 sm:p-7 shadow-2xl border border-gray-200">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-blue-darker">Edit Data Pelanggan</h3>
            <button type="button" onclick="tutupModalEditPelanggan()" class="text-white-dark-hover hover:text-blue-darker text-xl font-bold cursor-pointer">×</button>
          </div>

          <form action="pelanggan.php" method="POST" class="space-y-4">
            <input type="hidden" name="aksi" value="edit" />
            <input type="hidden" name="id_pelanggan" id="editIdPelanggan" value="" />

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-1.5">Nama Pelanggan</label>
              <input type="text" name="nama_pelanggan" id="editNamaPelanggan" required minlength="2"
                     class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition" />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-1.5">Nomor WhatsApp</label>
              <input type="tel" name="no_hp" id="editNoHpPelanggan" required
                     class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition" />
            </div>

            <div>
              <label class="block text-sm font-bold text-blue-darker mb-1.5">Alamat</label>
              <textarea name="alamat" id="editAlamatPelanggan" rows="3" required
                        class="w-full px-4 py-3 rounded-2xl border border-white-active bg-white-light text-sm font-medium text-blue-darker focus:outline-none focus:border-tosca-dark focus:ring-2 focus:ring-tosca-dark/20 transition"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
              <button type="button" onclick="tutupModalEditPelanggan()"
                      class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer">
                Batal
              </button>
              <button type="submit"
                      class="w-full rounded-full bg-orange hover:bg-orange-hover py-3 text-sm font-semibold text-white-light transition cursor-pointer">
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ==============================================================================
           MODAL 3: DETAIL PELANGGAN
           ============================================================================== -->
      <div id="modalDetailPelanggan" onclick="if(event.target === this) tutupModalDetailPelanggan()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-md rounded-3xl p-6 sm:p-7 shadow-2xl border border-gray-200">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-blue-darker">Detail Pelanggan</h3>
            <button type="button" onclick="tutupModalDetailPelanggan()" class="text-white-dark-hover hover:text-blue-darker text-xl font-bold cursor-pointer">×</button>
          </div>

          <div class="flex items-center gap-3.5 mb-5">
            <div id="detailInisialPelanggan" class="w-12 h-12 rounded-full bg-tosca-dark text-white-light font-bold text-base flex items-center justify-center shrink-0">
              PL
            </div>
            <div>
              <h4 id="detailNamaPelanggan" class="text-base font-bold text-blue-darker">-</h4>
              <p id="detailTotalServis" class="text-xs text-white-dark-hover font-medium">0 Servis</p>
            </div>
          </div>

          <div class="rounded-2xl bg-white-light-active border border-white-hover p-4 space-y-3 text-sm mb-6">
            <div>
              <span class="block text-xs text-white-dark-hover font-medium mb-0.5">Nomor WhatsApp</span>
              <span id="detailNoHpPelanggan" class="font-semibold text-blue-darker">-</span>
            </div>
            <div class="border-t border-white-hover pt-2.5">
              <span class="block text-xs text-white-dark-hover font-medium mb-0.5">Alamat</span>
              <span id="detailAlamatPelanggan" class="font-medium text-blue-darker">-</span>
            </div>
            <div class="border-t border-white-hover pt-2.5">
              <span class="block text-xs text-white-dark-hover font-medium mb-0.5">Riwayat Nomor Nota</span>
              <span id="detailNotaPelanggan" class="font-semibold text-tosca-dark">-</span>
            </div>
          </div>

          <button type="button" onclick="tutupModalDetailPelanggan()"
                  class="w-full rounded-full bg-tosca-dark hover:bg-tosca-dark-hover py-3 text-sm font-semibold text-white-light transition cursor-pointer">
            Tutup
          </button>
        </div>
      </div>

      <!-- ==============================================================================
           MODAL 4: KONFIRMASI HAPUS PELANGGAN
           ============================================================================== -->
      <div id="modalHapusPelanggan" onclick="if(event.target === this) tutupModalHapusPelanggan()" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div class="bg-white-light w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-gray-200">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-danger-light mb-5">
            <svg class="h-8 w-8 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>

          <h3 class="mb-2 text-xl font-bold text-blue-darker">Hapus Pelanggan?</h3>
          <p class="mb-7 text-sm text-white-dark-hover leading-relaxed">
            Data pelanggan <span id="textNamaHapusPelanggan" class="font-bold text-blue-darker"></span> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
          </p>

          <form action="pelanggan.php" method="POST" class="flex items-center justify-center gap-3">
            <input type="hidden" name="aksi" value="hapus" />
            <input type="hidden" name="id_pelanggan" id="hapusIdPelanggan" value="" />
            <button type="button" onclick="tutupModalHapusPelanggan()"
                    class="w-full rounded-full border border-gray-300 py-3 text-sm font-semibold text-blue-darker hover:bg-gray-50 transition cursor-pointer">
              Batal
            </button>
            <button type="submit"
                    class="w-full rounded-full bg-danger py-3 text-sm font-semibold text-white-light hover:bg-danger-dark transition cursor-pointer">
              Ya, Hapus
            </button>
          </form>
        </div>
      </div>

      <!-- JAVASCRIPT: Filter, Live Search, Pagination (8 Baris/Halaman) & Modal -->
      <script>
        document.addEventListener("DOMContentLoaded", function () {
          const filterButtons     = document.querySelectorAll(".filter-pelanggan-btn");
          const allRows           = Array.from(document.querySelectorAll(".row-pelanggan"));
          const emptyRow          = document.getElementById("emptyPelangganRow");
          const headerCountInfo   = document.getElementById("headerCountPelanggan");
          const paginationInfo    = document.getElementById("paginationPelangganInfo");
          const paginationButtons = document.getElementById("paginationPelangganButtons");
          const searchInput       = document.getElementById("searchNotaInput") || document.querySelector('input[placeholder*="Cari"], header input[type="text"], header input[type="search"]');

          const rowsPerPage = 8;
          let currentPage   = 1;
          let currentFilter = "all";
          let currentSearch = "";

          function getFilteredRows() {
            return allRows.filter(row => {
              const kategori   = row.getAttribute("data-kategori") || "";
              const searchText = row.getAttribute("data-search") || "";

              const matchFilter = currentFilter === "all" || kategori === currentFilter;
              const matchSearch = currentSearch === "" || searchText.includes(currentSearch);

              return matchFilter && matchSearch;
            });
          }

          function renderTable() {
            const filteredRows = getFilteredRows();
            const totalItems   = filteredRows.length;
            const totalPages   = Math.ceil(totalItems / rowsPerPage) || 1;

            if (currentPage > totalPages) {
              currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex   = startIndex + rowsPerPage;

            allRows.forEach(row => {
              row.style.display = "none";
            });

            const visibleRows = filteredRows.slice(startIndex, endIndex);
            visibleRows.forEach(row => {
              row.style.display = "";
            });

            if (emptyRow) {
              emptyRow.style.display = totalItems === 0 ? "" : "none";
            }

            if (headerCountInfo) {
              headerCountInfo.textContent = `${visibleRows.length} dari ${totalItems} pelanggan`;
            }

            if (paginationInfo) {
              if (totalItems === 0) {
                paginationInfo.textContent = "Menampilkan 0 pelanggan";
              } else {
                const showStart = startIndex + 1;
                const showEnd   = Math.min(endIndex, totalItems);
                paginationInfo.textContent = `Menampilkan ${showStart}-${showEnd} dari ${totalItems} pelanggan`;
              }
            }

            if (paginationButtons) {
              paginationButtons.innerHTML = "";

              if (totalPages > 1) {
                const prevBtn = document.createElement("button");
                prevBtn.type = "button";
                prevBtn.textContent = "Sebelumnya";
                prevBtn.disabled = currentPage === 1;
                prevBtn.className = currentPage === 1
                  ? "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed"
                  : "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
                prevBtn.addEventListener("click", () => {
                  if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                  }
                });
                paginationButtons.appendChild(prevBtn);

                for (let i = 1; i <= totalPages; i++) {
                  const pageBtn = document.createElement("button");
                  pageBtn.type = "button";
                  pageBtn.textContent = i;
                  pageBtn.className = i === currentPage
                    ? "w-9 h-9 rounded-full bg-orange hover:bg-orange-hover text-white-light text-xs font-semibold shadow-xs transition cursor-pointer"
                    : "w-9 h-9 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
                  pageBtn.addEventListener("click", () => {
                    currentPage = i;
                    renderTable();
                  });
                  paginationButtons.appendChild(pageBtn);
                }

                const nextBtn = document.createElement("button");
                nextBtn.type = "button";
                nextBtn.textContent = "Selanjutnya";
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.className = currentPage === totalPages
                  ? "px-4 py-2 rounded-full bg-white-light text-white-dark border border-gray-200 text-xs font-medium opacity-50 cursor-not-allowed"
                  : "px-4 py-2 rounded-full bg-white-light text-white-darker border border-gray-200 hover:border-orange hover:text-orange text-xs font-medium transition cursor-pointer";
                nextBtn.addEventListener("click", () => {
                  if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                  }
                });
                paginationButtons.appendChild(nextBtn);
              }
            }
          }

          if (searchInput) {
            searchInput.addEventListener("input", function () {
              currentSearch = this.value.toLowerCase().trim();
              currentPage = 1;
              renderTable();
            });
          }

          filterButtons.forEach(btn => {
            btn.addEventListener("click", function () {
              filterButtons.forEach(b => {
                b.className = "filter-pelanggan-btn shrink-0 px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap";
              });
              this.className = "filter-pelanggan-btn shrink-0 px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap";

              currentFilter = this.getAttribute("data-filter");
              currentPage = 1;
              renderTable();
            });
          });

          renderTable();
        });

        function bukaModalTambahPelanggan() {
          document.getElementById("modalTambahPelanggan").style.display = "flex";
        }
        function tutupModalTambahPelanggan() {
          document.getElementById("modalTambahPelanggan").style.display = "none";
        }

        function bukaModalEditPelanggan(data) {
          document.getElementById("editIdPelanggan").value = data.id || "";
          document.getElementById("editNamaPelanggan").value = data.nama || "";
          document.getElementById("editNoHpPelanggan").value = data.no_hp || "";
          document.getElementById("editAlamatPelanggan").value = data.alamat || "";
          document.getElementById("modalEditPelanggan").style.display = "flex";
        }
        function tutupModalEditPelanggan() {
          document.getElementById("modalEditPelanggan").style.display = "none";
        }

        function bukaModalDetailPelanggan(data) {
          document.getElementById("detailInisialPelanggan").textContent = data.inisial || "PL";
          document.getElementById("detailNamaPelanggan").textContent    = data.nama || "-";
          document.getElementById("detailTotalServis").textContent      = (data.total_servis || 0) + " Riwayat Servis";
          document.getElementById("detailNoHpPelanggan").textContent    = data.no_hp || "-";
          document.getElementById("detailAlamatPelanggan").textContent  = data.alamat || "-";
          document.getElementById("detailNotaPelanggan").textContent    = data.daftar_nota && data.daftar_nota !== "" ? data.daftar_nota : "Belum ada nota servis";
          document.getElementById("modalDetailPelanggan").style.display = "flex";
        }
        function tutupModalDetailPelanggan() {
          document.getElementById("modalDetailPelanggan").style.display = "none";
        }

        function bukaModalHapusPelanggan(id, nama) {
          document.getElementById("hapusIdPelanggan").value = id;
          document.getElementById("textNamaHapusPelanggan").textContent = nama;
          document.getElementById("modalHapusPelanggan").style.display = "flex";
        }
        function tutupModalHapusPelanggan() {
          document.getElementById("modalHapusPelanggan").style.display = "none";
        }
      </script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>