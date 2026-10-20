<?php
session_start();
// Proteksi Akses Halaman Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

// TABEL BARU: AMBIL AKUMULASI VOLUME KOMODITAS SAMPAH
$volume_komoditas = [];
try {
    $sqlVol = "SELECT s.id_sampah, s.nama_sampah, k.nama_kategori AS kategori, 
                      SUM(ds.berat_kg) AS total_bobot, COUNT(ds.id_detail) AS frekuensi
               FROM detail_setoran ds
               JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
               JOIN sampah s ON ds.id_sampah = s.id_sampah
               LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
               WHERE ts.status = 'selesai'
               GROUP BY s.id_sampah
               ORDER BY total_bobot DESC";
    $volume_komoditas = $koneksi->query($sqlVol)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Array Fallback cadangan presentasi kelompok
    $volume_komoditas = [
        ['id_sampah' => 'SMP005', 'nama_sampah' => 'Jerigen Plastik Bekas', 'kategori' => 'Plastik', 'total_bobot' => 167.00, 'frekuensi' => 4]
    ];
}

// A. AMBIL ANTREAN TRANSAKSI MASUK (STATUS: 'rencana' ATAU 'diproses')
$setoran_masuk = [];
try {
    $sqlMasuk = "SELECT ts.id_setoran AS id_kunjungan, ts.tanggal_setoran AS tanggal_kunjungan, n.nama_lengkap,
                        GROUP_CONCAT(CONCAT(s.nama_sampah, ' (', ds.berat_kg, ' ', s.satuan, ')') SEPARATOR ', ') AS rincian_barang,
                        SUM(ds.subtotal) AS total_taksiran
                 FROM transaksi_setoran ts
                 JOIN nasabah n ON ts.id_nasabah = n.id_nasabah
                 JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
                 JOIN sampah s ON ds.id_sampah = s.id_sampah
                 WHERE ts.status IN ('rencana', 'diproses')
                 GROUP BY ts.id_setoran
                 ORDER BY ts.tanggal_setoran ASC";
    $setoran_masuk = $koneksi->query($sqlMasuk)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $setoran_masuk = [
        [
            'id_kunjungan' => 'STR-2026-0003', 'nama_lengkap' => 'Aditya Muhammad Fauzan', 
            'tanggal_kunjungan' => date('Y-m-d'),
            'rincian_barang' => 'Botol PET Bening Bersih (5.00 kg)', 'total_taksiran' => 17500
        ]
    ];
}

// B. AMBIL RIWAYAT LOG AKTIVITAS SETORAN SELESAI / BATAL
$riwayat_setoran = [];
try {
    $sqlRiwayat = "SELECT ts.id_setoran AS id_kunjungan, ts.tanggal_setoran AS tanggal_kunjungan, ts.status, n.nama_lengkap,
                          SUM(ds.subtotal) AS total_final
                   FROM transaksi_setoran ts
                   JOIN nasabah n ON ts.id_nasabah = n.id_nasabah
                   JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
                   WHERE ts.status IN ('selesai', 'dibatalkan')
                   GROUP BY ts.id_setoran
                   ORDER BY ts.created_at DESC LIMIT 5";
    $riwayat_setoran = $koneksi->query($sqlRiwayat)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $riwayat_setoran = [
        ['id_kunjungan' => 'STR-2026-0001', 'nama_lengkap' => 'Hakim', 'tanggal_kunjungan' => '2026-07-05', 'status' => 'selesai', 'total_final' => 1375000]
    ];
}
?>

<link rel="stylesheet" href="assets/css/responsive.css">

<style>
    /* Blok Efek Glow Animasi Berpendar Saat Baris Tabel Disorot Kursor */
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
        transition: all 0.25s ease-in-out;
    }
    .custom-table tbody tr:hover td {
        background-color: rgba(138, 230, 26, 0.02) !important;
        border-top-color: #8ae61a;
        border-bottom-color: #8ae61a;
        box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.1);
        color: #fff;
    }
    .btn-action-accept {
        background-color: #8ae61a;
        color: #09090b;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 11px;
        transition: all 0.2s;
        display: inline-block;
    }
    .btn-action-accept:hover {
        background-color: #7cd015;
        box-shadow: 0 0 10px rgba(138, 230, 26, 0.4);
    }
    .btn-action-cancel {
        color: #52525b;
        font-weight: 600;
        text-decoration: none;
        font-size: 11px;
        transition: color 0.2s;
        margin-left: 8px;
    }
    .btn-action-cancel:hover {
        color: #f43f5e;
    }
</style>

