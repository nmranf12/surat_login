<?php
// Cek jika session belum dimulai, baru mulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path 'include' sudah benar
include 'core/koneksi.php';

// --- Query Statistik ---
// 1. Total HARI INI (Gabungan dari tb_surat dan tb_surat_arsip)
$today = date('Y-m-d');
$sql_today = "SELECT SUM(count_per_table) as total_today
              FROM (
                  (SELECT COUNT(*) as count_per_table FROM tb_surat WHERE DATE(created_at) = ?)
                  UNION ALL
                  (SELECT COUNT(*) as count_per_table FROM tb_surat_arsip WHERE DATE(created_at) = ?)
              ) as combined_counts";
$stmt_today = $koneksi->prepare($sql_today);
$stmt_today->bind_param("ss", $today, $today); // 's' untuk string
$stmt_today->execute();
$total_hari_ini = $stmt_today->get_result()->fetch_assoc()['total_today'];
$stmt_today->close();

// 2. Total BULAN INI (Gabungan dari tb_surat dan tb_surat_arsip)
$month = date('Y-m');
$sql_month = "SELECT SUM(count_per_table) as total_month
              FROM (
                  (SELECT COUNT(*) as count_per_table FROM tb_surat WHERE DATE_FORMAT(created_at, '%Y-%m') = ?)
                  UNION ALL
                  (SELECT COUNT(*) as count_per_table FROM tb_surat_arsip WHERE DATE_FORMAT(created_at, '%Y-%m') = ?)
              ) as combined_counts";
$stmt_month = $koneksi->prepare($sql_month);
$stmt_month->bind_param("ss", $month, $month); // 's' untuk string
$stmt_month->execute();
$total_bulan_ini = $stmt_month->get_result()->fetch_assoc()['total_month'];
$stmt_month->close();

// 3. Total TAHUN INI (Gabungan dari tb_surat dan tb_surat_arsip)
$year = date('Y');
$sql_year = "SELECT SUM(count_per_table) as total_year
             FROM (
                 (SELECT COUNT(*) as count_per_table FROM tb_surat WHERE tahun = ?)
                 UNION ALL
                 (SELECT COUNT(*) as count_per_table FROM tb_surat_arsip WHERE tahun = ?)
             ) as combined_counts";
$stmt_year = $koneksi->prepare($sql_year);
$stmt_year->bind_param("ii", $year, $year); // 'i' untuk integer
$stmt_year->execute();
$total_tahun_ini = $stmt_year->get_result()->fetch_assoc()['total_year'];
$stmt_year->close();

// 4. Total ARSIP (Ini sudah benar, hanya menghitung tb_surat_arsip)
$sql_arsip = "SELECT COUNT(*) as total_arsip FROM tb_surat_arsip";
$total_arsip = $koneksi->query($sql_arsip)->fetch_assoc()['total_arsip'];
// ------------------------------------------

// --- [BARU] Ambil daftar tahun unik untuk filter ---
$tahun_list_result = $koneksi->query("SELECT DISTINCT(tahun) FROM tb_surat WHERE tahun IS NOT NULL ORDER BY tahun DESC");
// --------------------------------------------------

// --- LOGIKA FILTER PENCARIAN, PAGINATION, SORT ---
$limit = 10;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil parameter pencarian & filter dari URL
$search_term  = $_GET['search'] ?? '';
$filter_tahun = $_GET['filter_tahun'] ?? ''; // Parameter baru

// Siapkan variabel untuk query SQL dinamis
$where_conditions = [];
$params = [];
$types = "";

// Tambahkan filter pencarian teks
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

// Tambahkan filter tahun
if (!empty($filter_tahun) && is_numeric($filter_tahun)) {
    $where_conditions[] = "tahun = ?"; // Kondisi SQL
    $params[] = $filter_tahun;         // Nilai untuk bind
    $types .= 'i';                     // Tipe data integer
}

// Gabungkan semua kondisi WHERE
$sql_where = "";
if (!empty($where_conditions)) {
    $sql_where = " WHERE " . implode(" AND ", $where_conditions);
}

// --- Hitung Total Data (untuk pagination) ---
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

// --- Logika Sorting ---
$allowed_sort_columns = ['nomor_surat', 'kode_surat', 'perihal_surat', 'tanggal_surat', 'tahun', 'nama_konseptor'];
$sort_column = $_GET['sort'] ?? 'tahun';
$sort_order = $_GET['order'] ?? 'DESC';
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'tahun';
    $sort_order = 'DESC';
}
$toggle_order = ($sort_order == 'DESC') ? 'ASC' : 'DESC';

// Klausa ORDER BY
$order_by = "tahun DESC, nomor_surat DESC"; // Default
if ($sort_column == 'tahun') {
    $order_by = "tahun $sort_order, nomor_surat DESC";
} else if ($sort_column == 'nomor_surat') {
    $order_by = "nomor_surat $sort_order, tahun DESC";
} else if ($sort_column == 'nama_konseptor') {
    $order_by = "nama_konseptor $sort_order, tahun DESC, nomor_surat DESC";
} else {
    $order_by = "$sort_column $sort_order, tahun DESC, nomor_surat DESC";
}

