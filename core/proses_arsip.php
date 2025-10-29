<?php
// 1. Keamanan
include 'auth_check.php';
// 2. Koneksi
include 'koneksi.php';

// 3. Tentukan mode (Tahun atau Tanggal)
$mode = '';
$sql_where_condition = '';
$params = [];
$types = '';

if (isset($_POST['tahun_arsip']) && !empty($_POST['tahun_arsip']) && is_numeric($_POST['tahun_arsip'])) {
    // --- MODE TAHUN ---
    $mode = 'tahun';
    $tahun = (int)$_POST['tahun_arsip'];
    
    $sql_where_condition = "WHERE tahun = ?";
    $params = [$tahun];
    $types = 'i';
    
} elseif (isset($_POST['tanggal_mulai']) && !empty($_POST['tanggal_mulai']) && 
          isset($_POST['tanggal_selesai']) && !empty($_POST['tanggal_selesai'])) {
    // --- MODE TANGGAL ---
    $mode = 'tanggal';
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    // Validasi tambahan: Cek jika tanggal mulai > tanggal selesai
    if (strtotime($tanggal_mulai) > strtotime($tanggal_selesai)) {
         header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=Tanggal mulai tidak boleh lebih besar dari tanggal selesai");
        exit;
    }
    
    $sql_where_condition = "WHERE tanggal_surat BETWEEN ? AND ?";
    $params = [$tanggal_mulai, $tanggal_selesai];
    $types = 'ss';

} else {
    // --- MODE TIDAK VALID ---
    header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=Input tidak valid. Pilih tahun atau rentang tanggal.");
    exit;
}

// 4. Gunakan Transaksi Database
$koneksi->begin_transaction();

try {
    // 5. Salin data dari tb_surat ke tb_surat_arsip
    $sql_insert = "INSERT INTO tb_surat_arsip SELECT * FROM tb_surat " . $sql_where_condition;
    $stmt_insert = $koneksi->prepare($sql_insert);
    $stmt_insert->bind_param($types, ...$params);
    $stmt_insert->execute();
    $stmt_insert->close();

    // 6. Hapus data dari tb_surat (setelah berhasil disalin)
    $sql_delete = "DELETE FROM tb_surat " . $sql_where_condition;
    $stmt_delete = $koneksi->prepare($sql_delete);
    $stmt_delete->bind_param($types, ...$params);
    $stmt_delete->execute();
    
    // Periksa apakah ada data yang benar-benar dihapus
    $rows_affected = $stmt_delete->affected_rows;
    $stmt_delete->close();

    // 7. Jika semua berhasil, simpan perubahan
    $koneksi->commit();

    if ($rows_affected > 0) {
        // Berhasil memindahkan data
        header("Location: ../admin/setting_nomor.php?status=arsip_sukses");
    } else {
        // Tidak ada data untuk kriteria tsb, tapi tidak error
        header("Location: ../admin/setting_nomor.php?status=arsip_kosong");
    }
    
} catch (mysqli_sql_exception $exception) {
    // 8. Jika ada error, batalkan semua perubahan
    $koneksi->rollback();
    
    // Kirim pesan error
    header("Location: ../admin/setting_nomor.php?status=arsip_gagal&error=" . urlencode($exception->getMessage()));
    
} finally {
    $koneksi->close();
}

exit;
?>