<?php
// 1. SATPAM: Pastikan hanya admin yang login bisa akses
include '../core/auth_check.php';
// 2. KONEKSI: Hubungkan ke database
include '../core/koneksi.php';

// 3. VALIDASI ID: Cek apakah ID ada di URL dan merupakan angka
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id = $_GET['id']; // Ambil ID dari URL

    // 4. GUNAKAN TRANSAKSI (PENTING!)
    $koneksi->begin_transaction();

    try {
        // 5. Salin data dari tb_surat ke tb_surat_arsip
        $sql_insert = "INSERT INTO tb_surat_arsip SELECT * FROM tb_surat WHERE id = ?";
        $stmt_insert = $koneksi->prepare($sql_insert);
        $stmt_insert->bind_param("i", $id);
        $stmt_insert->execute();
        $stmt_insert->close();

        // 6. Hapus data dari tb_surat (setelah berhasil disalin)
        $sql_delete = "DELETE FROM tb_surat WHERE id = ?";
        $stmt_delete = $koneksi->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // 7. Jika semua berhasil, simpan perubahan
        $koneksi->commit();
        header("Location: ../admin/halaman_surat.php?status=diarsipkan");

    } catch (mysqli_sql_exception $exception) {
        // 8. Jika ada error, batalkan semua perubahan
        $koneksi->rollback();
        die("Error saat memindahkan data: " . $exception->getMessage());
    }

    // 9. TUTUP
    $koneksi->close();

} else {
    // Jika ID tidak valid atau tidak ada, tendang ke halaman utama
    header("Location: ../admin/halaman_surat.php");
    exit();
}
?>