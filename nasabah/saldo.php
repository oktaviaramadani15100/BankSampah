<?php
session_start();
// Proteksi Akses Halaman Nasabah
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'warga') {
    header("Location: ../login.php");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

$id_nasabah = $_SESSION['user_id'];

// 1. AMBIL DATA DARI TABEL SALDO SECARA INSTAN & DINAMIS
try {
    // A. Hitung total berat setoran nyata dari tabel transaksi_setoran fisik (Selesai)
    $stmt_kg = $koneksi->prepare("SELECT SUM(ds.berat_kg) as total_kg 
                                  FROM detail_setoran ds
                                  JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
                                  WHERE ts.id_nasabah = :id AND LOWER(ts.status) = 'selesai'");
    $stmt_kg->execute([':id' => $id_nasabah]);
    $total_setoran = number_format(($stmt_kg->fetch(PDO::FETCH_ASSOC)['total_kg'] ?? 0), 2, ',', '.') . " Kg";

    // B. Ambil metrik keuangan langsung dari tabel saldo baru
    $stmt_saldo = $koneksi->prepare("SELECT * FROM saldo WHERE id_nasabah = :id");
    $stmt_saldo->execute([':id' => $id_nasabah]);
    $data_db_saldo = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

    $saldo_val       = $data_db_saldo['saldo_tersedia'] ?? 0;
    $total_pemasukan = $data_db_saldo['total_pemasukan'] ?? 0;
    $total_pencairan = "Rp " . number_format(($data_db_saldo['total_pengeluaran'] ?? 0), 0, ',', '.');
    $poin_reward     = number_format(($data_db_saldo['point_reward'] ?? 0), 0, ',', '.');

    $saldo_tersedia  = "Rp " . number_format($saldo_val, 0, ',', '.');

    // C. Klasifikasi Level Member
    if ($total_pemasukan >= 1000000) {
        $level_member = "Gold"; $next_level = "Platinum";
    } elseif ($total_pemasukan >= 250000) {
        $level_member = "Silver"; $next_level = "Gold";
    } else {
        $level_member = "Bronze"; $next_level = "Silver";
    }

} catch (Exception $e) {
    $saldo_tersedia = "Rp 0"; $total_setoran = "0 Kg"; $total_pencairan = "Rp 0"; $poin_reward = "0"; $level_member = "Bronze"; $next_level = "Silver";
}
?>

<link rel="stylesheet" href="../css/nasabah.css">

<style>
    /* Tambahan Layout Grid Khusus Halaman Detail Saldo */
    .layout-saldo-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    @media (min-width: 992px) {
        .layout-saldo-grid {
            grid-template-columns: 4fr 8fr;
        }
    }
    .grid-sub-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .card-saldo-utama {
        background: linear-gradient(135deg, #65a30d, #8ae61a);
        color: #09090b;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 24px;
        box-shadow: 0 10px 25px rgba(138, 230, 26, 0.15);
        transition: all 0.3s ease;
    }
    .card-saldo-utama:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(138, 230, 26, 0.3);
    }
    .btn-cairkan {
        width: 100%;
        padding: 12px;
        background-color: #09090b;
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        border-radius: 12px;
        text-decoration: none;
        text-align: center;
        transition: background-color 0.2s;
    }
    .btn-cairkan:hover {
        background-color: #1c1c24;
    }
</style>

<div class="dashboard-container">
    <div class="welcome-section">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: #fff; margin: 0;">Detail Saldo Tabungan</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Pantau mutasi finansial, poin reward, dan tingkatan keanggotaan Anda</p>
        </div>
    </div>

    <div class="layout-saldo-grid">
        <div class="card-saldo-utama">
            <div>
                <p style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8; margin: 0;">Saldo Tersedia</p>
                <p style="font-size: 32px; font-weight: 900; margin: 6px 0 0 0; letter-spacing: -0.02em;"><?= $saldo_tersedia; ?></p>
            </div>
            <div style="border-top: 1px solid rgba(0,0,0,0.08); pt-4; padding-top: 16px; font-size: 12px; font-weight: 600; display:flex; flex-direction:column; gap:8px;">
                <div style="display: flex; justify-content: space-between; opacity: 0.8;">
                    <span>Total Pemasukan</span>
                    <span>Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; opacity: 0.8;">
                    <span>Total Dana Ditarik</span>
                    <span><?= $total_pencairan; ?></span>
                </div>
            </div>
            <a href="pencairan.php" class="btn-cairkan">Cairkan Sekarang</a>
        </div>

        <div class="grid-sub-metrics">
            <div class="metric-card">
                <span style="font-size: 20px;">⚖️</span>
                <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 10px 0 0 0; font-weight: 600;">Total Massa Setoran</p>
                <p style="font-size: 22px; font-weight: 700; color: #fff; margin: 4px 0 0 0;"><?= $total_setoran; ?></p>
                <span style="font-size: 11px; color: #34d399; font-weight: 600; margin-top: 8px; display: block;">✓ Terverifikasi timbangan</span>
            </div>
            <div class="metric-card">
                <span style="font-size: 20px;">💵</span>
                <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 10px 0 0 0; font-weight: 600;">Total Penarikan</p>
                <p style="font-size: 22px; font-weight: 700; color: #fff; margin: 4px 0 0 0;"><?= $total_pencairan; ?></p>
                <span style="font-size: 11px; color: #52525b; margin-top: 8px; display: block;">Selama bergabung</span>
            </div>
            <div class="metric-card">
                <span style="font-size: 20px;">🏅</span>
                <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 10px 0 0 0; font-weight: 600;">Poin Reward</p>
                <p style="font-size: 22px; font-weight: 700; color: #8ae61a; margin: 4px 0 0 0;"><?= $poin_reward; ?></p>
                <span style="font-size: 11px; color: #52525b; margin-top: 8px; display: block;">Diperbarui otomatis</span>
            </div>
            <div class="metric-card">
                <span style="font-size: 20px;">🎯</span>
                <p style="font-size: 11px; color: #52525b; text-transform: uppercase; margin: 10px 0 0 0; font-weight: 600;">Membership Level</p>
                <p style="font-size: 22px; font-weight: 700; color: #fff; margin: 4px 0 0 0;"><?= $level_member; ?></p>
                <span style="font-size: 9px; font-weight: 700; padding: 2px 6px; bg: #1c1a06; color: #ffc107; border: 1px solid rgba(255,193,7,0.2); border-radius:4px; display:inline-block; margin-top:8px;">Next: <?= $next_level; ?></span>
            </div>
        </div>
    </div>
</div>
</body>
</html>