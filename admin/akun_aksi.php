<?php
session_start();
// Hubungkan konfigurasi database
require_once '../config/koneksi.php'; 

// Proteksi Sesi Keamanan Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['aksi']) && isset($_GET['id']) && isset($_GET['type'])) {
    $aksi = trim($_GET['aksi']);
    $id   = trim($_GET['id']);
    $type = trim($_GET['type']); // 'nasabah' atau 'petugas'

    try {
        if ($aksi === 'approve') {
            $koneksi->beginTransaction();

            if ($type === 'nasabah') {
                // 1. Update status tabel detail profil nasabah menjadi aktif
                $stmt = $koneksi->prepare("UPDATE nasabah SET status_akun = 'aktif' WHERE id_nasabah = :id");
                $stmt->execute([':id' => $id]);

                // 2. Sekaligus membuat baris record saldo awal bernilai 0 di dompet saldo e-wallet nasabah terkait
                $stmt_saldo = $koneksi->prepare("INSERT INTO saldo (id_nasabah, saldo_tersedia, total_pemasukan, total_pengeluaran, point_reward) VALUES (:id, 0, 0, 0, 0) ON DUPLICATE KEY UPDATE id_nasabah=id_nasabah");
                $stmt_saldo->execute([':id' => $id]);
            } else {
                // Skenario pembukaan status login tabel user pusat khusus untuk role petugas lapangan
                $stmt_user_id = $koneksi->prepare("SELECT id_user FROM petugas WHERE id_petugas = :id LIMIT 1");
                $stmt_user_id->execute([':id' => $id]);
                $userId = $stmt_user_id->fetchColumn();

                if ($userId) {
                    $stmt = $koneksi->prepare("UPDATE user SET status = 'aktif' WHERE id_user = :uid");
                    $stmt->execute([':uid' => $userId]);
                }
            }

            $koneksi->commit();
            header("Location: akun.php?status=sukses_approve");
            exit();

        } elseif ($aksi === 'hapus') {
            $koneksi->beginTransaction();

            if ($type === 'nasabah') {
                $stmt = $koneksi->prepare("SELECT id_user FROM nasabah WHERE id_nasabah = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $userId = $stmt->fetchColumn();

                // Hapus berantai dari tabel anak/detail profil baru ke tabel induk user pusat
                $koneksi->prepare("DELETE FROM nasabah WHERE id_nasabah = :id")->execute([':id' => $id]);
                if ($userId) {
                    $koneksi->prepare("DELETE FROM user WHERE id_user = :id")->execute([':id' => $userId]);
                }
            } else {
                $stmt = $koneksi->prepare("SELECT id_user FROM petugas WHERE id_petugas = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $userId = $stmt->fetchColumn();

                $koneksi->prepare("DELETE FROM petugas WHERE id_petugas = :id")->execute([':id' => $id]);
                if ($userId) {
                    $koneksi->prepare("DELETE FROM user WHERE id_user = :id")->execute([':id' => $userId]);
                }
            }

            $koneksi->commit();
            header("Location: akun.php?status=sukses_hapus");
            exit();
        }
    } catch (Exception $e) {
        $koneksi->rollBack();
        die("Gagal memproses aksi otorisasi akun: " . $e->getMessage());
    }
} else {
    header("Location: akun.php");
    exit();
}
?>