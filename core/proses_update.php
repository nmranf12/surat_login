<?php
// --- 1. MENYERTAKAN KONEKSI DATABASE ---
// ▼▼▼ PERBAIKAN 1: Path 'include' ▼▼▼
include 'koneksi.php';

// --- 2. VALIDASI METODE REQUEST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 3. PENGAMBILAN DATA POST (MENTAH/RAW) ---
    $id = $_POST['id'];
    $kode_surat     = $_POST['kode_surat'];
    $perihal_surat  = $_POST['perihal_surat'];
    $tanggal_surat  = $_POST['tanggal_surat'];
    $isi_ringkasan  = $_POST['isi_ringkasan'];
    $tujuan_surat   = $_POST['tujuan_surat'];
    $nama_konseptor = $_POST['nama_konseptor'];
    $unit_bidang    = $_POST['unit_bidang'];

    // 4. MENYIAPKAN PREPARED STATEMENT (Keamanan SQL Injection)
    $stmt = $koneksi->prepare(
        "UPDATE tb_surat SET 
            kode_surat = ?, 
            perihal_surat = ?, 
            tanggal_surat = ?, 
            isi_ringkasan = ?, 
            tujuan_surat = ?, 
            nama_konseptor = ?, 
            unit_bidang = ? 
         WHERE id = ?"
    );

    // 5. BIND PARAMETER KE STATEMENT 
    $stmt->bind_param(
        "sssssssi", 
        $kode_surat,
        $perihal_surat,
        $tanggal_surat,
        $isi_ringkasan,
        $tujuan_surat,
        $nama_konseptor,
        $unit_bidang,
        $id 
    );

    // --- 6. EKSEKUSI QUERY DAN PENGALIHAN (REDIRECT) ---
    if ($stmt->execute()) {
        // ▼▼▼ PERBAIKAN 2: Path 'header' (redirect) ▼▼▼
        header("Location: ../admin/halaman_surat.php?status=update_sukses");
    } else {
        echo "Error: " . $stmt->error;
    }

    // --- 7. TUTUP STATEMENT DAN KONEKSI ---
    $stmt->close();
    $koneksi->close();

} else {
    // ▼▼▼ PERBAIKAN 3: Path 'header' (redirect) ▼▼▼
    header("Location: ../index.php");
}
?>