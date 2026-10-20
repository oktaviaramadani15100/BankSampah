<?php
session_start();
// Proteksi Halaman Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

// 1. Panggil file header admin dan konfigurasi koneksi database
include 'header.php';
require_once '../config/koneksi.php';

// Inisialisasi variabel statistik default
$totalNasabah = 0;
$totalPetugas = 0;
$totalSampah = 0;

// 2. QUERY SINKRONISASI DATA REAL DARI DATABASE
try {
    // Menghitung Total nasabah
    $stmtNasabah = $koneksi->query("SELECT COUNT(*) FROM nasabah");
    $totalNasabah = $stmtNasabah->fetchColumn();
} catch (Exception $e) {
    error_log("Gagal mengambil data nasabah: " . $e->getMessage());
}

try {
    // Menghitung Total Petugas
    $stmtPetugas = $koneksi->query("SELECT COUNT(*) FROM petugas");
    $totalPetugas = $stmtPetugas->fetchColumn();
} catch (Exception $e) {
    error_log("Gagal mengambil data petugas: " . $e->getMessage());
}

try {
    // Menghitung Total Sampah Selesai
    $stmtSampah = $koneksi->query("SELECT COALESCE(SUM(ds.berat_kg), 0) FROM detail_setoran ds JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran WHERE ts.status = 'selesai'");
    $totalSampah = $stmtSampah->fetchColumn() ?? 0;
} catch (Exception $e) {
    error_log("Gagal mengambil data total sampah: " . $e->getMessage());
}

// 3. QUERY DATA UNTUK GRAFIK LINE SETORAN (7 Transaksi/Hari Terakhir)
$label_grafik = [];
$data_grafik = [];

