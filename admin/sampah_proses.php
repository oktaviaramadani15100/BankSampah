<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi Otorisasi Keamanan Sesi Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id_kunjungan']) && isset($_GET['aksi'])) {
    $id_kunjungan = trim($_GET['id_kunjungan']);
    $aksi         = trim($_GET['aksi']);

    try {
        if ($aksi === 'konfirmasi') {
            $koneksi->beginTransaction();

            // Perbaikan Query: Menyasar tabel transaksi_setoran fisik, bukan View bayangan lama
            $sqlGet = "SELECT ds.subtotal, ts.id_nasabah 
                       FROM detail_setoran ds
                       JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
                       WHERE ds.id_setoran = :id";
            $stmtGet = $koneksi->prepare($sqlGet);
            $stmtGet->execute([':id' => $id_kunjungan]);
            $details = $stmtGet->fetchAll(PDO::FETCH_ASSOC);

            if ($details) {
                $total_pendapatan = 0;
                $id_nasabah = $details[0]['id_nasabah'];

                foreach ($details as $item) {
                    $total_pendapatan += (int) ($item['subtotal'] ?? 0);
                }

                // Update status transaksi setoran menjadi selesai
                $stmtUpdateKjn = $koneksi->prepare("UPDATE transaksi_setoran SET status = 'selesai' WHERE id_setoran = :id");
                $stmtUpdateKjn->execute([':id' => $id_kunjungan]);

                // Sinkronisasi akumulasi neraca saldo digital e-wallet nasabah terkait
                $stmtSaldo = $koneksi->prepare("INSERT INTO saldo (id_nasabah, saldo_tersedia, total_pemasukan, total_pengeluaran, point_reward) 
                                                VALUES (:id_nasabah, :masuk, :masuk, 0, 0) 
                                                ON DUPLICATE KEY UPDATE 
                                                    saldo_tersedia = saldo_tersedia + :masuk_update, 
                                                    total_pemasukan = total_pemasukan + :masuk_update2");
                $stmtSaldo->execute([
                    ':id_nasabah' => $id_nasabah,
                    ':masuk' => $total_pendapatan,
                    ':masuk_update' => $total_pendapatan,
                    ':masuk_update2' => $total_pendapatan
                ]);
            }

            $koneksi->commit();
            header("Location: sampah.php?status=sukses_konfirmasi");
            exit();

        } elseif ($aksi === 'batal') {
            // Membatalkan rute transaksi setoran secara aman di tabel fisik
            $stmtBatal = $koneksi->prepare("UPDATE transaksi_setoran SET status = 'dibatalkan' WHERE id_setoran = :id");
            $stmtBatal->execute([':id' => $id_kunjungan]);
            header("Location: sampah.php?status=sukses_batal");
            exit();
        }
    } catch (Exception $e) {
        $koneksi->rollBack();
        die("Gagal memproses transaksi nasabah: " . $e->getMessage());
    }
} else {
    header("Location: sampah.php");
    exit();
}