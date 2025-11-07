<?php 

include '../core/superadmin_check.php'; 
include '../core/koneksi.php'; 


$user_list_result = $koneksi->query("SELECT id, nip, unit_bidang, nama_lengkap, role FROM tb_pegawai ORDER BY nama_lengkap");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Admin</title>
    <link rel="icon" href="../assets/logo-ciamis.ico" type="image/x-icon">
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
        .pagination .page-item .page-link {
            color: var(--purple);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--purple);
            border-color: var(--purple);
            color: #fff;
        }
  
        .table-custom-header th {
            background-color: var(--purple-dark);
            color: #fff;
            border-color: var(--purple-dark);
        }
 
        .table.align-middle th, .table.align-middle td {
            vertical-align: middle;
        }
        .table:not(.table-sm) th, .table:not(.table-sm) td {
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
            padding-left: 1.2rem;
            padding-right: 1.2rem;
        }
    </style>
    </head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="halaman_surat.php">
             <i class="bi bi-person-gear me-2"></i>Manajemen Admin
        </a>
        <div class="ms-auto">
             <a href="halaman_surat.php" class="btn btn-light me-2"><i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Data Surat</a>
             <a href="../core/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </div>
      </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> Daftar Admin</h4>
                    </div>
                    <div class="card-body p-0"> <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-custom-header">
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>NIP</th>
                                        <th>Unit Bidang</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($user_list_result->num_rows > 0): ?>
                                        <?php while($user = $user_list_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                                <td><?php echo htmlspecialchars($user['nip']); ?></td>
                                                <td><?php echo htmlspecialchars($user['unit_bidang']); ?></td>
                                                
                                                <td>
                                                    <span class="badge <?php echo ($user['role'] === 'superadmin' ) ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo ($user['role'] === 'superadmin') ? 'Superadmin' : 'User'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm btn-reset-pass text-dark" 
                                                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal" 
                                                            data-id="<?php echo $user['id']; ?>" 
                                                            data-nama_admin="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
                                                        <i class="bi bi-key-fill"></i> Reset
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center p-5">Belum ada admin terdaftar.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header" style="background-color: var(--purple); color: #fff;">
                        <h4 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i> Tambah Admin Baru</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <form action="../core/proses_registrasi.php" method="POST">
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="unit_bidang" class="form-label">Unit Bidang</label>
                                <input type="text" class="form-control" id="unit_bidang" name="unit_bidang" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                
                                <div class="form-text">
                                    Password minimal 8 karakter, harus mengandung:<br>
                                    - 1 huruf besar (A-Z)<br>
                                    - 1 huruf kecil (a-z)<br>
                                    - 1 angka (0-9)<br>
                                    - 1 simbol (contoh: !@#$%)
                                </div>
                            </div>
                            <hr>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-person-plus-fill me-2"></i>Daftarkan Admin
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

    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form action="../core/proses_reset_password.php" method="POST">
            <div class="modal-content">
              <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password untuk...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p>Anda akan mereset password untuk <strong id="namaAdminDiModal"></strong>.</p>
                <input type="hidden" id="idAdminReset" name="id_reset">
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">Masukkan Password Baru:</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required autofocus>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-key-fill"></i> Reset Password Sekarang</button>
              </div>
            </div>
        </form>
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
        modalHeader.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning', 'text-white', 'text-dark', 'btn-primary');
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
                modalHeader.classList.add('btn-primary', 'text-white'); // Ungu
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

        if (status) {
            let message = '';
            let type = 'success';

            switch (status) {
                case 'sukses':
                    message = 'Admin baru berhasil ditambahkan!';
                    type = 'success';
                    break;
                case 'reset_sukses':
                    message = 'Password admin berhasil direset!';
                    type = 'success';
                    break;
                case 'reset_gagal':
                    message = 'Gagal mereset password! ' + (error || '');
                    type = 'danger';
                    break;
            }
            if (message) {
                showModalNotification(message, type);
          
                const newUrl = window.location.pathname;
                history.replaceState(null, '', newUrl);
            }
        }
        if (error) {
            showModalNotification(error, 'danger');

            const newUrl = window.location.pathname;
            history.replaceState(null, '', newUrl);
        }

        const resetModal = document.getElementById('resetPasswordModal');
        if(resetModal) {
            resetModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const idAdmin = button.getAttribute('data-id');
                const namaAdmin = button.getAttribute('data-nama_admin');
                const modalTitle = resetModal.querySelector('.modal-title');
                const modalNama = resetModal.querySelector('#namaAdminDiModal');
                const modalInputId = resetModal.querySelector('#idAdminReset');
                
                modalTitle.textContent = 'Reset Password untuk ' + namaAdmin;
                modalNama.textContent = namaAdmin;
                modalInputId.value = idAdmin;
            });
        }
    });
    </script>
</body>
</html>