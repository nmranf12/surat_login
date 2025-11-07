<?php

include '../core/auth_check.php';

include '../core/koneksi.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id = $_GET['id']; 

    $stmt = $koneksi->prepare("DELETE FROM tb_surat_arsip WHERE id = ?");
 
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../admin/halaman_arsip.php?status=hapus_permanen_sukses");
    } else {
       
        die("Error saat menghapus data arsip: " . $stmt->error);
    }


    $stmt->close();
    $koneksi->close();

} else {
   
    header("Location: ../admin/halaman_arsip.php");
    exit();
}
?>