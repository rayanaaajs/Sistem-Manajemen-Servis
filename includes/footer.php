</div> <!-- Penutup dari <div class="px-8 pb-10"> (AREA KONTEN) -->
    </main> <!-- Penutup dari <main> -->


<!-- ==============================================================================
     GLOBAL MODAL POP-UP KONFIRMASI HAPUS
============================================================================== -->
<div id="modalHapus" class="fixed inset-0 z-[99] hidden flex items-center justify-center">
  <!-- Backdrop Gelap (Transisi Opacity ditaruh langsung di sini) -->
  <div id="modalBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="tutupModalHapus()"></div>

  <!-- Kotak Pop-up (Transisi Scale & Opacity ditaruh di sini) -->
  <div id="modalBox" class="relative bg-white-light rounded-3xl p-6 md:p-8 max-w-sm w-full mx-4 shadow-xl transform scale-95 opacity-0 transition-all duration-300 text-center">
    <!-- Icon Peringatan -->
    <div class="w-16 h-16 rounded-full bg-danger/10 text-danger-dark-hover mx-auto flex items-center justify-center mb-5">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
      </svg>
    </div>

    <h3 class="text-xl font-semibold text-danger-dark-hover tracking-tight mb-2">Hapus Data?</h3>
    <p class="text-sm text-white-dark-active mb-6">
      Data <span id="textDataHapus" class="font-bold text-danger-dark-hover"></span> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
    </p>

    <!-- Susunan tombol responsif (bawah-atas di HP, kiri-kanan di Desktop) -->
    <div class="flex flex-col-reverse sm:flex-row gap-3 justify-center">
      <button type="button" onclick="tutupModalHapus()" class="px-6 py-2.5 bg-white-light rounded-full border border-gray-300 text-blue-darker font-medium hover:bg-white-light-active transition w-full cursor-pointer">
        Batal
      </button>
      <a href="#" id="btnConfirmHapus" class="px-6 py-2.5 rounded-full bg-danger hover:bg-danger-hover text-white-light font-medium transition w-full text-center">
        Ya, Hapus
      </a>
    </div>
  </div>
</div>

    
    <!-- Panggil File JavaScript Custom -->
    <script src="<?= $base ?>assets/js/app.js"></script>
    <script src="<?= $base ?>assets/js/admin.js"></script>
</body>
</html>