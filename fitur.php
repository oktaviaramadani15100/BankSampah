<?php
// Memanggil komponen header sliding premium kustom kelompok
include 'header.php';
?>

<style>
    .landing-page-body {
        background-color: #09090b;
        color: #f4f4f5;
        font-family: 'Segoe UI', system-ui, sans-serif;
        margin: 0;
        padding: 0;
    }

    .section-wrapper {
        padding: 140px 24px 80px 24px;
        max-width: 1200px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .section-title-center {
        text-align: center;
        margin-bottom: 54px;
    }

    .section-title-center h1 {
        font-size: 36px;
        font-weight: 800;
        letter-spacing: -0.03em;
        margin: 0;
        color: #ffffff;
    }

    .section-title-center p {
        font-size: 14px;
        color: #52525b;
        margin: 8px 0 0 0;
    }

    .grid-features-layout {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }

    .premium-feature-card {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        padding: 36px;
        border-radius: 24px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .premium-feature-card:hover {
        transform: translateY(-6px);
        border-color: #8ae61a;
        box-shadow: 0 0 25px rgba(138, 230, 26, 0.15);
    }

    .feature-icon-badge {
        font-size: 28px;
        color: #8ae61a;
        margin-bottom: 20px;
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .premium-feature-card:hover .feature-icon-badge {
        transform: scale(1.15) rotate(5deg);
    }

    .premium-feature-card h3 {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 12px 0;
        color: #ffffff;
    }

    .premium-feature-card p {
        font-size: 13px;
        color: #a1a1aa;
        line-height: 1.6;
        margin: 0;
    }
</style>

<div class="landing-page-body">
    <main class="section-wrapper">
        <div class="section-title-center">
            <h1>Fitur Unggulan Platform</h1>
            <p>Solusi teknologi modern untuk memudahkan tata kelola sanitasi dan finansial digital Anda</p>
        </div>

        <div class="grid-features-layout">
            <div class="premium-feature-card">
                <div class="feature-icon-badge">🌱</div>
                <h3>Konversi Saldo E-Wallet</h3>
                <p>Setiap timbangan komoditas sampah divalidasi secara digital oleh petugas, lalu otomatis dikonversi menjadi saldo keuntungan finansial riil.</p>
            </div>

            <div class="premium-feature-card">
                <div class="feature-icon-badge">⚖️</div>
                <h3>Pencatatan Otomatis 24/7</h3>
                <p>Pantau riwayat rekap log berat muatan, kalkulasi nominal tarif, dan status penimbangan kas masuk secara transparan kapan saja.</p>
            </div>

            <div class="premium-feature-card">
                <div class="feature-icon-badge">🔒</div>
                <h3>Pencairan Multi-Metode</h3>
                <p>Cairkan akumulasi kas tabungan Anda secara fleksibel, mulai dari uang tunai lapangan hingga e-wallet populer (DANA, OVO, GoPay).</p>
            </div>
        </div>
    </main>
</div>

</body>
</html>