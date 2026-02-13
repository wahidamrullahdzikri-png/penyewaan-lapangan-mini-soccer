-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Feb 2026 pada 13.14
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `penyewaan_lapangan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `nomor_booking` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_pemesan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanggal_booking` date NOT NULL,
  `tanggal_main` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `total_harga` decimal(10,2) DEFAULT 0.00,
  `status_pembayaran` enum('MENUNGGU','LUNAS') DEFAULT 'MENUNGGU',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id`, `nomor_booking`, `user_id`, `nama_pemesan`, `no_hp`, `tanggal_booking`, `tanggal_main`, `jam_mulai`, `jam_selesai`, `total_harga`, `status_pembayaran`, `catatan`, `created_at`) VALUES
(11, 'BOOK202601221345299453', NULL, 'Ahidin', '09876567890', '2026-01-22', '2026-01-22', '13:00:00', '14:00:00', 310000.00, 'LUNAS', 'TIm Macan kemayoran', '2026-01-22 06:45:29'),
(12, 'BOOK202601221359264960', NULL, 'Aloi', '01923292991', '2026-01-22', '2026-01-22', '15:00:00', '16:00:00', 275000.00, 'LUNAS', 'Aloi mau main bola', '2026-01-22 06:59:26'),
(16, 'BOOK202601270802074440', NULL, 'Alex', '098765456789', '2026-01-27', '2026-01-27', '08:00:00', '09:00:00', 355000.00, 'LUNAS', '', '2026-01-27 01:02:07'),
(17, 'BOOK202601270802525473', NULL, 'Komar', '0102982681', '2026-01-27', '2026-01-27', '08:00:00', '09:00:00', 280000.00, 'LUNAS', '', '2026-01-27 01:02:52'),
(24, 'BOOK202601271006495463', NULL, 'Dzikri Wahid Amrullah', '083120474479', '2026-01-27', '2026-01-27', '10:06:00', '11:06:00', 350000.00, 'LUNAS', '', '2026-01-27 03:06:49'),
(26, 'BOOK202601271118087805', NULL, 'Abdul', '0987625671890', '2026-01-27', '2026-01-27', '17:17:00', '18:17:00', 250000.00, 'MENUNGGU', '', '2026-01-27 04:18:08'),
(32, 'BOOK202601271211591772', NULL, 'Bima Yudha', '083120474479', '2026-01-27', '2026-01-27', '16:11:00', '17:11:00', 280000.00, 'LUNAS', '', '2026-01-27 05:12:00'),
(33, 'BOOK202601271213367989', NULL, 'Abdul Kodir', '0987652789', '2026-01-27', '2026-01-27', '20:13:00', '21:13:00', 350000.00, 'MENUNGGU', '', '2026-01-27 05:13:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_detail`
--

