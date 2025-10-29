<?php
session_start();
$_SESSION = array();
session_destroy();

// Arahkan kembali ke halaman publik (index.php)
header("Location: ../index.php");
exit;
?>