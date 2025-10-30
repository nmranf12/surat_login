<?php
// Hubungkan ke database untuk ambil nomor

include 'core/koneksi.php';

// Ambil nomor berikutnya untuk ditampilkan ke user
$stmt_get = $koneksi->prepare("SELECT next_number, tahun FROM tb_counter WHERE id = 1");
$stmt_get->execute();
$result = $stmt_get->get_result();
$counter = $result->fetch_assoc();
$stmt_get->close();

// Ambil data dari database
$nomor_display = $counter['next_number'];
$tahun_display = $counter['tahun'];

// Ambil tahun sistem saat ini
$tahun_sistem = date('Y');

// Cek apakah tahun sistem SUDAH MELEWATI tahun di DB
if ($tahun_sistem > $tahun_display) {
    $nomor_display = 1;        // Nomor akan reset ke 1
    $tahun_display = $tahun_sistem; // Tampilkan tahun baru (tahun sistem)
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
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
            Sistem Pengambilan Nomor Surat
        </a>
        <span class="navbar-text d-none d-lg-block">
          BKPSDM CIAMIS
        </span>
      </div>
    </nav>
    <div class="container form-container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle-fill"></i> 
                    Formulir Input Data Surat
                </h4>
            </div>
            <div class="card-body p-4">
                
                <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
                    <div class="alert alert-success" role="alert">
                        Data berhasil disimpan! Anda mendapatkan <strong>Nomor Surat: <?php echo htmlspecialchars($_GET['nomor']); ?></strong>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info text-center" role="alert">
                    <h5 class="alert-heading mb-0">Nomor Surat Berikutnya: <strong><?php echo $nomor_display; ?></strong></h5>
                    <small>(Tahun: <?php echo $tahun_display; ?>)</small>
                </div>
                <form action="core/simpan.php" method="POST">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kode_surat" class="form-label">KODE SURAT</label>
                            <input type="text" class="form-control" id="kode_surat" name="kode_surat" placeholder="Contoh: 800.1.2.3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_surat" class="form-label">TANGGAL SURAT</label>
                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="perihal_surat" class="form-label">PERIHAL SURAT</label>
                        <input type="text" class="form-control" id="perihal_surat" name="perihal_surat" required>
                    </div>

                    <div class="mb-3">
                        <label for="isi_ringkasan" class="form-label">ISI RINGKASAN SURAT</label>
                        <textarea class="form-control" id="isi_ringkasan" name="isi_ringkasan" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tujuan_surat" class="form-label">TUJUAN SURAT / KEPADA</label>
                            <input type="text" class="form-control" id="tujuan_surat" name="tujuan_surat" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_konseptor" class="form-label">NAMA KONSEPTOR</label>
                            <input type="text" class="form-control" id="nama_konseptor" name="nama_konseptor" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unit_bidang" class="form-label">UNIT BIDANG</label>
                        <input type="text" class="form-control" id="unit_bidang" name="unit_bidang" required>
                    </div>

                    <hr>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> Simpan dan Ambil Nomor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>