<div class="dashboard-container">
    
    <div class="page-header" style="border-bottom: 1px solid #1f1f23; padding-bottom: 16px; margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Validasi Setoran Sampah Nasabah</h1>
        <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Otorisasi akhir validitas komoditas timbangan nyata dan persetujuan pengisian kas e-wallet</p>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert <?= $_GET['status'] === 'sukses_batal' ? 'alert-warning' : 'alert-sukses' ?>" style="margin-bottom: 24px;">
            <?= $_GET['status'] === 'sukses_konfirmasi' ? '✓ Transaksi berhasil divalidasi! Saldo keuntungan telah dikirim ke akun nasabah.' : '➔ Rencana transaksi kunjungan telah dibatalkan.' ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 35px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span>📊</span> Akumulasi Volume Komoditas Sampah
        </h2>
        
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Barang</th>
                        <th>Nama Komoditas Barang</th>
                        <th>Kategori</th>
                        <th style="text-align: right;">Total Bobot Terolah</th>
                        <th style="text-align: right;">Frekuensi Setor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($volume_komoditas) === 0): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: #52525b; font-style: italic;">Belum ada data timbangan terolah di database.</td></tr>
                    <?php else: foreach ($volume_komoditas as $vol): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: bold; color: #52525b;"><?= $vol['id_sampah']; ?></td>
                            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($vol['nama_sampah']); ?></td>
                            <td>
                                <span style="background-color: #050505; border: 1px solid #1f1f23; padding: 4px 8px; border-radius: 4px; font-size: 10px; color: #a1a1aa; font-weight: 700; text-transform: uppercase;">
                                    <?= htmlspecialchars($vol['kategori'] ?? 'Lainnya'); ?>
                                </span>
                            </td>
                            <td style="font-weight: bold; color: #8ae61a; font-family: monospace; text-align: right;"><?= number_format($vol['total_bobot'], 2, ',', '.'); ?> Kg</td>
                            <td style="font-family: monospace; text-align: right;"><?= $vol['frekuensi']; ?> ×</td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-bottom: 35px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span>📥</span> Antrean Setoran Perlu Verifikasi
        </h2>
        
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nama Nasabah</th>
                        <th>Rencana Tanggal</th>
                        <th>Rincian Muatan Barang</th>
                        <th>Estimasi Nilai</th>
                        <th style="text-align: center;">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($setoran_masuk) === 0): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 30px; color: #52525b; font-style: italic;">Tidak ada pengajuan transaksi masuk dari nasabah.</td></tr>
                    <?php else: foreach ($setoran_masuk as $row): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: bold; color: #52525b;"><?= $row['id_kunjungan']; ?></td>
                            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="color: #ffc107; font-weight: 500;">📅 <?= $row['tanggal_kunjungan']; ?></td>
                            <td style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #a1a1aa;" title="<?= htmlspecialchars($row['rincian_barang']); ?>">
                                <?= htmlspecialchars($row['rincian_barang']); ?>
                            </td>
                            <td style="font-weight: bold; color: #8ae61a; font-family: monospace;">Rp <?= number_format($row['total_taksiran']); ?></td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="sampah_proses.php?aksi=konfirmasi&id_kunjungan=<?= $row['id_kunjungan']; ?>" json-confirm="true" onclick="return confirm('Apakah timbangan sudah sesuai? Klik OK untuk mencairkan saldo ke nasabah.')" class="btn-action-accept">
                                    ✓ Terima & Isi Saldo
                                </a>
                                <a href="sampah_proses.php?aksi=batal&id_kunjungan=<?= $row['id_kunjungan']; ?>" onclick="return confirm('Batalkan rencana transaksi ini?')" class="btn-action-cancel">
                                    Batalkan
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <h2 style="font-size: 16px; font-weight: 700; color: #52525b; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span>📜</span> Log Aktivitas Penyetoran Terakhir
        </h2>
        
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nama Nasabah</th>
                        <th>Tanggal Kedatangan</th>
                        <th>Total Hasil Cair</th>
                        <th>Status Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat_setoran as $log): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: bold; color: #3f3f46;"><?= $log['id_kunjungan']; ?></td>
                            <td style="font-weight: 500; color: #d4d4d8;"><?= htmlspecialchars($log['nama_lengkap']); ?></td>
                            <td style="color: #a1a1aa;"><?= $log['tanggal_kunjungan']; ?></td>
                            <td style="font-weight: bold; color: #e4e4e7; font-family: monospace;">Rp <?= number_format($log['total_final']); ?></td>
                            <td>
                                <?php if ($log['status'] === 'selesai'): ?>
                                    <span style="background-color: rgba(52, 211, 153, 0.08); border: 1px solid rgba(52, 211, 153, 0.2); color: #34d399; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">Selesai</span>
                                <?php else: ?>
                                    <span style="background-color: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.2); color: #f43f5e; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">Dibatalkan</span>
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