/* Sidebar + header bersama (dirender dari atribut data-* pada <body>) */
(function () {
  const b = document.body.dataset;
  const H = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const M = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const d = new Date();
  const tgl = `${H[d.getDay()]}, ${d.getDate()} ${M[d.getMonth()]} ${d.getFullYear()}`;
  document.title = b.title + ' - Singhasari Computer';

  document.getElementById('sidebar').innerHTML = `
    <a class="brand" href="dashboard.html">
      <img class="brand-logo" src="assets/img/logo-polos.png" alt="Logo Singhasari Computer" onerror="this.style.display='none'">
      <span class="brand-name">SINGHASARI</span><span class="brand-sub">COMPUTER</span>
    </a>
    <nav class="nav">
      <a href="dashboard.html" class="${b.active === 'dashboard' ? 'active' : ''}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18"/><path d="M6 16v-5M11 16V6M16 16v-8M20 16v-3"/></svg>Dashboard</a>
      <a href="sparepart.html" class="${b.active === 'sparepart' ? 'active' : ''}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3v8M4 3v4a3 3 0 0 0 6 0V3M7 11v10M17 3c-2 2-3 5-3 8h3v10"/></svg>Sparepart</a>
    </nav>
    <div class="copy">Copyright 2026<br>© Singhasari Computer</div>`;

  document.getElementById('page-head').innerHTML = `
    <div><div class="date">${tgl}</div><h1>${SC.esc(b.title)}</h1></div>
    <div class="head-right">
      ${b.search ? `<label class="search"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" id="search" placeholder="${SC.esc(b.search)}" autocomplete="off"></label>` : ''}
      <div class="user-chip"><span class="avatar">TK</span>Teknisi</div>
    </div>`;
})();
