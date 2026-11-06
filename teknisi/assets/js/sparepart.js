(function () {
  const mode = document.body.dataset.mode; // 'kelola' | 'tambah'
  const PER = 6;
  let parts = SC.parts();
  const st = { q: '', f: 'Semua', page: 1 };
  const table = document.getElementById('table');
  const svcId = SC.param('service') || SC.store.get('sc_current', SC.services[0].nota);

  if (mode === 'tambah') document.getElementById('context').textContent = 'Untuk servis #' + svcId;

  const filtered = () => {
    const q = st.q.trim().toLowerCase();
    return parts.filter(p => (st.f === 'Semua' || SC.statusOf(p) === st.f) && (!q || p.name.toLowerCase().includes(q)));
  };

  function render() {
    const list = filtered(), pages = Math.max(1, Math.ceil(list.length / PER));
    if (st.page > pages) st.page = pages;
    const start = (st.page - 1) * PER, slice = list.slice(start, start + PER);
    const head = '<div class="tr th"><span>Nama Sparepart</span><span>Stok</span><span>Status</span><span>Harga Satuan</span><span>Terpakai</span><span>Aksi</span></div>';
    const rows = slice.map(p => {
      const s = SC.statusOf(p);
      const acts = mode === 'kelola'
        ? `<button class="btn btn-outline btn-sm" data-a="edit">Edit</button><button class="btn btn-dark btn-sm" data-a="detail">Detail</button><button class="btn btn-danger btn-sm" data-a="hapus">Hapus</button>`
        : `<button class="btn btn-outline btn-sm" data-a="tambah">Tambah</button>`;
      return `<div class="tr td" data-id="${p.id}"><div class="name"><span class="ico">${SC.esc(SC.initials(p.name))}</span>${SC.esc(p.name)}</div>
        <div class="stok" data-l="Stok"><b>${p.stock} pcs</b><small>min. ${p.min}</small></div><span data-l="Status"><span class="badge ${SC.statusClass(s)}">${s}</span></span>
        <span class="price" data-l="Harga Satuan">${SC.rp(p.price)}</span><span data-l="Terpakai">${p.used} item</span><div class="acts">${acts}</div></div>`;
    }).join('');
    table.innerHTML = head + (rows || '<div class="empty">Sparepart tidak ditemukan. Ubah kata kunci atau filter.</div>');
    document.getElementById('info').textContent = list.length ? `Menampilkan ${start + 1}-${start + slice.length} dari ${list.length} sparepart` : 'Menampilkan 0 sparepart';
    SC.pager(document.getElementById('pager'), st.page, pages, p => { st.page = p; render(); });
    const all = document.getElementById('chip-all'); if (all) all.textContent = 'Semua ' + parts.length;
  }

  /* ---- aksi baris ---- */
  table.addEventListener('click', e => {
    const b = e.target.closest('[data-a]'); if (!b) return;
    const p = parts.find(x => x.id === b.closest('[data-id]').dataset.id);
    ({ tambah: addToService, edit: editPart, detail: showDetail, hapus: delPart })[b.dataset.a](p);
  });

  function addToService(p) {
    SC.modal({
      title: 'Tambah ' + p.name,
      body: `<p>Stok tersedia: <b>${p.stock} pcs</b> · ${SC.rp(p.price)}/unit</p><label for="qty" style="display:block;margin:10px 0 6px;font-weight:700">Jumlah yang digunakan</label>
             <input class="input qty-input" id="qty" type="number" min="1" value="1"><div class="form-error" id="err"></div>`,
      actions: [{ label: 'Batal' }, { label: 'Tambahkan', cls: 'btn-primary', onClick: ov => {
        const q = Number(ov.querySelector('#qty').value), err = ov.querySelector('#err');
        if (!Number.isInteger(q) || q < 1) { err.textContent = 'Masukkan jumlah minimal 1.'; return false; }
        if (p.stock <= 0) { err.textContent = 'Stok habis, sparepart tidak bisa ditambahkan.'; return false; }
        const list = SC.svcParts(svcId), ex = list.find(x => x.partId === p.id), total = q + (ex ? ex.qty : 0);
        if (total > p.stock) { err.textContent = `Jumlah melebihi stok. Maksimal ${p.stock} pcs${ex ? ' (sudah dipakai ' + ex.qty + ')' : ''}.`; return false; }
        if (ex) ex.qty = total; else list.push({ partId: p.id, name: p.name, qty: q, price: p.price });
        SC.saveSvcParts(svcId, list);
        SC.toast(`${q} unit ${p.name} ditambahkan ke servis #${svcId}.`, 'success', { label: 'Lihat Edit Servis', href: 'edit_servis.html?id=' + svcId });
      } }]
    });
  }

  function editPart(p) {
    SC.modal({
      title: 'Edit Sparepart',
      body: `<div class="field"><label>Nama Sparepart</label><input class="input" id="e-name" value="${SC.esc(p.name)}"></div>
             <div class="row2"><div class="field"><label>Stok</label><input class="input" id="e-stock" type="number" min="0" value="${p.stock}"></div>
             <div class="field"><label>Stok minimum</label><input class="input" id="e-min" type="number" min="0" value="${p.min}"></div></div>
             <div class="field"><label>Harga satuan (Rp)</label><input class="input" id="e-price" type="number" min="0" value="${p.price}"></div><div class="form-error" id="err"></div>`,
      actions: [{ label: 'Batal' }, { label: 'Simpan', cls: 'btn-primary', onClick: ov => {
        const v = k => ov.querySelector('#e-' + k).value, name = v('name').trim();
        const stock = Number(v('stock')), min = Number(v('min')), price = Number(v('price'));
        if (!name || [stock, min, price].some(n => !Number.isFinite(n) || n < 0)) { ov.querySelector('#err').textContent = 'Lengkapi nama dan pastikan angka tidak negatif.'; return false; }
        Object.assign(p, { name, stock, min, price }); SC.saveParts(parts); render(); SC.toast('Sparepart berhasil diperbarui.');
      } }]
    });
  }

  function showDetail(p) {
    SC.modal({
      title: 'Detail Sparepart',
      body: `<dl class="dl"><dt>Nama</dt><dd>${SC.esc(p.name)}</dd><dt>Stok</dt><dd>${p.stock} pcs</dd><dt>Stok minimum</dt><dd>${p.min} pcs</dd>
             <dt>Status</dt><dd><span class="badge ${SC.statusClass(SC.statusOf(p))}">${SC.statusOf(p)}</span></dd><dt>Harga satuan</dt><dd>${SC.rp(p.price)}</dd><dt>Terpakai</dt><dd>${p.used} item</dd></dl>`,
      actions: [{ label: 'Tutup', cls: 'btn-dark' }]
    });
  }

  function delPart(p) {
    SC.modal({
      title: 'Hapus sparepart?', body: `<b>${SC.esc(p.name)}</b> akan dihapus dari daftar. Tindakan ini tidak dapat dibatalkan.`,
      actions: [{ label: 'Batal' }, { label: 'Hapus', cls: 'btn-primary', onClick: () => {
        parts = parts.filter(x => x.id !== p.id); SC.saveParts(parts); render(); SC.toast('Sparepart dihapus.');
      } }]
    });
  }

  document.getElementById('search').addEventListener('input', e => { st.q = e.target.value; st.page = 1; render(); });
  const chips = document.getElementById('chips');
  if (chips) chips.addEventListener('click', e => {
    const b = e.target.closest('.chip'); if (!b) return;
    chips.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === b));
    st.f = b.dataset.filter; st.page = 1; render();
  });
  render();
})();
