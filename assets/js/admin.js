/* global Chart */

document.addEventListener("DOMContentLoaded", function () {
  // ==============================================================================
  // 1. FITUR PENCARIAN REAL-TIME PADA TABEL SERVIS
  // ==============================================================================
  const searchInput = document.getElementById("searchPelanggan");
  const tableBody = document.getElementById("tableBodyServis");

  if (searchInput && tableBody) {
    searchInput.addEventListener("input", function (e) {
      const query = e.target.value.toLowerCase().trim();
      const rows = tableBody.querySelectorAll("tr");

      rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? "" : "none";
      });
    });
  }

  // ==============================================================================
  // 2. INTERAKSI TOMBOL FILTER WAKTU (Hari ini, 7 Hari, 30 Hari)
  // ==============================================================================
  const filterButtons = document.querySelectorAll(
    "#filterWaktuGroup .filter-btn",
  );

  if (filterButtons.length > 0) {
    filterButtons.forEach((btn) => {
      btn.addEventListener("click", function () {
        // Reset semua tombol ke tampilan putih / pasif
        filterButtons.forEach((b) => {
          b.className =
            "filter-btn px-5 py-1.5 rounded-full bg-white text-gray-700 border border-gray-200 text-xs font-medium shadow-2xs hover:bg-gray-50 transition cursor-pointer";
        });
        // Beri gaya warna biru tua / aktif pada tombol yang diklik
        this.className =
          "filter-btn px-5 py-1.5 rounded-full bg-[#0D4F56] text-white text-xs font-semibold shadow-xs transition cursor-pointer";
      });
    });
  }

  // ==============================================================================
  // 3. INISIALISASI GRAFIK CHART.JS (DIAGRAM GARIS SERVIS)
  // ==============================================================================
  const canvasGrafik = document.getElementById("servisChart");

  // Pastikan elemen canvas dan variabel data dari PHP (window.grafikServisData) tersedia
  if (canvasGrafik && typeof window.grafikServisData !== "undefined") {
    const grafikData = window.grafikServisData;

    // Mapping data untuk format Chart.js
    const labels = grafikData.map((d) => d.minggu);
    const dataMasuk = grafikData.map((d) => d.masuk);
    const dataSelesai = grafikData.map((d) => d.selesai);

    const ctx = canvasGrafik.getContext("2d");

    // Buat Grafik Garis
    new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Masuk",
            data: dataMasuk,
            borderColor: "#F26B21",
            backgroundColor: "#F26B21",
            borderWidth: 3,
            tension: 0.4, // melengkung halus
            pointRadius: 4,
            pointBackgroundColor: "#F26B21",
          },
          {
            label: "Selesai",
            data: dataSelesai,
            borderColor: "#0D4F56",
            backgroundColor: "#0D4F56",
            borderWidth: 3,
            tension: 0.4, // melengkung halus
            pointRadius: 4,
            pointBackgroundColor: "#0D4F56",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true },
        },
        scales: {
          x: {
            grid: { display: false },
          },
          y: {
            grid: { color: "#F1F5F9" },
            beginAtZero: true,
          },
        },
      },
    });
  }
});

