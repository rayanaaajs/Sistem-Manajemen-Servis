<?php
// Panggil header pelanggan
require_once __DIR__ . '/../includes/header_pelanggan.php';
require_once __DIR__ . '/../includes/helpers.php';
?>

<!-- ================= EFEK BACKGROUND BLOB ================= -->
<!-- Lingkaran krem/peach pudar di pojok kanan atas[cite: 9] -->
<div class="fixed top-0 right-0 w-100 h-100 lg:w-150 lg:h-150 bg-[#feeade] rounded-full translate-x-1/4 -translate-y-1/3 opacity-70 z-[-1] pointer-events-none"></div>

<main class="flex-1 w-full max-w-4xl mx-auto py-6 px-6 md:px-0 flex flex-col mb-20">
    
    <!-- Tombol Kembali[cite: 9] -->
    <a href="lacak.php" class="inline-flex items-center gap-2 text-tosca font-bold text-sm hover:text-tosca-hover transition-opacity mb-6 w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        Kembali
    </a>

    <!-- ================= KARTU HASIL PENCARIAN ================= -->
    <div class="bg-white-light rounded-4xl p-6 md:p-10 lg:p-12 border border-gray-100">
        
        <!-- Header Nota & Badge -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 border-b border-gray-100 pb-8">
            <div>
                <p class="text-xs text-gray-500 font-normal mb-1">Nota</p>
                <h1 class="text-4xl font-bold text-orange-hover tracking-tight">#5147</h1>
                
                <h2 class="text-xl font-bold text-blue-darker mt-5">Laptop Lenovo LOQ 15</h2>
                <p class="text-sm text-gray-500 font-normal mt-1">Atas nama Rina W.</p>
            </div>
            
            <div class="flex items-center gap-4 mt-2 md:mt-0">
                <?= badgeStatus('Express') ?>
                <?= badgeStatus('Diterima') ?>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-8">
            <div>
                <p class="text-[12px] text-gray-500 font-normal mb-1">Tanggal Masuk</p>
                <p class="text-sm font-semibold text-blue-darker">1 Okt 2026</p>
            </div>
            <div>
                <p class="text-[12px] text-gray-500 font-normal mb-1">Jenis Layanan</p>
                <p class="text-sm font-semibold text-blue-darker">Express</p>
            </div>
            <div>
                <p class="text-[12px] text-gray-500 font-normal mb-1">Keluhan</p>
                <p class="text-sm font-semibold text-blue-darker">Sering mati sendiri, kipas keras</p>
            </div>
        </div>

        <!-- Box Estimasi[cite: 9] -->
        <div class="bg-[#FFE4D2] rounded-[20px] p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <p class="text-[12px] text-gray-500 font-normal mb-1 opacity-90">Estimasi Pengambilan</p>
                <h3 class="text-2xl font-extrabold text-orange-hover">6 Okt 2026</h3>
            </div>
            <p class="text-xs text-gray-500 font-normal md:text-right max-w-xs leading-relaxed opacity-90">
                Estimasi bisa berubah jika ditemukan kerusakan tambahan.
            </p>
        </div>

        <!-- ================= RIWAYAT SERVIS (TIMELINE) ================= -->
        <div>
            <h3 class="text-lg font-semibold text-blue-darker mb-8">Riwayat Servis</h3>
            
            <div class="pl-2">
                <!-- ITEM 1: Diterima (Selesai) -->
                <div class="relative flex gap-5 pb-10">
                    <!-- Garis penyambung ke bawah (Tosca) -->
                    <div class="absolute left-2.75 top-7 bottom-0 w-0.5 bg-tosca-hover"></div>
                    <!-- Ikon Check -->
                    <div class="relative z-10 w-6 h-6 rounded-full bg-tosca-hover text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <!-- Konten -->
                    <div class="flex-1 flex flex-col sm:flex-row sm:justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-bold text-blue-darker">Diterima</h4>
                            <p class="text-xs text-gray-500 font-normal mt-1">Unit diterima oleh admin.</p>
                        </div>
                        <span class="text-[12px] text-gray-400 font-normal">1 Okt 2026, 09.40</span>
                    </div>
                </div>

                <!-- ITEM 2: Diperiksa (Selesai) -->
                <div class="relative flex gap-5 pb-10">
                    <!-- Garis penyambung ke bawah (Tosca) -->
                    <div class="absolute left-2.75 top-7 bottom-0 w-0.5 bg-tosca-hover"></div>
                    <!-- Ikon Check -->
                    <div class="relative z-10 w-6 h-6 rounded-full bg-tosca-hover text-white flex items-center justify-center shrink-0 mt-0.5">

                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <!-- Konten -->
                    <div class="flex-1 flex flex-col sm:flex-row sm:justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-bold text-blue-darker">Diperiksa</h4>
                            <p class="text-xs text-gray-500 font-normal mt-1">Pemeriksaan awal selesai.</p>
                        </div>
                        <span class="text-[12px] text-gray-400 font-normal">1 Okt 2026, 11.05</span>
                    </div>
                </div>

                <!-- ITEM 3: Menunggu Persetujuan (Aktif/Current)[cite: 9] -->
                <div class="relative flex gap-5 pb-10">
                    <!-- Garis penyambung ke bawah (Abu-abu, karena proses selanjutnya belum mulai) -->
                    <div class="absolute left-2.75 top-7 bottom-0 w-0.5 bg-gray-200"></div>
                    <!-- Ikon Aktif (Lingkaran Oranye dengan Border) -->
                    <div class="relative z-10 w-6 h-6 rounded-full bg-orange border-4 border-orange-light-hover shrink-0 mt-0.5"></div>
                    <!-- Konten -->
                    <div class="flex-1 flex flex-col sm:flex-row sm:justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-bold text-blue-darker">Menunggu Persetujuan</h4>
                            <p class="text-xs text-gray-500 font-normal mt-1">Ditemukan kerusakan tambahan. Menunggu konfirmasi Anda.</p>
                        </div>
                        <span class="text-[12px] text-gray-400 font-normal">3 Okt 2026, 14.20</span>
                    </div>
                </div>

                <!-- ITEM 4: Dikerjakan (Belum) -->
                <div class="relative flex gap-5 pb-10">
                    <!-- Garis penyambung ke bawah (Abu-abu) -->
                    <div class="absolute left-2.75 top-7 bottom-0 w-0.5 bg-gray-200"></div>
                    <!-- Ikon Kosong -->
                    <div class="relative z-10 w-6 h-6 rounded-full bg-white-light border-2 border-gray-200 shrink-0 mt-0.5"></div>
                    <!-- Konten (Teks dibuat lebih transparan/abu-abu) -->
                    <div class="flex-1 flex flex-col sm:flex-row sm:justify-between gap-2 opacity-60">
                        <div>
                            <h4 class="text-sm font-bold text-gray-400">Dikerjakan</h4>
                            <p class="text-xs text-gray-400 font-normal mt-1">Belum dimulai</p>
                        </div>
                    </div>
                </div>

                <!-- ITEM 5: Selesai (Terakhir, Tanpa Garis Bawah) -->
                <div class="relative flex gap-5">
                    <!-- Ikon Kosong -->
                    <!-- <div class="absolute left-2.75 top-7 bottom-0 w-0.5 bg-gray-200"></div> -->
                    <div class="relative z-10 w-6 h-6 rounded-full bg-white-light border-2 border-gray-200 shrink-0 mt-0.5"></div>
                    <!-- Konten -->
                    <div class="flex-1 flex flex-col sm:flex-row sm:justify-between gap-2 opacity-60">
                        <div>
                            <h4 class="text-sm font-bold text-gray-400">Selesai</h4>
                            <p class="text-xs text-gray-400 font-normal mt-1">Belum dimulai</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer_pelanggan.php';
?>