CREATE TABLE `booking_detail` (
  `id` int(11) NOT NULL,
  `lapangan_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` enum('lapangan','produk') NOT NULL,
  `booking_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `subtotal_harga` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking_detail`
--

INSERT INTO `booking_detail` (`id`, `lapangan_id`, `item_id`, `item_type`, `booking_id`, `jumlah`, `subtotal_harga`) VALUES
(3, 0, 0, 'lapangan', 4, 2, 700000.00),
(4, 0, 0, 'lapangan', 5, 2, 500000.00),
(5, 0, 0, 'lapangan', 6, 1, 357000.00),
(6, 0, 0, 'lapangan', 9, 1, 250000.00),
(7, NULL, 8, 'produk', 9, 1, 15000.00),
(8, NULL, 2, 'produk', 9, 2, 10000.00),
(9, NULL, 5, 'produk', 9, 2, 16000.00),
(10, NULL, 6, 'produk', 9, 1, 10000.00),
(11, NULL, 8, 'produk', 5, 1, 15000.00),
(12, NULL, 2, 'produk', 5, 4, 20000.00),
(13, NULL, 8, 'produk', 6, 2, 30000.00),
(14, NULL, 2, 'produk', 6, 3, 15000.00),
(15, NULL, 2, 'produk', 5, 1, 5000.00),
(16, 4, 0, 'lapangan', 11, 1, 250000.00),
(17, 4, 0, 'lapangan', 12, 1, 250000.00),
(18, 4, 0, 'lapangan', 13, 1, 250000.00),
(19, NULL, 8, 'produk', 12, 1, 15000.00),
(20, NULL, 2, 'produk', 12, 2, 10000.00),
(21, 3, 0, 'lapangan', 14, 1, 350000.00),
(22, 3, 0, 'lapangan', 15, 1, 350000.00),
(23, NULL, 8, 'produk', 11, 4, 60000.00),
(24, 3, 0, 'lapangan', 16, 1, 350000.00),
(25, 4, 0, 'lapangan', 17, 1, 250000.00),
(26, NULL, 8, 'produk', 17, 1, 15000.00),
(27, NULL, 1, 'produk', 17, 3, 15000.00),
(28, 4, 0, 'lapangan', 18, 2, 500000.00),
(29, NULL, 1, 'produk', 16, 1, 5000.00),
(30, 4, 0, 'lapangan', 19, 1, 250000.00),
(31, 3, 3, 'lapangan', 24, 1, 350000.00),
(32, 4, 0, 'lapangan', 26, 1, 250000.00),
(33, 4, 0, 'lapangan', 27, 1, 250000.00),
(34, 4, 0, 'lapangan', 28, 1, 250000.00),
(35, 4, 0, 'lapangan', 29, 1, 250000.00),
(36, 4, 0, 'lapangan', 30, 1, 250000.00),
(37, 3, 0, 'lapangan', 31, 1, 350000.00),
(38, 4, 0, 'lapangan', 32, 1, 250000.00),
(39, 3, 0, 'lapangan', 33, 1, 350000.00),
(40, NULL, 1, 'produk', 32, 6, 30000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `lapangan`
--

CREATE TABLE `lapangan` (
  `id` int(11) NOT NULL,
  `kode_lapangan` int(11) NOT NULL,
  `nama_lapangan` varchar(255) DEFAULT NULL,
  `harga_per_jam` decimal(10,2) DEFAULT 0.00,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default.jpg',
  `status_lapangan` enum('Tersedia','Maintenance','Non-Aktif') DEFAULT 'Tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lapangan`
--

INSERT INTO `lapangan` (`id`, `kode_lapangan`, `nama_lapangan`, `harga_per_jam`, `deskripsi`, `foto`, `status_lapangan`) VALUES
(3, 1, 'Lapangan A – Premium', 350000.00, 'Lapangan mini soccer ukuran 40 x 20 meter dengan rumput sintetis kualitas terbaik.\r\nCocok untuk latihan, fun match dan turnamen dengan harga terjangkau.', 'lapangan_20260119220813_e90450da.jpg', 'Tersedia'),
(4, 2, 'Lapangan B – Standar', 250000.00, 'Lapangan mini soccer berukuran 40 x 20 meter dengan rumput sintetis kualitas standar.\r\nCocok untuk latihan rutin, fun match, dan permainan santai dengan harga yang lebih terjangkau.\r\nTetap nyaman digunakan dan memiliki pencahayaan yang memadai untuk permainan sore hingga malam hari.', 'lapangan_20260120095606_9521828b.jpg', 'Tersedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kode_produk` varchar(50) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `jenis_produk` enum('Alat','Minuman','Snack','Makanan','Lainnya') NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status_produk` enum('Aktif','Non-Aktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `kode_produk`, `nama_produk`, `jenis_produk`, `harga`, `deskripsi`, `status_produk`) VALUES
(1, 'MIN001', 'Air Mineral (600ml)', 'Minuman', 5000.00, 'Air mineral kemasan botol 600ml.', 'Aktif'),
(2, 'MIN002', 'Air Mineral Le Minerale (600ml)', 'Minuman', 5000.00, 'Air mineral kemasan botol 600ml.', 'Aktif'),
(4, 'MIN004', 'Minuman Isotonik Pocari Sweat (500ml)', 'Minuman', 8000.00, 'Minuman isotonik kemasan botol 500ml.', 'Aktif'),
(5, 'MIN005', 'Minuman Isotonik Aquarius Jeruk (500ml)', 'Minuman', 8000.00, 'Minuman isotonik rasa jeruk kemasan botol 500ml.', 'Aktif'),
(7, 'SNK002', 'Kacang Atom Goreng', 'Snack', 8000.00, 'Kacang atom goreng renyah.', 'Aktif'),
(8, 'ALAT001', 'Sewa Sepatu per Jam', 'Alat', 15000.00, 'Sewa sepatu futsal per jam (ukuran bisa disesuaikan saat pengambilan).', 'Aktif'),
(9, 'MKN001', 'Roti Bakar Keju', 'Makanan', 15000.00, 'Roti bakar dengan olesan mentega dan taburan keju.', 'Aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2a$12$zzlN3JgtkXfcgQXzXXHzX.GKEGYQODt5jate.hvxX/ga7PyrsLS.O', 'admin', '2026-01-19 13:55:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_booking` (`nomor_booking`),
  ADD KEY `idx_booking_user` (`user_id`),
  ADD KEY `idx_booking_date` (`tanggal_main`),
  ADD KEY `idx_booking_status` (`status_pembayaran`);

--
-- Indeks untuk tabel `booking_detail`
--
ALTER TABLE `booking_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_detail_booking` (`booking_id`);

--
-- Indeks untuk tabel `lapangan`
--
ALTER TABLE `lapangan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_lapangan` (`kode_lapangan`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `booking_detail`
--
ALTER TABLE `booking_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `lapangan`
--
ALTER TABLE `lapangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
