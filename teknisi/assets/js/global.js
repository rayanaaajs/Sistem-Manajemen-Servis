/* Utilitas bersama (tanpa backend) */
window.SC = window.SC || {};

SC.esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
SC.rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
SC.rp2 = n => 'Rp.' + Number(n).toLocaleString('id-ID');
SC.param = k => new URLSearchParams(location.search).get(k);
SC.statusClass = s => 'b-' + String(s).toLowerCase();

SC.store = {
  get(k, d) { try { const v = sessionStorage.getItem(k); return v ? JSON.parse(v) : d; } catch (e) { return d; } },
  set(k, v) { try { sessionStorage.setItem(k, JSON.stringify(v)); } catch (e) {} },
  del(k) { try { sessionStorage.removeItem(k); } catch (e) {} }
};

SC.toast = (msg, type = 'success', action) => {
  const box = document.getElementById('toasts');
  const t = document.createElement('div');
  t.className = 'toast ' + (type === 'error' ? 'error' : '');
  t.innerHTML = '<span>' + SC.esc(msg) + '</span>' + (action ? '<a href="' + SC.esc(action.href) + '">' + SC.esc(action.label) + '</a>' : '');
  box.appendChild(t);
  setTimeout(() => t.remove(), action ? 6000 : 3500);
};

SC.modal = ({ title, body, actions = [] }) => {
  const ov = document.createElement('div');
  ov.className = 'modal-overlay';
  ov.innerHTML = '<div class="modal" role="dialog" aria-modal="true"><h3>' + SC.esc(title) + '</h3><div class="modal-body">' + body + '</div><div class="modal-actions"></div></div>';
  const close = () => { ov.remove(); document.removeEventListener('keydown', onKey); };
  const onKey = e => { if (e.key === 'Escape') close(); };
  document.addEventListener('keydown', onKey);
  ov.addEventListener('mousedown', e => { if (e.target === ov) close(); });
  const bar = ov.querySelector('.modal-actions');
  actions.forEach(a => {
    const b = document.createElement('button');
    b.type = 'button'; b.className = 'btn ' + (a.cls || 'btn-outline'); b.textContent = a.label;
    b.addEventListener('click', () => { if (!a.onClick || a.onClick(ov) !== false) close(); });
    bar.appendChild(b);
  });
  document.body.appendChild(ov);
  const first = ov.querySelector('input,select');
  if (first) first.focus();
  return { close, el: ov };
};

SC.pager = (el, page, pages, go) => {
  let h = '<button ' + (page <= 1 ? 'disabled' : '') + ' data-p="' + (page - 1) + '" aria-label="Sebelumnya">&lt;</button>';
  for (let i = 1; i <= pages; i++) h += '<button class="' + (i === page ? 'active' : '') + '" data-p="' + i + '">' + i + '</button>';
  h += '<button ' + (page >= pages ? 'disabled' : '') + ' data-p="' + (page + 1) + '" aria-label="Berikutnya">&gt;</button>';
  el.innerHTML = h;
  el.onclick = e => { const b = e.target.closest('button[data-p]'); if (b && !b.disabled) go(+b.dataset.p); };
};

SC.short = iso => { const d = new Date(iso + 'T00:00:00'); return d.getDate() + ' ' + ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][d.getMonth()]; };
