-- ==============================================================
-- DATABASE: profil_smks_prasetya_makassar
-- Sistem Profil Sekolah SMKS Prasetya Makassar
-- Eksekusi file ini di HeidiSQL / phpMyAdmin
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `profil_smks_prasetya_makassar` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `profil_smks_prasetya_makassar`;

-- --------------------------------------------------------
-- 1. Struktur Tabel `admin`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default login:
-- Username : admin
-- Password : admin123 (MD5: 0192023a7bbd73250516f069df18b500)
INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator SMKS Prasetya');

-- --------------------------------------------------------
-- 2. Struktur Tabel `jurusan`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jurusan`;
CREATE TABLE `jurusan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_jurusan` VARCHAR(100) NOT NULL,
  `singkatan` VARCHAR(20) DEFAULT NULL,
  `deskripsi` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `jurusan` (`id`, `nama_jurusan`, `singkatan`, `deskripsi`) VALUES
(1, 'Teknik Komputer dan Jaringan', 'TKJ', 'Mempelajari seluk beluk dunia komputer, mulai dari perakitan PC, instalasi sistem operasi, konfigurasi jaringan LAN/WAN, routing mikrotik, fiber optic, hingga keamanan jaringan siber.'),
(2, 'Rekayasa Perangkat Lunak', 'RPL', 'Fokus pada keahlian pengembangan aplikasi web modern, mobile programming (Android/iOS), perancangan basis data, cloud computing, serta UI/UX design.'),
(3, 'Otomatisasi & Tata Kelola Perkantoran', 'OTKP', 'Mempersiapkan tenaga ahli administrasi perkantoran modern, korespondensi digital, manajemen kearsipan elektronik, keprotokolan, dan public relations yang profesional.'),
(4, 'Akuntansi dan Keuangan Lembaga', 'AKL', 'Membekali siswa dengan keahlian pembukuan keuangan perusahaan, perpajakan, audit dasar, dan pengoperasian software akuntansi komputer terstandar.'),
(5, 'Teknik & Bisnis Sepeda Motor', 'TBSM', 'Mencetak teknisi mekanik otomotif roda dua yang terampil dalam perawatan, troubleshooting mesin injeksi, sistem kelistrikan motor, serta manajemen bengkel resmi.');

-- --------------------------------------------------------
-- 3. Struktur Tabel `berita`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `berita`;
CREATE TABLE `berita` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(255) NOT NULL,
  `isi` TEXT NOT NULL,
  `gambar` VARCHAR(255) NOT NULL,
  `penulis` VARCHAR(100) DEFAULT 'Admin',
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `berita` (`id`, `judul`, `isi`, `gambar`, `penulis`, `tanggal`) VALUES
(1, 'Penerimaan Peserta Didik Baru (PPDB) SMKS Prasetya Makassar Telah Dibuka', 'SMKS Prasetya Makassar resmi membuka pendaftaran peserta didik baru (PPDB) untuk tahun ajaran baru. Berbagai program beasiswa prestasi dan keringanan biaya pendidikan disediakan bagi calon siswa berprestasi. Segera daftarkan diri Anda langsung di kampus SMKS Prasetya Makassar atau secara online.', 'berita1.jpg', 'Admin Humas', '2026-11-01 08:30:00'),
(2, 'Siswa SMKS Prasetya Raih Juara Lomba Keterampilan Siswa (LKS) Tingkat Kota Makassar', 'Prestasi membanggakan kembali ditorehkan oleh siswa SMKS Prasetya Makassar. Dalam ajang Lomba Keterampilan Siswa (LKS) bidang IT Network Systems Administration dan Web Technologies tingkat Kota Makassar, perwakilan sekolah berhasil membawa pulang predikat Juara 1.', 'berita2.jpg', 'Admin Humas', '2026-11-05 10:15:00'),
(3, 'Kunjungan Industri & Pelepasan Siswa Magang ke Perusahaan Teknologi Makassar', 'Guna meningkatkan wawasan dunia kerja dan industri (DUDI), SMKS Prasetya Makassar menyelenggarakan kunjungan industri serta penyerahan siswa Praktik Kerja Lapangan (PKL) ke sejumlah perusahaan mitra telekomunikasi dan software house terkemuka di Kota Makassar.', 'berita3.jpg', 'Admin Akademik', '2026-11-08 13:00:00');

-- --------------------------------------------------------
-- 4. Struktur Tabel `pesan`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pesan`;
CREATE TABLE `pesan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subjek` VARCHAR(150) NOT NULL,
  `pesan` TEXT NOT NULL,
  `status` ENUM('belum dibaca', 'sudah dibaca') DEFAULT 'belum dibaca',
  `tanggal` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pesan` (`id`, `nama`, `email`, `subjek`, `pesan`, `status`) VALUES
(1, 'Budi Santoso', 'budi.santoso@gmail.com', 'Informasi Biaya Pendaftaran PPDB', 'Selamat pagi bapak/ibu panitia, saya ingin menanyakan rincian biaya pendaftaran dan seragam untuk jurusan Rekayasa Perangkat Lunak. Terima kasih.', 'belum dibaca'),
(2, 'Siti Nurhaliza', 'siti.nurhaliza@yahoo.com', 'Kerjasama Program Magang Siswa', 'Halo tim humas SMKS Prasetya, kami dari HRD Startup Makassar tertarik untuk menjalin kemitraan magang bagi siswa jurusan RPL dan TKJ. Mohon kontak person yang bisa dihubungi.', 'belum dibaca');

-- --------------------------------------------------------
-- 5. Struktur Tabel `pengunjung` (Statistik Pengunjung)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pengunjung`;
CREATE TABLE `pengunjung` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(50) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `halaman` VARCHAR(255) DEFAULT 'Beranda',
  `tanggal` DATE NOT NULL,
  `hits` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tanggal` (`tanggal`),
  INDEX `idx_ip_tanggal` (`ip_address`, `tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pengunjung` (`ip_address`, `user_agent`, `halaman`, `tanggal`, `hits`) VALUES
('192.168.1.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 4),
('192.168.1.11', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 2),
('192.168.1.12', 'Mozilla/5.0 (Linux; Android 13; SM-A536B) Mobile', 'Detail Berita', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 6),
('192.168.1.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 9 DAY), 3),
('192.168.1.14', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 9 DAY), 5),
('192.168.1.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Jurusan', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 7),
('192.168.1.16', 'Mozilla/5.0 (Linux; Android 14; Pixel 7) Mobile', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 4),
('192.168.1.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 2),
('192.168.1.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Galeri', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 6),
('192.168.1.19', 'Mozilla/5.0 (Linux; Android 13; Redmi Note 12)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 3),
('192.168.1.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0', 'Detail Berita', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 8),
('192.168.1.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 5),
('192.168.1.22', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 2),
('192.168.1.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Kontak', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 4),
('192.168.1.24', 'Mozilla/5.0 (Linux; Android 14; Samsung Galaxy S23)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 6),
('192.168.1.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 9),
('192.168.1.26', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X)', 'Jurusan', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 3),
('192.168.1.27', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 5),
('192.168.1.28', 'Mozilla/5.0 (Linux; Android 13; Vivo Y35)', 'Detail Berita', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 7),
('192.168.1.29', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 4),
('192.168.1.30', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 2),
('192.168.1.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0', 'Galeri', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 6),
('192.168.1.32', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 8),
('192.168.1.33', 'Mozilla/5.0 (Linux; Android 14; Oppo Reno 10)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 4),
('192.168.1.34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Detail Berita', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 10),
('192.168.1.35', 'Mozilla/5.0 (Linux; Android 13; Realme 11)', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 5),
('192.168.1.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1', 'Beranda', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 3),
('192.168.1.37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0', 'Kontak', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 4),
('127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0', 'Beranda', CURDATE(), 5),
('192.168.1.50', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)', 'Beranda', CURDATE(), 3),
('192.168.1.51', 'Mozilla/5.0 (Linux; Android 14; Pixel 8)', 'Detail Berita', CURDATE(), 4),
('192.168.1.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0', 'Jurusan', CURDATE(), 2);
