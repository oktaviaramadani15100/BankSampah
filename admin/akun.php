<?php
session_start();
// Proteksi Akses Halaman Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

// 1. AMBIL DATA AKUN YANG PENDING (Antrean Approval)
$antrean = [];
try {
    // Ambil data nasabah yang pending
    $stmtnasabah = $koneksi->query("SELECT n.id_nasabah AS id, n.nama_lengkap, u.email, 'nasabah' AS role FROM nasabah n JOIN user u ON n.id_user = u.id_user WHERE n.status_akun = 'pending'");
    $nasabahPending = $stmtnasabah->fetchAll(PDO::FETCH_ASSOC);

    // Ambil data petugas yang pending (jika menggunakan fitur approval petugas)
    // Ditambahkan LEFT JOIN ke user agar email ditarik dinamis dari tabel pusat
    $stmtPetugas = $koneksi->query("SELECT p.id_petugas AS id, p.nama_petugas AS nama_lengkap, u.email, 'petugas' AS role FROM petugas p JOIN user u ON p.id_user = u.id_user WHERE u.status = 'nonaktif'");
    $petugasPending = $stmtPetugas->fetchAll(PDO::FETCH_ASSOC);

    // Gabungkan antrean nasabah dan petugas
    $antrean = array_merge($nasabahPending, $petugasPending);
} catch (Exception $e) {
    // Fallback data mockup jika database belum siap / terputus
    $antrean = [
        ['id' => 'NSB003', 'nama_lengkap' => 'Dias Anugrah', 'email' => 'dias@email.com', 'role' => 'nasabah'],
        ['id' => 'NSB004', 'nama_lengkap' => 'Okta', 'email' => 'okta@email.com', 'role' => 'nasabah']
    ];
}

// 2. AMBIL DATA AKUN YANG SUDAH AKTIF (Kelola Akun)
$akun_aktif = [];
try {
    $stmtnasabahAktif = $koneksi->query("SELECT n.id_nasabah AS id, n.nama_lengkap, u.email, 'nasabah' AS role FROM nasabah n JOIN user u ON n.id_user = u.id_user WHERE n.status_akun = 'aktif'");
    $nasabahAktif = $stmtnasabahAktif->fetchAll(PDO::FETCH_ASSOC);

    $stmtPetugasAktif = $koneksi->query("SELECT p.id_petugas AS id, p.nama_petugas AS nama_lengkap, u.email, 'petugas' AS role FROM petugas p JOIN user u ON p.id_user = u.id_user WHERE u.status = 'aktif'");
    $petugasAktif = $stmtPetugasAktif->fetchAll(PDO::FETCH_ASSOC);

    $akun_aktif = array_merge($nasabahAktif, $petugasAktif);
} catch (Exception $e) {
    $akun_aktif = [
        ['id' => 'ADM001', 'nama_lengkap' => 'Super Admin', 'email' => 'admin@gmail.com', 'role' => 'admin'],
        ['id' => 'NSB001', 'nama_lengkap' => 'Aditya Muhammad Fauzan', 'email' => 'adit@email.com', 'role' => 'nasabah'],
        ['id' => 'NSB002', 'nama_lengkap' => 'Hakim', 'email' => 'hakim@email.com', 'role' => 'nasabah'],
        ['id' => 'PTG001', 'nama_lengkap' => 'Aditya Fauzan (Petugas)', 'email' => 'fauzan1@email.com', 'role' => 'petugas']
    ];
}
?>

<link rel="stylesheet" href="assets/css/responsive.css">

<style>
    /* Efek Glow Berpendar Pada Setiap Baris Tabel Ketika Disentuh Kursor (Hover Row) */
    .custom-table tbody tr {
        transition: all 0.25s ease-in-out;
    }
    .custom-table tbody tr:hover td {
        background-color: rgba(138, 230, 26, 0.02) !important;
        border-top-color: #8ae61a;
        border-bottom-color: #8ae61a;
        box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.1);
    }
    .btn-action-approve {
        background-color: #8ae61a;
        color: #09090b;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 11px;
        transition: all 0.2s;
    }
    .btn-action-approve:hover {
        background-color: #7cd015;
        box-shadow: 0 0 10px rgba(138, 230, 26, 0.4);
    }
    .btn-action-reject {
        background-color: #050505;
        border: 1px solid #1f1f23;
        color: #52525b;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 11px;
        transition: all 0.2s;
    }
    .btn-action-reject:hover {
        color: #f43f5e;
        border-color: #7f1d1d;
    }
