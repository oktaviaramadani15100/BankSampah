<?php
// Ambil data halaman aktif untuk penanda class navigasi
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nasabah - Bank Sampah</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../css/nasabah.css">
    
    <style>
        /* Struktur Navigasi Utama Nasabah Pengganti Tailwind */
        .main-navbar-nasabah {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background-color: #09090b;
            border-bottom: 1px solid #1f1f23;
            padding: 14px 24px;
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .main-navbar-nasabah {
                flex-direction: row;
                gap: 0;
            }
        }

        .nav-logo-group {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .nav-logo-group {
                width: auto;
            }
        }

        .logo-box-green {
            background: linear-gradient(135deg, #65a30d, #4ade80);
            padding: 8px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(101, 163, 13, 0.2);
        }

        .brand-text-wrapper {
            display: flex;
            flex-direction: column;
        }

        .brand-main-title {
            font-size: 15px;
            font-weight: 900;
            color: #f4f4f5;
            letter-spacing: -0.02em;
        }

        .brand-sub-title {
            font-size: 9px;
            color: #52525b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-links-nasabah {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            width: 100%;
            padding-bottom: 4px;
            scrollbar-width: none; /* Sembunyikan scrollbar Firefox */
        }

        .nav-links-nasabah::-webkit-scrollbar {
            display: none; /* Sembunyikan scrollbar Chrome/Safari */
        }

        @media (min-width: 768px) {
            .nav-links-nasabah {
                width: auto;
                padding-bottom: 0;
            }
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #a1a1aa;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 12px;
            border: 1px solid transparent;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .nav-item-link:hover, .nav-item-link.active {
            color: #8ae61a;
            background-color: rgba(138, 230, 26, 0.05);
            border-color: rgba(138, 230, 26, 0.15);
        }

        .logout-btn-nasabah {
            color: #a1a1aa;
            padding: 8px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            text-decoration: none;
        }

        .logout-btn-nasabah:hover {
            color: #f43f5e;
            background-color: rgba(244, 63, 94, 0.05);
        }
    </style>
</head>
<body class="bg-zinc-950 text-white antialiased">

<header class="main-navbar-nasabah">
    <div class="nav-logo-group">
        <div class="logo-box-green">
            <span style="font-size: 20px;">🌱</span>
        </div>
        <div class="brand-text-wrapper">
            <span class="brand-main-title">Bank Sampah</span>
            <span class="brand-sub-title">E-Wallet Apps</span>
        </div>
    </div>

    <nav class="nav-links-nasabah">
        <a href="dashboard.php" class="nav-item-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
        </a>

        <a href="transaksi.php" class="nav-item-link <?= ($current_page == 'transaksi.php') ? 'active' : '' ?>">
            <i class="fas fa-plus"></i> <span>Setoran Mandiri</span>
        </a>

        <a href="saldo.php" class="nav-item-link <?= ($current_page == 'saldo.php') ? 'active' : '' ?>">
            <i class="fas fa-wallet"></i> <span>Saldo Tabungan</span>
        </a>

        <a href="riwayat.php" class="nav-item-link <?= ($current_page == 'riwayat.php') ? 'active' : '' ?>">
            <i class="fas fa-history"></i> <span>Riwayat</span>
        </a>

        <a href="pencairan.php" class="nav-item-link <?= ($current_page == 'pencairan.php') ? 'active' : '' ?>">
            <i class="fas fa-money-bill-wave"></i> <span>Tarik Dana</span>
        </a>

        <a href="../login.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi?')" class="logout-btn-nasabah" title="Keluar Akun">
            <i class="fas fa-sign-out-alt" style="font-size: 14px;"></i>
        </a>
    </nav>
</header>