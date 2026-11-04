(function () {
  const PER = 6;
  const st = { q: '', f: 'Semua', page: 1 };
  const grid = document.getElementById('svc-grid');

  function filtered() {
    const q = st.q.trim().toLowerCase();
    return SC.services.filter(s => {
      if (st.f === 'Express' || st.f === 'Reguler') { if (s.layanan !== st.f) return false; }
      else if (st.f !== 'Semua' && s.status !== st.f) return false;
      return !q || [s.nota, s.device, s.pelanggan, s.status, s.layanan].join(' ').toLowerCase().includes(q);
    });
  }

  function stats() {
    const S = SC.services;
    const antre = S.filter(s => s.status !== 'Selesai' && s.status !== 'Dibatalkan').length;
    const kerja = S.filter(s => s.status === 'Dikerjakan').length;
    document.getElementById('st-selesai').textContent = SC.stats.selesai;
    document.getElementById('st-prev').textContent = 'Bulan lalu: ' + SC.stats.bulanLalu;
    document.getElementById('st-delta').textContent = SC.stats.delta;
    document.getElementById('st-antrean').textContent = antre;
    document.getElementById('st-menunggu').textContent = 'Menunggu: ' + S.filter(s => s.status === 'Menunggu').length;
    document.getElementById('st-kerja').textContent = kerja;
    document.getElementById('st-unit').textContent = kerja + ' unit';
    document.getElementById('chip-all').textContent = 'Semua ' + S.length;
  }

  function render() {
    const list = filtered();
    const pages = Math.max(1, Math.ceil(list.length / PER));
    if (st.page > pages) st.page = pages;
    const start = (st.page - 1) * PER, slice = list.slice(start, start + PER);
    grid.innerHTML = slice.length ? slice.map(s => `
      <article class="svc-card">
        <div class="svc-top"><span class="svc-nota">#${s.nota}</span><span class="svc-type ${s.layanan.toLowerCase()}">${s.layanan}</span><span class="badge ${SC.statusClass(s.status)}">${s.status}</span></div>
        <h3>${SC.esc(s.device)}</h3><p class="svc-cust">${SC.esc(s.pelanggan)}</p>
        <div class="svc-foot"><span>Estimasi ${SC.short(s.estimasi)} · Masuk ${SC.short(s.masuk)}</span>
          <div><a class="btn btn-outline btn-sm" href="edit_servis.html?id=${s.nota}">Edit</a><a class="btn btn-dark btn-sm" href="detail_servis.html?id=${s.nota}">Detail</a></div></div>
      </article>`).join('') : '<div class="empty">Tidak ada antrean yang cocok. Ubah kata kunci atau filter.</div>';
    document.getElementById('info').textContent = list.length ? `Menampilkan ${start + 1}-${start + slice.length} dari ${list.length} antrean` : 'Menampilkan 0 antrean';
    SC.pager(document.getElementById('pager'), st.page, pages, p => { st.page = p; render(); });
  }

  document.getElementById('search').addEventListener('input', e => { st.q = e.target.value; st.page = 1; render(); });
  document.getElementById('chips').addEventListener('click', e => {
    const b = e.target.closest('.chip'); if (!b) return;
    document.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === b));
    st.f = b.dataset.filter; st.page = 1; render();
  });
  stats(); render();
})();
