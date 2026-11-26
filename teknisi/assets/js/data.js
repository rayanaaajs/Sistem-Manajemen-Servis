/* Data dummy (hanya di browser). Tidak terhubung ke database. */
(function () {
  const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

  const CUSTOMERS = [
    ['Sari Lestari','081234567801','Jl. Ijen Boulevard 12, Malang'], ['Budi Santoso','081938274650','Jl. Veteran 45, Malang'],
    ['Rizal Mazda','081738742567','Jl. Soekarno Hatta 27 K9, Malang'], ['Dewi Anggraini','085745120933','Jl. Sigura-gura 8, Malang'],
    ['Agus Prasetyo','082134509871','Jl. Kawi 19, Malang'], ['Nadia Putri','087755310246','Jl. Bunga Merak 3, Malang'],
    ['Fajar Ramadhan','081332456780','Jl. Candi Panggung 21, Malang'], ['Melati Kusuma','089612340055','Jl. Dieng 77, Malang']
  ];
  const DEVICES = [
    ['Lenovo LOQ 15','Laptop','Intel i5 · RAM 16 GB · RTX 3050'], ['ASUS ROG Strix G15','Laptop','ROG · i9 · RAM 64 GB'],
    ['Acer Nitro 5','Laptop','Ryzen 5 · RAM 8 GB · GTX 1650'], ['PC Rakitan Gaming','PC','Ryzen 7 · RAM 32 GB · RTX 3060'],
    ['HP Pavilion 14','Laptop','Intel i3 · RAM 8 GB · SSD 256 GB'], ['MSI Modern 14','Laptop','Intel i5 · RAM 16 GB · SSD 512 GB']
  ];
  const STATUS = ['Selesai','Dikerjakan','Diterima','Menunggu','Menunggu','Dibatalkan'];
  const KELUHAN = ['PC saya mati','Laptop sering restart','Layar bergaris','Baterai cepat habis','Tidak bisa masuk Windows','Kipas berisik dan panas'];

  const services = [];
  for (let i = 0; i < 10; i++) {
    const first = i < 6;
    const c = first ? CUSTOMERS[0] : CUSTOMERS[i % CUSTOMERS.length];
    const dv = first ? DEVICES[0] : DEVICES[i % DEVICES.length];
    const masuk = new Date(2026, 9, first ? 5 : 5 - (i % 4));
    const est = new Date(masuk); est.setDate(est.getDate() + (first ? 1 : 1 + (i % 3)));
    services.push({
      nota: String(5143 + i), status: STATUS[i % 6], layanan: first || i % 3 ? 'Express' : 'Reguler',
      device: dv[0], jenis: dv[1], spec: dv[2], pelanggan: c[0], nomor: c[1], alamat: c[2],
      kondisi: i % 4 === 3 ? 'Mati' : 'Hidup', kelengkapan: 'Tas Laptop, Charger Laptop', password: 'rahasia123',
      bongkar: i % 6 !== 2, persetujuan: i % 6 === 2 ? '' : 'setuju', teknisi: 'Ilham',
      keluhan: KELUHAN[i % KELUHAN.length], catatan: '', masuk: iso(masuk), estimasi: iso(est)
    });
  }
  // Perubahan yang "disimpan" dari Edit Servis (hanya selama sesi browser)
  const over = SC.store.get('sc_overrides', {});
  services.forEach(s => Object.assign(s, over[s.nota] || {}));
  SC.services = services;
  SC.saveOverride = (id, data) => { over[id] = data; SC.store.set('sc_overrides', over); };
  SC.stats = { selesai: 49, bulanLalu: 45, delta: '+9%' };

  // Katalog sparepart (10 item)
  const seed = [[12,5],[12,5],[5,5],[12,5],[5,5],[0,5]].map((v, i) => ({ id: 's' + (i + 1), name: 'RAM DDR4 8GB SODIMM', stock: v[0], min: v[1], price: 285000, used: 8 }));
  [['RAM 16GB Fury Cube',9,4,150000,14],['GPU 16GB Fury Cube',3,3,150000,6],['GPU Fury Cube',7,3,150000,5],['SSD 256GB Fury Cube',10,5,150000,12]]
    .forEach((v, i) => seed.push({ id: 's' + (7 + i), name: v[0], stock: v[1], min: v[2], price: v[3], used: v[4] }));



  SC.statusOf = p => p.stock <= 0 ? 'Habis' : (p.stock <= p.min ? 'Menipis' : 'Aman');
  SC.initials = n => { const w = n.trim().split(/\s+/)[0] || '??'; return (w[0] + w[w.length - 1]).toUpperCase(); };
  SC.parts = () => { let p = SC.store.get('sc_parts_v6', null); if (!p) { p = seed; SC.store.set('sc_parts_v6', p); } return p; };
  SC.saveParts = p => SC.store.set('sc_parts_v6', p);

  // Sparepart yang dipakai pada suatu servis (daftar di Edit Servis)
  SC.svcParts = id => SC.store.get('sc_svc_parts_' + id, [
    { partId: 's7', name: 'RAM 16GB Fury Cube', qty: 2, price: 150000 }, { partId: 's8', name: 'GPU 16GB Fury Cube', qty: 2, price: 150000 },
    { partId: 's9', name: 'GPU Fury Cube', qty: 2, price: 150000 }, { partId: 's10', name: 'SSD 256GB Fury Cube', qty: 2, price: 150000 }
  ]);
  SC.saveSvcParts = (id, list) => SC.store.set('sc_svc_parts_' + id, list);
})();
