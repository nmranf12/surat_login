<?php
/**
 * Ekspor Data Surat ke CSV
 *
 * File ini bertugas untuk menghasilkan file CSV dari data di 'tb_surat'.
 * Fitur utama skrip ini adalah:
 * 1. Menyertakan koneksi database.
 * 2. Menerima parameter GET opsional: 'search', 'sort', dan 'order'.
 * 3. Membangun query SQL dinamis yang mencakup filter pencarian (WHERE)
 * dan pengurutan (ORDER BY) berdasarkan parameter tersebut.
 * 4. Menggunakan "Prepared Statements" untuk parameter pencarian (mencegah SQL Injection).
 * 5. Menggunakan "Allow List" (daftar putih) untuk parameter pengurutan
 * (mencegah SQL Injection pada ORDER BY).
 * 6. Mengatur HTTP Header untuk memaksa browser mengunduh file
 * sebagai file .csv.
 * 7. Menulis data langsung ke 'php://output' stream untuk efisiensi memori.
 *
 * @uses koneksi.php File untuk koneksi database (menyediakan var $koneksi).
 */

// --- 1. KONEKSI DATABASE ---
include 'core/koneksi.php';

// --- 2. LOGIKA PENCARIAN (SEARCH FILTERING) ---
// Inisialisasi variabel untuk query pencarian
$search_sql = "";    // Bagian query WHERE
$search_term = "";   // Istilah yang dicari
$search_params = []; // Array parameter untuk bind_param
$search_type = 'ssss'; // Tipe data untuk bind_param (4 string)

// Periksa apakah parameter 'search' ada di URL (misal: ...export.php?search=sesuatu)
if (!empty($_GET['search'])) {
    $search_term = $_GET['search'];
    // Siapkan istilah pencarian untuk query LIKE
    $search_like = '%' . $search_term . '%';
    
    // Buat klausa WHERE. Data dicari di 4 kolom.
    $search_sql = " WHERE (perihal_surat LIKE ? OR kode_surat LIKE ? OR tujuan_surat LIKE ? OR nama_konseptor LIKE ?)";
    
    // Siapkan array parameter yang akan di-bind ke placeholder (?)
    $search_params = [$search_like, $search_like, $search_like, $search_like];
}

// --- 3. LOGIKA PENGURUTAN (SORTING) ---
// Tentukan kolom mana saja yang BOLEH digunakan untuk sorting.
// di klausa ORDER BY, karena ORDER BY tidak bisa pakai placeholder (?).
$allowed_sort_columns = ['nomor_surat', 'kode_surat', 'perihal_surat', 'tanggal_surat'];

// Tentukan nilai default
$sort_column = 'nomor_surat'; // Kolom default untuk sort
$sort_order = 'DESC';         // Urutan default

// Periksa apakah parameter 'sort' ada dan valid (ada di allow list)
if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_columns)) {
    $sort_column = $_GET['sort'];
}
// Periksa apakah parameter 'order' ada dan valid (hanya ASC atau DESC)
if (isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC'])) {
    $sort_order = $_GET['order'];
}

// --- 4. PERSIAPAN QUERY SQL FINAL ---
// Gabungkan semua bagian: query dasar, query pencarian (jika ada), dan query pengurutan
$sql = "SELECT * FROM tb_surat" . $search_sql . " ORDER BY $sort_column $sort_order";

// --- 5. EKSEKUSI QUERY DENGAN PREPARED STATEMENTS ---
$stmt = $koneksi->prepare($sql);

// Bind parameter pencarian HANYA JIKA ada parameter pencarian
if (!empty($search_params)) {
    // '...' adalah "splat operator", untuk membongkar array $search_params
    // menjadi argumen individual untuk bind_param.
    $stmt->bind_param($search_type, ...$search_params);
}
$stmt->execute();
$result = $stmt->get_result(); // Ambil hasil query

// --- 6. PENGATURAN HTTP HEADER UNTUK DOWNLOAD CSV ---
// Buat nama file dinamis berdasarkan tanggal
$filename = "export_data_surat_" . date('Y-m-d') . ".csv";

// Beri tahu browser bahwa ini adalah file CSV, bukan HTML
header('Content-Type: text/csv; charset=utf-8');
// Beri tahu browser untuk men-download file ini dengan nama $filename
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Tulis baris Header (Nama Kolom) ke file CSV
fputcsv($output, [
    'No. Surat', 
    'Kode Surat', 
    'Perihal Surat', 
    'Isi Ringkasan', 
    'Tanggal Surat', 
    'Tujuan Surat', 
    'Nama Konseptor', 
    'Unit Bidang', 
    'Tgl. Input Sistem'
]);

// Loop melalui setiap baris hasil query
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Tulis data baris ke file CSV
        fputcsv($output, [
            $row['nomor_surat'],
            $row['kode_surat'],
            $row['perihal_surat'],
            $row['isi_ringkasan'],
            $row['tanggal_surat'],
            $row['tujuan_surat'],
            $row['nama_konseptor'],
            $row['unit_bidang'],
            $row['created_at'] // Asumsi ada kolom created_at
        ]);
    }
}

fclose($output); 
$stmt->close();
$koneksi->close();

exit();
?>