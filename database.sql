-- Database CBT LPK
CREATE DATABASE IF NOT EXISTS cbt_lpk;
USE cbt_lpk;

-- Tabel Admin
CREATE TABLE admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Peserta
CREATE TABLE peserta (
    id_peserta INT PRIMARY KEY AUTO_INCREMENT,
    nomor_peserta VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    firebase_uid VARCHAR(255) UNIQUE,
    jenis_kelamin ENUM('L', 'P'),
    tanggal_lahir DATE,
    telepon VARCHAR(15),
    alamat TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Soal
CREATE TABLE kategori_soal (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Bank Soal
CREATE TABLE bank_soal (
    id_soal INT PRIMARY KEY AUTO_INCREMENT,
    id_kategori INT,
    pertanyaan TEXT NOT NULL,
    pilihan_a TEXT NOT NULL,
    pilihan_b TEXT NOT NULL,
    pilihan_c TEXT NOT NULL,
    pilihan_d TEXT NOT NULL,
    pilihan_e TEXT,
    jawaban_benar ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    bobot INT DEFAULT 1,
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori_soal(id_kategori)
);

-- Tabel Jadwal Tes
CREATE TABLE jadwal_tes (
    id_jadwal INT PRIMARY KEY AUTO_INCREMENT,
    nama_tes VARCHAR(200) NOT NULL,
    id_kategori INT,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NOT NULL,
    durasi INT NOT NULL, -- dalam menit
    jumlah_soal INT NOT NULL,
    passing_grade DECIMAL(5,2) DEFAULT 70.00,
    instruksi TEXT,
    status ENUM('draft', 'aktif', 'selesai') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori_soal(id_kategori)
);

-- Tabel Peserta Tes (Relasi Peserta dengan Jadwal Tes)
CREATE TABLE peserta_tes (
    id_peserta_tes INT PRIMARY KEY AUTO_INCREMENT,
    id_jadwal INT,
    id_peserta INT,
    token VARCHAR(50) UNIQUE,
    status_tes ENUM('belum_mulai', 'sedang_tes', 'selesai') DEFAULT 'belum_mulai',
    waktu_mulai DATETIME,
    waktu_selesai DATETIME,
    nilai DECIMAL(5,2),
    status_kelulusan ENUM('lulus', 'tidak_lulus', 'belum_dinilai') DEFAULT 'belum_dinilai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_tes(id_jadwal),
    FOREIGN KEY (id_peserta) REFERENCES peserta(id_peserta)
);

-- Tabel Soal Tes (Soal yang digunakan dalam tes tertentu)
CREATE TABLE soal_tes (
    id_soal_tes INT PRIMARY KEY AUTO_INCREMENT,
    id_jadwal INT,
    id_soal INT,
    nomor_urut INT,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_tes(id_jadwal),
    FOREIGN KEY (id_soal) REFERENCES bank_soal(id_soal)
);

-- Tabel Jawaban Peserta
CREATE TABLE jawaban_peserta (
    id_jawaban INT PRIMARY KEY AUTO_INCREMENT,
    id_peserta_tes INT,
    id_soal INT,
    jawaban ENUM('A', 'B', 'C', 'D', 'E'),
    is_correct BOOLEAN,
    waktu_jawab TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_peserta_tes) REFERENCES peserta_tes(id_peserta_tes),
    FOREIGN KEY (id_soal) REFERENCES bank_soal(id_soal),
    UNIQUE KEY unique_jawaban (id_peserta_tes, id_soal)
);

-- Insert default admin
INSERT INTO admin (username, password, nama_lengkap, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@cbt.com');
-- Password: password

-- Insert default kategori
INSERT INTO kategori_soal (nama_kategori, deskripsi) VALUES 
('Bahasa Indonesia', 'Tes kemampuan Bahasa Indonesia'),
('Matematika', 'Tes kemampuan Matematika'),
('Bahasa Inggris', 'Tes kemampuan Bahasa Inggris'),
('Pengetahuan Umum', 'Tes pengetahuan umum');
