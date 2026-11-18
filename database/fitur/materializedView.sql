
-- MATERIALIZED VIEW: Rekap jumlah servis bulanan

CREATE MATERIALIZED VIEW mv_rekap_servis_bulanan AS
SELECT
    DATE_TRUNC('month', tanggal_masuk) AS bulan,
    COUNT(*) AS total_servis
FROM servis
GROUP BY DATE_TRUNC('month', tanggal_masuk)
ORDER BY bulan;
