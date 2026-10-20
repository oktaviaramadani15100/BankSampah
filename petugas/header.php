<?php
// Mendapatkan nama file yang sedang aktif di folder petugas
$current_page = basename($_SERVER['SCRIPT_NAME']);
$nama_petugas = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Petugas Lapangan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Petugas - Bank Sampah</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../css/petugas.css">
    
    <style>
        /* Struktur Navigasi Utama Petugas Bertema Dark SaaS Premium */
        .main-navbar-petugas {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #09090b;
            border-bottom: 1px solid #1f1f23;
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-box-amber {
            background-color: #8ae61a;
            padding: 8px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-name-group {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .brand-subtitle {
            font-size: 9px;
            color: #52525b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .nav-links-center {
            display: flex;
            gap: 12px;
        }

        .nav-links-center a {
            color: #a1a1aa;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-links-center a:hover, .nav-links-center a.active {
            color: #8ae61a;
            background-color: rgba(138, 230, 26, 0.05);
            border-color: rgba(138, 230, 26, 0.15);
        }

        .nav-profile-right {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 12px;
        }

        .profile-info {
            text-align: right;
        }

        .profile-name {
            font-weight: 700;
            color: #f4f4f5;
            margin: 0;
        }

        .badge-role-petugas {
            font-size: 9px;
            color: #fbbf24;
            font-weight: 800;
            text-transform: uppercase;
            background-color: rgba(251, 191, 36, 0.08);
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid rgba(251, 191, 36, 0.15);
            display: inline-block;
            margin-top: 2px;
        }

        .divider-line {
            width: 1px;
            height: 20px;
            background-color: #1f1f23;
        }

        .logout-link {
            color: #52525b;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .logout-link:hover {
            color: #f43f5e;
            background-color: rgba(244, 63, 94, 0.05);
        }
    </style>
</head>
<body>

<header class="main-navbar-petugas">
    <div class="nav-logo-section">
        <div class="logo-box-amber">
            <span style="font-size: 14px; color: #000;">🌱</span>
        </div>
        <div class="brand-name-group">
            <span class="brand-title">Bank Sampah</span>
            <span class="brand-subtitle">Console Petugas</span>
        </div>
    </div>

    <nav class="nav-links-center">
        <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="penimbangan.php" class="<?= ($current_page == 'penimbangan.php') ? 'active' : '' ?>">
            <i class="fas fa-scale-balanced"></i> Penimbangan
        </a>
        <a href="verifikasi.php" class="<?= ($current_page == 'verifikasi.php') ? 'active' : '' ?>">
            <i class="fas fa-check-circle"></i> Verifikasi
        </a>
        <a href="pencairan.php" class="<?= ($current_page == 'pencairan.php') ? 'active' : '' ?>">
            <i class="fas fa-money-bill-wave"></i> Pencairan
        </a>
    </nav>
    
    <div class="nav-profile-right">
        <div class="profile-info">
            <p class="profile-name"><?= htmlspecialchars($nama_petugas); ?></p>
            <span class="badge-role-petugas">Petugas Lapangan</span>
        </div>
        <div class="divider-line"></div>
        <a href="../login.php?logout=true" onclick="return confirm('Apakah Anda yakin ingin keluar dari panel petugas?')" class="logout-link" title="Keluar">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</header>