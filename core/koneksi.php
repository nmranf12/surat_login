<?php
/**
 * File Koneksi Database (koneksi.php)
 *
 * Mengkonfigurasi koneksi
 * ke database MySQL. 

 * @var mysqli $koneksi Objek koneksi database MySQLi yang digunakan di seluruh aplikasi.
 * @version 1.0
 */


$DB_HOST = 'localhost';       
$DB_USER = 'root';           
$DB_PASS = '';               
$DB_NAME = 'db_antrian_surat'; 


$koneksi = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($koneksi->connect_error) {
   
    die("Koneksi Gagal: " . $koneksi->connect_error);
}

date_default_timezone_set('Asia/Jakarta');

?>