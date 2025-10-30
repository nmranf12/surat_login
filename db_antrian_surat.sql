-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Okt 2025 pada 03.45
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
(1, 3, 2025);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pegawai`
--

CREATE TABLE `tb_pegawai` (
  `id` int(11) NOT NULL,
  `nip` varchar(100) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `unit_bidang` varchar(100) NOT NULL,
  `role` enum('admin','superadmin') NOT NULL DEFAULT 'admin',
  `password` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_pegawai`
--

INSERT INTO `tb_pegawai` (`id`, `nip`, `nama_lengkap`, `unit_bidang`, `role`, `password`) VALUES
(1, '198903232020121006', 'Angga', 'Pengadaan', 'superadmin', '$2y$10$sAMQg1qDtcxz4F3UF6b6ROLKSOvm0Tl0Bh/yOvhVbJ2mxyhCmsWZC'),
(2, '198903232020121002', 'Nam', 'pengadaan', 'admin', '$2y$10$/Red9eZRR2OtyYibGmKq7OxzOFQOVyz70chuKAI/Ypd1ifCEefCQS');

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

--
-- Dumping data untuk tabel `tb_surat`
--

INSERT INTO `tb_surat` (`id`, `nomor_surat`, `tahun`, `kode_surat`, `perihal_surat`, `tanggal_surat`, `isi_ringkasan`, `tujuan_surat`, `nama_konseptor`, `unit_bidang`, `created_at`) VALUES
(33, 147, 2025, '800.1.9', 'Pemberitahuan', '2025-05-08', 'Dipermaklumkan dengan hormat bersama ini kami laporkan, sehubungan disdukcapil sudah membeli mesin absensi yang baru', 'Kepala Bkpsdm', 'Disdukcapil', 'Disdukcapil', '2025-10-28 01:24:31'),
(34, 1, 2025, '800.2', 'Permohonan nara sumber', '2025-10-30', 'ada', 'BUPATI CIAMIS', 'Megantara Halley Ruckmawan', 'Pengadaan', '2025-10-30 00:46:24'),
(35, 2, 2025, '800.1.13.4', 'KEPUTUSAN PEMBERHANTIAN', '2025-10-30', 'qwq', 'Kepala Dinas Pendidikan', 'Megantara', 'PKA', '2025-10-30 01:46:15');

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
-- Dumping data untuk tabel `tb_surat_arsip`
--

INSERT INTO `tb_surat_arsip` (`id`, `nomor_surat`, `tahun`, `kode_surat`, `perihal_surat`, `tanggal_surat`, `isi_ringkasan`, `tujuan_surat`, `nama_konseptor`, `unit_bidang`, `created_at`) VALUES
(1, 1, 2025, '800.1.13.2', 'KEPUTUSAN PEMBERHANTIAN', '2025-10-22', 'Keputusan pemberhentian dengan hormat pppk', 'BUPATI CIAMIS', 'MEGANTARA ', 'PENGADAAN DAN PEMBERHENTIAN', '2025-10-22 01:19:03'),
(2, 2, 2025, '800.1.13.3', 'Permohonan nara sumber', '2025-10-22', 'Permohonan nara sumber', 'Kepala DISBUDPORA', 'Bu Nina ', 'PKA', '2025-10-22 01:39:14'),
(4, 4, 2025, '800.1.11.6', 'Permohonan Persetujuan Hukuman Disiplin Berat', '2025-10-23', 'Usul Pemutusan Hubungan Perjanjian Kerja Dengan Hormat Tidak Atas Permintaan Sendiri ', 'Kepala BKN', 'Mulkiyatus Sa\'adah', 'PKPDA', '2025-10-22 02:48:55'),
(5, 5, 2025, '800.1.2.2', 'Konsep Surat Validasi Peserta Seleksi CASN Jabatan Tampungan', '2025-10-22', 'Sehubungan dengan itu, kami mohon agar dilakukan validasi kembali keaktifan bekerja peserta dimaksud hingga saat ini. Hasil validasi tersebut agar disertai dengan Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) bermeterai 10000 ditandatangani Kepala OPD masing-masing dan diserahkan paling lambat pada hari Selasa, 15 Juli 2025 di ruang Pelayanan BKPSDM Kabupaten Ciamis.', 'Sekretaris Daerah', 'Megantara Halley Ruckmawan', 'PPIK', '2025-10-22 02:50:06'),
(6, 6, 2025, '800.1.6.2', 'Konsep Surat Keterangan  Untuk Melakukan Perceraian ', '2025-10-22', 'Pengantar Keterangan Perceraian atasnama IFTY WINAHYU DInas Kesehatan', 'Bupati Ciamis', 'Maher', 'BPKPDA', '2025-10-22 02:51:13'),
(7, 7, 2025, '800.2', 'Permohonan Penambahan/Penguatan Kapasitas Bandwidth Internet', '2025-10-22', '-', 'Diskominfo ciamis', 'Diki', 'Pka', '2025-10-22 02:52:31'),
(8, 8, 2025, '800.2.4', 'Permohonan narasumber', '2025-10-22', 'Permohonan narasumber webinar a.n. II SUTISNA, S.H.', 'II SUTISNA, S.H.', 'Nina Agustinia', 'Pengembangan Kompetensi Aparatur', '2025-10-22 02:53:28'),
(9, 9, 2025, '800.1.6.2', 'Klarifikasi Ketidakhadiran', '2025-10-22', 'Klarifikasi ketidakhadiran gur pada tanggal 28 Juli 2025.', 'Kepala Dinas Pendidikan', 'Mulkiyatus Sa\'adah', 'PKPDA', '2025-10-22 02:54:13'),
(12, 12, 2025, '800.1.6.2', 'Permohonan nara sumber', '2025-10-28', '1', '2', 'MEGANTARA ', 'pka', '2025-10-22 07:04:36'),
(31, 3, 2025, '440/KU.03.02', 'Implementasi Integrasi Zonita Pamor Kab Ciamis dan SiDakep', '2025-09-03', 'Disampaikan dengan hormat, dalam rangka optimalisasi penerimaan pajak kendaraan bermotor tahun 2025', 'Bapak Sekretariat Daerah Kabupaten Ciamis', 'Angga', 'Pengadaan', '2025-10-28 01:17:54'),
(32, 1291, 2025, '500.14.1', 'Diskominfo', '2025-05-15', 'Disampaikan dengan hormat, dalam rangka meningkatkan kualitas data statistik dan Implementasi Satu Data Indonesia', 'Diskominfo', 'Kepala Dinas Komunikasi', 'Diskominfo', '2025-10-28 01:22:47');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tb_surat`
--
ALTER TABLE `tb_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `tb_surat_arsip`
--
ALTER TABLE `tb_surat_arsip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
