<?php
session_start();
include 'koneksi.php'; // Path ini sudah benar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    // (MODIFIKASI) Ambil juga kolom 'role'
    $stmt = $koneksi->prepare("SELECT * FROM tb_pegawai WHERE nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Password benar! Buat session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['unit_bidang'] = $user['unit_bidang']; 
            
            // --- (INI BARIS BARU) ---
            // Simpan peran pengguna ke session
            $_SESSION['role'] = $user['role']; 
            // ------------------------

            // Path ini sudah benar
            header("Location: ../admin/halaman_surat.php");
            exit;
        }
    }
    // Path ini sudah benar
    header("Location: ../login.php?error=1");
    exit;
}
?>