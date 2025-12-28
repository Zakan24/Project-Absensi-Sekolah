-- --------------------------------------------------------
-- Database: db_absensi2
-- Dibuat untuk Tugas UTS Aplikasi Web Absensi Sekolah
-- Dengan PHP & Bootstrap
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS db_absensi2;
USE db_absensi2;

-- --------------------------------------------------------
-- Table: admin (akun login)
-- --------------------------------------------------------
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role ENUM('admin','guru') DEFAULT 'guru'
);

-- Data awal admin
INSERT INTO admin (username, password, nama_lengkap, role) VALUES
('admin', MD5('admin123'), 'Administrator Sekolah', 'admin'),
('guru1', MD5('guru123'), 'Guru Kelas 10A', 'guru');

-- --------------------------------------------------------
-- Table: kelas
-- --------------------------------------------------------
CREATE TABLE kelas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kelas VARCHAR(50) NOT NULL
);

-- Data awal kelas
INSERT INTO kelas (nama_kelas) VALUES
('10A'),
('10B'),
('11A'),
('12A');

-- --------------------------------------------------------
-- Table: siswa
-- --------------------------------------------------------
CREATE TABLE siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_siswa VARCHAR(100) NOT NULL,
  nis VARCHAR(20) NOT NULL,
  id_kelas INT,
  FOREIGN KEY (id_kelas) REFERENCES kelas(id) ON DELETE CASCADE
);

-- Data awal siswa
INSERT INTO siswa (nama_siswa, nis, id_kelas) VALUES
('Ahmad Fauzan', '1001', 1),
('Siti Nurhaliza', '1002', 1),
('Budi Santoso', '2001', 2),
('Dewi Anggraini', '3001', 3),
('Rizky Pratama', '4001', 4);

-- --------------------------------------------------------
-- Table: absensi
-- --------------------------------------------------------
CREATE TABLE absensi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('Hadir','Izin','Sakit','Alfa') NOT NULL,
  keterangan VARCHAR(255),
  FOREIGN KEY (id_siswa) REFERENCES siswa(id) ON DELETE CASCADE
);

-- Data awal absensi
INSERT INTO absensi (id_siswa, tanggal, status, keterangan) VALUES
(1, '2025-10-25', 'Hadir', ''),
(2, '2025-10-25', 'Izin', 'Acara keluarga'),
(3, '2025-10-25', 'Sakit', 'Demam tinggi'),
(4, '2025-10-25', 'Hadir', ''),
(5, '2025-10-25', 'Alfa', 'Tidak hadir tanpa keterangan');

-- --------------------------------------------------------
-- Table: rekap (rekapan absensi harian)
-- --------------------------------------------------------
CREATE TABLE rekap (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  total_hadir INT DEFAULT 0,
  total_izin INT DEFAULT 0,
  total_sakit INT DEFAULT 0,
  total_alfa INT DEFAULT 0
);

-- Data contoh rekap
INSERT INTO rekap (tanggal, total_hadir, total_izin, total_sakit, total_alfa) VALUES
('2025-10-25', 2, 1, 1, 1);
