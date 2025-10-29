<?php
// 1. SATPAM: Pastikan hanya admin yang login bisa akses
include '../core/auth_check.php';
// 2. KONEKSI: Hubungkan ke database
include '../core/koneksi.php';

// 3. VALIDASI ID: Cek apakah ID ada di URL dan merupakan angka
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id = $_GET['id']; // Ambil ID dari URL

    // 4. PERSIAPAN PREPARED STATEMENT (Mencegah SQL Injection)
    // PERHATIKAN: Menghapus dari tb_surat_arsip
    $stmt = $koneksi->prepare("DELETE FROM tb_surat_arsip WHERE id = ?");
    
    // 5. BIND PARAMETER: 'i' untuk integer
    $stmt->bind_param("i", $id);

    // 6. EKSEKUSI
    if ($stmt->execute()) {
        // Jika berhasil, kembali ke HALAMAN ARSIP dengan pesan sukses
        header("Location: ../admin/halaman_arsip.php?status=hapus_permanen_sukses");
    } else {
        // Jika gagal
        die("Error saat menghapus data arsip: " . $stmt->error);
    }

    // 7. TUTUP
    $stmt->close();
    $koneksi->close();

} else {
    // Jika ID tidak valid atau tidak ada, tendang ke halaman arsip
    header("Location: ../admin/halaman_arsip.php");
    exit();
}
?>