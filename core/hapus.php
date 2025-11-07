<?php

include '../core/auth_check.php';

include '../core/koneksi.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id = $_GET['id']; 

    $koneksi->begin_transaction();

    try {
        $sql_insert = "INSERT INTO tb_surat_arsip SELECT * FROM tb_surat WHERE id = ?";
        $stmt_insert = $koneksi->prepare($sql_insert);
        $stmt_insert->bind_param("i", $id);
        $stmt_insert->execute();
        $stmt_insert->close();

        $sql_delete = "DELETE FROM tb_surat WHERE id = ?";
        $stmt_delete = $koneksi->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        $stmt_delete->close();

        $koneksi->commit();
        header("Location: ../admin/halaman_surat.php?status=diarsipkan");

    } catch (mysqli_sql_exception $exception) {
        $koneksi->rollback();
        die("Error saat memindahkan data: " . $exception->getMessage());
    }

    $koneksi->close();

} else {
    header("Location: ../admin/halaman_surat.php");
    exit();
}
?>