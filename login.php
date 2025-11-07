<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php"); 
    exit;
}

$num1 = rand(1, 9);
$num2 = rand(1, 9);
$_SESSION['captcha_answer'] = $num1 + $num2;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penomoran Surat</title>
    <link rel="icon" href="assets/logo-ciamis.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh; 
            background-color: #f8f9fa;
        }
        .login-container {
            display: flex;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden; 
            max-width: 900px; 
            width: 100%;
            margin: 20px; 
        }
        .login-illustration {
            flex: 1;
            background-color: #e6f7ff; 
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            min-width: 300px;
        }
        .login-illustration img {
            max-width: 100%;
            height: auto;
            max-height: 300px; 
        }
        .login-form-area {
            flex: 1;
            padding: 40px;
            min-width: 350px; 
        }
        .form-label {
            font-weight: 600;
        }
        .login-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .login-subtitle {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 30px;
        }
       
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 450px; 
            }
            .login-illustration {
                padding: 20px;
            }
            .login-form-area {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-illustration">
            <img src="assets/logo-ciamis.png" alt="Logo Kabupaten Ciamis">
        </div>

        <div class="login-form-area">
            <h2 class="login-title">Selamat Datang di SIPS!</h2>
            <p class="login-subtitle">Silakan masukkan NIP dan Kata Sandi Anda untuk melanjutkan.</p>
            
            <?php if(isset($_GET['error']) && $_GET['error'] == '1'): ?>
                <div class="alert alert-danger" role="alert">
                    NIP atau Kata Sandi salah!
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error']) && $_GET['error'] == 'captcha'): ?>
                <div class="alert alert-warning" role="alert">
                    Jawaban perhitungan keamanan salah!
                </div>
            <?php endif; ?>

            <form action="core/proses_login.php" method="POST">
                <div class="mb-3">
                    <label for="nip" class="form-label">NIP</label>
                    <input type="text" class="form-control" id="nip" name="nip" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="captcha_input" class="form-label">Perhitungan Keamanan (Berapa <?php echo "$num1 + $num2 = ?"; ?>)</label>
                    <input type="number" class="form-control" id="captcha_input" name="captcha_input" placeholder="Jawaban Anda" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>