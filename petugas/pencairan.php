<?php
session_start();
// Proteksi Akses Halaman Petugas
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'petugas') {
    header("Location: ../login.php?role=petugas");
    exit();
}

require_once '../config/koneksi.php'; 

$cabang_lokasi = isset($_SESSION['user_extra']) ? trim($_SESSION['user_extra']) : '';
$notif = "";

// A. PROSES BACKEND: APPROVAL STATUS & EKSEKUSI POTONG SALDO NASABAH SECARA RIIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setujui_pencairan') {
    $id_pencairan = trim($_POST['id_pencairan']);

    try {
        $koneksi->beginTransaction();

        $stmt_get = $koneksi->prepare("SELECT id_nasabah, jumlah_dana FROM pencairan WHERE id_pencairan = :id");
        $stmt_get->execute([':id' => $id_pencairan]);
        $data_pencairan = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if ($data_pencairan) {
            $id_nasabah  = $data_pencairan['id_nasabah'];
            $jumlah_dana = (int)$data_pencairan['jumlah_dana'];

            // Update status menjadi 'disetujui' dan catat id_petugas penanggung jawab
            $id_petugas_login = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['id_petugas']) ? $_SESSION['id_petugas'] : null);
            $stmt_update = $koneksi->prepare("UPDATE pencairan SET status = 'disetujui', id_petugas = :id_petugas, tanggal_verifikasi = NOW() WHERE id_pencairan = :id");
            $stmt_update->execute([':id_petugas' => $id_petugas_login, ':id' => $id_pencairan]);

            // POTONG SALDO: Kurangi saldo_tersedia dan naikkan total_pengeluaran di tabel saldo nasabah
            $stmt_potong = $koneksi->prepare("UPDATE saldo SET 
                                                saldo_tersedia = saldo_tersedia - :dana,
                                                total_pengeluaran = total_pengeluaran + :dana2 
                                              WHERE id_nasabah = :id_nasabah");
            $stmt_potong->execute([
                ':dana' => $jumlah_dana,
                ':dana2' => $jumlah_dana,
                ':id_nasabah' => $id_nasabah
            ]);
        }

        $koneksi->commit();
        $notif = "sukses";
    } catch (Exception $e) {
        $koneksi->rollBack();
        $notif = "gagal";
    }
}

// B. QUERY STATISTIK PANEL ATAS
$total_menunggu = 0;
$nominal_menunggu = 0;
$total_selesai = 0;
$nominal_selesai = 0;

try {
    $stmt_wait = $koneksi->query("SELECT COUNT(*) as total, SUM(jumlah_dana) as nominal FROM pencairan WHERE LOWER(status) = 'menunggu'");
    $stat_wait = $stmt_wait->fetch(PDO::FETCH_ASSOC);
    $total_menunggu = $stat_wait['total'] ?? 0;
    $nominal_menunggu = $stat_wait['nominal'] ?? 0;

    $stmt_done = $koneksi->query("SELECT COUNT(*) as total, SUM(jumlah_dana) as nominal FROM pencairan WHERE LOWER(status) = 'disetujui' AND DATE(tanggal_pengajuan) = CURDATE()");
    $stat_done = $stmt_done->fetch(PDO::FETCH_ASSOC);
    $total_selesai = $stat_done['total'] ?? 0;
    $nominal_selesai = $stat_done['nominal'] ?? 0;
} catch (Exception $e) {
    // Muted
}

// C. QUERY DAFTAR ANTREAN GLOBAL
$daftar_pencairan = [];
try {
    $sql_pencairan = "SELECT p.id_pencairan, p.tanggal_pengajuan, p.jumlah_dana, p.status, p.metode, w.nama_lengkap 
                      FROM pencairan p
                      JOIN nasabah w ON p.id_nasabah = w.id_nasabah
                      WHERE LOWER(p.status) = 'menunggu'
                      ORDER BY p.tanggal_pengajuan DESC";
    $stmt_pencairan = $koneksi->query($sql_pencairan);
    $daftar_pencairan = $stmt_pencairan->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $daftar_pencairan = [];
}

include 'header.php';
?>

<link rel="stylesheet" href="../css/petugas.css">

