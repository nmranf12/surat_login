<?php

include '../core/auth_check.php'; 

include '../core/koneksi.php';

$search_term  = $_GET['search'] ?? '';
$filter_tahun = $_GET['filter_tahun'] ?? '';
$filter_nomor = $_GET['filter_nomor'] ?? '';

$where_conditions = []; 
$params = [];           
$types = "";           

if (!empty($search_term)) {
    $search_like = '%' . $search_term . '%';
    $where_conditions[] = "(perihal_surat LIKE ? OR kode_surat LIKE ? OR tujuan_surat LIKE ? OR nama_konseptor LIKE ?)";
    array_push($params, $search_like, $search_like, $search_like, $search_like);
    $types .= 'ssss';
}

if (!empty($filter_tahun)) {
    $where_conditions[] = "tahun = ?";
    $params[] = $filter_tahun;
    $types .= 'i'; 
}

if (!empty($filter_nomor) && is_numeric($filter_nomor)) {
    $where_conditions[] = "nomor_surat >= ?";
    $params[] = $filter_nomor;
    $types .= 'i'; 
}

$sql_where = "";
if (!empty($where_conditions)) {
    $sql_where = " WHERE " . implode(" AND ", $where_conditions);
}

$allowed_sort_columns = ['nomor_surat', 'kode_surat', 'perihal_surat', 'tanggal_surat', 'tahun'];
$sort_column = $_GET['sort'] ?? 'tahun'; 
$sort_order = $_GET['order'] ?? 'DESC';
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'tahun';
    $sort_order = 'DESC';
}

$order_by = "";
if ($sort_column == 'tahun') {
    $order_by = "tahun $sort_order, nomor_surat DESC";
} else if ($sort_column == 'nomor_surat') {
    $order_by = "nomor_surat $sort_order, tahun DESC";
} else {
    $order_by = "$sort_column $sort_order, tahun DESC, nomor_surat DESC";
}

$sql = "SELECT * FROM tb_surat_arsip" . $sql_where . " ORDER BY $order_by";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="export_arsip_surat.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Nomor Surat', 
    'Tahun', 
    'Kode Surat', 
    'Perihal Surat', 
    'Isi Ringkasan', 
    'Tanggal Surat', 
    'Tujuan Surat', 
    'Nama Konseptor', 
    'Unit Bidang'
]);

try {
    $stmt = $koneksi->prepare($sql);
    if (!empty($params)) { 
        $stmt->bind_param($types, ...$params); 
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
     
            $tanggal_formatted = date('d/m/Y', strtotime($row["tanggal_surat"]));
     
            fputcsv($output, [
                $row["nomor_surat"],
                $row["tahun"],
                $row["kode_surat"],
                $row["perihal_surat"],
                $row["isi_ringkasan"],
                $tanggal_formatted, 
                $row["tujuan_surat"],
                $row["nama_konseptor"],
                $row["unit_bidang"]
            ]);
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {

    fputcsv($output, ['Terjadi error saat mengambil data: ' . $e->getMessage()]);
}

fclose($output);
$koneksi->close();
exit; 

?>