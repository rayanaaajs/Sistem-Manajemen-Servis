console.log("File app.js berhasil dipanggil!");
// Aksi Toggle Sidebar Mobile
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const btnToggle = document.getElementById('btn-toggle');
    const overlay = document.getElementById('sidebar-overlay');

    if (btnToggle && sidebar && overlay) {
        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        btnToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    }
});

document.addEventListener('DOMContentLoaded', () => {
  // ==========================================================================
  // 1. BUKA / TUTUP MATA PASSWORD
  // ==========================================================================
  const passwordInput = document.getElementById('password');
  const togglePasswordBtn = document.getElementById('togglePassword');
  const iconEyeOpen = document.getElementById('iconEyeOpen');
  const iconEyeClosed = document.getElementById('iconEyeClosed');

  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', () => {
      const isPassword = passwordInput.getAttribute('type') === 'password';

      // Toggle input type
      passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

      // Toggle icon visibility
      if (isPassword) {
        iconEyeOpen.classList.add('hidden');
        iconEyeClosed.classList.remove('hidden');
        togglePasswordBtn.setAttribute('aria-label', 'Sembunyikan kata sandi');
        togglePasswordBtn.setAttribute('title', 'Sembunyikan kata sandi');
      } else {
        iconEyeOpen.classList.remove('hidden');
        iconEyeClosed.classList.add('hidden');
        togglePasswordBtn.setAttribute('aria-label', 'Tampilkan kata sandi');
        togglePasswordBtn.setAttribute('title', 'Tampilkan kata sandi');
      }

      // Keep focus on input for seamless UX
      passwordInput.focus();
    });
  }

  // ==========================================================================
  // 2. INTERAKSI "INGAT SAYA" (REMEMBER ME) DENGAN LOCALSTORAGE
  // ==========================================================================
  const rememberWrapper = document.getElementById('rememberWrapper');
  const rememberCheckbox = document.getElementById('rememberCheckbox');
  const rememberInput = document.getElementById('rememberMe');
  const usernameInput = document.getElementById('username');
  const loginForm = document.getElementById('loginForm');

  const STORAGE_REMEMBER_KEY = 'sms_remember_me';
  const STORAGE_USERNAME_KEY = 'sms_saved_username';

  // Restore saved state from localStorage
  try {
    const isRemembered = localStorage.getItem(STORAGE_REMEMBER_KEY) === 'true';
    const savedUsername = localStorage.getItem(STORAGE_USERNAME_KEY);

    if (isRemembered) {
      setRememberState(true);
      if (savedUsername && usernameInput) {
        usernameInput.value = savedUsername;
      }
    }
  } catch (err) {
    console.warn('LocalStorage tidak dapat diakses:', err);
  }

  function setRememberState(checked) {
    if (rememberInput) rememberInput.checked = checked;
    if (rememberCheckbox) {
      if (checked) {
        rememberCheckbox.classList.add('checked');
      } else {
        rememberCheckbox.classList.remove('checked');
      }
    }
  }

  function toggleRemember() {
    const newState = !rememberInput.checked;
    setRememberState(newState);

    try {
      if (newState) {
        localStorage.setItem(STORAGE_REMEMBER_KEY, 'true');
        if (usernameInput && usernameInput.value.trim()) {
          localStorage.setItem(STORAGE_USERNAME_KEY, usernameInput.value.trim());
        }
      } else {
        localStorage.removeItem(STORAGE_REMEMBER_KEY);
        localStorage.removeItem(STORAGE_USERNAME_KEY);
      }
    } catch (err) {
      console.warn('LocalStorage error:', err);
    }
  }

  if (rememberWrapper) {
    rememberWrapper.addEventListener('click', (e) => {
      e.preventDefault();
      toggleRemember();
    });

    // Keyboard accessibility for remember me (Space / Enter)
    rememberWrapper.addEventListener('keydown', (e) => {
      if (e.key === ' ' || e.key === 'Enter') {
        e.preventDefault();
        toggleRemember();
      }
    });
  }

  // Update saved username when typing if remember me is active
  if (usernameInput) {
    usernameInput.addEventListener('input', () => {
      if (rememberInput && rememberInput.checked) {
        try {
          localStorage.setItem(STORAGE_USERNAME_KEY, usernameInput.value.trim());
        } catch (err) {
          // ignore
        }
      }
    });
  }

  // Form submit handler
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      if (rememberInput && rememberInput.checked && usernameInput) {
        try {
          localStorage.setItem(STORAGE_REMEMBER_KEY, 'true');
          localStorage.setItem(STORAGE_USERNAME_KEY, usernameInput.value.trim());
        } catch (err) {
          // ignore
        }
      }
    });
  }

  // ==========================================================================
  // 3. ANIMASI NAIK TURUN 3 LINGKARAN DENGAN JAVASCRIPT
  // ==========================================================================
  const circleElements = document.querySelectorAll('.floating-circle');

  // Konfigurasi physics / osilasi unik untuk setiap lingkaran
  const circleConfigs = [
    {
      // Lingkaran 1: Wrench & Obeng (Atas Kanan)
      amplitude: 11, // Jarak naik-turun dalam piksel
      speed: 0.0019, // Frekuensi osilasi
      phase: 0,      // Offset sudut awal
      tilt: 1.5      // Derajat rotasi halus
    },
    {
      // Lingkaran 2: Handshake (Kiri)
      amplitude: 14,
      speed: 0.0015,
      phase: 2.2,
      tilt: -2
    },
    {
      // Lingkaran 3: Headset (Bawah Kanan)
      amplitude: 10,
      speed: 0.0022,
      phase: 4.4,
      tilt: 1.8
    }
  ];

  let animationFrameId = null;
  const isHovered = [false, false, false];


  function animateCircles(timestamp) {
    circleElements.forEach((el, index) => {
      const config = circleConfigs[index % circleConfigs.length];
      if (!config) return;

      // Jika sedang di-hover, beri sedikit efek levitasi ekstra
      if (isHovered[index]) {
        el.style.transform = `translateY(-6px) scale(1.06)`;
        return;
      }

      // Hitung posisi vertikal (naik-turun) berbasis gelombang sinus
      const angle = timestamp * config.speed + config.phase;
      const yOffset = Math.sin(angle) * config.amplitude;
      const subtleRotation = Math.cos(angle * 0.7) * config.tilt;

      // Terapkan translasi ke elemen
      el.style.transform = `translateY(${yOffset.toFixed(2)}px) rotate(${subtleRotation.toFixed(2)}deg)`;
    });

    animationFrameId = requestAnimationFrame(animateCircles);
  }

  // Mulai animasi
  animationFrameId = requestAnimationFrame(animateCircles);

  // Bersihkan animasi jika halaman ditinggalkan
  window.addEventListener('beforeunload', () => {
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
    }
  });
});













