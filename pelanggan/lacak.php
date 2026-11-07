<?php
// session harus mulai SEBELUM ada output apa pun (dipakai buat batas percobaan PIN)
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../includes/header_pelanggan.php';

$data_servis  = null;
$riwayat_list = [];
$pesan_error  = '';

$maks_percobaan = 5;
$durasi_kunci   = 300; // detik

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nota'], $_POST['pin_verifikasi'])) {

    $nota_input = strtoupper(trim($_POST['nota']));
    $pin_input  = trim($_POST['pin_verifikasi']);

    if (($_SESSION['lacak_kunci'] ?? 0) > time()) {
        $menit = (int) ceil(($_SESSION['lacak_kunci'] - time()) / 60);
        $pesan_error = "Terlalu banyak percobaan. Coba lagi dalam {$menit} menit.";
    } else {
        try {
            // 1 query: servis + pelanggan (JOIN lewat id, bukan nama)
            $stmt = $pdo->prepare("
                SELECT s.no_nota               AS nota,
                       p.nama_pelanggan        AS pelanggan,
                       p.no_hp                 AS no_hp,
                       CONCAT_WS(' ', s.jenis_barang, s.merek_dan_spesifikasi) AS perangkat,
                       s.keluhan_kerusakan     AS keluhan,
                       s.tanggal_masuk         AS tanggal_masuk,
                       s.estimasi_pengambilan  AS estimasi,
                       s.status_pengerjaan     AS status,
                       s.jenis_layanan         AS layanan
                FROM servis s
                JOIN pelanggan p ON p.id_pelanggan = s.id_pelanggan
                WHERE UPPER(s.no_nota) = :nota
                LIMIT 1
            ");
            $stmt->execute(['nota' => $nota_input]);
            $servis = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cek PIN = 4 angka terakhir no_hp
            $valid = false;
            if ($servis) {
                $digits = preg_replace('/\D/', '', $servis['no_hp']);
                $valid  = hash_equals(substr($digits, -4), $pin_input);
            }

            if ($valid) {
                unset($servis['no_hp']);
                $data_servis = $servis;
                unset($_SESSION['lacak_gagal'], $_SESSION['lacak_kunci']);

                // Timeline riwayat
                $stmt = $pdo->prepare("
                    SELECT status_pengerjaan AS status,
                           keterangan        AS deskripsi,
                           waktu_update      AS waktu
                    FROM tracking_servis
                    WHERE no_nota = :nota
                    ORDER BY waktu_update ASC, id_tracking ASC
                ");
                $stmt->execute(['nota' => $servis['nota']]);
                $riwayat_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Satu pesan untuk "nota salah" dan "PIN salah", biar nota nggak bisa ditebak
                $_SESSION['lacak_gagal'] = ($_SESSION['lacak_gagal'] ?? 0) + 1;
                if ($_SESSION['lacak_gagal'] >= $maks_percobaan) {
                    $_SESSION['lacak_kunci'] = time() + $durasi_kunci;
                    $_SESSION['lacak_gagal'] = 0;
                }
                $pesan_error = 'Nomor nota atau PIN tidak sesuai.';
            }
        } catch (PDOException $e) {
            error_log('lacak_servis: ' . $e->getMessage());   // detail error cuma di log server
            $pesan_error = 'Terjadi kesalahan koneksi database. Silakan coba lagi.';
        }
    }
}
?>

<!-- ================= EFEK BACKGROUND BLOB ================= -->
<?php if (!$data_servis): ?>
    <!-- Blob untuk Halaman Pencarian -->
    <div class="fixed bottom-0 left-0 w-150 h-150 bg-[#dceae8] rounded-full -translate-x-1/3 translate-y-1/4 opacity-60 z-[-1] pointer-events-none"></div>
<?php else: ?>
    <!-- Blob untuk Halaman Hasil -->
    <div class="fixed top-0 right-0 w-100 h-100 lg:w-150 lg:h-150 bg-[#feeade] rounded-full translate-x-1/4 -translate-y-1/3 opacity-70 z-[-1] pointer-events-none"></div>
<?php endif; ?>

<div class="relative z-10 w-full">

    <?php if (!$data_servis): ?>
        <!-- ================= HALAMAN 1: FORM PENCARIAN ================= -->
        <div class="text-center px-4 mt-10">
            <h1 class="text-3xl md:text-[42px] font-semibold text-blue-darker mb-4 tracking-tight">Lacak Servis Perangkatmu</h1>
            <p class="text-blue-darker text-sm md:text-sm font-normal mb-10">Masukkan nomor nota dari bukti penerimaan. Tanpa perlu login.</p>

            <?php if ($pesan_error): ?>
                <div class="max-w-2xl mx-auto mb-6 p-4 rounded-xl bg-danger-light text-danger-dark text-sm border border-danger-light-active">
                    <?= $pesan_error ?>
                </div>
            <?php endif; ?>

            <!-- Form Pencarian -->
            <form id="form-lacak" method="POST" action="">
                <div class="max-w-2xl mx-auto relative mb-4">
                    <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-blue-darker" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <input type="text" id="input-nota" name="nota" value="<?= isset($_POST['nota']) ? e($_POST['nota']) : '' ?>" placeholder="Contoh: SC-..." required class="w-full pl-14 pr-32 py-5 bg-white-light border border-orange/25 rounded-full text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-orange/50 transition-all">
                    <input type="hidden" name="pin_verifikasi" id="hidden-pin" value="">

                    <button type="button" id="btn-lacak" class="absolute right-2 top-2 bottom-2 bg-orange hover:bg-orange-hover text-white px-8 rounded-full text-sm font-semibold transition-colors cursor-pointer">
                        Lacak
                    </button>
                </div>
            </form>
            <p class="text-[12px] text-white-dark-hover font-normal">Nomor nota ada di bagian kanan atas nota kertas.</p>
        </div>

        <!-- Info Tempat Servis -->
        <div class="mt-22">
            <h2 class="text-[20px] font-bold text-blue-darker mb-6">Info Tempat Servis</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card Alamat -->
                <div class="bg-white-light rounded-3xl p-6  border border-orange/40 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Alamat</h3>
                    <p class="text-xs text-gray-500 leading-relaxed font-normal">Jl. Kertanegara No. 103,<br>Ruko Singhasari Utama Kav. 7,<br>Singosari</p>
                </div>

                <!-- Card Hubungi Kami -->
                <div class="bg-white-light rounded-3xl p-6 border border-transparent hover:border-orange/40 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Hubungi Kami</h3>
                    <p class="text-xs text-gray-500 font-normal leading-relaxed">0822-9053-8991</p>
                </div>

                <!-- Card Jam Buka -->
                <div class="bg-white-light rounded-3xl p-6 border border-transparent hover:border-orange/40 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Jam Buka</h3>
                    <p class="text-xs text-gray-500 leading-relaxed font-normal">Senin - Sabtu<br>09.00 - 17.00</p>
                </div>

                <!-- Card Layanan -->
                <div class="bg-white-light rounded-3xl p-6 border border-transparent hover:border-orange/40 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Layanan</h3>
                    <p class="text-xs text-gray-500 leading-relaxed font-normal">Laptop, komputer, printer,<br>dan perangkat lainnya.</p>
                </div>
            </div>
        </div>

        <!-- Cara Kerja -->
        <div class="mt-16 mb-10">
            <h2 class="text-[20px] font-bold text-blue-darker mb-6">Cara Kerja</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white-light rounded-3xl p-6 border border-orange/40 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 shrink-0 rounded-full bg-orange flex items-center justify-center text-white font-bold text-lg">1</div>
                    <div>
                        <h3 class="font-semibold text-blue-darker text-sm mb-1">Serahkan unit</h3>
                        <p class="text-[12px] text-gray-500 font-normal">Admin mencatat dan memberi nomor nota.</p>
                    </div>
                </div>
                <div class="bg-white-light rounded-3xl p-6 border border-gray-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 shrink-0 rounded-full bg-orange flex items-center justify-center text-white font-bold text-lg">2</div>
                    <div>
                        <h3 class="font-semibold text-blue-darker text-sm mb-1">Pantau status</h3>
                        <p class="text-[12px] text-gray-500 font-normal">Masukkan nomor nota di halaman ini kapan saja.</p>
                    </div>
                </div>
                <div class="bg-white-light rounded-3xl p-6 border border-gray-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                    <div class="w-12 h-12 shrink-0 rounded-full bg-orange flex items-center justify-center text-white font-bold text-lg">3</div>
                    <div>
                        <h3 class="font-semibold text-blue-darker text-sm mb-1">Ambil perangkat</h3>
                        <p class="text-[12px] text-gray-500 font-normal">Datang saat status Selesai dan bawa nota.</p>
                    </div>
                </div>
            </div>
        </div>


    <?php else: ?>
        <!-- ================= HALAMAN 2: HASIL PENCARIAN ================= -->

        <a href="lacak.php" class="inline-flex items-center gap-2 text-tosca font-bold text-sm hover:text-tosca-hover transition-opacity mb-6 w-fit mt-5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali
        </a>

        <div class="bg-white-light rounded-4xl p-8 sm:p-10 md:p-10 lg:p-12 border border-gray-100 animate-[fadeIn_0.4s_ease-out]">

            <!-- Header Nota & Badge -->
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 border-b border-gray-100 pb-8">
                <div>
                    <p class="text-xs text-gray-500 font-normal mb-1">Nota</p>
                    <h1 class="text-4xl font-bold text-orange-hover tracking-tight"><?= e($data_servis['nota']) ?></h1>

                    <h2 class="text-xl font-bold text-blue-darker mt-5"><?= e($data_servis['perangkat']) ?></h2>
                    <p class="text-sm text-gray-500 font-normal mt-1">Atas nama <?= e($data_servis['pelanggan']) ?></p>
                </div>

                <div class="flex items-center gap-4 mt-2 md:mt-0">
                    <!-- call konten status dari helper -->
                    <div>
                        <?= badgeStatus($data_servis['layanan']) ?>
                    </div>
                    <div>
                        <?= badgeStatus($data_servis['status']) ?>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-8">
                <div>
                    <p class="text-[12px] text-gray-500 font-normal mb-1">Tanggal Masuk</p>
                    <p class="text-sm font-semibold text-blue-darker"><?= e(formatTanggal($data_servis['tanggal_masuk'])) ?></p>
                </div>
                <div>
                    <p class="text-[12px] text-gray-500 font-normal mb-1">Jenis Layanan</p>
                    <p class="text-sm font-semibold text-blue-darker"><?= e($data_servis['layanan']) ?></p>
                </div>
                <div>
                    <p class="text-[12px] text-gray-500 font-normal mb-1">Keluhan</p>
                    <p class="text-sm font-semibold text-blue-darker"><?= e($data_servis['keluhan'] ?? '-') ?></p>
                </div>
            </div>

            <!-- Box Estimasi -->
            <div class="bg-[#FFE4D2] rounded-[20px] p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div>
                    <p class="text-[12px] text-gray-500 font-normal mb-1 opacity-90">Estimasi Pengambilan</p>
                    <h3 class="text-2xl font-extrabold text-orange-hover"><?= e(formatTanggal($data_servis['estimasi'])) ?></h3>
                </div>
                <p class="text-xs text-gray-500 font-normal md:text-right max-w-xs leading-relaxed opacity-90">
                    Estimasi bisa berubah jika ditemukan kerusakan tambahan.
                </p>
            </div>

            <!-- RIWAYAT SERVIS (TIMELINE) -->
            <div>
                <h3 class="text-lg font-semibold text-blue-darker mb-8">Riwayat Servis</h3>

                <div class="pl-0 sm:pl-2">
                    <?php
                    $total_riwayat = count($riwayat_list);
                    foreach ($riwayat_list as $index => $riwayat):
                        // Logika Styling Timeline
                        $is_last = ($index === $total_riwayat - 1);
                        $is_selesai_global = (strtolower($data_servis['status']) === 'selesai');

                        // Jika ini item terakhir dan status belum selesai -> warnai oranye (Aktif)
                        $is_current = ($is_last && !$is_selesai_global);
                        // Jika ini history lama atau global udah selesai -> warnai tosca (Selesai)
                        $is_done = (!$is_current);
                    ?>
                        <div class="relative flex gap-5 <?= $is_last ? '' : 'pb-10' ?>">

                            <!-- Garis penyambung ke bawah (Tampil jika bukan yang terakhir) -->
                            <?php if (!$is_last): ?>
                                <div class="absolute left-2.75 top-7 bottom-0 w-0.5 <?= $is_done ? 'bg-tosca-hover' : 'bg-gray-200' ?>"></div>
                            <?php endif; ?>

                            <!-- Ikon -->
                            <?php if ($is_current): ?>
                                <!-- Ikon Oranye (Sedang Berjalan) -->
                                <div class="relative z-10 w-6 h-6 rounded-full bg-orange border-4 border-orange-light-hover shrink-0 mt-0.5"></div>
                            <?php elseif ($is_done): ?>
                                <!-- Ikon Tosca (Sudah Selesai) -->
                                <div class="relative z-10 w-6 h-6 rounded-full bg-tosca-hover text-white flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <!-- Konten Teks -->
                            <div class="flex-1 flex flex-col sm:flex-row sm:justify-between gap-2">
                                <div>
                                    <h4 class="text-sm font-bold text-blue-darker"><?= e($riwayat['status']) ?></h4>
                                    <p class="text-xs text-gray-500 font-normal mt-1"><?= e($riwayat['deskripsi'] ?? '') ?></p>
                                </div>
                                <span class="text-[12px] text-gray-500 font-normal"><?= e(formatTanggal($riwayat['waktu'], true)) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Jika belum ada riwayat sama sekali -->
                    <?php if ($total_riwayat === 0): ?>
                        <p class="text-sm text-gray-500 italic">Belum ada riwayat servis yang tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<!-- Pop-Up Modal Verifikasi HP (Tetap sama) -->
<?php if (!$data_servis): ?>
    <div id="modal-verifikasi-hp" class="fixed inset-0 bg-black/50 z-50 hidden flex flex-col items-center justify-center backdrop-blur-sm transition-opacity px-4">
        <div class="bg-white-light border border-white p-8 md:p-10 rounded-4xl w-full max-w-md shadow-sm relative transform scale-95 transition-transform text-center flex flex-col items-center">
            <!-- Ikon Gembok Orange -->
            <div class="w-16 h-16 bg-orange-light-active text-orange-hover rounded-full flex items-center justify-center mb-6">
                <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
                    <path d="M0 0h14v14H0z" fill="none" />
                    <path fill="currentColor" fill-rule="evenodd" d="M7 .125a3.667 3.667 0 0 0-3.667 3.667v1.124a3.46 3.46 0 0 0-1.942 2.713c-.058.512-.1 1.04-.1 1.58c0 .539.042 1.067.1 1.578a3.46 3.46 0 0 0 3.298 3.048a65 65 0 0 0 4.622 0a3.46 3.46 0 0 0 3.298-3.048c.058-.511.1-1.04.1-1.579c0-.54-.042-1.067-.1-1.579a3.46 3.46 0 0 0-1.943-2.713V3.792A3.667 3.667 0 0 0 7 .125m2.166 4.451v-.784a2.167 2.167 0 1 0-4.333 0v.784A65 65 0 0 1 7 4.542c.762 0 1.474.011 2.166.034M7 7.876a.75.75 0 0 1 .75.75v.999a.75.75 0 0 1-1.5 0v-1a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-blue-darker mb-3">Verifikasi Nomor</h3>
            <p class="text-[14px] text-gray-500 font-normal leading-relaxed mb-8 px-2">Masukkan 4 angka terakhir nomor<br>WhatsApp yang didaftarkan saat servis.</p>

            <div class="flex justify-center gap-4 mb-8" id="pin-container">
                <input type="text" maxlength="1" inputmode="numeric" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
                <input type="text" maxlength="1" inputmode="numeric" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
                <input type="text" maxlength="1" inputmode="numeric" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
                <input type="text" maxlength="1" inputmode="numeric" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
            </div>

            <button id="btn-submit-pin" type="button" class="w-75 bg-[#E7E2D6] text-[#938F85] hover:bg-orange hover:text-white py-4 rounded-full font-semibold text-md mb-5 transition-colors pointer-events-none cursor-pointer">Lihat Status</button>
            <button id="btn-tutup-modal" type="button" class="text-[#6F6A5B] font-bold text-[14px] mb-6 hover:text-blue-darker transition-opacity cursor-pointer">Batal</button>
            <p class="text-xs text-gray-400 font-normal">Maksimal 5 percobaan.</p>
        </div>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer_pelanggan.php';
?>