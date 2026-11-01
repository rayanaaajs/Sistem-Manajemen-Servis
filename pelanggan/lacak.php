<?php
// Panggil header pelanggan
require_once __DIR__ . '/../includes/header_pelanggan.php';
?>

<!-- ================= EFEK BACKGROUND BLOB ================= -->
<!-- Lingkaran hijau pudar di pojok kiri bawah seperti di desain -->
<div class="fixed bottom-0 left-0 w-150 h-150 bg-[#dceae8] rounded-full -translate-x-1/3 translate-y-1/4 opacity-60 z-[-1] pointer-events-none"></div>

<div class="relative z-10 w-full">
    
    <!-- ================= HERO SECTION (PENCARIAN NOTA) ================= -->
    <div class="text-center px-4">
        <h1 class="text-3xl md:text-[42px] font-semibold text-blue-darker mb-4 tracking-tight">Lacak Servis Perangkatmu</h1>
        <p class="text-blue-darker text-sm md:text-sm font-normal mb-10">Masukkan nomor nota dari bukti penerimaan. Tanpa perlu login.</p>

        <!-- Form Pencarian -->
        <div class="max-w-2xl mx-auto relative mb-4">
            <!-- Icon Kaca Pembesar -->
            <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-blue-darker" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <input type="text" placeholder="Contoh: SC-5147" class="w-full pl-14 pr-32 py-5 bg-white-light border border-orange/25 rounded-full  text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-orange/50 transition-all">
            
            <!-- Tombol Lacak (Dikasih id btn-lacak buat trigger pop-up JS nanti) -->
            <button id="btn-lacak" class="absolute right-2 top-2 bottom-2 bg-orange hover:bg-orange-hover text-white px-8 rounded-full text-sm font-semibold transition-colors">
                Lacak
            </button>
        </div>
        <p class="text-[12px] text-white-dark-hover font-normal">Nomor nota ada di bagian kanan atas nota kertas.</p>
    </div>

    <!-- ================= INFO TEMPAT SERVIS ================= -->
    <div class="mt-22">
        <h2 class="text-[20px] font-bold text-blue-darker mb-6">Info Tempat Servis</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card Alamat -->
            <div class="bg-white-light rounded-3xl p-6  border border-orange/40 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Alamat</h3>
                <p class="text-xs text-gray-500 leading-relaxed font-normal">Jl. Kertanegara No. 103,<br>Ruko Singhasari Utama Kav. 7,<br>Singosari</p>
            </div>

            <!-- Card Hubungi Kami -->
            <div class="bg-white-light rounded-3xl p-6 border border-transparent hover:border-orange/40 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Hubungi Kami</h3>
                <p class="text-xs text-gray-500 font-normal leading-relaxed">0822-9053-8991</p>
            </div>

            <!-- Card Jam Buka -->
            <div class="bg-white-light rounded-3xl p-6 border border-transparent hover:border-orange/40 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Jam Buka</h3>
                <p class="text-xs text-gray-500 leading-relaxed font-normal">Senin - Sabtu<br>09.00 - 17.00</p>
            </div>

            <!-- Card Layanan -->
            <div class="bg-white-light rounded-3xl p-6 border border-transparent hover:border-orange/40 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 rounded-full bg-orange flex items-center justify-center text-white mb-5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="font-semibold text-blue-darker text-[14px] mb-1.5">Layanan</h3>
                <p class="text-xs text-gray-500 leading-relaxed font-normal">Laptop, komputer, printer,<br>dan perangkat lainnya.</p>
            </div>
        </div>
    </div>

    <!-- ================= CARA KERJA ================= -->
    <div class="mt-16 mb-10">
        <h2 class="text-[20px] font-bold text-blue-darker mb-6">Cara Kerja</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Step 1 -->
            <div class="bg-white-light rounded-3xl p-6 border border-orange/40 hover:border-orange/40 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 shrink-0 rounded-full bg-orange flex items-center justify-center text-white font-bold text-lg">
                    1
                </div>
                <div>
                    <h3 class="font-semibold text-blue-darker text-sm mb-1">Serahkan unit</h3>
                    <p class="text-[12px] text-gray-500 font-normal">Admin mencatat dan memberi nomor nota.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white-light rounded-3xl p-6 border border-gray-100 hover:border-orange/40 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 shrink-0 rounded-full bg-orange flex items-center justify-center text-white font-bold text-lg">
                    2
                </div>
                <div>
                    <h3 class="font-semibold text-blue-darker text-sm mb-1">Pantau status</h3>
                    <p class="text-[12px] text-gray-500 font-normal">Masukkan nomor nota di halaman ini kapan saja.</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white-light rounded-3xl p-6 border border-gray-100 hover:border-orange/40 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 shrink-0 rounded-full bg-orange flex items-center justify-center text-white font-bold text-lg">
                    3
                </div>
                <div>
                    <h3 class="font-semibold text-blue-darker text-sm mb-1">Ambil perangkat</h3>
                    <p class="text-[12px] text-gray-500 font-normal">Datang saat status Selesai dan bawa nota.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Pop-Up Modal Verifikasi HP -->