/* global Chart */

document.addEventListener("DOMContentLoaded", function() {

  // ==============================================================================
  // 1. FITUR PENCARIAN REAL-TIME PADA TABEL SERVIS
  // ==============================================================================
const searchInput = document.getElementById('searchPelanggan');
const tableBody = document.getElementById('tableBodyServis');

if (searchInput && tableBody) {
    searchInput.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const rows = tableBody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
    });
}

  // ==============================================================================
  // 2. INTERAKSI TOMBOL FILTER WAKTU (Hari ini, 7 Hari, 30 Hari)
  // ==============================================================================
const filterButtons = document.querySelectorAll('#filterWaktuGroup .filter-btn');

if (filterButtons.length > 0) {
    filterButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        // Reset semua tombol ke tampilan putih / pasif
        filterButtons.forEach(b => {
        b.className = 'filter-btn px-5 py-1.5 rounded-full bg-white text-gray-700 border border-gray-200 text-xs font-medium shadow-2xs hover:bg-gray-50 transition cursor-pointer';
        });
        // Beri gaya warna biru tua / aktif pada tombol yang diklik
        this.className = 'filter-btn px-5 py-1.5 rounded-full bg-[#0D4F56] text-white text-xs font-semibold shadow-xs transition cursor-pointer';
    });
    });
}

  // ==============================================================================
  // 3. INISIALISASI GRAFIK CHART.JS (DIAGRAM GARIS SERVIS)
  // ==============================================================================
const canvasGrafik = document.getElementById('servisChart');

  // Pastikan elemen canvas dan variabel data dari PHP (window.grafikServisData) tersedia
if (canvasGrafik && typeof window.grafikServisData !== 'undefined') {
    
    const grafikData = window.grafikServisData;
    
    // Mapping data untuk format Chart.js
    const labels = grafikData.map(d => d.minggu);
    const dataMasuk = grafikData.map(d => d.masuk);
    const dataSelesai = grafikData.map(d => d.selesai);

    const ctx = canvasGrafik.getContext('2d');
    
    // Buat Grafik Garis
    new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
        {
            label: 'Masuk',
            data: dataMasuk,
            borderColor: '#F26B21',
            backgroundColor: '#F26B21',
            borderWidth: 3,
            tension: 0.4, // melengkung halus
            pointRadius: 4,
            pointBackgroundColor: '#F26B21',
        },
        {
            label: 'Selesai',
            data: dataSelesai,
            borderColor: '#0D4F56',
            backgroundColor: '#0D4F56',
            borderWidth: 3,
            tension: 0.4, // melengkung halus
            pointRadius: 4,
            pointBackgroundColor: '#0D4F56',
        }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
        legend: { display: false }, 
        tooltip: { enabled: true } 
        },
        scales: {
        x: { 
            grid: { display: false } 
        },
        y: { 
            grid: { color: '#F1F5F9' }, 
            beginAtZero: true 
        }
        }
    }
    });
    }

});
