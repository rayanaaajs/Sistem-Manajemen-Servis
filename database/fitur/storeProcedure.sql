
-- STORED PROCEDURE: Memperbarui status servis
-- Sekaligus mencatat perubahan ke tabel tracking_servis

CREATE OR REPLACE PROCEDURE sp_update_status_servis(
    IN p_no_nota VARCHAR,
    IN p_status_baru VARCHAR,
    IN p_id_karyawan INTEGER
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_karyawan_valid BOOLEAN;
BEGIN
    IF p_no_nota IS NULL OR BTRIM(p_no_nota) = '' THEN
        RAISE EXCEPTION 'Nomor nota tidak boleh kosong';
    END IF;

    IF p_status_baru IS NULL OR p_status_baru NOT IN (
        'Menunggu',
        'Sedang Dicek',
        'Menunggu Persetujuan',
        'Proses Servis',
        'Selesai',
        'Dibatalkan'
    ) THEN
        RAISE EXCEPTION 'Status pengerjaan tidak valid';
    END IF;

    IF p_id_karyawan IS NULL THEN
        RAISE EXCEPTION 'ID karyawan tidak boleh kosong';
    END IF;

    SELECT EXISTS (
        SELECT 1
        FROM karyawan
        WHERE id_karyawan = p_id_karyawan
    ) INTO v_karyawan_valid;

    IF NOT v_karyawan_valid THEN
        RAISE EXCEPTION 'ID karyawan tidak ditemukan';
    END IF;

    UPDATE servis
    SET status_pengerjaan = p_status_baru
    WHERE no_nota = p_no_nota;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Nomor nota % tidak ditemukan', p_no_nota;
    END IF;

    INSERT INTO tracking_servis (
        no_nota,
        keterangan,
        status_pengerjaan,
        id_karyawan
    )
    VALUES (
        p_no_nota,
        'Perubahan status melalui Stored Procedure',
        p_status_baru,
        p_id_karyawan
    );
END;
$$;
