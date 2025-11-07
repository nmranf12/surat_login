<?php

include 'auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
   
    header("Location: ../admin/halaman_surat.php?error=unauthorized");
    exit;
}
?>