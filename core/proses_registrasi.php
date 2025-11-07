<?php
include 'superadmin_check.php';

include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nip = $_POST['nip'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $unit_bidang = $_POST['unit_bidang'];
    $password_mentah = $_POST['password'];
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!preg_match($regex, $password_mentah)) {
        header("Location: ../admin/manage_admin.php?error=Password tidak kuat");
        exit;
    }


    $password_hash = password_hash($password_mentah, PASSWORD_DEFAULT);

    $stmt_cek = $koneksi->prepare("SELECT id FROM tb_pegawai WHERE nip = ?");
    $stmt_cek->bind_param("s", $nip);
    $stmt_cek->execute();
    
    $result_cek = $stmt_cek->get_result(); 

    if ($result_cek->num_rows > 0) {

        header("Location: ../admin/manage_admin.php?error=NIP sudah terdaftar");
        exit;
    }
    $stmt_cek->close();

    $stmt = $koneksi->prepare(
        "INSERT INTO tb_pegawai (nip, nama_lengkap, password, unit_bidang, role) 
         VALUES (?, ?, ?, ?, 'user')" 
    );
    $stmt->bind_param("ssss", $nip, $nama_lengkap, $password_hash, $unit_bidang);

    if ($stmt->execute()) {

        header("Location: ../admin/manage_admin.php?status=sukses");
    } else {
       
        header("Location: ../admin/manage_admin.php?error=Gagal menyimpan data");
    }
    $stmt->close();
    $koneksi->close();

} else {
    
    header("Location: ../admin/manage_admin.php");
    exit;
}
?>