-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 03:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addon`
--

CREATE TABLE `addon` (
  `id_addon` int(11) NOT NULL,
  `nama_addon` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addon`
--

INSERT INTO `addon` (`id_addon`, `nama_addon`, `harga`) VALUES
(1, 'Breakfast', 100000.00),
(2, 'Extra bed', 150000.00),
(3, 'Laundry', 80000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_reservasi_addon`
--

CREATE TABLE `detail_reservasi_addon` (
  `id_detail` int(11) NOT NULL,
  `id_reservasi` int(11) NOT NULL,
  `id_addon` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_reservasi_kamar`
--

CREATE TABLE `detail_reservasi_kamar` (
  `id_detail_kamar` int(11) NOT NULL,
  `id_reservasi` int(11) NOT NULL,
  `nama_tamu_utama` varchar(255) NOT NULL,
  `jumlah_tamu` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kamar`
--

CREATE TABLE `kamar` (
  `id_kamar` int(11) NOT NULL,
  `id_tipe_kamar` int(11) NOT NULL,
  `nomor_kamar` varchar(10) NOT NULL,
  `lantai` int(5) NOT NULL,
  `status` enum('Tersedia','Terisi','Perbaikan','Kotor') NOT NULL DEFAULT 'Tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kamar`
--

INSERT INTO `kamar` (`id_kamar`, `id_tipe_kamar`, `nomor_kamar`, `lantai`, `status`) VALUES
(1, 3, '101', 1, 'Terisi'),
(2, 5, '103', 1, 'Tersedia'),
(3, 3, '102', 1, 'Tersedia'),
(4, 6, '302', 3, 'Tersedia'),
(5, 7, '301', 3, 'Tersedia'),
(6, 4, '303', 3, 'Tersedia'),
(7, 4, '304', 3, 'Tersedia'),
(8, 2, '201', 2, 'Tersedia'),
(10, 1, '202', 2, 'Tersedia'),
(11, 8, '305', 3, 'Terisi'),
(12, 2, '203', 2, 'Tersedia'),
(13, 1, '105', 1, 'Tersedia'),
(14, 9, '104', 1, 'Tersedia'),
(15, 4, '204', 2, 'Tersedia'),
(16, 1, '205', 2, 'Tersedia'),
(17, 5, '206', 2, 'Tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `reservasi`
--

CREATE TABLE `reservasi` (
  `id_reservasi` int(11) NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_tipe_kamar` int(11) NOT NULL,
  `id_kamar_ditempati` int(11) DEFAULT NULL,
  `durasi_inap` int(11) DEFAULT NULL,
  `tanggal_checkin` date NOT NULL,
  `tanggal_checkout` date NOT NULL,
  `jumlah_tamu` int(11) NOT NULL DEFAULT 1,
  `nama_pemesan` varchar(100) NOT NULL,
  `email_pemesan` varchar(100) NOT NULL,
  `telp_pemesan` varchar(20) NOT NULL,
  `ktp_pemesan` varchar(20) DEFAULT NULL,
  `total_biaya_kamar` decimal(10,2) NOT NULL,
  `total_biaya_addon` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_bayar` decimal(10,2) NOT NULL,
  `metode_pembayaran` enum('Transfer Bank','Bayar di Hotel') NOT NULL,
  `status_pembayaran` enum('Belum Bayar','Menunggu Verifikasi','Lunas','Dibatalkan') NOT NULL DEFAULT 'Belum Bayar',
  `status_reservasi` enum('Pending','Confirmed','Check-in','Check-out','Canceled') NOT NULL DEFAULT 'Pending',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `tanggal_pemesanan` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservasi`
--

INSERT INTO `reservasi` (`id_reservasi`, `kode_booking`, `id_user`, `id_tipe_kamar`, `id_kamar_ditempati`, `durasi_inap`, `tanggal_checkin`, `tanggal_checkout`, `jumlah_tamu`, `nama_pemesan`, `email_pemesan`, `telp_pemesan`, `ktp_pemesan`, `total_biaya_kamar`, `total_biaya_addon`, `total_bayar`, `metode_pembayaran`, `status_pembayaran`, `status_reservasi`, `bukti_pembayaran`, `tanggal_pemesanan`) VALUES
(2, 'CNI1765833739160', 3, 1, NULL, NULL, '2025-12-15', '2025-12-16', 1, 'Cath', 'cath@example.com', '08251630225', '7805556212', 0.00, 0.00, 1089000.00, '', 'Belum Bayar', 'Pending', NULL, '2025-12-16 04:22:19'),
(3, 'CNI1765833955478', 3, 1, NULL, NULL, '2025-12-15', '2025-12-16', 1, 'Charlene Angkadjaja', 'chacha@example.com', '082187883777', '782055553811', 0.00, 0.00, 1089000.00, '', 'Belum Bayar', 'Pending', NULL, '2025-12-16 04:25:55'),
(4, 'CNI1765888866645', 3, 4, NULL, NULL, '2025-12-16', '2025-12-18', 2, 'Charlene Angkadjaja', 'test@gmail.com', '082187883777', '728400000225', 0.00, 0.00, 3396000.00, '', 'Belum Bayar', 'Pending', NULL, '2025-12-16 19:41:06'),
(5, 'CNI1765888921765', 3, 1, NULL, NULL, '2025-12-16', '2025-12-17', 1, 'Charlene Angkadjaja', 'test@gmail.com', '082187883777', '728400000225', 0.00, 0.00, 1089000.00, '', 'Belum Bayar', 'Pending', NULL, '2025-12-16 19:42:01'),
(6, 'CNI1765888968178', 3, 1, NULL, NULL, '2025-12-16', '2025-12-17', 1, 'Charlene Angkadjaja', 'test@gmail.com', '082187883777', '728400000225', 0.00, 0.00, 1089000.00, '', 'Belum Bayar', 'Pending', NULL, '2025-12-16 19:42:48'),
(8, 'WALK-2DD244', 2, 3, 1, NULL, '2025-12-17', '2025-12-21', 1, 'John Doe', 'johndoe@example.com', '081278912230', NULL, 1650000.00, 0.00, 1650000.00, 'Transfer Bank', 'Lunas', 'Check-in', NULL, '2025-12-17 20:13:40'),
(9, 'WALK-D6A83B', 2, 8, 11, NULL, '2025-12-17', '2025-12-18', 4, 'Sicsa', 'sicsa123@example.com', '081278912287', NULL, 0.00, 0.00, 1700000.00, 'Transfer Bank', 'Lunas', 'Check-in', NULL, '2025-12-17 20:23:50'),
(10, 'WALK-60CAFF', 2, 3, 3, 1, '2025-12-17', '2025-12-17', 1, 'David Jr', 'davidJr@example.com', '0812789122111', NULL, 0.00, 0.00, 550000.00, 'Transfer Bank', 'Lunas', '', NULL, '2025-12-17 20:25:02');

-- --------------------------------------------------------

--
-- Table structure for table `tipe_kamar`
--

CREATE TABLE `tipe_kamar` (
  `id_tipe_kamar` int(11) NOT NULL,
  `nama_tipe` varchar(50) NOT NULL,
  `harga_per_malam` decimal(10,2) NOT NULL,
  `kapasitas` int(11) NOT NULL DEFAULT 2,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `kategori_hunian` enum('Single','Double','Family','Connecting Room') DEFAULT NULL,
  `tingkat_fasilitas` varchar(50) DEFAULT NULL,
  `jenis_tempat_tidur` varchar(50) DEFAULT NULL,
  `luas_kamar` int(11) DEFAULT NULL,
  `add_on` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipe_kamar`
--

INSERT INTO `tipe_kamar` (`id_tipe_kamar`, `nama_tipe`, `harga_per_malam`, `kapasitas`, `deskripsi`, `foto`, `kategori_hunian`, `tingkat_fasilitas`, `jenis_tempat_tidur`, `luas_kamar`, `add_on`) VALUES
(1, 'Deluxe Room 1', 1089000.00, 2, 'Ukuran lebih luas dengan Balkon pribadi. Fasilitas mencakup: Bathtub, Kulkas mini (Minibar), Hairdryer, TV 40 inch, dan Brankas pribadi.', 'tipe_kamar_1765818875.jpeg', 'Double', 'Deluxe', 'Queen Size Bed', 32, 'Breakfast Inculude'),
(2, 'Superior Room 1', 850000.00, 1, 'Upgrade dari Standard: Lokasi kamar dengan view lebih baik, tambahan fasilitas pembuat Kopi/Teh (Coffee Maker), dan Meja kerja compact.', 'tipe_kamar_1765818848.jpeg', 'Single', 'Superior', 'Single Bed', 20, 'Breakfast Inculude'),
(3, 'Standar Room 1', 550000.00, 1, 'Fasilitas Dasar: AC, TV Kabel 32 inch, Wi-Fi gratis, Kamar mandi shower (Hot/Cold), Air mineral botol, dan Perlengkapan mandi dasar.', 'tipe_kamar_1765818905.jpeg', 'Single', 'Standard', 'Single Bed', 20, 'Breakfast Inculude'),
(4, 'Superior Room 2', 849000.00, 2, 'Upgrade dari Standard: Lokasi kamar dengan view lebih baik, tambahan fasilitas pembuat Kopi/Teh (Coffee Maker), dan Meja kerja compact.', 'tipe_kamar_1765819029.jpeg', 'Double', 'Superior', 'Twin Bed', 32, 'Breakfast Inculude'),
(5, 'Smoking Standard Room', 579000.00, 1, 'Fasilitas Dasar: AC, TV Kabel 32 inch, Wi-Fi gratis, Kamar mandi shower (Hot/Cold), Air mineral botol, dan Perlengkapan mandi dasar.', 'tipe_kamar_1765819290.jpeg', 'Single', 'Standard', 'Single Bed', 20, 'Breakfast Inculude'),
(6, 'Family Room 1', 1850000.00, 4, 'Kamar Keluarga: Ruangan sangat luas dengan area duduk (Sofa). Dilengkapi 1 Bed besar dan 1 Bunk Bed, Smart TV 50 inch, Microwave, dan Meja makan kecil.', 'tipe_kamar_1765819106.jpeg', 'Family', 'Family Room', 'Queen Size Bed, Bunk Bed', 55, 'Breakfast Inculude'),
(7, 'Family Room 2', 1650000.00, 4, 'Kamar Keluarga: Ruangan sangat luas dengan area duduk (Sofa). Dilengkapi 2 Bed besar, Smart TV 50 inch, Microwave, dan Meja makan kecil.', 'tipe_kamar_1765819089.jpeg', 'Family', 'Family Room', 'Queen Size Bed', 55, 'Breakfast Inculude'),
(8, 'Family Room 3', 1700000.00, 4, 'Kamar Keluarga: Ruangan sangat luas dengan area duduk (Sofa). Dilengkapi 1 Bed besar dan 1 Twin Bed, Smart TV 50 inch, Microwave, dan Meja makan kecil.', 'tipe_kamar_1765819129.jpeg', 'Family', 'Family Room', 'Queen Size Bed, Twin Bed', 55, 'Breakfast Inculude'),
(9, 'Superior Room 3', 879000.00, 2, 'Upgrade dari Standard: Lokasi kamar dengan view lebih baik, tambahan fasilitas pembuat Kopi/Teh (Coffee Maker), dan Meja kerja compact.', 'tipe_kamar_1765902384_694188304966f.jpeg', 'Double', 'Superior', 'Double Bed', 32, 'Breakfast Inculude');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `kode_booking` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_kamar` int(11) NOT NULL,
  `tgl_check_in` date NOT NULL,
  `tgl_check_out` date NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status_transaksi` enum('Lunas','Pending','Reserved','Canceled','done') NOT NULL DEFAULT 'Pending',
  `tgl_transaksi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `no_ktp` varchar(20) DEFAULT NULL,
  `role` enum('admin','resepsionis','customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `email`, `no_telp`, `no_ktp`, `role`, `created_at`, `status`) VALUES
(1, 'Charlene Manuella Angkadjaja', 'admincloud9', '$2y$10$J2hZX6KShz2oHGFxy7rkHey/ryPcUAZotgEwnQ0sdv50xqyyIb5Eq', 'admincloud9@cloudninein.com', '08123456789', NULL, 'admin', '2025-11-17 17:40:09', 'active'),
(2, 'Charlene Manuella Angkadjaja', 'coca', '$2y$10$8ZMzva/fVCk/62rll3UPkOqKtxVTULpiA12FapxLUodjOs3jFlRHq', 'coca@cloudninein.com', '08123456789', NULL, 'resepsionis', '2025-12-15 19:04:04', 'active'),
(3, 'Catherine', 'cath', '$2y$10$8ZMzva/fVCk/62rll3UPkOqKtxVTULpiA12FapxLUodjOs3jFlRHq', 'cath@example.com', '081325556127', '782500001236', 'customer', '2025-12-15 21:18:52', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addon`
--
ALTER TABLE `addon`
  ADD PRIMARY KEY (`id_addon`);

--
-- Indexes for table `detail_reservasi_addon`
--
ALTER TABLE `detail_reservasi_addon`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_reservasi` (`id_reservasi`),
  ADD KEY `id_addon` (`id_addon`);

--
-- Indexes for table `detail_reservasi_kamar`
--
ALTER TABLE `detail_reservasi_kamar`
  ADD PRIMARY KEY (`id_detail_kamar`),
  ADD KEY `fk_reservasi_kamar_detail` (`id_reservasi`);

--
-- Indexes for table `kamar`
--
ALTER TABLE `kamar`
  ADD PRIMARY KEY (`id_kamar`),
  ADD UNIQUE KEY `nomor_kamar` (`nomor_kamar`),
  ADD KEY `id_tipe_kamar` (`id_tipe_kamar`);

--
-- Indexes for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_tipe_kamar` (`id_tipe_kamar`),
  ADD KEY `id_kamar_ditempati` (`id_kamar_ditempati`);

--
-- Indexes for table `tipe_kamar`
--
ALTER TABLE `tipe_kamar`
  ADD PRIMARY KEY (`id_tipe_kamar`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`kode_booking`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kamar` (`id_kamar`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addon`
--
ALTER TABLE `addon`
  MODIFY `id_addon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `detail_reservasi_addon`
--
ALTER TABLE `detail_reservasi_addon`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_reservasi_kamar`
--
ALTER TABLE `detail_reservasi_kamar`
  MODIFY `id_detail_kamar` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kamar`
--
ALTER TABLE `kamar`
  MODIFY `id_kamar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id_reservasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tipe_kamar`
--
ALTER TABLE `tipe_kamar`
  MODIFY `id_tipe_kamar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_reservasi_addon`
--
ALTER TABLE `detail_reservasi_addon`
  ADD CONSTRAINT `detail_reservasi_addon_ibfk_1` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi` (`id_reservasi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_reservasi_addon_ibfk_2` FOREIGN KEY (`id_addon`) REFERENCES `addon` (`id_addon`);

--
-- Constraints for table `detail_reservasi_kamar`
--
ALTER TABLE `detail_reservasi_kamar`
  ADD CONSTRAINT `fk_reservasi_kamar_detail` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi` (`id_reservasi`) ON DELETE CASCADE;

--
-- Constraints for table `kamar`
--
ALTER TABLE `kamar`
  ADD CONSTRAINT `kamar_ibfk_1` FOREIGN KEY (`id_tipe_kamar`) REFERENCES `tipe_kamar` (`id_tipe_kamar`) ON DELETE CASCADE;

--
-- Constraints for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `reservasi_ibfk_2` FOREIGN KEY (`id_tipe_kamar`) REFERENCES `tipe_kamar` (`id_tipe_kamar`),
  ADD CONSTRAINT `reservasi_ibfk_3` FOREIGN KEY (`id_kamar_ditempati`) REFERENCES `kamar` (`id_kamar`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_kamar`) REFERENCES `kamar` (`id_kamar`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
