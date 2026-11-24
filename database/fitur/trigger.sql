
-- FUNCTION: Mengurangi stok sparepart
-- ketika sparepart digunakan dalam detail servis

CREATE OR REPLACE FUNCTION fungsi_kurangi_stok_sparepart()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE sparepart
    SET stok_tersedia = stok_tersedia - NEW.jumlah_dipakai
    WHERE id_sparepart = NEW.id_sparepart;

    RETURN NEW;
END;
$$;

-- TRIGGER: Menjalankan function setelah data detail servis ditambahkan

CREATE TRIGGER trg_kurangi_stok_sparepart
AFTER INSERT ON detail_servis
FOR EACH ROW
EXECUTE FUNCTION fungsi_kurangi_stok_sparepart();
