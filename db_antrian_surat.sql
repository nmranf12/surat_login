-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Nov 2025 pada 02.22
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
-- Database: `db_antrian_surat`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_counter`
--

CREATE TABLE `tb_counter` (
  `id` int(11) NOT NULL DEFAULT 1,
  `next_number` int(11) NOT NULL DEFAULT 1,
  `tahun` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_counter`
--

INSERT INTO `tb_counter` (`id`, `next_number`, `tahun`) VALUES
(1, 1, 2025);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pegawai`
--

CREATE TABLE `tb_pegawai` (
  `id` int(11) NOT NULL,
  `nip` varchar(100) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `unit_bidang` varchar(100) NOT NULL,
  `role` enum('user','superadmin') NOT NULL DEFAULT 'user',
  `password` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_pegawai`
--

INSERT INTO `tb_pegawai` (`id`, `nip`, `nama_lengkap`, `unit_bidang`, `role`, `password`) VALUES
(1, '198903232020121006', 'Angga', 'Pengadaan', 'superadmin', '$2y$10$sAMQg1qDtcxz4F3UF6b6ROLKSOvm0Tl0Bh/yOvhVbJ2mxyhCmsWZC'),
(2, '198903232020121002', 'Nam', 'pengadaan', '', '$2y$10$HyRngnLoI8L/pcVV79nu1eQzGb6FFKOAre1FeNVHUKtEKFZK4K.7G'),
(3, '198903232020121003', 'Abdi', 'PPIK', '', '$2y$10$P9WgRQpS6MRJdhn24PuLdOM7QAjnUvrdbUMgKeNcX5hG.Ox4uinVC');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_surat`
--

CREATE TABLE `tb_surat` (
  `id` int(11) NOT NULL,
  `nomor_surat` int(11) NOT NULL,
  `tahun` int(4) NOT NULL,
  `kode_surat` varchar(100) DEFAULT NULL,
  `perihal_surat` varchar(255) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `isi_ringkasan` text DEFAULT NULL,
  `tujuan_surat` varchar(255) NOT NULL,
  `nama_konseptor` text NOT NULL,
  `unit_bidang` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_surat_arsip`
--

CREATE TABLE `tb_surat_arsip` (
  `id` int(11) NOT NULL,
  `nomor_surat` int(11) NOT NULL,
  `tahun` int(4) NOT NULL,
  `kode_surat` varchar(100) DEFAULT NULL,
  `perihal_surat` varchar(255) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `isi_ringkasan` text DEFAULT NULL,
  `tujuan_surat` varchar(255) NOT NULL,
  `nama_konseptor` text NOT NULL,
  `unit_bidang` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tb_counter`
--
ALTER TABLE `tb_counter`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_pegawai`
--
ALTER TABLE `tb_pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indeks untuk tabel `tb_surat`
--
ALTER TABLE `tb_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_nomor_tahun` (`nomor_surat`,`tahun`);

--
-- Indeks untuk tabel `tb_surat_arsip`
--
ALTER TABLE `tb_surat_arsip`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_nomor_tahun` (`nomor_surat`,`tahun`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tb_pegawai`
--
ALTER TABLE `tb_pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tb_surat`
--
ALTER TABLE `tb_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `tb_surat_arsip`
--
ALTER TABLE `tb_surat_arsip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
