<?php
// --- 1. KONEKSI DATABASE ---

include '../core/koneksi.php';

// --- 2. VALIDASI DAN PENGAMBILAN ID DARI URL ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // --- 3. PERSIAPAN PREPARED STATEMENT (SELECT) ---
    $stmt = $koneksi->prepare("SELECT * FROM tb_surat WHERE id = ?");
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $result = $stmt->get_result();

    // --- 4. PENGECEKAN HASIL QUERY ---
    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc(); 
    } else {
        die("Error: Data surat tidak ditemukan.");
    }
    $stmt->close(); 
} else {
    die("Error: ID tidak valid.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Surat</title>
    <link rel="icon" href="../assets/logo-ciamis.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">
          <img src="../assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
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
                    <i class="bi bi-pencil-square"></i>
                    Edit Data Surat
                </h4>
            </div>
            <div class="card-body p-4">

                <form action="../core/proses_update.php" method="POST">
                    
                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">NOMOR SURAT</label>
                        <input type="text" class="form-control" value="<?php echo $data['nomor_surat']; ?>" disabled readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kode_surat" class="form-label">KODE SURAT</label>
                            <input type="text" class="form-control" id="kode_surat" name="kode_surat" 
                                   value="<?php echo htmlspecialchars($data['kode_surat']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_surat" class="form-label">TANGGAL SURAT</label>
                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" 
                                   value="<?php echo $data['tanggal_surat']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="perihal_surat" class="form-label">PERIHAL SURAT</label>
                        <input type="text" class="form-control" id="perihal_surat" name="perihal_surat" 
                               value="<?php echo htmlspecialchars($data['perihal_surat']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="isi_ringkasan" class="form-label">ISI RINGKASAN SURAT</label>
                        <textarea class="form-control" id="isi_ringkasan" name="isi_ringkasan" rows="3"><?php echo htmlspecialchars($data['isi_ringkasan']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tujuan_surat" class="form-label">TUJUAN SURAT / KEPADA</label>
                            <input type="text" class="form-control" id="tujuan_surat" name="tujuan_surat" 
                                   value="<?php echo htmlspecialchars($data['tujuan_surat']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_konseptor" class="form-label">NAMA KONSEPTOR</label>
                            <input type="text" class="form-control" id="nama_konseptor" name="nama_konseptor" 
                                   value="<?php echo htmlspecialchars($data['nama_konseptor']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unit_bidang" class="form-label">UNIT BIDANG</label>
                        <input type="text" class="form-control" id="unit_bidang" name="unit_bidang" 
                               value="<?php echo htmlspecialchars($data['unit_bidang']); ?>" required>
                    </div>

                    <hr>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="halaman_surat.php" class="btn btn-secondary me-md-2">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>