//  <!-- ==============================================================================
//       BAGIAN 5: SCRIPT JAVASCRIPT (FILTER REAL-TIME & PENCARIAN NOTA)
//     ============================================================================== -->
document.addEventListener("DOMContentLoaded", function () {
  const searchNotaInput = document.getElementById("searchNotaInput");
  const filterTabs = document.querySelectorAll(
    "#filterStatusGroup .filter-tab-btn",
  );
  const rows = document.querySelectorAll("#tableBodyAntrean .row-item");

  let currentFilter = "all";

  // Fungsi menyaring baris tabel
  function filterTable() {
    const query = searchNotaInput
      ? searchNotaInput.value.toLowerCase().trim()
      : "";

    rows.forEach((row) => {
      const nota = row.getAttribute("data-nota") || "";
      const status = row.getAttribute("data-status") || "";
      const layanan = row.getAttribute("data-layanan") || "";

      const matchNota =
        nota.includes(query) || row.textContent.toLowerCase().includes(query);

      let matchFilter = false;
      if (currentFilter === "all") {
        matchFilter = true;
      } else if (currentFilter === "Express" || currentFilter === "Reguler") {
        matchFilter = layanan === currentFilter;
      } else {
        matchFilter = status
          .toLowerCase()
          .includes(currentFilter.toLowerCase());
      }

      row.style.display = matchNota && matchFilter ? "" : "none";
    });
  }

  // Event Listener: Pencarian Real-Time
  if (searchNotaInput) {
    searchNotaInput.addEventListener("input", filterTable);
  }

  // Event Listener: Tab Filter Status
  filterTabs.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Reset style semua tab
      filterTabs.forEach((b) => {
        b.className =
          'filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium  hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap" data-filter="Selesai';
      });

      // Set style tab aktif (tosca-dark)
      this.className =
        'filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold transition cursor-pointer whitespace-nowrap" data-filter="all"';

      currentFilter = this.getAttribute("data-filter") || "all";
      filterTable();
    });
  });
});

//script pop up hapus
const modalHapus = document.getElementById("modalHapus");
const modalBackdrop = document.getElementById("modalBackdrop");
const modalBox = document.getElementById("modalBox");
const textNotaHapus = document.getElementById("textNotaHapus");
const btnConfirmHapus = document.getElementById("btnConfirmHapus");

function bukaModalHapus(nota, linkHapus) {
  textNotaHapus.textContent = nota;
  btnConfirmHapus.href = linkHapus;

  // Munculin pembungkus utamanya dulu
  modalHapus.classList.remove("hidden");

  // Kasih jeda 10ms biar browser nafas, baru jalanin animasinya barengan
  setTimeout(() => {
    modalBackdrop.classList.remove("opacity-0");
    modalBox.classList.remove("opacity-0", "scale-95");
    modalBox.classList.add("opacity-100", "scale-100");
  }, 10);
}

function tutupModalHapus() {
  // Balikin efek pudarnya barengan
  modalBackdrop.classList.add("opacity-0");
  modalBox.classList.remove("opacity-100", "scale-100");
  modalBox.classList.add("opacity-0", "scale-95");

  // Tunggu transisi beres (300ms), baru div utama di-hidden total
  setTimeout(() => {
    modalHapus.classList.add("hidden");
  }, 300);
}

