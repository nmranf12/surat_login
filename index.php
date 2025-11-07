<?php

include 'core/auth_check_root.php';

include 'core/koneksi.php';

$today = date('Y-m-d');
$sql_today = "SELECT SUM(count_per_table) as total_today
              FROM (
                  (SELECT COUNT(*) as count_per_table FROM tb_surat WHERE DATE(created_at) = ?)
                  UNION ALL
                  (SELECT COUNT(*) as count_per_table FROM tb_surat_arsip WHERE DATE(created_at) = ?)
              ) as combined_counts";
$stmt_today = $koneksi->prepare($sql_today);
$stmt_today->bind_param("ss", $today, $today); 
$stmt_today->execute();
$total_hari_ini = $stmt_today->get_result()->fetch_assoc()['total_today'] ?? 0;
$stmt_today->close();

$month = date('Y-m');
$sql_month = "SELECT SUM(count_per_table) as total_month
              FROM (
                  (SELECT COUNT(*) as count_per_table FROM tb_surat WHERE DATE_FORMAT(created_at, '%Y-%m') = ?)
                  UNION ALL
                  (SELECT COUNT(*) as count_per_table FROM tb_surat_arsip WHERE DATE_FORMAT(created_at, '%Y-%m') = ?)
              ) as combined_counts";
$stmt_month = $koneksi->prepare($sql_month);
$stmt_month->bind_param("ss", $month, $month);
$stmt_month->execute();
$total_bulan_ini = $stmt_month->get_result()->fetch_assoc()['total_month'] ?? 0;
$stmt_month->close();

$year = date('Y');
$sql_year = "SELECT SUM(count_per_table) as total_year
             FROM (
                 (SELECT COUNT(*) as count_per_table FROM tb_surat WHERE tahun = ?)
                 UNION ALL
                 (SELECT COUNT(*) as count_per_table FROM tb_surat_arsip WHERE tahun = ?)
             ) as combined_counts";
$stmt_year = $koneksi->prepare($sql_year);
$stmt_year->bind_param("ii", $year, $year); 
$stmt_year->execute();
$total_tahun_ini = $stmt_year->get_result()->fetch_assoc()['total_year'] ?? 0;
$stmt_year->close();

$sql_arsip = "SELECT COUNT(*) as total_arsip FROM tb_surat_arsip";
$total_arsip = $koneksi->query($sql_arsip)->fetch_assoc()['total_arsip'] ?? 0;

$tahun_list_result = $koneksi->query("SELECT DISTINCT(tahun) FROM tb_surat WHERE tahun IS NOT NULL ORDER BY tahun DESC");

$limit = 10;

$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

$offset = ($page - 1) * $limit;

$search_term  = $_GET['search'] ?? '';
$filter_tahun = $_GET['filter_tahun'] ?? '';

$where_conditions = [];
$params = [];
$types = "";

if (!empty($search_term)) {
    $search_like = '%' . $search_term . '%';
    $where_conditions[] = "(
        nomor_surat LIKE ? OR
        tahun LIKE ? OR
        kode_surat LIKE ? OR
        perihal_surat LIKE ? OR
        isi_ringkasan LIKE ? OR
        tujuan_surat LIKE ? OR
        nama_konseptor LIKE ? OR
        unit_bidang LIKE ?
    )";
    for ($i=0; $i < 8; $i++) {
        $params[] = $search_like;
    }
    $types .= 'ssssssss';
}

if (!empty($filter_tahun) && is_numeric($filter_tahun)) {
    $where_conditions[] = "tahun = ?"; 
    $params[] = $filter_tahun;        
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
$total_rows = $result_total->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_rows / $limit);
$stmt_total->close();

$allowed_sort_columns = ['nomor_surat', 'kode_surat', 'perihal_surat', 'tanggal_surat', 'tahun', 'nama_konseptor', 'created_at']; // Tambah created_at
$sort_column = $_GET['sort'] ?? 'created_at'; 
$sort_order = $_GET['order'] ?? 'DESC';
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'created_at';
    $sort_order = 'DESC';
}
$toggle_order = ($sort_order == 'DESC') ? 'ASC' : 'DESC'; 

$order_by = "$sort_column $sort_order"; 

if ($sort_column != 'created_at') {
    $order_by .= ", created_at DESC";
}
$order_by = "ORDER BY $order_by";


