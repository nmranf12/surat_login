<?php

include 'superadmin_check.php';

include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (!isset($_POST['id_reset']) || !is_numeric($_POST['id_reset']) || empty($_POST['new_password'])) {
        header("Location: ../admin/manage_admin.php?status=reset_gagal&error=" . urlencode("Data tidak lengkap."));
        exit;
    }

    $id = $_POST['id_reset'];
    $password_baru_mentah = $_POST['new_password'];


    $password_hash_baru = password_hash($password_baru_mentah, PASSWORD_DEFAULT);

   
    $stmt = $koneksi->prepare("UPDATE tb_pegawai SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $password_hash_baru, $id);

    if ($stmt->execute()) {
 
        header("Location: ../admin/manage_admin.php?status=reset_sukses");
    } else {
        header("Location: ../admin/manage_admin.php?status=reset_gagal&error=" . urlencode($stmt->error));
    }
    
    $stmt->close();
    $koneksi->close();

} else {
 
    header("Location: ../admin/manage_admin.php");
    exit;
}
?>