<style>
    /* -------------------------------------------------------------
       KUSTOMISASI UTAMA & WARNA VARIABEL (TEMA DARK + NEON GLOW)
    ---------------------------------------------------------------- */

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
    :root {
        --neon-green: #8ae61a;
        --neon-glow: rgba(138, 230, 26, 0.25);
        --dark-bg: #0d0d11;
        --border-color: #191922;
        --text-muted: #71717a;
    }

    .container-main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    
    /* -------------------------------------------------------------
       STATISTIK CARD (PANEL ATAS)
    ---------------------------------------------------------------- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: linear-gradient(145deg, #0f0e12, #070709);
        border: 1px solid #1f1f25;
        padding: 24px;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Efek Glow & Naik saat Hover pada Stat Card */
    .stat-card:hover {
        transform: translateY(-3px);
        border-color: var(--neon-green);
        box-shadow: 0 0 20px var(--neon-glow), 0 4px 25px rgba(0, 0, 0, 0.4);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-icon.wait {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .stat-icon.done {
        background: rgba(138, 230, 26, 0.1);
        color: var(--neon-green);
        border: 1px solid rgba(138, 230, 26, 0.2);
    }

    /* -------------------------------------------------------------
       PENCAIRAN BOX (LIST ANTREAN)
    ---------------------------------------------------------------- */
    .pencairan-box {
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        padding: 24px;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    /* Efek Animasi Glow Interaktif saat Cursor Menyentuh List Card */
    .pencairan-box:hover {
        transform: translateY(-4px);
        border-color: var(--neon-green) !important;
        box-shadow: 0 0 25px var(--neon-glow), 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    /* Dekorasi garis tipis neon di atas card saat di-hover */
    .pencairan-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--neon-green);
        transform: scaleX(0);
        transition: transform 0.3s ease-in-out;
    }

    .pencairan-box:hover::before {
        transform: scaleX(1);
    }

    /* -------------------------------------------------------------
       BADGES & DETAILS
    ---------------------------------------------------------------- */
    .badge-method {
        background: #16161f;
        border: 1px solid #2d2d3d;
        color: #e4e4e7;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-status-waiting {
        background: rgba(255, 193, 7, 0.06);
        border: 1px solid rgba(255, 193, 7, 0.15);
        color: #ffc107;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* -------------------------------------------------------------
       BUTTONS STYLING
    ---------------------------------------------------------------- */
    .btn-approve-neon {
        background: var(--neon-green);
        color: #000;
        padding: 12px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 10px;
        border: 1px solid var(--neon-green);
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 4px 12px rgba(138, 230, 26, 0.1);
    }

    .btn-approve-neon:hover {
        background: #9cf51d;
        box-shadow: 0 0 15px rgba(138, 230, 26, 0.4);
        transform: translateY(-1px);
    }

    .btn-approve-neon:active {
        transform: translateY(1px);
    }

    /* -------------------------------------------------------------
       ANIMASI PULSA & UTILITY
    ---------------------------------------------------------------- */
    .pulse-dot {
        width: 6px;
        height: 6px;
        background-color: #ffc107;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.9); opacity: 0.6; }
        50% { transform: scale(1.3); opacity: 1; box-shadow: 0 0 8px #ffc107; }
        100% { transform: scale(0.9); opacity: 0.6; }
    }
</style>

<div class="container-main">
    
    <div class="page-header" style="margin-bottom: 30px;">
        <h1 style="font-size: 28px; font-weight: 800; tracking-tight: -0.03em;">Proses Pencairan</h1>
        <p style="color: #71717a; font-size: 14px;">Membayar antrean penarikan tunai saldo nasabah E-Wallet secara global</p>
    </div>

    <?php if ($notif === 'sukses'): ?>
        <div class="alert alert-sukses" style="margin-bottom: 25px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
            ✓ <strong>Approval Berhasil!</strong> Status pengajuan diperbarui dan saldo tabungan nasabah resmi terpotong.
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <p style="font-size: 12px; color: #71717a; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; margin: 0;">Total Menunggu</p>
                <p style="font-size: 28px; font-weight: 800; color: #fff; margin: 6px 0 2px 0;"><?= $total_menunggu; ?> <span style="font-size: 14px; color: #71717a; font-weight: 400;">Antrean</span></p>
                <p style="font-size: 13px; color: #ffc107; font-weight: 600; margin: 0;">Rp <?= number_format($nominal_menunggu, 0, ',', '.'); ?></p>
            </div>
            <div class="stat-icon wait">⏳</div>
        </div>
        <div class="stat-card">
            <div>
                <p style="font-size: 12px; color: #71717a; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; margin: 0;">Selesai Hari Ini</p>
                <p style="font-size: 28px; font-weight: 800; color: #fff; margin: 6px 0 2px 0;"><?= $total_selesai; ?> <span style="font-size: 14px; color: #71717a; font-weight: 400;">Klaim</span></p>
                <p style="font-size: 13px; color: #8ae61a; font-weight: 600; margin: 0;">Rp <?= number_format($nominal_selesai, 0, ',', '.'); ?></p>
            </div>
            <div class="stat-icon done">✓</div>
        </div>
    </div>

    <div class="section-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #1f1f25; padding-bottom: 12px;">
            <div>
                <h2 style="font-size: 18px; color: #fff; font-weight: 700; margin: 0;">Permintaan Menunggu</h2>
                <p style="font-size: 12px; color: #71717a; margin: 4px 0 0 0;">Daftar berkas klaim dana kas keluar aktif</p>
            </div>
            <span style="background: #16161f; color: #fff; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 20px; border: 1px solid #2d2d3d;">
                <?= count($daftar_pencairan); ?> Total Antrean
            </span>
        </div>

        <div class="list-container" style="display: flex; flex-direction: column; gap: 20px;">
            <?php if (empty($daftar_pencairan)): ?>
                <div class="empty-state" style="background: #09090b; border: 1px dashed #222; text-align: center; padding: 50px 20px; font-size: 13px; color: #52525b; border-radius: 16px; font-style: italic;">
                    Tidak ada antrean klaim pencairan dana tunai di dalam database saat ini.
                </div>
            <?php else: foreach ($daftar_pencairan as $row): ?>
                <div class="pencairan-box">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;"><?= htmlspecialchars($row['nama_lengkap']); ?></h3>
                            <p style="font-size: 11px; color: #52525b; margin: 4px 0 0 0; font-family: monospace;">Ref ID: <?= htmlspecialchars($row['id_pencairan']); ?> • Waktu: <?= date('d M Y - H:i', strtotime($row['tanggal_pengajuan'])); ?></p>
                        </div>
                        <span style="font-size: 24px; font-weight: 800; color: #8ae61a; tracking-tight: -0.02em;">Rp <?= number_format($row['jumlah_dana'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <div style="background: #070709; border: 1px solid #16161f; padding: 14px; border-radius: 10px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                        <div>
                            <p style="color: #52525b; margin: 0; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">Tahap Otorisasi</p>
                            <div class="badge-status-waiting" style="margin-top: 4px;">
                                <span class="pulse-dot"></span>
                                <span style="text-transform: capitalize;"><?= htmlspecialchars($row['status']); ?></span>
                            </div>
                        </div>
                        <div style="width: 1px; height: 30px; background: #1f1f25;"></div>
                        <div>
                            <p style="color: #52525b; margin: 0; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">Metode Penarikan</p>
                            <div style="margin-top: 4px;"><span class="badge-method"><?= htmlspecialchars($row['metode']); ?></span></div>
                        </div>
                        <div style="width: 1px; height: 30px; background: #1f1f25;"></div>
                        <div>
                            <p style="color: #52525b; margin: 0; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">Jam Masuk</p>
                            <p style="color: #a1a1aa; font-weight: 600; font-size: 12px; margin: 5px 0 0 0;"><?= date('H:i A', strtotime($row['tanggal_pengajuan'])); ?></p>
                        </div>
                    </div>

                    <form action="pencairan.php" method="POST" onsubmit="return confirm('Setujui pengajuan penarikan ini? Poin saldo riil pada e-wallet nasabah akan langsung dikurangi.');" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <input type="hidden" name="action" value="setujui_pencairan">
                        <input type="hidden" name="id_pencairan" value="<?= $row['id_pencairan']; ?>">
                        
                        <button type="submit" class="btn-submit-active" style="padding: 12px; font-size: 12px; font-weight: 700; border-radius: 10px;">
                            ✓ Setujui & Approve
                        </button>
                        <button type="button" class="btn-review-disabled" style="background: #16161f; border: 1px solid #2d2d3d; color: #52525b; padding: 12px; font-size: 12px; font-weight: 700; border-radius: 10px; cursor: not-allowed; text-align: center;">
                            🛡️ Tinjau Sistem
                        </button>
                    </form>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

</body>
</html>