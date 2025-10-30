<?php 
// 1. KUNCI HALAMAN
include '../core/auth_check.php'; 
// (Kode PHP Anda sudah benar)
include '../core/koneksi.php';
$tahun_list_result = $koneksi->query("SELECT DISTINCT(tahun) FROM tb_surat_arsip WHERE tahun IS NOT NULL ORDER BY tahun DESC");
$limit = 10; 
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search_term  = $_GET['search'] ?? '';
$filter_tahun = $_GET['filter_tahun'] ?? '';
$filter_nomor = $_GET['filter_nomor'] ?? '';
$where_conditions = []; 
$params = [];
$types = "";
if (!empty($search_term)) {
    $search_like = '%' . $search_term . '%';
    $where_conditions[] = "(perihal_surat LIKE ? OR kode_surat LIKE ? OR tujuan_surat LIKE ? OR nama_konseptor LIKE ?)";
    array_push($params, $search_like, $search_like, $search_like, $search_like);
    $types .= 'ssss';
}
if (!empty($filter_tahun)) {
    $where_conditions[] = "tahun = ?";
    $params[] = $filter_tahun;
    $types .= 'i';
}
if (!empty($filter_nomor) && is_numeric($filter_nomor)) {
    $where_conditions[] = "nomor_surat >= ?";
    $params[] = $filter_nomor;
    $types .= 'i';
}
$sql_where = "";
if (!empty($where_conditions)) {
    $sql_where = " WHERE " . implode(" AND ", $where_conditions);
}
$sql_total = "SELECT COUNT(*) as total FROM tb_surat_arsip" . $sql_where;
$stmt_total = $koneksi->prepare($sql_total);
if (!empty($params)) { 
    $stmt_total->bind_param($types, ...$params); 
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_rows = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_total->close();
$allowed_sort_columns = ['nomor_surat', 'kode_surat', 'perihal_surat', 'tanggal_surat', 'tahun'];
$sort_column = $_GET['sort'] ?? 'tahun'; 
$sort_order = $_GET['order'] ?? 'DESC';
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'tahun';
    $sort_order = 'DESC';
}
$toggle_order = ($sort_order == 'DESC') ? 'ASC' : 'DESC';
$order_by = "";
if ($sort_column == 'tahun') {
    $order_by = "tahun $sort_order, nomor_surat DESC";
} else if ($sort_column == 'nomor_surat') {
    $order_by = "nomor_surat $sort_order, tahun DESC";
} else {
    $order_by = "$sort_column $sort_order, tahun DESC, nomor_surat DESC";
}
$sql = "SELECT * FROM tb_surat_arsip" . $sql_where . " ORDER BY $order_by LIMIT $limit OFFSET $offset";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Arsip Surat</title> <link rel="icon" href="../assets/logo-ciamis.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="halaman_surat.php">
          <img src="../assets/logo-ciamis.ico" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
         Sistem Informasi Penomoran Surat
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <span class="navbar-text me-3">
                Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
              </span>
            </li>
            <li class="nav-item"><a href="../index.php" class="btn btn-light me-2">Dashboard Publik</a></li>
            <li class="nav-item">
              <a href="../core/logout.php" class="btn btn-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container-fluid mt-4">

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h4 class="mb-0">Data Arsip Surat</h4> </div>
                <div class="col-lg-6 text-lg-end mt-2 mt-lg-0">
                    <a href="../form.php" class="btn btn-primary btn-sm mb-1 mb-lg-0">
                        <i class="bi bi-plus-circle-fill"></i> Input Baru
                    </a>
                    <a href="../core/export_arsip.php?search=<?php echo htmlspecialchars($search_term); ?>&filter_tahun=<?php echo htmlspecialchars($filter_tahun); ?>&filter_nomor=<?php echo htmlspecialchars($filter_nomor); ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>" class="btn btn-success btn-sm ms-lg-1 mb-1 mb-lg-0">
                        <i class="bi bi-file-earmark-excel-fill"></i> Export Arsip Ini
                    </a>
                    <a href="halaman_surat.php" class="btn btn-info btn-sm ms-lg-1 mb-1 mb-lg-0 text-dark">
                        <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Data Aktif
                    </a>
                     </div>
            </div>
        </div>
        <div class="card-body">
            
            <form action="halaman_arsip.php" method="GET" class="mb-3"> <div class="row g-2">
                    <div class="col-md-5 mb-1 mb-md-0">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Cari perihal, kode, tujuan, konseptor..." 
                               value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <div class="col-md-2 mb-1 mb-md-0">
                        <select name="filter_tahun" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            <?php while($tahun_row = $tahun_list_result->fetch_assoc()): ?>
                                <option value="<?php echo $tahun_row['tahun']; ?>" <?php echo ($tahun_row['tahun'] == $filter_tahun) ? 'selected' : ''; ?>>
                                    <?php echo $tahun_row['tahun']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-1 mb-md-0">
                        <input type="number" name="filter_nomor" class="form-control form-control-sm"
                               placeholder="Nomor >= (misal: 10)"
                               value="<?php echo htmlspecialchars($filter_nomor); ?>">
                    </div>
                    <div class="col-md-3 d-flex">
                        <button type="submit" class="btn btn-info btn-sm text-dark">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="halaman_arsip.php" class="btn btn-danger btn-sm ms-1"> <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <?php
                            function getSortIcon($column_name, $sort_column, $sort_order) {
                                if ($column_name == $sort_column) {
                                    return ($sort_order == 'DESC') ? ' <i class="bi bi-caret-down-fill"></i>' : ' <i class="bi bi-caret-up-fill"></i>';
                                }
                                return '';
                            }
                            function getSortUrl($column_name, $toggle_order, $search, $tahun, $nomor) {
                                return "halaman_arsip.php?sort=$column_name&order=$toggle_order&search=" . htmlspecialchars($search) . "&filter_tahun=" . htmlspecialchars($tahun) . "&filter_nomor=" . htmlspecialchars($nomor);
                            }
                            ?>
                            <th><a href="<?php echo getSortUrl('nomor_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>" class="text-white text-decoration-none">No. Surat <?php echo getSortIcon('nomor_surat', $sort_column, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('tahun', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>" class="text-white text-decoration-none">Tahun <?php echo getSortIcon('tahun', $sort_column, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('kode_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>" class="text-white text-decoration-none">Kode Surat <?php echo getSortIcon('kode_surat', $sort_column, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('perihal_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>" class="text-white text-decoration-none">Perihal Surat <?php echo getSortIcon('perihal_surat', $sort_column, $sort_order); ?></a></th>
                            <th>Isi Ringkasan</th>
                            <th><a href="<?php echo getSortUrl('tanggal_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>" class="text-white text-decoration-none">Tanggal Surat <?php echo getSortIcon('tanggal_surat', $sort_column, $sort_order); ?></a></th>
                            <th>Tujuan Surat</th>
                            <th>Nama Konseptor</th>
                            <th>Unit Bidang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                   <tbody>
                        <?php
                        $stmt = $koneksi->prepare($sql);
                        if (!empty($params)) { 
                            $stmt->bind_param($types, ...$params); 
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($row["nomor_surat"]) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row["tahun"]) . "</td>"; 
                                echo "<td>" . htmlspecialchars($row["kode_surat"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["perihal_surat"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["isi_ringkasan"]) . "</td>";
                                echo "<td>" . date('d/m/Y', strtotime($row["tanggal_surat"])) . "</td>";
                                echo "<td>" . htmlspecialchars($row["tujuan_surat"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["nama_konseptor"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["unit_bidang"]) . "</td>";
                                
                                echo '<td class="text-nowrap">
                                    <a href="../core/hapus_permanen.php?id=' . $row["id"] . '" class="btn btn-danger btn-sm text-white" title="Hapus Permanen" onclick="return confirm(\'PERINGATAN: Anda akan menghapus data ini secara PERMANEN. Data tidak bisa dikembalikan. Lanjutkan?\')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                  </td>';
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>Data arsip tidak ditemukan</td></tr>";
                        }
                        $stmt->close();
                        $koneksi->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end pagination-sm">
                    <?php 
                    $base_url = "halaman_arsip.php?sort=$sort_column&order=$sort_order&search=" . htmlspecialchars($search_term) . "&filter_tahun=" . htmlspecialchars($filter_tahun) . "&filter_nomor=" . htmlspecialchars($filter_nomor);
                    ?>

                    <?php if($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $page-1; ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <?php if($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $page+1; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

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
    // ### FUNGSI JAVASCRIPT MODAL ###
    function showModalNotification(message, type = 'success') {
        const modalElement = document.getElementById('notificationModal');
        if (!modalElement) return;
        const modal = new bootstrap.Modal(modalElement);
        const modalTitle = document.getElementById('notificationModalTitle');
        const modalBody = document.getElementById('notificationModalBody');
        const modalHeader = document.getElementById('notificationModalHeader');
        const modalCloseButton = modalHeader.querySelector('.btn-close');
        modalHeader.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning', 'text-white', 'text-dark');
        modalTitle.classList.remove('text-white', 'text-dark');
        modalCloseButton.classList.remove('btn-close-white');
        switch (type) {
            case 'danger':
                modalTitle.textContent = 'Berhasil!';
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

    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status === 'hapus_permanen_sukses') {
            showModalNotification('Data arsip telah berhasil dihapus permanen!', 'danger');
            history.replaceState(null, '', window.location.pathname);
        }
    });
    </script>
</body>
</html>s