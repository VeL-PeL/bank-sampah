-- =========================================================
-- DATABASE BANK SAMPAH
-- PHP Native + MySQL
-- =========================================================

CREATE DATABASE IF NOT EXISTS bank_sampah
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE bank_sampah;


-- =========================================================
-- 1. TABLE USERS
-- =========================================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'nasabah') NOT NULL DEFAULT 'nasabah',
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================================
-- 2. TABLE NASABAH
-- =========================================================

CREATE TABLE IF NOT EXISTS nasabah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    nomor_nasabah VARCHAR(30) NOT NULL UNIQUE,
    nik VARCHAR(20) NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NULL,
    no_hp VARCHAR(20) NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_nasabah_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 3. TABLE JENIS SAMPAH
-- =========================================================

CREATE TABLE IF NOT EXISTS jenis_sampah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_sampah VARCHAR(100) NOT NULL UNIQUE,
    harga_per_kg DECIMAL(15,2) NOT NULL DEFAULT 0,
    deskripsi TEXT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_jenis_sampah_status (status)
) ENGINE=InnoDB;


-- =========================================================
-- 4. TABLE SALDO
-- =========================================================

CREATE TABLE IF NOT EXISTS saldo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nasabah_id INT UNSIGNED NOT NULL UNIQUE,
    saldo DECIMAL(15,2) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_saldo_nasabah
        FOREIGN KEY (nasabah_id)
        REFERENCES nasabah(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_saldo_tidak_negatif
        CHECK (saldo >= 0)
) ENGINE=InnoDB;


-- =========================================================
-- 5. TABLE TRANSAKSI SETOR
-- =========================================================

CREATE TABLE IF NOT EXISTS transaksi_setor (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(50) NOT NULL UNIQUE,
    nasabah_id INT UNSIGNED NOT NULL,
    jenis_sampah_id INT UNSIGNED NOT NULL,
    berat DECIMAL(10,2) NOT NULL,
    harga_per_kg DECIMAL(15,2) NOT NULL,
    total_harga DECIMAL(15,2) NOT NULL,
    tanggal_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('berhasil', 'dibatalkan') NOT NULL DEFAULT 'berhasil',
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_transaksi_nasabah (nasabah_id),
    INDEX idx_transaksi_jenis_sampah (jenis_sampah_id),
    INDEX idx_transaksi_tanggal (tanggal_transaksi),
    INDEX idx_transaksi_status (status),

    CONSTRAINT fk_transaksi_nasabah
        FOREIGN KEY (nasabah_id)
        REFERENCES nasabah(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_transaksi_jenis_sampah
        FOREIGN KEY (jenis_sampah_id)
        REFERENCES jenis_sampah(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_berat_positif
        CHECK (berat > 0),

    CONSTRAINT chk_harga_tidak_negatif
        CHECK (harga_per_kg >= 0),

    CONSTRAINT chk_total_tidak_negatif
        CHECK (total_harga >= 0)
) ENGINE=InnoDB;


-- =========================================================
-- 6. TABLE PENARIKAN
-- =========================================================

CREATE TABLE IF NOT EXISTS penarikan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_penarikan VARCHAR(50) NOT NULL UNIQUE,
    nasabah_id INT UNSIGNED NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    metode ENUM('bank', 'e_wallet') NOT NULL,
    nomor_tujuan VARCHAR(100) NOT NULL,
    catatan TEXT NULL,
    status ENUM(
        'pending',
        'disetujui',
        'ditolak',
        'selesai'
    ) NOT NULL DEFAULT 'pending',
    tanggal_pengajuan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tanggal_diproses DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_penarikan_nasabah (nasabah_id),
    INDEX idx_penarikan_status (status),
    INDEX idx_penarikan_tanggal (tanggal_pengajuan),

    CONSTRAINT fk_penarikan_nasabah
        FOREIGN KEY (nasabah_id)
        REFERENCES nasabah(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_jumlah_penarikan
        CHECK (jumlah > 0)
) ENGINE=InnoDB;


-- =========================================================
-- DATA DUMMY
-- =========================================================


-- =========================================================
-- 7. USER ADMIN
-- Password: admin123
-- =========================================================

INSERT INTO users
(nama, email, password, role, status)
VALUES
(
    'Administrator',
    'admin@banksampah.test',
    '$2y$12$9fO5hr8PuLcbjaId..ucVuoWBLH5woAzGKARcTWTspibgE2AclFSm',
    'admin',
    'aktif'
);


-- =========================================================
-- 8. USER NASABAH
-- Password: nasabah123
-- =========================================================

INSERT INTO users
(nama, email, password, role, status)
VALUES
(
    'Budi Santoso',
    'budi@banksampah.test',
    '$2y$12$CkxDnzdpZpUW21OLCQ9imuJ8tZhl5PWso4xQbilqId9.4.eWeVGHW',
    'nasabah',
    'aktif'
);


-- =========================================================
-- 9. DATA NASABAH
-- =========================================================

INSERT INTO nasabah
(
    user_id,
    nomor_nasabah,
    nik,
    nama,
    alamat,
    no_hp,
    status
)
VALUES
(
    2,
    'NSB-000001',
    '3200000000000001',
    'Budi Santoso',
    'Jl. Contoh No. 1',
    '081234567890',
    'aktif'
);


-- =========================================================
-- 10. DATA JENIS SAMPAH
-- =========================================================

INSERT INTO jenis_sampah
(
    nama_sampah,
    harga_per_kg,
    deskripsi,
    status
)
VALUES
(
    'Botol Plastik',
    5000,
    'Botol plastik bekas yang sudah dipilah.',
    'aktif'
),
(
    'Kardus',
    3000,
    'Kardus atau karton bekas yang masih dapat didaur ulang.',
    'aktif'
),
(
    'Kertas',
    2500,
    'Kertas bekas yang sudah dipilah.',
    'aktif'
),
(
    'Besi',
    7000,
    'Besi bekas yang dapat didaur ulang.',
    'aktif'
),
(
    'Aluminium',
    12000,
    'Aluminium bekas.',
    'aktif'
),
(
    'Kaleng',
    6000,
    'Kaleng bekas yang sudah dipilah.',
    'aktif'
),
(
    'Plastik',
    4000,
    'Berbagai jenis plastik yang dapat didaur ulang.',
    'aktif'
);


-- =========================================================
-- 11. SALDO NASABAH
-- =========================================================

INSERT INTO saldo
(
    nasabah_id,
    saldo
)
VALUES
(
    1,
    0
);