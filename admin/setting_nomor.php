<?php

include '../core/auth_check.php';

include '../core/koneksi.php';

$pesan_sukses = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    if (isset($_POST['next_number']) && is_numeric($_POST['next_number'])) {
        $nomor_baru = intval($_POST['next_number']);
        $tahun_baru = intval($_POST['tahun']);

        $stmt_update = $koneksi->prepare("UPDATE tb_counter SET next_number = ?, tahun = ? WHERE id = 1");
        $stmt_update->bind_param("ii", $nomor_baru, $tahun_baru);
        
        if ($stmt_update->execute()) {
            $pesan_sukses = "Pengaturan nomor berhasil disimpan. Nomor berikutnya adalah $nomor_baru untuk tahun $tahun_baru.";
        }
        $stmt_update->close();
    }
}

$stmt_get = $koneksi->prepare("SELECT next_number, tahun FROM tb_counter WHERE id = 1");
$stmt_get->execute();
$result = $stmt_get->get_result();
$counter = $result->fetch_assoc();
$stmt_get->close();

$nomor_sekarang = $counter['next_number'];
$tahun_sekarang = $counter['tahun'];

$tahun_list_result_aktif = $koneksi->query("SELECT DISTINCT(tahun) FROM tb_surat WHERE tahun IS NOT NULL ORDER BY tahun DESC");

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Nomor Surat</title>
    <link rel="icon" href="../assets/logo-ciamis.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">

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
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .card-header {
            border-bottom: 1px solid var(--card-border-color);
            background-color: #fff;
            padding: 1.25rem 1.5rem;
        }
        .card-footer {
            background-color: #fcfcfc;
            border-top: 1px solid var(--card-border-color);
            padding: 1rem 1.5rem;
        }
        .btn-primary {
            background-color: var(--purple);
            border-color: var(--purple);
        }
        .btn-primary:hover {
            background-color: var(--purple-dark);
            border-color: var(--purple-dark);
        }
        .btn-outline-primary {
            color: var(--purple);
            border-color: var(--purple);
        }
        .btn-outline-primary:hover {
            background-color: var(--purple);
            color: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--purple-light);
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }
        .nav-tabs .nav-link {
            color: var(--purple);
        }
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>
    </head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: var(--purple) !important;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="halaman_surat.php">
          <img src="../assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
          Pengaturan Penomoran Surat Dan Arsip Surat
        </a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li class="nav-item">
              <span class="navbar-text me-3">
                Login sebagai: <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
              </span>
            </li>
            <li class="nav-item"><a href="halaman_surat.php" class="btn btn-light me-2"><i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Data Surat</a></li>
            <li class="nav-item"><a href="../core/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container form-container mt-4" style="max-width: 900px;">
        
        <div class="row justify-content-center">
            <div class="col-lg-12"> <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $pesan_sukses; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header" style="background-color: var(--purple); color: #fff;">
                <h4 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Pengaturan Nomor Surat</h4>
            </div>
            <div class="card-body p-4">
                <form action="setting_nomor.php" method="POST">
                    <div class="mb-3">
                        <label for="next_number" class="form-label">Atur Nomor Surat Berikutnya</label>
                        <input type="number" class="form-control" id="next_number" name="next_number" 
                               value="<?php echo $nomor_sekarang; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tahun" class="form-label">Untuk Tahun</label>
                        <input type="number" class="form-control" id="tahun" name="tahun" 
                               value="<?php echo $tahun_sekarang; ?>" required>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save-fill me-2"></i> Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>

        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
        
            <div class="card shadow-sm mt-4 mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-archive-fill me-2"></i> Arsipkan Data (Reset)</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-danger">
                        <strong>PERINGATAN:</strong> Tindakan ini akan <strong>MEMINDAHKAN</strong> data surat dari tabel aktif ke tabel arsip.
                    </p>

                    <ul class="nav nav-tabs nav-fill mb-3" id="arsipTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="by-tahun-tab" data-bs-toggle="tab" data-bs-target="#by-tahun" type="button" role="tab" aria-controls="by-tahun" aria-selected="true">Berdasarkan Tahun</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="by-tanggal-tab" data-bs-toggle="tab" data-bs-target="#by-tanggal" type="button" role="tab" aria-controls="by-tanggal" aria-selected="false">Berdasarkan Tanggal</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="arsipTabContent">
                        
                        <div class="tab-pane fade show active" id="by-tahun" role="tabpanel" aria-labelledby="by-tahun-tab">
                            <form action="../core/proses_arsip.php" method="POST" 
                                  onsubmit="return confirm('Anda yakin ingin mengarsipkan SEMUA data di tahun yang dipilih?');">
                                
                                <div class="mb-3">
                                    <label for="tahun_arsip" class="form-label fw-bold">Pilih Tahun dari Data Aktif untuk Diarsipkan:</label>
                                    <select name="tahun_arsip" id="tahun_arsip" class="form-select" required>
                                        <option value="">-- Pilih Tahun --</option>
                                        <?php if ($tahun_list_result_aktif->num_rows > 0): ?>
                                            <?php while($tahun_row = $tahun_list_result_aktif->fetch_assoc()): ?>
                                                <option value="<?php echo $tahun_row['tahun']; ?>">
                                                    <?php echo $tahun_row['tahun']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <option value="" disabled>Tidak ada data aktif untuk diarsip</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg" <?php echo ($tahun_list_result_aktif->num_rows == 0) ? 'disabled' : ''; ?>>
                                        <i class="bi bi-archive-fill me-2"></i> Arsipkan Berdasarkan Tahun
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="by-tanggal" role="tabpanel" aria-labelledby="by-tanggal-tab">
                             <form action="../core/proses_arsip.php" method="POST" 
                                  onsubmit="return confirm('Anda yakin ingin mengarsipkan SEMUA data dalam rentang tanggal ini?');">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_mulai" class="form-label fw-bold">Dari Tanggal:</label>
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_selesai" class="form-label fw-bold">Sampai Tanggal:</label>
                                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="bi bi-calendar-range-fill me-2"></i> Arsipkan Berdasarkan Tanggal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        
        <?php endif; ?> </div> 
    
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" id="notificationModalHeader">
            <h5 class="modal-title" id="notificationModalTitle">Modal title</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="notificationModalBody">
            ...
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function showModalNotification(message, type = 'success') {
        const modalElement = document.getElementById('notificationModal');
        if (!modalElement) return;
        const modal = new bootstrap.Modal(modalElement);
        const modalTitle = document.getElementById('notificationModalTitle');
        const modalBody = document.getElementById('notificationModalBody');
        const modalHeader = document.getElementById('notificationModalHeader');
        const modalCloseButton = modalHeader.querySelector('.btn-close');
        modalHeader.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning', 'btn-primary');
        modalTitle.classList.remove('text-white', 'text-dark');
        modalCloseButton.classList.remove('btn-close-white');
        switch (type) {
            case 'danger':
                modalTitle.textContent = 'Gagal!';
                modalHeader.classList.add('bg-danger', 'text-white');
                modalTitle.classList.add('text-white');
                modalCloseButton.classList.add('btn-close-white');
                break;
            case 'info':
                modalTitle.textContent = 'Informasi';
                modalHeader.classList.add('btn-primary', 'text-white');
                modalTitle.classList.add('text-white');
                modalCloseButton.classList.add('btn-close-white');
                break;
            case 'warning':
                modalTitle.textContent = 'Peringatan';
                modalHeader.classList.add('bg-warning', 'text-dark');
                modalTitle.classList.add('text-dark');
                break;
            case 'success':
            default:
                modalTitle.textContent = 'Sukses!';
                modalHeader.classList.add('bg-success', 'text-white');
                modalTitle.classList.add('text-white');
                modalCloseButton.classList.add('btn-close-white');
                break;
        }
        modalBody.textContent = message;
        modal.show();
    }
    
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const error = urlParams.get('error');

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
            if (status) {
                let message = '';
                let type = 'success';

                switch (status) {
                    case 'arsip_sukses':
                        message = 'Data berhasil diarsipkan!';
                        type = 'success';
                        break;
                    case 'arsip_kosong':
                        message = 'Tidak ada data untuk diarsipkan pada kriteria tersebut.';
                        type = 'warning';
                        break;
                    case 'arsip_gagal':
                        message = 'Gagal mengarsipkan data! ' + (error || 'Terjadi kesalahan.');
                        type = 'danger';
                        break;
                }

                if (message) {
                    showModalNotification(message, type);
         
                    history.replaceState(null, '', window.location.pathname);
                }
            }
        <?php endif; ?>
     
    });
    </script>
</body>
</html>