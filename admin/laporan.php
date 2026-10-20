<?php
session_start();
// 1. Proteksi Akses Halaman Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

require_once '../config/koneksi.php';

// 2. PROSES EKSPOR MULTI-SHEET EXCEL (XML FORMAT)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $nama_file = "laporan_bank_sampah_" . date('d-m-Y') . ".xls";
    
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=" . $nama_file);
    header("Pragma: no-cache");
    header("Expires: 0");

    try {
        // Ambil data dari database tanpa LIMIT
        $sql1 = "SELECT s.id_sampah, s.nama_sampah, k.nama_kategori AS kategori, SUM(ds.berat_kg) AS total_bobot, SUM(ds.subtotal) AS total_nilai
                 FROM detail_setoran ds
                 JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
                 JOIN sampah s ON ds.id_sampah = s.id_sampah
                 LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
                 WHERE ts.status = 'selesai' GROUP BY s.id_sampah ORDER BY total_bobot DESC";
        $data_vol = $koneksi->query($sql1)->fetchAll(PDO::FETCH_ASSOC);

        $sql2 = "SELECT ts.id_setoran, n.nama_lengkap, ts.tanggal_setoran, ts.total_berat, ts.total_penerimaan, ts.status 
                 FROM transaksi_setoran ts 
                 JOIN nasabah n ON ts.id_nasabah = n.id_nasabah 
                 ORDER BY ts.created_at DESC";
        $data_setoran = $koneksi->query($sql2)->fetchAll(PDO::FETCH_ASSOC);

        $sql3 = "SELECT p.id_pencairan, n.nama_lengkap, p.jumlah_dana, p.metode, p.status, p.tanggal_pengajuan 
                 FROM pencairan p 
                 JOIN nasabah n ON p.id_nasabah = n.id_nasabah 
                 ORDER BY p.tanggal_pengajuan DESC";
        $data_pencairan = $koneksi->query($sql3)->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        die("Gagal memuat ekspor biner: " . $e->getMessage());
    }

    echo "<?xml version=\"1.0\"?>\n";
    echo "<?mso-application progid=\"Excel.Sheet\"?>\n";
    ?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
              xmlns:o="urn:schemas-microsoft-com:office:office"
              xmlns:x="urn:schemas-microsoft-com:office:excel"
              xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
              xmlns:html="http://www.w3.org/TR/REC-html40">
        
        <Styles>
            <Style ss:ID="HeaderStyle">
                <Font ss:Bold="1"/>
                <Interior ss:Color="#D3D3D3" ss:Pattern="Solid"/>
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
            <Style ss:ID="DataStyle">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
        </Styles>

        <Worksheet ss:Name="Volume Sampah">
            <Table>
                <Column ss:AutoFitWidth="1" ss:Width="100"/>
                <Column ss:AutoFitWidth="1" ss:Width="180"/>
                <Column ss:AutoFitWidth="1" ss:Width="120"/>
                <Column ss:AutoFitWidth="1" ss:Width="140"/>
                <Column ss:AutoFitWidth="1" ss:Width="160"/>

                <Row ss:Height="22">
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Sampah</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Nama Komoditas Barang</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Kategori</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Bobot Masuk</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Nilai Perputaran Kas</Data></Cell>
                </Row>
                <?php foreach ($data_vol as $v): ?>
                <Row ss:Height="18">
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= $v['id_sampah']; ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= htmlspecialchars($v['nama_sampah']); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= htmlspecialchars($v['kategori']); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= number_format($v['total_bobot'], 2, ',', '.'); ?> Kg</Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String">Rp <?= number_format($v['total_nilai'], 0, ',', '.'); ?></Data></Cell>
                </Row>
                <?php endforeach; ?>
            </Table>
        </Worksheet>

        <Worksheet ss:Name="Data Transaksi">
            <Table>
                <Column ss:AutoFitWidth="1" ss:Width="130"/>
                <Column ss:AutoFitWidth="1" ss:Width="180"/>
                <Column ss:AutoFitWidth="1" ss:Width="120"/>
                <Column ss:AutoFitWidth="1" ss:Width="120"/>
                <Column ss:AutoFitWidth="1" ss:Width="140"/>
                <Column ss:AutoFitWidth="1" ss:Width="100"/>

                <Row ss:Height="22">
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Setoran</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Nama Nasabah</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Tanggal Setoran</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Berat</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Penerimaan</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Status</Data></Cell>
                </Row>
                <?php foreach ($data_setoran as $s): ?>
                <Row ss:Height="18">
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= $s['id_setoran']; ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= htmlspecialchars($s['nama_lengkap']); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= date('d-m-Y', strtotime($s['tanggal_setoran'])); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= number_format($s['total_berat'], 2, ',', '.'); ?> Kg</Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String">Rp <?= number_format($s['total_penerimaan'], 0, ',', '.'); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= ucfirst($s['status']); ?></Data></Cell>
                </Row>
                <?php endforeach; ?>
            </Table>
        </Worksheet>

        <Worksheet ss:Name="Penarikan Dana">
            <Table>
                <Column ss:AutoFitWidth="1" ss:Width="130"/>
                <Column ss:AutoFitWidth="1" ss:Width="180"/>
                <Column ss:AutoFitWidth="1" ss:Width="140"/>
                <Column ss:AutoFitWidth="1" ss:Width="120"/>
                <Column ss:AutoFitWidth="1" ss:Width="140"/>
                <Column ss:AutoFitWidth="1" ss:Width="120"/>

                <Row ss:Height="22">
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Pencairan</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Nama Nasabah</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Tanggal Pengajuan</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Metode Finansial</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Nominal Cair</Data></Cell>
                    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Status Otorisasi</Data></Cell>
                </Row>
                <?php foreach ($data_pencairan as $p): ?>
                <Row ss:Height="18">
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= $p['id_pencairan']; ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= htmlspecialchars($p['nama_lengkap']); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= date('d-m-Y H:i', strtotime($p['tanggal_pengajuan'])); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= $p['metode']; ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String">Rp <?= number_format($p['jumlah_dana'], 0, ',', '.'); ?></Data></Cell>
                    <Cell ss:StyleID="DataStyle"><Data ss:Type="String"><?= ucfirst($p['status']); ?></Data></Cell>
                </Row>
                <?php endforeach; ?>
            </Table>
        </Worksheet>
    </Workbook>
    <?php
    exit();
}

