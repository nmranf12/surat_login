<?php

include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'];
    $kode_surat     = $_POST['kode_surat'];
    $perihal_surat  = $_POST['perihal_surat'];
    $tanggal_surat  = $_POST['tanggal_surat'];
    $isi_ringkasan  = $_POST['isi_ringkasan'];
    $tujuan_surat   = $_POST['tujuan_surat'];
    $nama_konseptor = $_POST['nama_konseptor'];
    $unit_bidang    = $_POST['unit_bidang'];

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

    if ($stmt->execute()) {
     
        header("Location: ../admin/halaman_surat.php?status=update_sukses");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $koneksi->close();

} else {
  
    header("Location: ../index.php");
}
?>