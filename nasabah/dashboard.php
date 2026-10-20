<?php
session_start();
// 1. Proteksi Akses Halaman Nasabah
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'warga') {
    header("Location: ../login.php");
    exit();
}

// Jika status akun diubah admin kembali menjadi pending di tengah jalan, lempar kembali ke gerbang verifikasi
if (isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'pending') {
    header("Location: ../approval.php");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

// 2. QUERY DINAMIS SINKRONISASI DATA NASABAH REAL FROM DB
$id_nasabah = $_SESSION['user_id']; // Berisi id_nasabah bertipe VARCHAR
$nama_user  = $_SESSION['user_name'];

try {
    // A. Hitung total berat setoran nyata dari tabel transaksi_setoran
    $stmt_kg = $koneksi->prepare("SELECT SUM(ds.berat_kg) as total_kg 
                                  FROM detail_setoran ds
                                  JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
                                  WHERE ts.id_nasabah = :id AND LOWER(ts.status) = 'selesai'");
    $stmt_kg->execute([':id' => $id_nasabah]);
    $res_kg = $stmt_kg->fetch(PDO::FETCH_ASSOC);
    $total_setoran = number_format(($res_kg['total_kg'] ?? 0), 2, ',', '.') . " Kg";

    // B. Ambil data keuangan riil dari tabel saldo
    $stmt_saldo = $koneksi->prepare("SELECT * FROM saldo WHERE id_nasabah = :id");
    $stmt_saldo->execute([':id' => $id_nasabah]);
    $data_db_saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

    $saldo_val       = $data_db_saldo['saldo_tersedia'] ?? 0;
    $pemasukan_val   = $data_db_saldo['total_pemasukan'] ?? 0;
    $poin_val        = $data_db_saldo['point_reward'] ?? 0;

    $saldo        = "Rp " . number_format($saldo_val, 0, ',', '.');
    $poin_reward  = number_format($poin_val, 0, ',', '.');

    // C. Klasifikasi Level Member Dinamis
    if ($pemasukan_val >= 1000000) {
        $level_member = "Gold";
    } elseif ($pemasukan_val >= 250000) {
        $level_member = "Silver";
    } else {
        $level_member = "Bronze";
    }

} catch (Exception $e) {
    $saldo         = "Rp 0";
    $total_setoran = "0,00 Kg";
    $poin_reward   = "0";
    $level_member  = "Bronze";
}

// 3. QUERY LOG TRANSAKSI GABUNGAN (SETORAN SAMPAH & PENCAIRAN DANA)
$riwayat = [];
try {
    $sql_riwayat = "(SELECT ts.created_at as tanggal, 
                            GROUP_CONCAT(s.nama_sampah SEPARATOR ', ') as keterangan, 
                            'Setoran' as tipe, 
                            CONCAT('+Rp ', FORMAT(SUM(ds.berat_kg * s.harga_perkg), 0, 'id_ID')) as jumlah
                     FROM transaksi_setoran ts
                     JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
                     JOIN sampah s ON ds.id_sampah = s.id_sampah
                     WHERE ts.id_nasabah = :id_w1 AND LOWER(ts.status) = 'selesai'
                     GROUP BY ts.id_setoran)
                    UNION ALL
                    (SELECT p.tanggal_pengajuan as tanggal,
                            CONCAT('Penarikan Tunai Via ', p.metode) as keterangan, 
                            'Pencairan' as tipe, 
                            CONCAT('-Rp ', FORMAT(p.jumlah_dana, 0, 'id_ID')) as jumlah
                     FROM pencairan p
                     WHERE p.id_nasabah = :id_w2 AND LOWER(p.status) = 'disetujui')
                    ORDER BY tanggal DESC LIMIT 5";
                    
    $stmt_riwayat = $koneksi->prepare($sql_riwayat);
    $stmt_riwayat->execute([':id_w1' => $id_nasabah, ':id_w2' => $id_nasabah]);
    $riwayat = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $riwayat = [];
}
?>

<link rel="stylesheet" href="../css/nasabah.css">

<style>
    /* Animasi Glow Kustom Ketika Elemen Disentuh Kursor (Hover Effect) */
    .metric-card, .action-card {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Efek pendaran neon hijau limau */
    .metric-card:hover, .action-card:hover {
        transform: translateY(-3px);
        border-color: #8ae61a !important;
        box-shadow: 0 0 20px rgba(138, 230, 26, 0.25);
    }

    .dashboard-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 24px 16px;
        display: flex;
        flex-direction: column;
        gap: 24px;
        box-sizing: border-box;
    }

    .welcome-section {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid #1f1f23;
        padding-bottom: 16px;
    }

    @media (min-width: 768px) {
        .welcome-section {
            flex-direction: row;
            align-items: center;
        }
    }

    .btn-action-primary {
        background-color: #8ae61a;
        color: #09090b;
        font-weight: 700;
        font-size: 13px;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        text-align: center;
        transition: background-color 0.2s;
    }

    .btn-action-primary:hover {
        background-color: #7cd015;
    }

    .alert-banner {
        background-color: rgba(6, 78, 59, 0.1);
        border: 1px solid #064e3b;
        padding: 16px;
        border-radius: 12px;
        display: flex;
        gap: 12px;
    }

    .grid-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .grid-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }

    .action-card {
        text-decoration: none;
        color: inherit;
        text-align: center;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        background-color: rgba(255, 255, 255, 0.01);
        border: 1px solid #1f1f23;
        border-radius: 16px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .custom-table th {
        padding: 16px;
        color: #52525b;
        font-weight: 600;
        background-color: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid #1f1f23;
    }

    .custom-table td {
        padding: 16px;
        border-bottom: 1px solid rgba(31, 31, 35, 0.6);
        color: #d4d4d8;
    }

    .custom-table tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="dashboard-container">
    
    <div class="welcome-section">
        <div>
            <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Selamat Datang, <?= htmlspecialchars($nama_user); ?>!</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Kelola sampah Anda dan raih penghasilan tambahan</p>
        </div>
        <a href="transaksi.php" class="btn-action-primary">
            🚀 Ayo transaksi sampahmu untuk meraih keuntungan
        </a>
    </div>

    <div class="alert-banner">
        <span style="color: #34d399;">ℹ️</span>
        <div>
            <p style="font-size: 12px; font-weight: 700; color: #34d399; margin: 0;">Bonus Setoran Minggu Ini!</p>
            <p style="font-size: 11px; color: #a1a1aa; margin: 2px 0 0 0;">Dapatkan bonus tambahan otomatis untuk meningkatkan tabungan e-wallet Anda.</p>
        </div>
    </div>

    <div class="grid-metrics">
        <div class="metric-card">
            <span style="font-size: 20px;">💼</span>
            <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 8px 0 0 0; font-weight: 600;">Saldo Saya</p>
            <p style="font-size: 22px; font-weight: 700; color: #fff; margin: 4px 0 0 0;"><?= $saldo; ?></p>
        </div>
        <div class="metric-card">
            <span style="font-size: 20px;">📈</span>
            <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 8px 0 0 0; font-weight: 600;">Total Setoran</p>
            <p style="font-size: 22px; font-weight: 700; color: #fff; margin: 4px 0 0 0;"><?= $total_setoran; ?></p>
        </div>
        <div class="metric-card">
            <span style="font-size: 20px;">🏅</span>
            <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 8px 0 0 0; font-weight: 600;">Poin Reward</p>
            <p style="font-size: 22px; font-weight: 700; color: #fff; margin: 4px 0 0 0;"><?= $poin_reward; ?></p>
        </div>
        <div class="metric-card">
            <span style="font-size: 20px;">🎯</span>
            <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 8px 0 0 0; font-weight: 600;">Level</p>
            <p style="font-size: 22px; font-weight: 700; color: #8ae61a; margin: 4px 0 0 0;"><?= $level_member; ?></p>
        </div>
    </div>

    <div class="grid-actions">
        <a href="setor.php" class="action-card">
            <span style="font-size: 24px; display: block; margin-bottom: 6px;">📈</span>
            <strong style="font-size: 14px; color: #fff; display: block;">Setor Sampah</strong>
            <span style="font-size: 11px; color: #52525b; display: block; margin-top: 2px;">Setor sampah ke bank sampah terdekat</span>
        </a>
        <a href="lokasi.php" class="action-card">
            <span style="font-size: 24px; display: block; margin-bottom: 6px;">🎯</span>
            <strong style="font-size: 14px; color: #fff; display: block;">Lokasi Terdekat</strong>
            <span style="font-size: 11px; color: #52525b; display: block; margin-top: 2px;">Temukan bank sampah di sekitar Anda</span>
        </a>
        <a href="pencairan.php" class="action-card">
            <span style="font-size: 24px; display: block; margin-bottom: 6px;">💼</span>
            <strong style="font-size: 14px; color: #fff; display: block;">Cairkan Saldo</strong>
            <span style="font-size: 11px; color: #52525b; display: block; margin-top: 2px;">Tarik saldo Anda kapan saja</span>
        </a>
    </div>

    <div style="margin-top: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">Riwayat Transaksi</h2>
                <p style="font-size: 11px; color: #52525b; margin: 2px 0 0 0;">Log mutasi finansial akun Anda</p>
            </div>
            <a href="riwayat.php" style="font-size: 12px; font-weight: 600; color: #8ae61a; text-decoration: none;">Lihat Semua →</a>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayat)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 24px; font-style: italic; color: #52525b;">Belum ada riwayat aktivitas transaksi pada akun ini.</td>
                        </tr>
                    <?php else: foreach ($riwayat as $row): ?>
                        <tr>
                            <td style="white-space: nowrap; font-family: monospace; color: #a1a1aa;"><?= date('d M Y - H:i', strtotime($row['tanggal'])); ?></td>
                            <td style="font-weight: 500; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($row['keterangan']); ?></td>
                            <td>
                                <?php if ($row['tipe'] === 'Setoran'): ?>
                                    <span style="background-color: rgba(138, 230, 26, 0.1); border: 1px solid rgba(138, 230, 26, 0.2); color: #8ae61a; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Setoran</span>
                                <?php else: ?>
                                    <span style="background-color: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.2); color: #f43f5e; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Pencairan</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; font-family: monospace; <?= (strpos($row['jumlah'], '+') !== false) ? 'color: #8ae61a;' : 'color: #f43f5e;'; ?>">
                                <?= $row['jumlah']; ?>
                            </td>
                            <td>
                                <span style="color: #34d399; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="width: 6px; height: 6px; background-color: #34d399; border-radius: 50%;"></span>
                                    Selesai
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>