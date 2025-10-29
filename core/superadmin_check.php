<?php
// 1. Cek dulu apakah sudah login
include 'auth_check.php';

// 2. Cek apakah rolenya 'superadmin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    // Jika bukan superadmin, tendang ke panel admin biasa
    header("Location: ../admin/halaman_surat.php?error=unauthorized");
    exit;
}
?>