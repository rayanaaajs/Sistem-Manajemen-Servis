(function () {
  const page = document.body.dataset.page; // 'edit' | 'detail'
  const svc = SC.services.find(s => s.nota === SC.param('id')) || SC.services[0];
  const id = svc.nota;
  const $ = s => document.querySelector(s);
  const fields = [...document.querySelectorAll('[data-f]')];
  SC.store.set('sc_current', id);

  /* isi data (ditimpa draft jika baru kembali dari halaman Tambah Sparepart) */
  const draft = page === 'edit' ? SC.store.get('sc_draft_' + id, null) : null;
  const data = Object.assign({}, svc, draft || {});
  fields.forEach(el => { const v = data[el.dataset.f]; if (el.type === 'checkbox') el.checked = !!v; else el.value = v ?? ''; });
  const t = document.querySelector('[data-f-text="teknisi"]'); if (t) t.textContent = svc.teknisi;

  /* tampil/sembunyikan password */
  $('#toggle-pass').addEventListener('click', () => { const p = $('#f-password'); p.type = p.type === 'password' ? 'text' : 'password'; });

  if (page === 'detail') {
    $('#btn-edit').href = 'edit_servis.html?id=' + id;
    const L = [['Diterima','Unit diterima oleh admin.'],['Diperiksa','Pemeriksaan awal selesai.'],['Masuk Antrean','Telah masuk ke antrean.'],
      ['Mulai Mengerjakan','Teknisi mulai mengerjakan servis.'],['Menunggu Persetujuan','Ditemukan kerusakan tambahan. Menunggu konfirmasi Anda.'],['Dikerjakan','Perangkat sedang diperbaiki.'],['Selesai','Servis selesai dan siap diambil.']];
    const stage = { Diterima: 0, Menunggu: 4, Dikerjakan: 5, Selesai: 7, Dibatalkan: 1 }[svc.status];
    $('#timeline').innerHTML = L.map((x, i) => {
      let cls = i < stage ? 'done' : i === stage ? 'now' : 'todo';
      if (svc.status === 'Dibatalkan' && i === stage) cls = 'cancel';
      const sub = i <= stage ? x[1] : 'Belum dimulai';
      return `<li class="tl ${cls}"><span class="dot"></span><div><b>${x[0]}</b><small>${sub}</small></div></li>`;
    }).join('');
    return;
  }

  /* ===== Edit Servis ===== */
  let parts = SC.svcParts(id);
  const badge = $('#track-badge'), statusSel = $('#f-status');
  const paintBadge = () => { badge.textContent = statusSel.value; badge.className = 'badge ' + SC.statusClass(statusSel.value); };
  statusSel.addEventListener('change', paintBadge); paintBadge();
  $('#p-setuju').checked = data.persetujuan === 'setuju'; $('#p-tidak').checked = data.persetujuan === 'tidak';

  function renderParts() {
    const box = $('#part-list');
    box.innerHTML = parts.length ? parts.map((p, i) => `
      <div class="part-row"><div class="part-box"><span class="pn" title="${SC.esc(p.name)}">${SC.esc(p.name)}</span><span class="pq">${p.qty} unit</span><span class="pp">${SC.rp2(p.qty * p.price)}</span></div>
      <button type="button" class="btn btn-danger btn-sm" data-del="${i}">Hapus</button></div>`).join('')
      : '<div class="part-empty">Belum ada sparepart. Klik "+ Tambah Sparepart".</div>';
    $('#total').textContent = SC.rp2(parts.reduce((a, p) => a + p.qty * p.price, 0));
  }
  $('#part-list').addEventListener('click', e => {
    const b = e.target.closest('[data-del]'); if (!b) return;
    const [rm] = parts.splice(+b.dataset.del, 1);
    SC.saveSvcParts(id, parts); renderParts(); SC.toast('"' + rm.name + '" dihapus dari daftar.');
  });
  renderParts();

  function collect() {
    const o = {};
    fields.forEach(el => { o[el.dataset.f] = el.type === 'checkbox' ? el.checked : el.value; });
    o.persetujuan = $('#p-setuju').checked ? 'setuju' : $('#p-tidak').checked ? 'tidak' : '';
    return o;
  }
  const saveDraft = () => SC.store.set('sc_draft_' + id, collect());

  $('#btn-add-part').addEventListener('click', e => { e.preventDefault(); saveDraft(); location.href = 'tambah_sparepart.html?service=' + id; });

  $('#btn-batal').addEventListener('click', e => {
    if (document.referrer && history.length > 1) { e.preventDefault(); SC.store.del('sc_draft_' + id); history.back(); }
  });

  $('#btn-save').addEventListener('click', () => {
    const o = collect();
    if (!o.pelanggan.trim() || !o.nomor.trim()) { SC.toast('Nama pelanggan dan nomor wajib diisi.', 'error'); return; }
    SC.saveOverride(id, o); SC.store.del('sc_draft_' + id);
    SC.toast('Perubahan servis #' + id + ' berhasil disimpan (simulasi).');
  });
})();
