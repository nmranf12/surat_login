<?php
// 0. SATPAM: Hanya admin yang boleh akses
include '../core/auth_check.php';
include '../core/koneksi.php'; // Koneksi dibuka

$pesan_sukses = '';

// 1. Logika saat Admin menyimpan pengaturan baru
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cek apakah ini form untuk 'next_number'
    if (isset($_POST['next_number']) && is_numeric($_POST['next_number'])) {
        $nomor_baru = intval($_POST['next_number']);
        $tahun_baru = intval($_POST['tahun']);

        // Update nomor di database
        $stmt_update = $koneksi->prepare("UPDATE tb_counter SET next_number = ?, tahun = ? WHERE id = 1");
        $stmt_update->bind_param("ii", $nomor_baru, $tahun_baru);
        
        if ($stmt_update->execute()) {
            $pesan_sukses = "Pengaturan nomor berhasil disimpan. Nomor berikutnya adalah $nomor_baru untuk tahun $tahun_baru.";
        }
        $stmt_update->close();
    }
}

// 2. Ambil data counter saat ini untuk ditampilkan di form
$stmt_get = $koneksi->prepare("SELECT next_number, tahun FROM tb_counter WHERE id = 1");
$stmt_get->execute();
$result = $stmt_get->get_result();
$counter = $result->fetch_assoc();
$stmt_get->close();

$nomor_sekarang = $counter['next_number'];
$tahun_sekarang = $counter['tahun'];

// 3. (KODE BARU) Ambil daftar tahun unik dari tabel AKTIF (tb_surat)
//    Ini diperlukan untuk Opsi Arsip berdasarkan Tahun
$tahun_list_result_aktif = $koneksi->query("SELECT DISTINCT(tahun) FROM tb_surat WHERE tahun IS NOT NULL ORDER BY tahun DESC");

// Tutup koneksi di akhir
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting Nomor Surat</title>
    <link rel="icon" href="../assets/logo-ciamis.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="halaman_surat.php">
          <img src="../assets/logo-ciamis.ico" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
          Panel Admin
        </a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <span class="navbar-text me-3">
                Login sebagai: <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
              </span>
            </li>
            <li class="nav-item"><a href="halaman_surat.php" class="btn btn-light me-2">Kembali ke Data Surat</a></li>
            <li class="nav-item"><a href="../core/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container form-container mt-4" style="max-width: 600px;">
        
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-gear-fill"></i> Pengaturan Nomor Surat</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $pesan_sukses; ?>
                    </div>
                <?php endif; ?>
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="card shadow-sm mt-4 mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-archive-fill"></i> Arsipkan Data (Reset)</h5>
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
                                <button type="submit" class="btn btn-danger" <?php echo ($tahun_list_result_aktif->num_rows == 0) ? 'disabled' : ''; ?>>
                                    <i class="bi bi-archive-fill"></i> Arsipkan Berdasarkan Tahun
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
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-calendar-range-fill"></i> Arsipkan Berdasarkan Tanggal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div> 
    
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
    // ### 2. FUNGSI JAVASCRIPT MODAL ###
    function showModalNotification(message, type = 'success') {
        const modalElement = document.getElementById('notificationModal');
        if (!modalElement) return;
        const modal = new bootstrap.Modal(modalElement);
        const modalTitle = document.getElementById('notificationModalTitle');
        const modalBody = document.getElementById('notificationModalBody');
        const modalHeader = document.getElementById('notificationModalHeader');
        const modalCloseButton = modalHeader.querySelector('.btn-close');

        // Reset classes
        modalHeader.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning');
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
                modalHeader.classList.add('bg-info', 'text-dark');
                modalTitle.classList.add('text-dark');
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
    
    // ### 3. PEMICU NOTIFIKASI (UNTUK setting_nomor.php) ###
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const error = urlParams.get('error'); // Ambil juga pesan error

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
                // Bersihkan URL
                history.replaceState(null, '', window.location.pathname);
            }
        }
    });
    </script>
</body>
</html>