$sql = "SELECT * FROM tb_surat" . $sql_where . " " . $order_by . " LIMIT ? OFFSET ?";
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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Penomoran Surat - BKPSDM Ciamis</title>
    <link rel="icon" href="assets/logo-ciamis.ico" type="image/x-icon">
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
        .sidebar {
            background-color: #fff;
            padding: 20px;
            border-right: 1px solid var(--card-border-color);
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            padding-top: 70px; 
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .content-wrapper {
            margin-left: 250px; 
            padding-top: 20px;
            padding-bottom: 20px;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.show {
                transform: translateX(0%);
            }
            .content-wrapper {
                margin-left: 0;
            }
        }

        .stat-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .stat-card.bg-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
        .stat-card.bg-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
        .stat-card.bg-warning { background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%); color: #333; }
        .stat-card.bg-secondary { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); }


        .stat-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
        }
        .stat-card .card-text-data {
            flex-grow: 1;
            padding-right: 15px;
        }
        .stat-card .card-title {
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .card-text {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-card .card-icon {
            font-size: 3.5rem;
            opacity: 0.2;
            position: absolute;
            right: 15px;
            bottom: 10px;
        }

        .graph-placeholder {
            height: 150px;
            background-color: var(--purple-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--purple-dark);
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 15px;
            background-image: url("data:image/svg+xml,%3Csvg width='100%25' height='100%25' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='none' stroke='%23C1B3E0' stroke-width='1' stroke-dasharray='10%2C 5' d='M0 50h100M50 0v100'%3E%3C/path%3E%3C/svg%3E");
            background-size: 20px 20px;
            position: relative;
        }
        .graph-placeholder::after {
            content: 'Grafik Data Surat (Placeholder)';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--purple-dark);
            font-size: 0.9em;
            opacity: 0.7;
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

        .pagination .page-item .page-link {
            color: var(--purple);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--purple);
            border-color: var(--purple);
            color: #fff;
        }
        .pagination .page-item.active .page-link:hover {
            background-color: var(--purple-dark);
            border-color: var(--purple-dark);
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
        .form-control:focus {
            border-color: var(--purple-light);
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .badge-info {
            background-color: #17a2b8;
        }

     
        .nav-pills .nav-link {
            color: var(--purple);
            border: 1px solid var(--purple-light);
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 8px;
        }
        .nav-pills .nav-link.active {
            background-color: var(--purple);
            color: #fff;
            border-color: var(--purple);
        }
        .nav-pills .nav-link:hover {
             background-color: var(--purple-light);
        }
        .nav-pills .nav-link.active:hover {
             background-color: var(--purple-dark);
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

        .card-header-actions {
            display: flex;
            flex-wrap: wrap; 
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .card-header-actions .search-form {
             min-width: 250px;
        }

    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
          <img src="assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
          Sistem Informasi Penomoran Surat 
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">

            <li class="nav-item">
                <span class="navbar-text me-3 d-flex align-items-center">
                    Halo, <strong class="ms-1"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>
                </span>
            </li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                <li class="nav-item">
                    <a href="admin/halaman_surat.php" class="btn btn-light me-2 d-flex align-items-center">
                        <i class="bi bi-person-badge me-2"></i> Panel Admin
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="core/logout.php" class="btn btn-danger d-flex align-items-center">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>

          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid mt-4">
        
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-success h-100">
                    <div class="card-body">
                        <div class="card-text-data">
                            <h5 class="card-title">Surat Hari Ini</h5>
                            <p class="card-text"><?php echo $total_hari_ini; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-calendar-day"></i> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-primary h-100">
                    <div class="card-body">
                        <div class="card-text-data">
                            <h5 class="card-title">Surat Bulan Ini</h5>
                            <p class="card-text"><?php echo $total_bulan_ini; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-calendar-month"></i> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-warning h-100">
                    <div class="card-body">
                        <div class="card-text-data">
                            <h5 class="card-title">Surat Tahun Ini</h5>
                            <p class="card-text"><?php echo $total_tahun_ini; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-calendar-event"></i> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card bg-secondary h-100">
                    <div class="card-body">
                        <div class="card-text-data">
                           <h5 class="card-title">Total Arsip</h5>
                            <p class="card-text"><?php echo $total_arsip; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-archive-fill"></i> </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white" style="padding: 1.5rem;">
                        <h5 class="mb-3">Manajemen Data Surat</h5>
                        
                        <div class="card-header-actions">
                            <div class="btn-group" role="group" aria-label="Aksi Cepat">
                                <a href="form.php" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="bi bi-plus-lg me-2"></i> Input Surat Baru
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary btn-lg" title="Refresh Halaman">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                            
                            <form action="index.php" method="GET" class="search-form">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control form-control-lg"
                                           placeholder="Cari data surat..."
                                           value="<?php echo htmlspecialchars($search_term); ?>">
                                    <button type="submit" class="btn btn-info text-dark">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <?php if (!empty($search_term) || !empty($filter_tahun)): ?>
                                        <a href="index.php" class="btn btn-danger" title="Reset Pencarian/Filter">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-2 text-muted">Filter Cepat Tahun:</h6>
                        <ul class="nav nav-pills" role="tablist">
                            <?php
                            $search_param_filter = !empty($search_term) ? '&search=' . urlencode($search_term) : '';
                            $sort_param_filter = "&sort=$sort_column&order=$sort_order";
                            ?>
                            <li class="nav-item" role="presentation">
                                <a href="index.php?<?php echo trim($search_param_filter . $sort_param_filter, '&'); ?>"
                                   class="nav-link <?php echo (empty($_GET['filter_tahun'])) ? 'active' : ''; ?>">
                                   Semua
                                </a>
                            </li>

                            <?php mysqli_data_seek($tahun_list_result, 0); // Reset pointer hasil query ?>
                            <?php while($tahun_row = $tahun_list_result->fetch_assoc()): ?>
                                <?php $current_tahun = $tahun_row['tahun']; ?>
                                <li class="nav-item" role="presentation">
                                    <a href="index.php?filter_tahun=<?php echo $current_tahun . $search_param_filter . $sort_param_filter; ?>"
                                       class="nav-link <?php echo (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] == $current_tahun) ? 'active' : ''; ?>">
                                       <?php echo $current_tahun; ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>

                    </div>
                    
                    <div class="card-body" style="padding: 0;"> <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-custom-header">
                                    <tr>
                                        <?php
                                        function getSortIcon($column_name, $sort_column, $sort_order) {
                                            if ($column_name == $sort_column) {
                                                return ($sort_order == 'DESC') ? ' <i class="bi bi-caret-down-fill"></i>' : ' <i class="bi bi-caret-up-fill"></i>';
                                            } return '';
                                        }
                                        
                                        function getSortUrl($column_name, $current_active_column, $current_order, $search, $tahun) {
                                            if ($column_name == $current_active_column) {
                                                $new_order = ($current_order == 'DESC') ? 'ASC' : 'DESC';
                                            } else {
                                                $new_order = 'DESC';
                                            }
                                            return "index.php?sort=$column_name&order=$new_order&search=" . urlencode($search) . "&filter_tahun=" . urlencode($tahun);
                                        }
                                        ?>
                                        
                                        <th><a href="<?php echo getSortUrl('nomor_surat', $sort_column, $sort_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">No. Surat <?php echo getSortIcon('nomor_surat', $sort_column, $sort_order); ?></a></th>
                                        <th><a href="<?php echo getSortUrl('nama_konseptor', $sort_column, $sort_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Nama Konseptor <?php echo getSortIcon('nama_konseptor', $sort_column, $sort_order); ?></a></th>
                                        <th><a href="<?php echo getSortUrl('kode_surat', $sort_column, $sort_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Kode Surat <?php echo getSortIcon('kode_surat', $sort_column, $sort_order); ?></a></th>
                                        <th><a href="<?php echo getSortUrl('perihal_surat', $sort_column, $sort_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Perihal Surat <?php echo getSortIcon('perihal_surat', $sort_column, $sort_order); ?></a></th>
                                        <th><a href="<?php echo getSortUrl('tanggal_surat', $sort_column, $sort_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Tgl. Surat <?php echo getSortIcon('tanggal_surat', $sort_column, $sort_order); ?></a></th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                               <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $tgl_surat_formatted = date('d F Y', strtotime($row["tanggal_surat"]));
                                    ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($row["nomor_surat"]); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row["nama_konseptor"]); ?></td>
                                                <td><?php echo htmlspecialchars($row["kode_surat"]); ?></td>
                                                <td><?php echo htmlspecialchars($row["perihal_surat"]); ?></td>
                                                <td><?php echo date('d/m/y', strtotime($row["tanggal_surat"])); ?></td>

                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm btn-view"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewModal"
                                                        data-perihal="<?php echo htmlspecialchars($row['perihal_surat']); ?>"
                                                        data-ringkasan="<?php echo htmlspecialchars($row['isi_ringkasan']); ?>"
                                                        data-tujuan="<?php echo htmlspecialchars($row['tujuan_surat']); ?>"
                                                        data-konseptor="<?php echo htmlspecialchars($row['nama_konseptor']); ?>"
                                                        data-unit="<?php echo htmlspecialchars($row['unit_bidang']); ?>"
                                                        data-tanggal="<?php echo $tgl_surat_formatted; ?>"
                                                        data-tahun="<?php echo htmlspecialchars($row['tahun']); ?>"
                                                        data-kode="<?php echo htmlspecialchars($row['kode_surat']); ?>"
                                                        data-nomor="<?php echo htmlspecialchars($row['nomor_surat']); ?>">
                                                        <i class="bi bi-eye-fill"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php
                                        } // Akhir while
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center p-5'>Tidak ada data surat yang ditemukan.</td></tr>";
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                                </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end pagination-sm mb-0">
                                 <?php
                                $base_url = "index.php?sort=$sort_column&order=$sort_order&search=" . urlencode($search_term) . "&filter_tahun=" . urlencode($filter_tahun);
                                ?>

                                <?php if($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?php echo $base_url; ?>&page=<?php echo $page-1; ?>">Prev</a></li>
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
                </div> </div> </div> </div> <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewModalLabel">Detail Surat</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nomor Surat:</label>
                    <p id="modalNomor" class="fs-5"></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kode Surat:</label>
                    <p id="modalKode"></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tahun:</label>
                    <p id="modalTahun"></p>
                </div>
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label fw-bold">Perihal Surat:</label>
                <p id="modalPerihal"></p>
            </div>
            <hr>
             <div class="mb-3">
                <label class="form-label fw-bold">Isi Ringkasan:</label>
                <p id="modalRingkasan" style="white-space: pre-wrap;"></p>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tujuan Surat:</label>
                    <p id="modalTujuan"></p>
                </div>
                 <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Surat:</label>
                    <p id="modalTanggal"></p>
                </div>
            </div>
             <div class="row">
                 <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nama Konseptor:</label>
                    <p id="modalKonseptor"></p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Unit Bidang:</label>
                    <p id="modalUnit"></p>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        var viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var perihal = button.getAttribute('data-perihal');
            var ringkasan = button.getAttribute('data-ringkasan');
            var tujuan = button.getAttribute('data-tujuan');
            var konseptor = button.getAttribute('data-konseptor');
            var unit = button.getAttribute('data-unit');
            var tanggal = button.getAttribute('data-tanggal');
            var tahun = button.getAttribute('data-tahun');
            var kode = button.getAttribute('data-kode');
            var nomor = button.getAttribute('data-nomor');
            var modalPerihal = viewModal.querySelector('#modalPerihal');
            var modalRingkasan = viewModal.querySelector('#modalRingkasan');
            var modalTujuan = viewModal.querySelector('#modalTujuan');
            var modalKonseptor = viewModal.querySelector('#modalKonseptor');
            var modalUnit = viewModal.querySelector('#modalUnit');
            var modalTanggal = viewModal.querySelector('#modalTanggal');
            var modalTahun = viewModal.querySelector('#modalTahun');
            var modalKode = viewModal.querySelector('#modalKode');
            var modalNomor = viewModal.querySelector('#modalNomor');
            modalPerihal.textContent = perihal;
            modalTujuan.textContent = tujuan;
            modalKonseptor.textContent = konseptor;
            modalUnit.textContent = unit;
            modalTanggal.textContent = tanggal;
            modalTahun.textContent = tahun;
            modalKode.textContent = kode;
            modalNomor.textContent = nomor;
            if (!ringkasan) {
                modalRingkasan.textContent = '(Tidak ada ringkasan)';
                modalRingkasan.style.fontStyle = 'italic';
                modalRingkasan.style.color = '#6c757d';
            } else {
                 modalRingkasan.textContent = ringkasan;
                 modalRingkasan.style.fontStyle = 'normal';
                 modalRingkasan.style.color = 'inherit';
            }
        });
    </script>
    </body>
</html>