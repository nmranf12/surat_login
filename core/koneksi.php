<?php
/**
 * File Koneksi Database (koneksi.php)
 *
 * Mengkonfigurasi koneksi
 * ke database MySQL. 

 * File ini biasanya di-include atau di-require di awal
 * setiap file PHP lain yang membutuhkan akses ke database.
 *
 * @var mysqli $koneksi Objek koneksi database MySQLi yang digunakan di seluruh aplikasi.
 * @version 1.0
 */

// --- 1. PENGATURAN KONFIGURASI DATABASE ---
// Sesuaikan nilai-nilai di bawah ini dengan konfigurasi server database Anda.

$DB_HOST = 'localhost';       // Host server database (biasanya 'localhost')
$DB_USER = 'root';            // Username database
$DB_PASS = '';                // Password database (kosong jika tidak ada)
$DB_NAME = 'db_antrian_surat'; // Nama database yang dituju

// ---------------------------

// --- 2. MEMBUAT KONEKSI DATABASE ---
// Membuat koneksi baru ke server MySQL menggunakan ekstensi MySQLi.
// Objek $koneksi akan digunakan oleh file-file lain untuk query.
$koneksi = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// --- 3. PEMERIKSAAN KONEKSI ---
// Memeriksa apakah terjadi error saat mencoba terhubung.
if ($koneksi->connect_error) {
    /**
     * Hentikan eksekusi skrip (die) jika koneksi gagal.
     * Menampilkan pesan error agar developer tahu apa yang salah.
     * Ini adalah langkah keamanan penting untuk mencegah skrip
     * berjalan tanpa koneksi database yang valid.
     */
    die("Koneksi Gagal: " . $koneksi->connect_error);
}

// --- 4. PENGATURAN ZONA WAKTU ---
// Mengatur zona waktu default untuk semua fungsi date/time di PHP.
// Ini memastikan bahwa semua cap waktu (timestamp) konsisten
// dengan lokasi server atau target audiens (WIB).
date_default_timezone_set('Asia/Jakarta');

?>