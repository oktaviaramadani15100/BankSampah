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

// Menangkap filter tipe transaksi dari URL jika ada (?type=Semua, ?type=Setoran, ?type=Pencairan)
$filter_type = isset($_GET['type']) ? trim($_GET['type']) : 'Semua';

// 1. DATA RINGKASAN REKAP KARTU ATAS SECARA DINAMIS DARI DATABASE
$total_setoran_rp = 0; $count_setoran = 0;
$total_pencairan_rp = 0; $count_pencairan = 0;

try {
    // A. Akumulasi Kategori Setoran Sampah yang Sudah Selesai
    $stmt_setoran = $koneksi->prepare("SELECT COUNT(DISTINCT ts.id_setoran) as jumlah, SUM(ds.subtotal) as total 
                                       FROM transaksi_setoran ts
                                       JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
                                       WHERE ts.id_nasabah = :id AND ts.status = 'selesai'");
    $stmt_setoran->execute([':id' => $id_nasabah]);
    $res_setoran = $stmt_setoran->fetch(PDO::FETCH_ASSOC);
    $count_setoran = $res_setoran['jumlah'] ?? 0;
    $total_setoran_rp = $res_setoran['total'] ?? 0;

    // B. Akumulasi Kategori Pencairan Saldo
    $stmt_pencairan = $koneksi->prepare("SELECT COUNT(*) as jumlah, SUM(jumlah_dana) as total 
                                         FROM pencairan 
                                         WHERE id_nasabah = :id AND LOWER(status) = 'disetujui'");
    $stmt_pencairan->execute([':id' => $id_nasabah]);
    $res_pencairan = $stmt_pencairan->fetch(PDO::FETCH_ASSOC);
    $count_pencairan = $res_pencairan['jumlah'] ?? 0;
    $total_pencairan_rp = $res_pencairan['total'] ?? 0;

} catch (Exception $e) {
    // Muted error
}

// 2. QUERY AMBIL LOG MUTASI GABUNGAN BERDASARKAN FILTER
$riwayat_transaksi = [];
try {
    $sql_base = "SELECT * FROM (
                    (SELECT ts.created_at as tanggal, 
                            GROUP_CONCAT(s.nama_sampah SEPARATOR ', ') as keterangan, 
                            'Setoran' as tipe, 
                            CONCAT('+Rp ', FORMAT(SUM(ds.subtotal), 0, 'id_ID')) as jumlah
                     FROM transaksi_setoran ts
                     JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
                     JOIN sampah s ON ds.id_sampah = s.id_sampah
                     WHERE ts.id_nasabah = :id_w1 AND ts.status = 'selesai'
                     GROUP BY ts.id_setoran)
                    UNION ALL
                    (SELECT p.tanggal_pengajuan as tanggal, 
                            CONCAT('Penarikan Tunai Via ', p.metode) as keterangan, 
                            'Pencairan' as tipe, 
                            CONCAT('-Rp ', FORMAT(p.jumlah_dana, 0, 'id_ID')) as jumlah
                     FROM pencairan p
                     WHERE p.id_nasabah = :id_w2 AND LOWER(p.status) = 'disetujui')
                 ) as gabungan";

    if ($filter_type !== 'Semua') {
        $sql_base .= " WHERE tipe = :tipe ORDER BY tanggal DESC";
        $stmt_riwayat = $koneksi->prepare($sql_base);
        $stmt_riwayat->execute([':id_w1' => $id_nasabah, ':id_w2' => $id_nasabah, ':tipe' => $filter_type]);
    } else {
        $sql_base .= " ORDER BY tanggal DESC";
        $stmt_riwayat = $koneksi->prepare($sql_base);
        $stmt_riwayat->execute([':id_w1' => $id_nasabah, ':id_w2' => $id_nasabah]);
    }
    
    $riwayat_transaksi = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $riwayat_transaksi = [];
}

$total_tampil = count($riwayat_transaksi);
?>

<link rel="stylesheet" href="../css/nasabah.css">

<style>
    /* Gaya Tambahan Khusus untuk Menu Pilihan Filter */
    .filter-wrapper-row {
        display: flex;
        align-items: center;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 8px;
        scrollbar-width: none;
    }
    .filter-wrapper-row::-webkit-scrollbar {
        display: none;
    }
    .btn-filter-item {
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid #1f1f23;
        background-color: #050505;
        color: #a1a1aa;
        transition: all 0.2s;
    }
    .btn-filter-item.active, .btn-filter-item:hover {
        background-color: #8ae61a;
        color: #09090b;
        border-color: #8ae61a;
    }
    .grid-riwayat-rekap {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }
    .custom-table tbody tr {
        transition: all 0.2s ease;
    }
    .custom-table tbody tr:hover td {
        background-color: rgba(138, 230, 26, 0.02) !important;
        box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.05);
    }
