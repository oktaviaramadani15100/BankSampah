<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'petugas') {
    header("Location: ../login.php?role=petugas");
    exit();
}


require_once '../config/koneksi.php';


include 'header.php';

$transaksi_hari_ini = 0;
$total_sampah_db = 0;
$menunggu_verifikasi = 0;
$pencairan_diproses = 0;

try {

    $stmt_hari_ini = $koneksi->query("SELECT COUNT(*) as total FROM transaksi_setoran WHERE DATE(tanggal_setoran) = CURDATE()");
    $transaksi_hari_ini = $stmt_hari_ini->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;


    $stmt_sampah = $koneksi->query("SELECT SUM(total_berat) as total FROM transaksi_setoran WHERE status = 'selesai'");
    $total_sampah_db = $stmt_sampah->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;


    $stmt_verif = $koneksi->query("SELECT COUNT(*) as total FROM transaksi_setoran WHERE status IN ('rencana', 'diproses')");
    $menunggu_verifikasi = $stmt_verif->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;


    $stmt_cair = $koneksi->query("SELECT SUM(jumlah_dana) as total FROM pencairan WHERE status = 'menunggu'");
    $pencairan_diproses = $stmt_cair->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

} catch (Exception $e) {
    error_log($e->getMessage());
}
?>

<style>



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
     
        padding-top: 200px; 
    }
}
    .dash-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: 'Segoe UI', system-ui, sans-serif;
        background-color: #09090b;
        color: #f4f4f5;
    }

    .dash-header {
        border-bottom: 1px solid #1f1f23;
        padding-bottom: 20px;
        margin-bottom: 28px;
    }

    .dash-header h1 {
        font-size: 28px;
        font-weight: 800;
        margin: 0;
        color: #ffffff;
        letter-spacing: -0.02em;
    }

    .dash-header p {
        font-size: 13px;
        color: #52525b;
        margin: 6px 0 0 0;
    }


    .banner-notif-box {
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.03), rgba(251, 191, 36, 0.01));
        border: 1px solid rgba(251, 191, 36, 0.15);
        padding: 16px 20px;
        border-radius: 16px;
        font-size: 13px;
        color: #a1a1aa;
        margin-bottom: 28px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .banner-notif-box strong {
        color: #fbbf24;
        font-weight: 700;
    }

    .metrics-layout-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .metric-card-premium {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.25s ease, border-color 0.25s ease;
    }

    .metric-card-premium:hover {
        transform: translateY(-2px);
        border-color: #8ae61a;
    }

    .icon-box-badge {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .text-box-group .card-label {
        font-size: 11px;
        font-weight: 700;
        color: #52525b;
        text-transform: uppercase;
        margin: 0;
        letter-spacing: 0.05em;
    }

    .text-box-group h3 {
        font-size: 24px;
        font-weight: 700;
        color: #ffffff;
        margin: 4px 0 0 0;
        font-family: monospace;
    }


    .shortcut-layout-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        margin-bottom: 36px;
    }

    .shortcut-card-link {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        transition: all 0.25s ease-in-out;
    }

    .shortcut-card-link:hover {
        border-color: #8ae61a;
        background-color: rgba(138, 230, 26, 0.01);
        box-shadow: 0 0 20px rgba(138, 230, 26, 0.15);
        transform: translateY(-2px);
    }

    .shortcut-icon-circle {
        font-size: 20px;
        color: #8ae61a;
        flex-shrink: 0;
    }

    .shortcut-details h4 {
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
    }

    .shortcut-details p {
        font-size: 11px;
        color: #52525b;
        margin: 4px 0 0 0;
    }


    .table-section-head {
        margin-bottom: 14px;
    }

    .table-section-head h2 {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
    }

    .table-section-head p {
        font-size: 12px;
        color: #a1a1aa;
        margin: 4px 0 0 0;
    }

    .premium-table-wrapper {
        width: 100%;
        overflow-x: auto;
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 16px;
    }

    .premium-custom-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .premium-custom-table th {
        padding: 16px;
        color: #52525b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.05em;
        background-color: rgba(255, 255, 255, 0.01);
        border-bottom: 1px solid #1f1f23;
    }

    .premium-custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(31, 31, 35, 0.6);
        vertical-align: middle;
    }

    .premium-custom-table tr:last-child td {
        border-bottom: none;
    }

    .premium-custom-table tbody tr {
        transition: all 0.2s ease;
    }

    .premium-custom-table tbody tr:hover td {
        background-color: rgba(138, 230, 26, 0.01) !important;
        border-top-color: #8ae61a;
        border-bottom-color: #8ae61a;
    }


    .badge-label-status {
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .dot-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
</style>

<div class="dash-container">
    
    <div class="dash-header">
        <h1>Dashboard Petugas</h1>
        <p>Kelola penimbangan, pemrosesan e-wallet, dan verifikasi setoran nasabah</p>
    </div>

    <div class="banner-notif-box">
        <div class="banner-title" style="font-weight:700; color:#fbbf24; margin-bottom:4px;">⚠️ Transaksi Menunggu Tindakan</div>
        <p>Anda memiliki <strong><?php echo $menunggu_verifikasi; ?></strong> transaksi yang tertahan dalam antrean sistem cabang.</p>
    </div>

    <div class="metrics-layout-grid">
        <div class="metric-card-premium">
            <div class="icon-box-badge" style="color: #34d399; background: rgba(52, 211, 153, 0.05); border: 1px solid rgba(52, 211, 153, 0.1);"><i class="fas fa-chart-line"></i></div>
            <div class="text-box-group">
                <p class="card-label">Transaksi Hari Ini</p>
                <h3><?php echo $transaksi_hari_ini; ?></h3>
            </div>
        </div>
        <div class="metric-card-premium">
            <div class="icon-box-badge" style="color: #f43f5e; background: rgba(244, 63, 94, 0.05); border: 1px solid rgba(244, 63, 94, 0.1);"><i class="fas fa-balance-scale"></i></div>
            <div class="text-box-group">
                <p class="card-label">Total Sampah Terolah</p>
                <h3><?php echo round($total_sampah_db, 1); ?> <span style="font-size: 13px; color: #52525b; font-weight:400;">Kg</span></h3>
            </div>
        </div>
        <div class="metric-card-premium">
            <div class="icon-box-badge" style="color: #fbbf24; background: rgba(251, 191, 36, 0.05); border: 1px solid rgba(251, 191, 36, 0.1);"><i class="fas fa-hourglass-half"></i></div>
            <div class="text-box-group">
                <p class="card-label">Menunggu Verifikasi</p>
                <h3><?php echo $menunggu_verifikasi; ?></h3>
            </div>
        </div>
        <div class="metric-card-premium">
            <div class="icon-box-badge" style="color: #8ae61a; background: rgba(138, 230, 26, 0.05); border: 1px solid rgba(138, 230, 26, 0.1);"><i class="fas fa-wallet"></i></div>
            <div class="text-box-group">
                <p class="card-label">Pencairan Diproses</p>
                <h3 style="color: #8ae61a;">Rp <?php echo number_format($pencairan_diproses, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <div class="shortcut-layout-grid">
        <a href="penimbangan.php" class="shortcut-card-link">
            <div class="shortcut-icon-circle"><i class="fas fa-scale-balanced"></i></div>
            <div class="shortcut-details">
                <h4>Input Penimbangan</h4>
                <p>Catat muatan timbangan digital warga baru</p>
            </div>
        </a>
        <a href="verifikasi.php" class="shortcut-card-link">
            <div class="shortcut-icon-circle"><i class="fas fa-check-circle"></i></div>
            <div class="shortcut-details">
                <h4>Verifikasi Setoran</h4>
                <p>Validasi kesesuaian berkas fisik warga</p>
            </div>
        </a>
        <a href="pencairan.php" class="shortcut-card-link">
            <div class="shortcut-icon-circle"><i class="fas fa-money-bill-wave"></i></div>
            <div class="shortcut-details">
                <h4>Proses Pencairan</h4>
                <p>Otorisasi pencairan kas keluar dompet</p>
            </div>
        </a>
    </div>

    <div class="table-section-head">
        <h2>Riwayat Kunjungan & Setoran</h2>
        <p>Aktivitas muatan penimbangan masuk log basis data nasabah terbaru</p>
    </div>

    <div class="premium-table-wrapper">
        <table class="premium-custom-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Nasabah / ID</th>
                    <th>Tipe</th>
                    <th>Total Berat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody style="color: #e4e4e7;">
                <?php
                $ada_data = false;
                try {
                    $query_tabel = $koneksi->query("
                        SELECT 
                            ts.tanggal_setoran AS tanggal_kunjungan, 
                            ts.id_setoran AS id_kunjungan, 
                            ts.status, 
                            n.nama_lengkap,
                            ts.total_berat AS total_berat
                        FROM transaksi_setoran ts
                        JOIN nasabah n ON ts.id_nasabah = n.id_nasabah
                        ORDER BY ts.created_at DESC
                        LIMIT 5
                    ");
                    
                    while ($row = $query_tabel->fetch(PDO::FETCH_ASSOC)) {
                        $ada_data = true;
                        
                        if ($row['status'] == 'selfinished' || $row['status'] == 'selesai') {
                            $status_html = '<span class="badge-label-status" style="color: #34d399;"><span class="dot-indicator" style="background-color: #34d399;"></span>✓ Selesai</span>';
                        } elseif ($row['status'] == 'dibatalkan') {
                            $status_html = '<span class="badge-label-status" style="color: #f43f5e;"><span class="dot-indicator" style="background-color: #f43f5e;"></span>🗙 Batal</span>';
                        } else {
                            $status_html = '<span class="badge-label-status" style="color: #fbbf24;"><span class="dot-indicator" style="background-color: #fbbf24;"></span>⏳ Rencana</span>';
                        }
                        
                        echo "<tr>
                                <td style='color: #a1a1aa; font-family: monospace; font-weight: 500;'>" . date('d M Y', strtotime($row['tanggal_kunjungan'])) . "</td>
                                <td style='font-weight: 600; color: #ffffff;'>
                                    " . htmlspecialchars($row['nama_lengkap']) . "
                                    <div style='color: #52525b; font-size: 10px; font-weight: 400; font-family: monospace; margin-top:2px;'>ID: " . htmlspecialchars($row['id_kunjungan']) . "</div>
                                </td>
                                <td>
                                    <span style='background: rgba(138, 230, 26, 0.06); border: 1px solid rgba(138, 230, 26, 0.15); color: #8ae61a; font-weight: 700; text-transform: uppercase; font-size: 10px; padding: 2px 8px; border-radius: 4px;'>Setoran</span>
                                </td>
                                <td style='font-weight: bold; color: #ffffff; font-family: monospace; font-size:14px;'>" . round($row['total_berat'], 2) . " Kg</td>
                                <td>{$status_html}</td>
                              </tr>";
                    }
                } catch (Exception $e) {
                    $ada_data = false;
                }
                
                if (!$ada_data) { ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            Belum ada data transaksi tersimpan di database.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>