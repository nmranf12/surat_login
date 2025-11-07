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

include '../core/koneksi.php';

$search_sql = "";   
$search_term = "";   
$search_params = [];
$search_type = 'ssss'; 

if (!empty($_GET['search'])) {
    $search_term = $_GET['search'];

    $search_like = '%' . $search_term . '%';

    $search_sql = " WHERE (perihal_surat LIKE ? OR kode_surat LIKE ? OR tujuan_surat LIKE ? OR nama_konseptor LIKE ?)";
    
    $search_params = [$search_like, $search_like, $search_like, $search_like];
}

$allowed_sort_columns = ['nomor_surat', 'kode_surat', 'perihal_surat', 'tanggal_surat'];

$sort_column = 'nomor_surat'; 
$sort_order = 'DESC';         

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_columns)) {
    $sort_column = $_GET['sort'];
}
if (isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC'])) {
    $sort_order = $_GET['order'];
}

$sql = "SELECT * FROM tb_surat" . $search_sql . " ORDER BY $sort_column $sort_order";

$stmt = $koneksi->prepare($sql);

if (!empty($search_params)) {

    $stmt->bind_param($search_type, ...$search_params);
}
$stmt->execute();
$result = $stmt->get_result(); 
$filename = "export_data_surat_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');

header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

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

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        fputcsv($output, [
            $row['nomor_surat'],
            $row['kode_surat'],
            $row['perihal_surat'],
            $row['isi_ringkasan'],
            $row['tanggal_surat'],
            $row['tujuan_surat'],
            $row['nama_konseptor'],
            $row['unit_bidang'],
            $row['created_at']
        ]);
    }
}

fclose($output); 
$stmt->close();
$koneksi->close();

exit();
?>