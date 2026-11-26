CREATE TABLE karyawan (
    id_karyawan    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_karyawan  VARCHAR(100) NOT NULL,
    username       VARCHAR(50)  NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,          -- isi dengan hasil password_hash() PHP
    peran          VARCHAR(20)  NOT NULL
                   CHECK (peran IN ('Admin', 'Teknisi'))
    email          VARCHAR(255) UNIQUE
);

CREATE TABLE pelanggan (
    id_pelanggan    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_pelanggan  VARCHAR(50)  NOT NULL,
    no_whatsapp     VARCHAR(20)  NOT NULL,
    alamat          VARCHAR(255)
);

CREATE TABLE sparepart (
    id_sparepart    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_sparepart  VARCHAR(100)  NOT NULL,
    stok_tersedia   INTEGER       NOT NULL DEFAULT 0 CHECK (stok_tersedia >= 0),
    harga_satuan    NUMERIC(12,2) NOT NULL CHECK (harga_satuan >= 0)
);

CREATE SEQUENCE seq_no_nota START WITH 1 INCREMENT BY 1;

CREATE TABLE servis (
    no_nota               VARCHAR(10) PRIMARY KEY
                          DEFAULT ('SC-' || LPAD(nextval('seq_no_nota')::TEXT, 3, '0')),
    tanggal_masuk         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    jenis_barang          VARCHAR(30)   NOT NULL,
    merek_dan_spesifikasi VARCHAR(255),
    kelengkapan_barang    VARCHAR(255),
    kondisi_awal_unit     VARCHAR(255),
    password_perangkat    VARCHAR(255),            -- simpan dalam bentuk terenkripsi (AES), bukan plain text
    keluhan_kerusakan     VARCHAR(255)  NOT NULL,
    catatan_cek_fisik     VARCHAR(255),
    persetujuan_barang    VARCHAR(10)
                          CHECK (persetujuan_barang IN ('Ya', 'Tidak')),
    biaya_jasa            NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (biaya_jasa >= 0),
    status_pengerjaan     VARCHAR(50)   NOT NULL DEFAULT 'Menunggu'
                          CHECK (status_pengerjaan IN (
                              'Menunggu', 'Sedang Dicek', 'Menunggu Persetujuan',
                              'Proses Servis', 'Selesai', 'Dibatalkan')),
    id_pelanggan          INTEGER       NOT NULL REFERENCES pelanggan (id_pelanggan),
    id_karyawan           INTEGER       REFERENCES karyawan (id_karyawan),   -- teknisi yang menangani
    estimasi_pengambilan  TIMESTAMP,
    jenis_layanan         VARCHAR(30),
    biaya_urgent          NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (biaya_urgent >= 0)
);

ALTER SEQUENCE seq_no_nota OWNED BY servis.no_nota;

CREATE TABLE detail_servis (
    id_detail       INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    jumlah_dipakai  INTEGER       NOT NULL CHECK (jumlah_dipakai > 0),
    subtotal_harga  NUMERIC(12,2) NOT NULL CHECK (subtotal_harga >= 0),
    no_nota         VARCHAR(10)   NOT NULL REFERENCES servis (no_nota),
    id_sparepart    INTEGER       NOT NULL REFERENCES sparepart (id_sparepart),
    harga_satuan    NUMERIC(12,2) NOT NULL CHECK (harga_satuan >= 0)
);

CREATE TABLE tracking_servis (
    id_tracking        INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    no_nota            VARCHAR(10)  NOT NULL REFERENCES servis (no_nota),
    keterangan         VARCHAR(255),
    waktu_update       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_pengerjaan  VARCHAR(50)  NOT NULL
                       CHECK (status_pengerjaan IN (
                           'Menunggu', 'Sedang Dicek', 'Menunggu Persetujuan',
                           'Proses Servis', 'Selesai', 'Dibatalkan')),
    id_karyawan        INTEGER      NOT NULL REFERENCES karyawan (id_karyawan)  -- siapa yang update
);

CREATE TABLE validasi (
    no_nota             VARCHAR(10) PRIMARY KEY REFERENCES servis (no_nota),
    status_pembayaran   VARCHAR(20) NOT NULL DEFAULT 'Belum Lunas',
    status_pengambilan  VARCHAR(20) NOT NULL DEFAULT 'Belum Diambil',
    tanggal_diambil     TIMESTAMP                                   -- NULL sampai barang diambil
);