<div id="modal-verifikasi-hp" class="fixed inset-0 bg-black/50 z-50 hidden flex flex-col items-center justify-center backdrop-blur-sm transition-opacity px-4">
    
    <!-- Kotak Modal -->
    <div class="bg-white-light border border-white p-8 md:p-10 rounded-4xl w-full max-w-md shadow-sm relative transform scale-95 transition-transform text-center flex flex-col items-center">
        
        <!-- Ikon Gembok Orange -->
        <div class="w-16 h-16 bg-orange-light-active text-orange-hover rounded-full flex items-center justify-center mb-6">
        <svg
          class="w-7 h-7"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 14 14"
        >
          <path d="M0 0h14v14H0z" fill="none" />
          <path
            fill="currentColor"
            fill-rule="evenodd"
            d="M7 .125a3.667 3.667 0 0 0-3.667 3.667v1.124a3.46 3.46 0 0 0-1.942 2.713c-.058.512-.1 1.04-.1 1.58c0 .539.042 1.067.1 1.578a3.46 3.46 0 0 0 3.298 3.048a65 65 0 0 0 4.622 0a3.46 3.46 0 0 0 3.298-3.048c.058-.511.1-1.04.1-1.579c0-.54-.042-1.067-.1-1.579a3.46 3.46 0 0 0-1.943-2.713V3.792A3.667 3.667 0 0 0 7 .125m2.166 4.451v-.784a2.167 2.167 0 1 0-4.333 0v.784A65 65 0 0 1 7 4.542c.762 0 1.474.011 2.166.034M7 7.876a.75.75 0 0 1 .75.75v.999a.75.75 0 0 1-1.5 0v-1a.75.75 0 0 1 .75-.75Z"
            clip-rule="evenodd"
          />
        </svg>
        </div>
        
        <!-- Judul & Subjudul -->
        <h3 class="text-2xl font-bold text-blue-darker mb-3">Verifikasi Nomor</h3>
        <p class="text-[14px] text-gray-500 font-normal leading-relaxed mb-8 px-2">
            Masukkan 4 angka terakhir nomor<br>WhatsApp yang didaftarkan saat servis.
        </p>
        
        <!-- 4 Kotak Input PIN -->
        <div class="flex justify-center gap-4 mb-8" id="pin-container">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="pin-input w-16 h-18 text-center text-[28px] font-bold text-blue-darker border-2 border-gray-200 rounded-2xl focus:outline-none focus:border-orange-hover focus:ring-0 transition-colors bg-transparent">
        </div>
        
        <!-- Tombol Utama (Warna krem kalau belum diisi full) -->
        <button id="btn-submit-pin" class="w-75 bg-[#E7E2D6] text-[#938F85] py-4 rounded-full font-semibold text-md mb-5 transition-colors pointer-events-none">
            Lihat Status
        </button>
        
        <!-- Tombol Batal -->
        <button id="btn-tutup-modal" class="text-[#6F6A5B] font-bold text-[14px] mb-6 hover:text-blue-darker transition-opacity">
            Batal
        </button>

        <!-- Keterangan Footer -->
        <p class="text-xs text-gray-400 font-normal">Maksimal 5 percobaan.</p>
    </div>
</div>

<?php
// Panggil footer pelanggan (yang isinya script pelanggan.js)
require_once __DIR__ . '/../includes/footer_pelanggan.php';
?>