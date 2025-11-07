<?php
session_start();
include 'koneksi.php'; 

if (!isset($_POST['captcha_input']) || !is_numeric($_POST['captcha_input']) || intval($_POST['captcha_input']) !== $_SESSION['captcha_answer']) {

    unset($_SESSION['captcha_answer']); 
    header("Location: ../login.php?error=captcha");
    exit;
}

unset($_SESSION['captcha_answer']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT * FROM tb_pegawai WHERE nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
        
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['unit_bidang'] = $user['unit_bidang']; 
            
          
            $_SESSION['role'] = $user['role']; 
   
            header("Location: ../index.php");
            exit;
        }
    }
    
    header("Location: ../login.php?error=1");
    exit;
}
?>