try {
    $queryGrafik = $koneksi->query("
        SELECT 
            DATE(ts.tanggal_setoran) as tanggal, 
            SUM(ds.berat_kg) as total_berat
        FROM transaksi_setoran ts
        JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
        WHERE ts.status = 'selesai'
        GROUP BY DATE(ts.tanggal_setoran)
        ORDER BY tanggal ASC 
        LIMIT 7
    ");
    
    while ($row = $queryGrafik->fetch(PDO::FETCH_ASSOC)) {
        $label_grafik[] = date('d M', strtotime($row['tanggal']));
        $data_grafik[] = (float)$row['total_berat'];
    }
} catch (Exception $e) {
    error_log("Gagal mengambil data grafik: " . $e->getMessage());
}

// 4. QUERY RANKING KOMODITAS SAMPAH PALING SERING DISETOR
$rank_sampah = [];
try {
    $queryRank = $koneksi->query("
        SELECT 
            s.id_sampah,
            s.nama_sampah,
            k.nama_kategori AS kategori,
            SUM(ds.berat_kg) AS total_bobot,
            COUNT(ds.id_detail) AS frekuensi_setor
        FROM detail_setoran ds
        JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
        JOIN sampah s ON ds.id_sampah = s.id_sampah
        LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
        WHERE ts.status = 'selesai'
        GROUP BY s.id_sampah
        ORDER BY total_bobot DESC
        LIMIT 5
    ");
    $rank_sampah = $queryRank->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Gagal mengambil data ranking sampah: " . $e->getMessage());
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="assets/css/responsive.css">

<style>
    /* Blok Efek Glow Animasi Berpendar khusus Dashboard Admin */
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
    height: 75px; 
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
    .stat-card-admin, .chart-container-box, .rank-container-box {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-sizing: border-box;
        transition: all 0.3s ease-in-out;
    }
    .stat-card-admin:hover, .chart-container-box:hover, .rank-container-box:hover {
        transform: translateY(-2px);
        border-color: #8ae61a !important;
        box-shadow: 0 0 18px rgba(138, 230, 26, 0.25);
    }
    .grid-3-kolom {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }
    .grid-2-kolom-charts {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-top: 24px;
    }
    @media (min-width: 992px) {
        .grid-2-kolom-charts {
            grid-template-columns: 7fr 5fr;
        }
    }
    /* Style Tabel Kustom Ringkas */
    .table-rank {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
        margin-top: 10px;
    }
    .table-rank th {
        padding: 12px;
        color: #52525b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        border-bottom: 1px solid var(--border-color);
    }
    .table-rank td {
        padding: 12px;
        border-bottom: 1px solid rgba(31, 31, 35, 0.6);
        color: #a1a1aa;
    }
    .table-rank tr:last-child td { border-bottom: none; }
    .badge-rank {
        background-color: #8ae61a;
        color: #09090b;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
    }
</style>

<div class="dashboard-container" >
    
    <div class="page-header">
        <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Dashboard</h1>
        <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Ringkasan statistik operasional konsol utama Bank Sampah</p>
    </div>

    <div class="grid-3-kolom">
        <div class="stat-card-admin">
            <p class="form-label" style="margin: 0;">Total Nasabah</p>
            <p style="font-size: 28px; font-weight: 800; color: #fff; margin: 6px 0 0 0;"><?php echo number_format($totalNasabah); ?></p>
        </div>

        <div class="stat-card-admin">
            <p class="form-label" style="margin: 0;">Total Petugas</p>
            <p style="font-size: 28px; font-weight: 800; color: #fff; margin: 6px 0 0 0;"><?php echo number_format($totalPetugas); ?></p>
        </div>

        <div class="stat-card-admin">
            <p class="form-label" style="margin: 0;">Total Sampah</p>
            <p style="font-size: 28px; font-weight: 800; color: #fff; margin: 6px 0 0 0;"><?php echo round($totalSampah, 1); ?> <span style="font-size: 14px; color: #52525b; font-weight: 400;">Kg</span></p>
        </div>
    </div>

    <div class="grid-2-kolom-charts">
        <div>
            <div class="section-title-box" style="margin-bottom: 14px;">
                <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">Grafik Tren Setoran Sampah</h2>
                <p style="font-size: 12px; color: #52525b; margin: 4px 0 0 0;">Visualisasi total berat setoran sampah (Kg) yang berhasil diproses</p>
            </div>
            
            <div class="chart-container-box" style="height: 340px; display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($data_grafik)): ?>
                    <div style="position: relative; width: 100%; height: 100%;">
                        <canvas id="chartSetoran"></canvas>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: #52525b; font-style: italic; font-size: 13px;">Belum ada data setoran sampah yang berstatus selesai untuk dimuat ke dalam grafik.</div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="section-title-box" style="margin-bottom: 14px;">
                <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">Proporsi Peran Pengguna</h2>
                <p style="font-size: 12px; color: #52525b; margin: 4px 0 0 0;">Perbandingan distribusi total akun Petugas Lapangan vs Nasabah</p>
            </div>
            
            <div class="chart-container-box" style="height: 340px; display: flex; align-items: center; justify-content: center;">
                <div style="position: relative; width: 100%; height: 100%; max-height: 280px;">
                    <canvas id="chartRasioUser"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 10px;">
        <div class="section-title-box" style="margin-bottom: 14px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">🏆 Peringkat Komoditas Terpopuler</h2>
            <p style="font-size: 12px; color: #52525b; margin: 4px 0 0 0;">5 Jenis barang/sampah yang paling sering disetor berdasarkan volume bobot akumulatif</p>
        </div>

        <div class="rank-container-box" style="overflow-x: auto;">
            <table class="table-rank">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Rank</th>
                        <th>ID Sampah</th>
                        <th>Nama Komoditas Barang</th>
                        <th>Kategori</th>
                        <th style="text-align: right;">Total Muatan</th>
                        <th style="text-align: right;">Frekuensi Setor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    if(!empty($rank_sampah)):
                        foreach($rank_sampah as $row): 
                    ?>
                        <tr>
                            <td style="text-align: center;"><span class="badge-rank">#<?= $rank++; ?></span></td>
                            <td style="font-family: monospace; font-weight: bold;"><?= $row['id_sampah']; ?></td>
                            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['nama_sampah']); ?></td>
                            <td><?= htmlspecialchars($row['kategori'] ?? 'Lainnya'); ?></td>
                            <td style="text-align: right; font-weight: bold; color: #8ae61a; font-family: monospace;"><?= number_format($row['total_bobot'], 1, ',', '.'); ?> Kg</td>
                            <td style="text-align: right; font-family: monospace;"><?= $row['frekuensi_setor']; ?> ×</td>
                        </tr>
                    <?php 
                        endforeach; 
                    else:
                    ?>
                        <tr>
                            <td colspan="6" style="text-align: center; font-style: italic; padding: 30px 0;">Belum ada transaksi selesai untuk memetakan peringkat.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // 1. GENERATE GRAFIK TREN SETORAN (LINE CHART)
    (function() {
        let chartInterval = setInterval(function() {
            const ctxSetoran = document.getElementById('chartSetoran');

            if (typeof Chart !== 'undefined' && ctxSetoran) {
                clearInterval(chartInterval);

                let instanceSetoran = Chart.getChart(ctxSetoran);
                if (instanceSetoran) { instanceSetoran.destroy(); }

                new Chart(ctxSetoran, {
                    type: 'line', 
                    data: {
                        labels: <?php echo json_encode($label_grafik); ?>,
                        datasets: [{
                            label: 'Berat Sampah (Kg)',
                            data: <?php echo json_encode($data_grafik); ?>,
                            borderColor: '#8ae61a', 
                            backgroundColor: 'rgba(138, 230, 26, 0.05)', 
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#8ae61a',
                            pointRadius: 4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#a1a1aa', font: { size: 11 } } }
                        },
                        scales: {
                            x: { grid: { color: 'rgba(255, 255, 255, 0.01)' }, ticks: { color: '#52525b', font: { size: 10 } } },
                            y: { grid: { color: 'rgba(255, 255, 255, 0.03)' }, ticks: { color: '#52525b', font: { size: 10 } }, beginAtZero: true }
                        }
                    }
                });
            }
        }, 100);
    })();

    // 2. GENERATE GRAFIK RASIO USER (PIE CHART)
    (function() {
        let pieInterval = setInterval(function() {
            const ctxUser = document.getElementById('chartRasioUser');

            if (typeof Chart !== 'undefined' && ctxUser) {
                clearInterval(pieInterval);

                let instanceUser = Chart.getChart(ctxUser);
                if (instanceUser) { instanceUser.destroy(); }

                new Chart(ctxUser, {
                    type: 'pie',
                    data: {
                        labels: ['Petugas', 'Nasabah'],
                        datasets: [{
                            data: [<?php echo $totalPetugas; ?>, <?php echo $totalNasabah; ?>],
                            backgroundColor: [
                                '#fbbf24', // Kuning Amber untuk Petugas
                                '#8ae61a'  // Hijau Limau untuk Nasabah
                            ],
                            borderWidth: 1,
                            borderColor: '#0e0e0e'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#a1a1aa', font: { size: 12 } }
                            }
                        }
                    }
                });
            }
        }, 100);
    })();
</script>
</body>
</html>