// 3. QUERY PREVIEW INTERFACE DASHBOARD (WEB PREVIEW)
include 'header.php';

try {
    $view_vol = $koneksi->query("SELECT s.id_sampah, s.nama_sampah, k.nama_kategori AS kategori, SUM(ds.berat_kg) AS total_bobot, SUM(ds.subtotal) AS total_nilai FROM detail_setoran ds JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran JOIN sampah s ON ds.id_sampah = s.id_sampah LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori WHERE ts.status = 'selesai' GROUP BY s.id_sampah ORDER BY total_bobot DESC")->fetchAll(PDO::FETCH_ASSOC);
    $view_setoran = $koneksi->query("SELECT ts.id_setoran, n.nama_lengkap, ts.tanggal_setoran, ts.total_berat, ts.total_penerimaan, ts.status FROM transaksi_setoran ts JOIN nasabah n ON ts.id_nasabah = n.id_nasabah ORDER BY ts.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $view_pencairan = $koneksi->query("SELECT p.id_pencairan, n.nama_lengkap, p.jumlah_dana, p.metode, p.status, p.tanggal_pengajuan FROM pencairan p JOIN nasabah n ON p.id_nasabah = n.id_nasabah ORDER BY p.tanggal_pengajuan DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $view_vol = []; $view_setoran = []; $view_pencairan = [];
}
?>

<link rel="stylesheet" href="assets/css/responsive.css">
<style>

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
    .btn-export-excel { background-color: #10b981; color: #09090b; font-weight: 700; font-size: 13px; padding: 12px 20px; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); transition: all 0.2s ease; }
    .btn-export-excel:hover { background-color: #059669; transform: translateY(-1px); }
    .section-divider-title { font-size: 18px; font-weight: 700; color: #ffffff; margin: 32px 0 14px 0; display: flex; align-items: center; gap: 8px; }
</style>

<div class="dashboard-container" >
    
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1f1f23; padding-bottom: 20px; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #fff; margin: 0;">Pusat Konsol Laporan Besar</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Unduh pembukuan gabungan multi-sheet (tanpa batas limit baris data)</p>
        </div>
        <a href="laporan.php?export=excel" class="btn-export-excel">📊 Export Excel</a>
    </div>

    <div class="section-divider-title">📁 1. Akumulasi Volume Komoditas</div>
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID Sampah</th>
                    <th>Nama Komoditas Barang</th>
                    <th>Kategori</th>
                    <th style="text-align: right;">Total Bobot</th>
                    <th style="text-align: right;">Perputaran Kas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($view_vol as $v): ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: bold; color: #52525b;"><?= $v['id_sampah']; ?></td>
                        <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($v['nama_sampah']); ?></td>
                        <td><span style="background-color:#050505; border:1px solid #1f1f23; padding:4px 8px; border-radius:4px; font-size:10px; color:#a1a1aa; font-weight:700; text-transform:uppercase;"><?= htmlspecialchars($v['kategori'] ?? 'Lainnya'); ?></span></td>
                        <td style="text-align: right; font-weight: bold; color: #8ae61a; font-family: monospace;"><?= number_format($v['total_bobot'], 2, ',', '.'); ?> Kg</td>
                        <td style="text-align: right; font-weight: bold; font-family: monospace;">Rp <?= number_format($v['total_nilai'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section-divider-title">💳 2. Log Transaksi Setoran Keseluruhan</div>
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID Setoran</th>
                    <th>Nama Nasabah</th>
                    <th>Tanggal</th>
                    <th style="text-align: right;">Berat Timbangan</th>
                    <th style="text-align: right;">Penerimaan E-Wallet</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($view_setoran as $s): ?>
                    <tr>
                        <td style="font-family: monospace; color:#a1a1aa;"><?= $s['id_setoran']; ?></td>
                        <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($s['nama_lengkap']); ?></td>
                        <td><?= date('d M Y', strtotime($s['tanggal_setoran'])); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?= number_format($s['total_berat'], 2, ',', '.'); ?> Kg</td>
                        <td style="text-align: right; color:#8ae61a; font-weight:bold; font-family: monospace;">Rp <?= number_format($s['total_penerimaan'], 0, ',', '.'); ?></td>
                        <td><span style="color: #fbbf24; font-weight:600;"><?= ucfirst($s['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section-divider-title">💵 3. Log Riwayat Penarikan Dana Tunai Keseluruhan</div>
    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID Pencairan</th>
                    <th>Nama Nasabah</th>
                    <th>Tanggal Klaim</th>
                    <th>Metode</th>
                    <th style="text-align: right;">Nominal Tarik</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($view_pencairan as $p): ?>
                    <tr>
                        <td style="font-family: monospace; color:#a1a1aa;"><?= $p['id_pencairan']; ?></td>
                        <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                        <td><?= date('d M Y H:i', strtotime($p['tanggal_pengajuan'])); ?></td>
                        <td><?= $p['metode']; ?></td>
                        <td style="text-align: right; color:#f43f5e; font-weight:bold; font-family: monospace;">Rp <?= number_format($p['jumlah_dana'], 0, ',', '.'); ?></td>
                        <td><span style="color:#34d399; font-weight:600;"><?= ucfirst($p['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>