// --- Query Utama (untuk mengambil data) ---
$sql = "SELECT * FROM tb_surat" . $sql_where . " ORDER BY $order_by LIMIT $limit OFFSET $offset";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Penomoran Surat - BKPSDM Ciamis</title>
    <link rel="icon" href="assets/logo-ciamis.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        .stat-card .card-body { display: flex; align-items: center; justify-content: space-between; }
        .stat-card .card-body .card-text-data { flex-grow: 1; }
        .stat-card .card-body .card-icon { font-size: 2.5rem; opacity: 0.25; }
        .stat-card .card-title { font-size: 0.9rem; margin-bottom: 0.25rem; }
        .stat-card .card-text { font-size: 1.75rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
          <img src="assets/logo-ciamis.png" alt="Logo BKPSDM Ciamis" class="navbar-logo me-2">
          Sistem Informasi Penomoran Surat
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <li class="nav-item"><span class="navbar-text me-3">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span></li>
                <li class="nav-item"><a href="admin/halaman_surat.php" class="btn btn-light me-2"><i class="bi bi-person-badge"></i> Admin</a></li>
                <li class="nav-item"><a href="core/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a href="login.php" class="btn btn-light"><i class="bi bi-box-arrow-in-right"></i> Login Admin</a></li>
            <?php endif; ?>

          </ul>
        </div>
      </div>
    </nav>
    <div class="container-fluid mt-4">

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-white bg-success shadow h-100 stat-card">
                    <div class="card-body">
                        <div class="card-text-data">
                            <h5 class="card-title text-uppercase">Surat Hari Ini</h5>
                            <p class="card-text fw-bold"><?php echo $total_hari_ini; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-calendar-day"></i> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-white bg-primary shadow h-100 stat-card">
                    <div class="card-body">
                        <div class="card-text-data">
                            <h5 class="card-title text-uppercase">Bulan Ini</h5>
                            <p class="card-text fw-bold"><?php echo $total_bulan_ini; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-calendar-month"></i> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-dark bg-warning shadow h-100 stat-card">
                    <div class="card-body">
                        <div class="card-text-data">
                            <h5 class="card-title text-uppercase">Tahun Ini</h5>
                            <p class="card-text fw-bold"><?php echo $total_tahun_ini; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-calendar-event"></i> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-white bg-secondary shadow h-100 stat-card">
                    <div class="card-body">
                        <div class="card-text-data">
                           <h5 class="card-title text-uppercase">Total Arsip</h5>
                            <p class="card-text fw-bold"><?php echo $total_arsip; ?></p>
                        </div>
                        <div class="card-icon"> <i class="bi bi-archive-fill"></i> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
             <div class="card-header bg-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0"> Data Surat Masuk</h4>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <a href="form.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle-fill"></i> Input Surat Baru
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <form action="index.php" method="GET" class="mb-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari berdasarkan nomor, tahun, kode, perihal, isi, tujuan, konseptor, atau unit bidang..."
                               value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn btn-info text-dark">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <?php if (!empty($search_term)): ?>
                            <a href="index.php" class="btn btn-danger">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="mb-3">
                    <span class="me-2 fw-bold">Filter Tahun:</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filter Tahun">

                        <?php
                        // Ambil parameter GET lainnya untuk disertakan di link filter
                        $search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : '';
                        $sort_param = "&sort=$sort_column&order=$sort_order";
                        ?>

                        <a href="index.php?<?php echo trim($search_param . $sort_param, '&'); // Hapus & jika search kosong ?>"
                           class="btn <?php echo (empty($_GET['filter_tahun'])) ? 'btn-primary active' : 'btn-outline-primary'; ?>">
                           Semua
                        </a>

                        <?php mysqli_data_seek($tahun_list_result, 0); // Reset pointer hasil query ?>
                        <?php while($tahun_row = $tahun_list_result->fetch_assoc()): ?>
                            <?php $current_tahun = $tahun_row['tahun']; ?>
                            <a href="index.php?filter_tahun=<?php echo $current_tahun . $search_param . $sort_param; ?>"
                               class="btn <?php echo (isset($_GET['filter_tahun']) && $_GET['filter_tahun'] == $current_tahun) ? 'btn-primary active' : 'btn-outline-primary'; ?>">
                               <?php echo $current_tahun; ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <?php
                                function getSortIcon($column_name, $sort_column, $sort_order) {
                                    if ($column_name == $sort_column) {
                                        return ($sort_order == 'DESC') ? ' <i class="bi bi-caret-down-fill"></i>' : ' <i class="bi bi-caret-up-fill"></i>';
                                    } return '';
                                }
                                // Fungsi URL Sorting (sudah termasuk filter tahun)
                                function getSortUrl($column_name, $toggle_order, $search, $tahun) {
                                    return "index.php?sort=$column_name&order=$toggle_order&search=" . urlencode($search) . "&filter_tahun=" . urlencode($tahun);
                                }
                                ?>
                                <th><a href="<?php echo getSortUrl('nomor_surat', $toggle_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">No. Surat <?php echo getSortIcon('nomor_surat', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('nama_konseptor', $toggle_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Nama Konseptor <?php echo getSortIcon('nama_konseptor', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('kode_surat', $toggle_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Kode Surat <?php echo getSortIcon('kode_surat', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('perihal_surat', $toggle_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Perihal Surat <?php echo getSortIcon('perihal_surat', $sort_column, $sort_order); ?></a></th>
                                <th><a href="<?php echo getSortUrl('tanggal_surat', $toggle_order, $search_term, $filter_tahun); ?>" class="text-white text-decoration-none">Tgl. Surat <?php echo getSortIcon('tanggal_surat', $sort_column, $sort_order); ?></a></th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                       <tbody>
                            <?php
                            $stmt = $koneksi->prepare($sql);
                            if (!empty($params)) { $stmt->bind_param($types, ...$params); }
                            $stmt->execute();
                            $result = $stmt->get_result();

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
                                echo "<tr><td colspan='6' class='text-center'>Data tidak ditemukan.</td></tr>";
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
                        // Base URL pagination (sudah termasuk filter tahun)
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
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
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