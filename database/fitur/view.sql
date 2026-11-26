CREATE OR REPLACE VIEW vw_antrean_servis AS
SELECT
    s.no_nota,
    p.nama_pelanggan,
    p.no_whatsapp,
    s.jenis_barang,
    s.merek_dan_spesifikasi,
    s.keluhan_kerusakan,
    k.nama_karyawan AS nama_teknisi,
    s.tanggal_masuk,
    s.estimasi_pengambilan,
    s.status_pengerjaan,
    s.jenis_layanan
FROM servis s
JOIN pelanggan p
    ON s.id_pelanggan = p.id_pelanggan
LEFT JOIN karyawan k
    ON s.id_karyawan = k.id_karyawan;

SELECT * FROM vw_antrean_servis;
