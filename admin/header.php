<?php
// Mendapatkan nama file yang sedang aktif di folder admin
$current_page = basename($_SERVER['SCRIPT_NAME']);
$nama_admin = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Bank Sampah</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <style>
        /* Struktur Navigasi Utama Admin Bertema Dark SaaS Premium */
        .main-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #09090b;
            border-bottom: 1px solid #1f1f23;
            padding: 16px 24px;
            position: sticky;
            top: 10;
            z-index: 50;
        }

        .nav-logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-box {
            background-color: #8ae61a;
            padding: 8px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-box svg {
            width: 16px;
            height: 16px;
            stroke: #000;
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

        .badge-role-admin {
            font-size: 9px;
            color: #f43f5e;
            font-weight: 800;
            text-transform: uppercase;
            background-color: rgba(244, 63, 94, 0.08);
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid rgba(244, 63, 94, 0.15);
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
        }

        .logout-link:hover {
            color: #f43f5e;
            background-color: rgba(244, 63, 94, 0.05);
        }
    </style>
</head>
<body>

<header class="main-navbar">
    <div class="nav-logo-section">
        <div class="logo-box">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A8.99 8.99 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
            </svg>
        </div>
        <div class="brand-name-group">
            <span class="brand-title">Bank Sampah</span>
            <span class="brand-subtitle">Console Admin</span>
        </div>
    </div>

    <nav class="nav-links-center">
        <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-pie-chart"></i> Dashboard
        </a>
        <a href="nasabah.php" class="<?= ($current_page == 'nasabah.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Nasabah
        </a>
        <a href="petugas.php" class="<?= ($current_page == 'petugas.php') ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> Petugas
        </a>
        <a href="sampah.php" class="<?= ($current_page == 'sampah.php') ? 'active' : '' ?>">
            <i class="fas fa-recycle"></i> Sampah
        </a>
        <a href="harga.php" class="<?= ($current_page == 'harga.php') ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Harga
        </a>
        <a href="laporan.php" class="<?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
            <i class="fas fa-file-excel"></i> Laporan
        </a>
    </nav>
    
    <div class="nav-profile-right">
        <div class="profile-info">
            <p class="profile-name"><?= htmlspecialchars($nama_admin); ?></p>
            <span class="badge-role-admin">Administrator</span>
        </div>
        <div class="divider-line"></div>
        <a href="../login.php?logout=true" onclick="return confirm('Apakah Anda yakin ingin keluar dari panel admin?')" class="logout-link" title="Keluar">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</header>