// <!-- ==============================================================================
//    BAGIAN 3: JAVASCRIPT INTERAKTIF
//    ============================================================================== -->
document.addEventListener("DOMContentLoaded", function () {
  // 1. Toggle Lihat Password Perangkat
  const togglePasswordBtn = document.getElementById("togglePasswordBtn");
  const passwordInput = document.getElementById("passwordPerangkat");
  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener("click", function () {
      const isPassword = passwordInput.type === "password";
      passwordInput.type = isPassword ? "text" : "password";
    });
  }

  // 2. Toggle Pilihan Kondisi Awal Unit (Hidup / Mati / Blank)
  const kondisiBtns = document.querySelectorAll(
    "#groupKondisiUnit .kondisi-btn",
  );
  const inputKondisi = document.getElementById("inputKondisiUnit");
  kondisiBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      kondisiBtns.forEach((b) => {
        b.className =
          "kondisi-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer";
      });
      this.className =
        "kondisi-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition text-center cursor-pointer";
      if (inputKondisi) inputKondisi.value = this.getAttribute("data-val");
    });
  });

  // 3. Toggle Pilihan Jenis Layanan (Reguler / Express)
  const layananBtns = document.querySelectorAll("#groupLayanan .layanan-btn");
  const inputLayanan = document.getElementById("inputJenisLayanan");
  layananBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      layananBtns.forEach((b) => {
        b.className =
          "layanan-btn py-3 px-4 rounded-2xl border border-white-active bg-white-light text-blue-darker hover:bg-white-hover text-sm font-medium transition text-center cursor-pointer";
      });
      this.className =
        "layanan-btn py-3 px-4 rounded-2xl bg-tosca-dark text-white-light text-sm font-semibold shadow-xs transition text-center cursor-pointer";
      if (inputLayanan) inputLayanan.value = this.getAttribute("data-val");
    });
  });

  // 4. Update Style Persetujuan Bongkar saat Radio berubah (Tampilan Centang Persegi)
  const persetujuanCards = document.querySelectorAll(
    "#groupPersetujuan .persetujuan-card",
  );
  persetujuanCards.forEach((card) => {
    card.addEventListener("click", function () {
      persetujuanCards.forEach((c) => {
        c.className =
          "persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-white-active bg-white-light cursor-pointer hover:bg-white-hover transition select-none";
        const icon = c.querySelector(".check-box-icon");
        if (icon) {
          icon.className =
            "check-box-icon w-5 h-5 rounded-md border border-white-active bg-white-light flex items-center justify-center text-xs font-bold text-transparent transition";
        }
        const span = c.querySelector("span");
        if (span) {
          span.className = "text-sm font-medium text-blue-darker";
        }
        const radio = c.querySelector('input[type="radio"]');
        if (radio) radio.checked = false;
      });

      this.className =
        "persetujuan-card flex items-center gap-2.5 px-4 py-3 rounded-2xl border border-blue-darker/60 bg-white-light cursor-pointer transition select-none";
      const activeIcon = this.querySelector(".check-box-icon");
      if (activeIcon) {
        activeIcon.className =
          "check-box-icon w-5 h-5 rounded-md bg-tosca-dark text-white-light flex items-center justify-center text-xs font-bold transition";
      }
      const activeSpan = this.querySelector("span");
      if (activeSpan) {
        activeSpan.className = "text-sm font-semibold text-blue-darker";
      }
      const currentRadio = this.querySelector('input[type="radio"]');
      if (currentRadio) currentRadio.checked = true;
    });
  });
});







    //script filter karyawan
    document.addEventListener("DOMContentLoaded", function() {
      const searchInput = document.getElementById('searchKaryawanInput');
      const filterTabs = document.querySelectorAll('#filterGroup .filter-tab-btn');
      const rows = document.querySelectorAll('#tableBodyKaryawan .row-karyawan');

      let currentFilter = 'all';

      function filterTable() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          const role = row.getAttribute('data-role') || '';
          const status = row.getAttribute('data-status') || '';

          const matchSearch = text.includes(query);

          let matchFilter = false;
          if (currentFilter === 'all') {
            matchFilter = true;
          } else if (currentFilter === 'teknisi') {
            matchFilter = (role === 'teknisi');
          } else if (currentFilter === 'aktif') {
            matchFilter = (status === 'aktif');
          } else if (currentFilter === 'nonaktif') {
            matchFilter = (status === 'nonaktif');
          }

          row.style.display = (matchSearch && matchFilter) ? '' : 'none';
        });
      }

      if (searchInput) {
        searchInput.addEventListener('input', filterTable);
      }

      // Perubahan Class JS disamakan dengan gaya menu Antrean
      filterTabs.forEach(btn => {
        btn.addEventListener('click', function() {
          filterTabs.forEach(b => {
            b.className = 'filter-tab-btn px-6 py-2.5 rounded-full bg-white-light text-white-darker border border-gray-200 text-xs font-medium hover:bg-white-light-hover transition cursor-pointer whitespace-nowrap';
          });
          this.className = 'filter-tab-btn px-6 py-2.5 rounded-full bg-tosca-dark text-white-light text-xs font-semibold shadow-xs transition cursor-pointer whitespace-nowrap';
          currentFilter = this.getAttribute('data-filter') || 'all';
          filterTable();
        });
      });
    });
