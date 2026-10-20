<?php
session_start();
// Proteksi Halaman Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

// Menangkap keyword pencarian jika ada
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 1. AMBIL DATA PETUGAS (Dinamis Berdasarkan Gabungan Relasi Tabel User Baru)
try {
    if (!empty($search)) {
        $sql = "SELECT p.*, u.email 
                FROM petugas p 
                JOIN user u ON p.id_user = u.id_user
                WHERE p.nama_petugas LIKE :search OR 
                      u.email LIKE :search OR 
                      p.no_hp LIKE :search OR 
                      p.cabang_lokasi LIKE :search 
                ORDER BY p.id_petugas DESC";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([':search' => "%$search%"]);
    } else {
        $sql = "SELECT p.*, u.email FROM petugas p JOIN user u ON p.id_user = u.id_user ORDER BY p.id_petugas DESC";
        $stmt = $koneksi->query($sql);
    }
    $list_petugas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Data mockup presisi sesuai record dummy records baru
    $all_mockup = [
        ['id_petugas' => 'PTG001', 'nama_petugas' => 'Aditya Fauzan', 'email' => 'fauzan1@email.com', 'no_hp' => '0812435345', 'cabang_lokasi' => 'Bank Sampah Jakarta Timur', 'status' => 'aktif'],
        ['id_petugas' => 'PTG002', 'nama_petugas' => 'Sarnita', 'email' => 'sarnita@email.com', 'no_hp' => '089999999', 'cabang_lokasi' => 'Bank Sampah Jakarta Pusat', 'status' => 'aktif'],
    ];

    if (!empty($search)) {
        $list_petugas = array_filter($all_mockup, function($item) use ($search) {
            return (strpos(strtolower($item['nama_petugas']), strtolower($search)) !== false) ||
                   (strpos(strtolower($item['email']), strtolower($search)) !== false) ||
                   (strpos(strtolower($item['no_hp']), strtolower($search)) !== false) ||
                   (strpos(strtolower($item['cabang_lokasi']), strtolower($search)) !== false);
        });
    } else {
        $list_petugas = $all_mockup;
    }
}

$total_tampil = count($list_petugas);
?>

<link rel="stylesheet" href="assets/css/responsive.css">

<style>
    /* Animasi Glow Hover khusus baris petugas */
    /* ==========================================================================
   1. REGULASI VARIABEL & RESET DASAR GLOBAL
   ========================================================================== */
:root {
    --bg-main: #09090b;
    --bg-card: #0e0e0e;
    --bg-darker: #050505;
    --border-color: #1f1f23;
    --text-primary: #f4f4f5;
    --text-secondary: #a1a1aa;
    --text-muted: #52525b;
    --lime-primary: #8ae61a;
    --lime-hover: #7cd015;
    --lime-glow: rgba(138, 230, 26, 0.25);
    --emerald-alert: #34d399;
    --rose-alert: #f43f5e;
    --amber-alert: #ffc107;
}



header, .admin-header {
    position: fixed !important;
    top: 20;
    left: 0;
    width: 100% !important;
    z-index: 999;
    box-sizing: border-box;
    background-color: #050505;
    border-bottom: 1px solid var(--border-color);
    height: 70px; 
}


@media (max-width: 991px) {
    header, .admin-header {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
    box-sizing: border-box;

    padding: 110px 20px 40px 20px; 
}


@media (max-width: 767px) {
    .dashboard-container {
     
        padding-top: 100px; 
    }
}

.page-header {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 16px;
    margin-bottom: 8px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: #fff;
    margin: 0;
}

.page-header p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 4px 0 0 0;
}

.form-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
}


.alert {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 12px;
    line-height: 1.5;
    box-sizing: border-box;
}

.alert-sukses {
    background-color: rgba(52, 211, 153, 0.05);
    border: 1px solid rgba(52, 211, 153, 0.2);
    color: var(--emerald-alert);
}

.alert-warning {
    background-color: rgba(244, 63, 94, 0.05);
    border: 1px solid rgba(244, 63, 94, 0.2);
    color: var(--rose-alert);
}


.table-wrapper {
    width: 100%;
    overflow-x: auto;
    background-color: rgba(255, 255, 255, 0.01);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-sizing: border-box;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
    min-width: 600px;
}

.custom-table th {
    padding: 16px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.05em;
    background-color: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid var(--border-color);
}

