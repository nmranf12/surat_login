<?php

include 'core/auth_check_root.php';
include 'core/koneksi.php';

$stmt_get = $koneksi->prepare("SELECT next_number, tahun FROM tb_counter WHERE id = 1");
$stmt_get->execute();
$result = $stmt_get->get_result();
$counter = $result->fetch_assoc();
$stmt_get->close();

$nomor_display = $counter['next_number'];
$tahun_display = $counter['tahun'];

$tahun_sistem = date('Y');

if ($tahun_sistem > $tahun_display) {
    $nomor_display = 1;       
    $tahun_display = $tahun_sistem; 
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logo-ciamis.ico" type="image/x-icon">
    <title>Form Input Surat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --purple: #6f42c1;
            --purple-dark: #5a369e;
            --purple-light: #e0d8f0;
            --secondary-bg: #f0f2f5;
            --card-border-color: rgba(0,0,0,.125);
        }
        body {
            background-color: var(--secondary-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background-color: var(--purple) !important;
            border-bottom: 1px solid var(--purple-dark);
        }
        .navbar-brand .navbar-logo {
            height: 35px;
            width: auto;
        }
        
        .form-container-modern {
            max-width: 900px;
        }
        .form-container-modern .card-header {
            background-color: var(--purple);
            color: #fff;
        }
        .form-container-modern .card-body {
            padding: 2.5rem;
        }
        
        .form-group-custom {
            margin-bottom: 2rem;
            position: relative;
        }
        .form-group-custom .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .form-group-custom .form-control {
            border: none;
            border-bottom: 2px solid #ced4da; 
            border-radius: 0;
            padding-left: 0;
            padding-right: 0;
            background-color: transparent;
            font-size: 1.1rem; 
        }
        .form-group-custom .form-control:focus {
            border-color: var(--purple); 
            box-shadow: none;
            background-color: transparent;
        }
  
        .form-group-custom textarea.form-control {
             border: 2px solid #ced4da;
             border-radius: 5px;
             padding: 0.75rem;
             font-size: 1rem;
        }
        .form-group-custom textarea.form-control:focus {
             border-color: var(--purple);
             background-color: #fff;
        }
        
      
        .btn-custom-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }
        .btn-custom-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            color: #fff;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
             Sistem Informasi Penomoran Surat 
        </a>
        <span class="navbar-text d-none d-lg-block">
          BKPSDM CIAMIS
        </span>
      </div>
    </nav>
    <div class="container form-container-modern mt-4 mb-4">
        <div class="card shadow-sm" style="border-radius: 10px;">
            <div class="card-header" style="border-radius: 10px 10px 0 0;">
                <h4 class="mb-0 d-flex align-items-center">
                    <i class="bi bi-plus-circle-fill me-2"></i> 
                    Formulir Input Data Surat
                </h4>
            </div>
            <div class="card-body">
                
                <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
                    <div class="alert alert-success" role="alert">
                        Data berhasil disimpan! Anda mendapatkan <strong>Nomor Surat: <?php echo htmlspecialchars($_GET['nomor']); ?></strong>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info text-center" role="alert" style="border-radius: 8px;">
                    <h5 class="alert-heading mb-0">Nomor Surat Berikutnya: <strong><?php echo $nomor_display; ?></strong></h5>
                    <small>(Tahun: <?php echo $tahun_display; ?>)</small>
                </div>

                <form action="core/simpan.php" method="POST">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="kode_surat" class="form-label">KODE SURAT</label>
                                <input type="text" class="form-control" id="kode_surat" name="kode_surat" placeholder="Contoh: 800.1.2.3" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="tanggal_surat" class="form-label">TANGGAL SURAT</label>
                                <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group-custom">
                        <label for="perihal_surat" class="form-label">PERIHAL SURAT</label>
                        <input type="text" class="form-control" id="perihal_surat" name="perihal_surat" required>
                    </div>

                    <div class="form-group-custom">
                        <label for="isi_ringkasan" class="form-label">ISI RINGKASAN SURAT</label>
                        <textarea class="form-control" id="isi_ringkasan" name="isi_ringkasan" rows="3" placeholder="(Opsional)"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="tujuan_surat" class="form-label">TUJUAN SURAT / KEPADA</label>
                                <input type="text" class="form-control" id="tujuan_surat" name="tujuan_surat" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="nama_konseptor" class="form-label">NAMA KONSEPTOR</label>
                                <input type="text" class="form-control" id="nama_konseptor" name="nama_konseptor" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label for="unit_bidang" class="form-label">UNIT BIDANG</label>
                        <input type="text" class="form-control" id="unit_bidang" name="unit_bidang" required>
                    </div>

                    <hr class="my-4">
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-custom-secondary btn-lg me-md-2">
                            <i class="bi bi-x-circle me-2"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save-fill me-2"></i> Simpan dan Ambil Nomor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>