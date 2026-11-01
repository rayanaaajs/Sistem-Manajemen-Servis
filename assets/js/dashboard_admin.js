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