.custom-table td {
    padding: 16px;
    border-bottom: 1px solid rgba(31, 31, 35, 0.6);
    color: #d4d4d8;
    vertical-align: middle;
}

.custom-table tr:last-child td {
    border-bottom: none;
}

.custom-table tbody tr {
    transition: all 0.25s ease-in-out;
}

.custom-table tbody tr:hover td {
    background-color: rgba(138, 230, 26, 0.01) !important;
    border-top-color: var(--lime-primary);
    border-bottom-color: var(--lime-primary);
    box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.05);
}


.btn-action-primary {
    background-color: var(--lime-primary);
    color: var(--bg-main);
    font-weight: 700;
    padding: 12px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 13px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(138, 230, 26, 0.15);
    transition: all 0.2s ease-in-out;
}

.btn-action-primary:hover {
    background-color: var(--lime-hover);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px var(--lime-glow);
}


.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(4px);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}

.modal-content-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    width: 100%;
    max-width: 440px;
    border-radius: 20px;
    padding: 24px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    transition: border-color 0.3s, box-shadow 0.3s;
}

.modal-content-box:hover {
    border-color: var(--lime-primary);
    box-shadow: 0 0 20px var(--lime-glow);
}
    .custom-table tbody tr {
        transition: all 0.2s ease-in-out;
    }
    .custom-table tbody tr:hover td {
        background-color: rgba(138, 230, 26, 0.02) !important;
        border-top-color: #8ae61a;
        border-bottom-color: #8ae61a;
        box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.1);
    }
    .badge-online {
        color: #34d399;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .online-dot {
        width: 6px;
        height: 6px;
        background-color: #34d399;
        border-radius: 50%;
    }
</style>

<div class="dashboard-container" >
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Kelola Petugas</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Lihat dan kelola penempatan operasional semua petugas cabang</p>
        </div>
        <a href="../register.php?role=petugas" class="btn-action-primary" style="background-color: #8ae61a; color: #09090b; font-weight: 700; padding: 12px 20px; border-radius: 12px; text-decoration: none; font-size: 13px;">
            + Tambah Petugas
        </a>
    </div>

    <form action="petugas.php" method="GET" style="position: relative; width: 100%;">
        <span style="absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #52525b; pointer-events: none; position: absolute;">🔍</span>
        <input 
            type="text" 
            name="search" 
            value="<?= htmlspecialchars($search); ?>"
            placeholder="Cari petugas berdasarkan nama, email, no telepon, atau cabang..." 
            style="width: 100%; padding: 14px 16px 14px 46px; background: #0e0e0e; border: 1px solid #1f1f23; border-radius: 12px; color: #fff; font-size: 13px; outline: none; box-sizing: border-box;"
        >
    </form>

    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Nama / ID Petugas</th>
                    <th>Kontak Lapangan</th>
                    <th>Cabang Penempatan</th>
                    <th>Status Kerja</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_tampil === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #52525b; font-style: italic;">Data petugas tidak ditemukan atau hasil pencarian kosong.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list_petugas as $row): ?>
                        <tr>
                            <td style="font-weight: 700; color: #fff;">
                                <?= htmlspecialchars($row['nama_petugas']); ?>
                                <div style="font-size: 10px; color: #52525b; font-weight: 400; font-family: monospace; margin-top: 2px;"><?= htmlspecialchars($row['id_petugas']); ?></div>
                            </td>
                            <td style="color: #a1a1aa; font-weight: 500;">
                                <div><?= htmlspecialchars($row['email']); ?></div>
                                <div style="font-size: 11px; color: #52525b; margin-top: 2px;"><?= htmlspecialchars($row['no_hp']); ?></div>
                            </td>
                            <td style="color: #a1a1aa;"><?= htmlspecialchars($row['cabang_lokasi']); ?></td>
                            <td>
                                <div class="badge-online">
                                    <span class="online-dot"></span>
                                    <span>Aktif / Operasional</span>
                                </div>
                            </td>
                            <td style="text-align: center; space-x: 3; white-space: nowrap;">
                                <a href="akun.php?id=<?= $row['id_petugas']; ?>" style="text-decoration:none; margin-right:10px;" title="Edit Petugas">📝</a>
                                <a href="akun.php?hapus=<?= $row['id_petugas']; ?>" onclick="return confirm('Hapus hak akses petugas ini?')" style="text-decoration:none;" title="Hapus Petugas">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>