</style>

<div class="dashboard-container" style="max-width: 1200px; margin: 0 auto; padding: 30px 20px; display: flex; flex-direction: column; gap: 24px;">
    
    <div class="page-header">
        <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Manajemen Otorisasi Akun</h1>
        <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Berikan persetujuan registrasi member baru dan kelola hak akses pengguna</p>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'sukses_approve'): ?>
            <div class="alert alert-sukses">
                ✓ Akun berhasil diaktivasi! User yang bersangkutan kini bisa mengakses dashboard mereka.
            </div>
        <?php elseif ($_GET['status'] === 'sukses_hapus'): ?>
            <div class="alert alert-warning" style="background-color: rgba(255,255,255,0.02); border-color:#222; color:#a1a1aa;">
                ➔ Akun berhasil dihapus permanen dari sistem Bank Sampah.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-bottom: 15px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin:0;">Antrean Persetujuan</h2>
            <span style="background-color: rgba(251, 191, 36, 0.08); border: 1px solid rgba(251, 191, 36, 0.2); color: #ffc107; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px;">
                <?= count($antrean); ?> Menunggu
            </span>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Pengguna</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Tipe Akses (Role)</th>
                        <th style="text-align: center;">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($antrean) === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #52525b; font-style: italic;">Tidak ada registrasi baru dalam antrean.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($antrean as $row): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: bold; color: #52525b;"><?= $row['id']; ?></td>
                                <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td style="color: #a1a1aa;"><?= htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span style="font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; border: 1px solid <?= $row['role'] === 'petugas' ? 'rgba(59,130,246,0.2); background:rgba(59,130,246,0.05); color:#3b82f6;' : 'rgba(255,255,255,0.05); background:#050505; color:#a1a1aa;' ?>">
                                        <?= $row['role']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="akun_aksi.php?aksi=approve&id=<?= $row['id']; ?>&type=<?= $row['role']; ?>" class="btn-action-approve">
                                        ✓ Setujui
                                    </a>
                                    <a href="akun_aksi.php?aksi=hapus&id=<?= $row['id']; ?>&type=<?= $row['role']; ?>" onclick="return confirm('Apakah Anda yakin ingin menolak & menghapus registrasi ini?')" class="btn-action-reject" style="margin-left: 6px;">
                                        Tolak
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 12px 0;">Data Semua Akun Aktif</h2>
        
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Pengguna</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status Otoritas</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($akun_aktif as $row): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: bold; color: #3f3f46;"><?= $row['id']; ?></td>
                            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="color: #a1a1aa;"><?= htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span style="font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; border: 1px solid <?= $row['role'] === 'admin' ? 'rgba(244,63,94,0.2); background:rgba(244,63,94,0.05); color:#f43f5e;' : ($row['role'] === 'petugas' ? 'rgba(59,130,246,0.2); background:rgba(59,130,246,0.05); color:#3b82f6;' : 'rgba(255,255,255,0.05); background:#050505; color:#a1a1aa;') ?>">
                                    <?= $row['role']; ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: #34d399; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                    <span style="width: 5px; height: 5px; background-color: #34d399; border-radius: 50%;"></span>
                                    Aktif Terverifikasi
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($row['role'] === 'admin'): ?>
                                    <span style="color: #52525b; font-size: 11px; font-style: italic;">Utama (Sistem)</span>
                                <?php else: ?>
                                    <a href="akun_aksi.php?aksi=hapus&id=<?= $row['id']; ?>&type=<?= $row['role']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus akun aktif ini secara permanen?')" class="btn-action-reject" style="padding: 4px 10px;">
                                        Hapus Akun
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>