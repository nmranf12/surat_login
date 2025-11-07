<?php 

include '../core/auth_check.php'; 

include '../core/koneksi.php';
$tahun_list_result = $koneksi->query("SELECT DISTINCT(tahun) FROM tb_surat WHERE tahun IS NOT NULL ORDER BY tahun DESC");
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
$sql_total = "SELECT COUNT(*) as total FROM tb_surat" . $sql_where;
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

$sql = "SELECT * FROM tb_surat" . $sql_where . " ORDER BY $order_by LIMIT ? OFFSET ?";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Surat Masuk</title> 
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
        .table-custom-header th a {
            color: #fff;
            text-decoration: none;
        }
        .table-custom-header th a:hover {
            text-decoration: underline;
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
     
        .header-button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: flex-end;
        }
    </style>
    </head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="halaman_surat.php">
          <img src="../assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
         Sistem Informasi Penomoran Surat
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
            <li class="nav-item">
              <span class="navbar-text me-3">
                Halo, <strong class="ms-1"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
              </span>
            </li>
            <li class="nav-item">
                <a href="../index.php" class="btn btn-light me-2">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard Publik
                </a>
            </li>
            <li class="nav-item">
              <a href="../core/logout.php" class="btn btn-danger">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container-fluid mt-4">
        
        <div class="card shadow-sm">
            <div class="card-header bg-white" style="padding: 1.5rem;">
                <div class="row align-items-center justify-content-between gy-3">
                    <div class="col-lg-4">
                        <h4 class="mb-0">Manajemen Data Surat</h4>
                    </div>
                    <div class="col-lg-8">
                        <div class="header-button-group">
                            <a href="../form.php" class="btn btn-primary btn-lg shadow-sm">
                                <i class="bi bi-plus-lg me-2"></i> Input Surat Baru
                            </a>
                            <div class="btn-group" role="group">
                                <a href="../core/export.php?search=<?php echo htmlspecialchars($search_term); ?>&filter_tahun=<?php echo htmlspecialchars($filter_tahun); ?>&filter_nomor=<?php echo htmlspecialchars($filter_nomor); ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>" class="btn btn-outline-success">
                                    <i class="bi bi-file-earmark-excel-fill me-2"></i> Export
                                </a>
                                <a href="halaman_arsip.php" class="btn btn-outline-secondary">
                                     <i class="bi bi-archive-fill me-2"></i> Lihat Arsip
                                </a>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="setting_nomor.php" class="btn btn-outline-warning text-dark" title="Pengaturan">
                                    <i class="bi bi-gear-fill"></i>
                                </a>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                                    <a href="manage_admin.php" class="btn btn-outline-info text-dark" title="Manajemen Admin">
                                        <i class="bi bi-person-gear"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">

                <form action="halaman_surat.php" method="GET" class="mb-0">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari perihal, kode, tujuan, konseptor..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                        <div class="col-lg-2">
                            <select name="filter_tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                <?php mysqli_data_seek($tahun_list_result, 0); ?>
                                <?php while($tahun_row = $tahun_list_result->fetch_assoc()): ?>
                                    <option value="<?php echo $tahun_row['tahun']; ?>" <?php echo ($tahun_row['tahun'] == $filter_tahun) ? 'selected' : ''; ?>>
                                        <?php echo $tahun_row['tahun']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <input type="number" name="filter_nomor" class="form-control"
                                   placeholder="Nomor >= (misal: 10)"
                                   value="<?php echo htmlspecialchars($filter_nomor); ?>">
                        </div>
                        <div class="col-lg-3 d-flex">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i> Filter
                            </button>
                            <a href="halaman_surat.php" class="btn btn-outline-danger ms-2" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-custom-header">
                            <tr>
                                <?php
                                function getSortIcon($column_name, $sort_column, $sort_order) {
                                    if ($column_name == $sort_column) {
                                        return ($sort_order == 'DESC') ? ' <i class="bi bi-caret-down-fill"></i>' : ' <i class="bi bi-caret-up-fill"></i>';
                                    }
                                    return '';
                                }
                                function getSortUrl($column_name, $toggle_order, $search, $tahun, $nomor) {
                                    return "halaman_surat.php?sort=$column_name&order=$toggle_order&search=" . htmlspecialchars($search) . "&filter_tahun=" . htmlspecialchars($tahun) . "&filter_nomor=" . htmlspecialchars($nomor);
                                }
                                ?>
                                <th><a href="<?php echo getSortUrl('nomor_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>">No. Surat <?php echo getSortIcon('nomor_surat', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('tahun', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>">Tahun <?php echo getSortIcon('tahun', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('kode_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>">Kode Surat <?php echo getSortIcon('kode_surat', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('perihal_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>">Perihal Surat <?php echo getSortIcon('perihal_surat', $sort_column, $sort_order); ?></a></th>
                                <th>Isi Ringkasan</th>
                                <th><a href="<?php echo getSortUrl('tanggal_surat', $toggle_order, $search_term, $filter_tahun, $filter_nomor); ?>">Tanggal Surat <?php echo getSortIcon('tanggal_surat', $sort_column, $sort_order); ?></a></th>
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
                                $types .= 'ii'; 
                                $params[] = $limit;
                                $params[] = $offset;
                                $stmt->bind_param($types, ...$params); 
                            } else {
                                $stmt->bind_param("ii", $limit, $offset); 
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
                                            <a href="edit.php?id=' . $row["id"] . '" class="btn btn-primary btn-sm" title="Edit Data">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>';
                                    
                                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
                                        echo ' <a href="../core/hapus.php?id=' . $row["id"] . '" class="btn btn-danger btn-sm" title="Arsipkan Data" onclick="return confirm(\'Apakah Anda yakin ingin MENGARSIPKAN data ini? Data akan dipindah ke halaman arsip.\')">
                                                    <i class="bi bi-archive-fill"></i>
                                               </a>';
                                    }
                                    
                                    echo '</td>';
                                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10' class='text-center p-5'>Data surat tidak ditemukan.</td></tr>";
                            }
                            $stmt->close();
                            $koneksi->close();
                            ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation" class="p-3">
                    <ul class="pagination justify-content-end pagination-sm mb-0">
                        <?php 
                        $base_url = "halaman_surat.php?sort=$sort_column&order=$sort_order&search=" . htmlspecialchars($search_term) . "&filter_tahun=" . htmlspecialchars($filter_tahun) . "&filter_nomor=" . htmlspecialchars($filter_nomor);
                        ?>

                        <?php if($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $page-1; ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="'.$base_url.'&page=1">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php endfor;

                         if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                             echo '<li class="page-item"><a class="page-link" href="'.$base_url.'&page='.$total_pages.'">'.$total_pages.'</a></li>';
                        }
                        ?>
                        <?php if($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $page+1; ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

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
        if (status) {
            let message = '';
            let type = 'success';
            switch (status) {
                case 'sukses':
                    message = 'Data surat baru berhasil disimpan!';
                    type = 'success';
                    break;
                case 'update_sukses':
                    message = 'Data surat berhasil diperbarui!';
                    type = 'success';
                    break;
                case 'diarsipkan':
                    message = 'Data surat berhasil diarsipkan!';
                    type = 'info'; 
                    break;
            }
            if (message) {
                showModalNotification(message, type);
              
                const newUrl = window.location.pathname + window.location.search.replace(/[\?&]status=[^&]+/, '').replace(/^&/, '?');
                history.replaceState(null, '', newUrl);
            }
        }
    });
    </script>
</body>
</html>