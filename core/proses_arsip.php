<?php
include 'auth_check.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {

    header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=" . urlencode("Hanya Superadmin yang dapat mengarsipkan data."));
    exit;
}

include 'koneksi.php';

$mode = '';
$sql_where_condition = '';
$params = [];
$types = '';

if (isset($_POST['tahun_arsip']) && !empty($_POST['tahun_arsip']) && is_numeric($_POST['tahun_arsip'])) {
   
    $mode = 'tahun';
    $tahun = (int)$_POST['tahun_arsip'];
    
    $sql_where_condition = "WHERE tahun = ?";
    $params = [$tahun];
    $types = 'i';
    
} elseif (isset($_POST['tanggal_mulai']) && !empty($_POST['tanggal_mulai']) && 
          isset($_POST['tanggal_selesai']) && !empty($_POST['tanggal_selesai'])) {
   
    $mode = 'tanggal';
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

  
    if (strtotime($tanggal_mulai) > strtotime($tanggal_selesai)) {
         header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=Tanggal mulai tidak boleh lebih besar dari tanggal selesai");
        exit;
    }
    
    $sql_where_condition = "WHERE tanggal_surat BETWEEN ? AND ?";
    $params = [$tanggal_mulai, $tanggal_selesai];
    $types = 'ss';

} else {
    
    header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=Input tidak valid. Pilih tahun atau rentang tanggal.");
    exit;
}

$koneksi->begin_transaction();

try {
    $sql_insert = "INSERT INTO tb_surat_arsip SELECT * FROM tb_surat " . $sql_where_condition;
    $stmt_insert = $koneksi->prepare($sql_insert);
    $stmt_insert->bind_param($types, ...$params);
    $stmt_insert->execute();
    $stmt_insert->close();

    $sql_delete = "DELETE FROM tb_surat " . $sql_where_condition;
    $stmt_delete = $koneksi->prepare($sql_delete);
    $stmt_delete->bind_param($types, ...$params);
    $stmt_delete->execute();
    
    $rows_affected = $stmt_delete->affected_rows;
    $stmt_delete->close();

    $koneksi->commit();

    if ($rows_affected > 0) {
  
        header("Location: ../admin/setting_nomor.php?status=arsip_sukses");
    } else {

        header("Location: ../admin/setting_nomor.php?status=arsip_kosong");
    }
    
} catch (mysqli_sql_exception $exception) {

    $koneksi->rollback();
    
  
    header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=" . urlencode($exception->getMessage()));
    
} finally {
    $koneksi->close();
}

exit;
?>