</style>

<div class="dashboard-container">
    
    <div class="welcome-section">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: #fff; margin: 0;">Riwayat Log Mutasi</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Lihat pembukuan terperinci mutasi masuk setoran sampah dan penarikan tunai dompet Anda</p>
        </div>
    </div>

    <div class="grid-riwayat-rekap">
        <div class="metric-card">
            <p class="form-label">Total Hasil Setoran</p>
            <p style="font-size: 24px; font-weight: 900; color: #8ae61a; margin: 6px 0 0 0;">Rp <?= number_format($total_setoran_rp, 0, ',', '.'); ?></p>
            <span style="font-size: 11px; color: #52525b; margin-top: 4px; display:block;"><?= $count_setoran; ?> Transaksi Sukses</span>
        </div>
        <div class="metric-card">
            <p class="form-label">Total Hasil Penarikan</p>
            <p style="font-size: 24px; font-weight: 900; color: #f43f5e; margin: 6px 0 0 0;">Rp <?= number_format($total_pencairan_rp, 0, ',', '.'); ?></p>
            <span style="font-size: 11px; color: #52525b; margin-top: 4px; display:block;"><?= $count_pencairan; ?> Klaim Dana Keluar</span>
        </div>
    </div>

    <div class="filter-wrapper-row" style="margin-top: 10px;">
        <a href="riwayat.php?type=Semua" class="btn-filter-item <?= $filter_type === 'Semua' ? 'active' : '' ?>">Semua</a>
        <a href="riwayat.php?type=Setoran" class="btn-filter-item <?= $filter_type === 'Setoran' ? 'active' : '' ?>">Setoran (<?= $count_setoran; ?>)</a>
        <a href="riwayat.php?type=Pencairan" class="btn-filter-item <?= $filter_type === 'Pencairan' ? 'active' : '' ?>">Pencairan (<?= $count_pencairan; ?>)</a>
    </div>

    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tanggal Transaksi</th>
                    <th>Keterangan Komoditas</th>
                    <th>Tipe</th>
                    <th>Jumlah Dana</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_tampil === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 32px; color: #52525b; font-style: italic;">Belum ada mutasi data transaksi untuk kategori filter ini.</td>
                    </tr>
                <?php else: foreach ($riwayat_transaksi as $row):
                    $is_minus = (strpos($row['jumlah'], '-') !== false);
                ?>
                    <tr>
                        <td style="white-space: nowrap; font-family: monospace; color: #a1a1aa;"><?= date('d M Y - H:i', strtotime($row['tanggal'])); ?></td>
                        <td style="font-weight: 600; color: #fff; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($row['keterangan']); ?>"><?= htmlspecialchars($row['keterangan']); ?></td>
                        <td>
                            <?php if ($row['tipe'] === 'Setoran'): ?>
                                <span style="background-color: rgba(138, 230, 26, 0.08); border: 1px solid rgba(138, 230, 26, 0.15); color: #8ae61a; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Setoran</span>
                            <?php else: ?>
                                <span style="background-color: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.15); color: #f43f5e; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">Pencairan</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: bold; font-family: monospace; <?= $is_minus ? 'color: #f43f5e;' : 'color: #8ae61a;'; ?>">
                            <?= $row['jumlah']; ?>
                        </td>
                        <td>
                            <span style="color: #34d399; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <span style="width: 5px; height: 5px; background-color: #34d399; border-radius: 50%;"></span>
                                Selesai
                            </span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>