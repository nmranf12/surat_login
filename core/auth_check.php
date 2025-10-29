<?php
// Cek jika session belum dimulai, baru mulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login atau belum
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Jika belum, tendang ke halaman login
    // ▼▼▼ PERBAIKAN 1: Path 'header' (redirect) ▼▼▼
    header("Location: ../login.php");
    exit;
}
?>