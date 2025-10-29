<?php
// ▼▼▼ PERBAIKAN 1: Path 'include' ▼▼▼
include 'koneksi.php';

// Validasi data POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil semua data dari form
    $kode_surat = $_POST['kode_surat'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $perihal_surat = $_POST['perihal_surat'];
    $isi_ringkasan = $_POST['isi_ringkasan'];
    $tujuan_surat = $_POST['tujuan_surat'];
    $nama_konseptor = $_POST['nama_konseptor'];
    $unit_bidang = $_POST['unit_bidang'];

    $nomor_surat_baru = 0;

    // 2. Mulai Transaksi Database (KUNCI UTAMA)
    $koneksi->begin_transaction();

    try {
        // Ambil tahun dari tanggal surat yang diinput
        $tahun_surat = date('Y', strtotime($tanggal_surat)); 

        // 3. KUNCI baris counter dan ambil nomornya
        $stmt_get = $koneksi->prepare("SELECT next_number, tahun FROM tb_counter WHERE id = 1 FOR UPDATE");
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        $counter = $result->fetch_assoc();
        $stmt_get->close();

        // 4. Logika Reset Nomor Jika Beda Tahun
        if ($counter['tahun'] != $tahun_surat) {
            $nomor_surat_baru = 1;
            $nomor_selanjutnya = 2;
            
            $stmt_update = $koneksi->prepare("UPDATE tb_counter SET next_number = ?, tahun = ? WHERE id = 1");
            $stmt_update->bind_param("is", $nomor_selanjutnya, $tahun_surat);
            $stmt_update->execute();
            $stmt_update->close();

        } else {
            $nomor_surat_baru = $counter['next_number'];
            $nomor_selanjutnya = $nomor_surat_baru + 1;

            $stmt_update = $koneksi->prepare("UPDATE tb_counter SET next_number = ? WHERE id = 1");
            $stmt_update->bind_param("i", $nomor_selanjutnya);
            $stmt_update->execute();
            $stmt_update->close();
        }

        // ▼▼▼ PERBAIKAN: PINDAHKAN BLOK INSERT KE SINI ▼▼▼
        // 5. Simpan data surat ke tb_surat DENGAN NOMOR BARU
        if ($nomor_surat_baru > 0) {
            $stmt_insert = $koneksi->prepare(
                "INSERT INTO tb_surat 
                (nomor_surat, tahun, kode_surat, perihal_surat, isi_ringkasan, tanggal_surat, tujuan_surat, nama_konseptor, unit_bidang, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            
            $stmt_insert->bind_param(
                "iisssssss", 
                $nomor_surat_baru,
                $tahun_surat, 
                $kode_surat,
                $perihal_surat,
                $isi_ringkasan,
                $tanggal_surat,
                $tujuan_surat,
                $nama_konseptor,
                $unit_bidang
            );

            // Jika eksekusi insert gagal, lempar error agar ditangkap 'catch'
            if (!$stmt_insert->execute()) {
                throw new Exception("Error saat menyimpan data surat: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        } else {
            throw new Exception("Nomor surat tidak valid.");
        }
        // ▲▲▲ SELESAI PEMINDAHAN BLOK INSERT ▲▲▲

        // 6. Selesaikan Transaksi (Lepas Kunci)
        // HANYA JIKA SEMUA BERHASIL, commit akan dijalankan
        $koneksi->commit();

    } catch (Exception $e) {
        // Jika ada error (di update counter ATAU insert surat), batalkan semua
        $koneksi->rollback();
        die("Error Transaksi Gagal: " . $e->getMessage());
    }

    // 7. Arahkan ke halaman sukses
    // Baris ini hanya akan tercapai jika 'commit' berhasil
    header("Location: ../form.php?status=sukses&nomor=" . $nomor_surat_baru);
    exit();

} else {
    header("Location: ../index.php");
    exit();
}
?>