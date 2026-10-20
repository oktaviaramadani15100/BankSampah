<?php
session_start();

// 1. PROTEKSI AKSES
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'petugas') {
    header("Location: ../login.php?role=petugas");
    exit();
}

// 2. KONEKSI DATABASE
require_once '../config/koneksi.php';

// 3. VALIDASI INPUT FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kunjungan']) && isset($_POST['items'])) {
    $id_kunjungan = trim($_POST['id_kunjungan']);
    $items = $_POST['items']; 

    try {
        $koneksi->beginTransaction();

        // 4. LOOP UPDATE DATA TIMBANGAN NYATA KE DATABASE (Berdasarkan ID VARCHAR)
        foreach ($items as $item) {
            $id_detail  = $item['id_detail'];
            $id_sampah  = $item['id_sampah'];
            $berat_riil = floatval($item['berat']);

            // Mengambil patokan harga per kg komoditas master sampah terbaru
            $stmt_harga = $koneksi->prepare("SELECT harga_perkg FROM sampah WHERE id_sampah = :id_sampah LIMIT 1");
            $stmt_harga->execute([':id_sampah' => $id_sampah]);
            $harga_baru = (int) $stmt_harga->fetchColumn();
            $subtotal = (int) round($berat_riil * $harga_baru);

            $sql_update = "UPDATE detail_setoran 
                           SET id_sampah = :id_sampah, berat_kg = :berat, harga_perkg = :harga, subtotal = :subtotal 
                           WHERE id_detail = :id_detail AND id_setoran = :id_kunjungan";
            
            $stmt = $koneksi->prepare($sql_update);
            $stmt->execute([
                ':id_sampah'     => $id_sampah,
                ':berat'         => $berat_riil,
                ':harga'         => $harga_baru,
                ':subtotal'      => $subtotal,
                ':id_detail'     => $id_detail,
                ':id_kunjungan'  => $id_kunjungan
            ]);
        }

        // Hitung akumulasi total berat dan total penerimaan real dari detail_setoran
        $stmt_total = $koneksi->prepare("SELECT SUM(berat_kg) AS total_berat, SUM(subtotal) AS total_penerimaan FROM detail_setoran WHERE id_setoran = :id_kunjungan");
        $stmt_total->execute([':id_kunjungan' => $id_kunjungan]);
        $totals = $stmt_total->fetch(PDO::FETCH_ASSOC);

        // Ubah status transaksi menjadi 'diproses' agar masuk antrean verifikasi di file verifikasi.php
        $stmt_status = $koneksi->prepare("UPDATE transaksi_setoran SET status = 'diproses', total_berat = :total_berat, total_penerimaan = :total_penerimaan WHERE id_setoran = :id");
        $stmt_status->execute([
            ':total_berat' => (float) ($totals['total_berat'] ?? 0),
            ':total_penerimaan' => (int) ($totals['total_penerimaan'] ?? 0),
            ':id' => $id_kunjungan
        ]);

        $koneksi->commit();

        header("Location: verifikasi.php?status=timbang_sukses");
        exit();

    } catch (Exception $e) {
        $koneksi->rollBack();
        die("Gagal menyimpan data penimbangan riil: " . $e->getMessage());
    }
} else {
    header("Location: penimbangan.php");
    exit();
}
?>