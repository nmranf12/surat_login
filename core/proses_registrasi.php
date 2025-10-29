<?php
// 1. Kunci file ini juga
include 'superadmin_check.php';
// 2. Sertakan koneksi
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data mentah dari form
    $nip = $_POST['nip'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $unit_bidang = $_POST['unit_bidang'];
    $password_mentah = $_POST['password']; // misal: "123456"

    // --- INI JAWABAN ANDA ---
    // Buat HASH dari password mentah. PHP yang bekerja.
    $password_hash = password_hash($password_mentah, PASSWORD_DEFAULT);
    // $password_hash sekarang isinya -> "$2y$10$..."
    // ------------------------

    // Cek dulu apakah NIP sudah ada
    $stmt_cek = $koneksi->prepare("SELECT id FROM tb_pegawai WHERE nip = ?");
    $stmt_cek->bind_param("s", $nip);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($result_cek->num_rows > 0) {
        // NIP sudah terdaftar
        header("Location: ../admin/registrasi_admin.php?error=NIP sudah terdaftar");
        exit;
    }
    $stmt_cek->close();

    // Simpan admin baru (dengan password yang sudah di-hash)
    // Role otomatis diset sebagai 'admin'
    $stmt = $koneksi->prepare(
        "INSERT INTO tb_pegawai (nip, nama_lengkap, password, unit_bidang, role) 
         VALUES (?, ?, ?, ?, 'admin')"
    );
    $stmt->bind_param("ssss", $nip, $nama_lengkap, $password_hash, $unit_bidang);

    if ($stmt->execute()) {
        header("Location: ../admin/registrasi_admin.php?status=sukses");
    } else {
        header("Location: ../admin/registrasi_admin.php?error=Gagal menyimpan data");
    }
    $stmt->close();
    $koneksi->close();

} else {
    header("Location: ../admin/registrasi_admin.php");
    exit;
}
?>