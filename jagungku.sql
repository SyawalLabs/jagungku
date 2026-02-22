-- Tabel lahan
CREATE TABLE
    lahan (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_lahan VARCHAR(100),
        luas_hektar DECIMAL(5, 2),
        lokasi VARCHAR(255),
        jenis_tanah VARCHAR(50)
    );

-- Tabel bibit
CREATE TABLE
    bibit (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_bibit VARCHAR(100),
        sumber VARCHAR(100),
        harga_per_kg DECIMAL(10, 2)
    );

-- Tabel musim_tanam
CREATE TABLE
    musim_tanam (
        id INT PRIMARY KEY AUTO_INCREMENT,
        lahan_id INT,
        bibit_id INT,
        tanggal_tanam DATE,
        estimasi_panen DATE,
        status ENUM ('aktif', 'selesai') DEFAULT 'aktif',
        FOREIGN KEY (lahan_id) REFERENCES lahan (id),
        FOREIGN KEY (bibit_id) REFERENCES bibit (id)
    );

-- Tabel perawatan
CREATE TABLE
    perawatan (
        id INT PRIMARY KEY AUTO_INCREMENT,
        musim_tanam_id INT,
        tanggal DATE,
        jenis ENUM ('pupuk', 'obat', 'air'),
        item VARCHAR(100),
        dosis VARCHAR(50),
        biaya DECIMAL(10, 2),
        keterangan TEXT,
        FOREIGN KEY (musim_tanam_id) REFERENCES musim_tanam (id)
    );

-- Tabel panen
CREATE TABLE
    panen (
        id INT PRIMARY KEY AUTO_INCREMENT,
        musim_tanam_id INT,
        tanggal_panen DATE,
        hasil_kg DECIMAL(10, 2),
        harga_jual DECIMAL(10, 2),
        pembeli VARCHAR(100),
        total_pendapatan DECIMAL(10, 2),
        FOREIGN KEY (musim_tanam_id) REFERENCES musim_tanam (id)
    );

-- Insert data dummy
INSERT INTO
    lahan (nama_lahan, luas_hektar, lokasi, jenis_tanah)
VALUES
    ('Lahan A', 2.5, 'Desa Sukamaju', 'Tanah Hitam'),
    ('Lahan B', 1.8, 'Desa Sumberejo', 'Tanah Merah');

INSERT INTO
    bibit (nama_bibit, sumber, harga_per_kg)
VALUES
    ('Jagung Hibrida BISI-2', 'Toko Tani', 45000),
    ('Jagung Pulut', 'Kelompok Tani', 38000);