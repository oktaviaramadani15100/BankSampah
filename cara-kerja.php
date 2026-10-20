<?php
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
        max-width: 800px;
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
        margin: 0;
        color: #ffffff;
    }

    .section-title-center p {
        font-size: 14px;
        color: #52525b;
        margin: 8px 0 0 0;
    }

    .timeline-steps-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .timeline-step-row {
        display: flex;
        gap: 24px;
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        padding: 28px;
        border-radius: 20px;
        align-items: center;
        transition: border-color 0.25s, transform 0.25s;
    }

    .timeline-step-row:hover {
        border-color: #8ae61a;
        transform: scale(1.01);
    }

    .step-number-circle {
        width: 44px;
        height: 44px;
        background-color: rgba(138, 230, 26, 0.08);
        border: 1px solid rgba(138, 230, 26, 0.2);
        color: #8ae61a;
        font-weight: 800;
        font-size: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .step-text-details h4 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 6px 0;
        color: #ffffff;
    }

    .step-text-details p {
        font-size: 13px;
        color: #a1a1aa;
        margin: 0;
        line-height: 1.6;
    }
</style>

<div class="landing-page-body">
    <main class="section-wrapper">
        <div class="section-title-center">
            <h1>Bagaimana Cara Kerjanya?</h1>
            <p>Langkah mudah berkontribusi menjaga kelestarian lingkungan sekaligus mendapat untung</p>
        </div>

        <div class="timeline-steps-container">
            <div class="timeline-step-row">
                <div class="step-number-circle">1</div>
                <div class="step-text-details">
                    <h4>Pilah Sampah & Buat Rencana Rute</h4>
                    <p>Pisahkan sampah anorganik rumah tangga Anda berdasarkan kategori master (Plastik, Kertas, Logam) lalu daftarkan rencana kedatangan pada sistem Portal Nasabah.</p>
                </div>
            </div>

            <div class="timeline-step-row">
                <div class="step-number-circle">2</div>
                <div class="step-text-details">
                    <h4>Penimbangan Real Lapangan</h4>
                    <p>Bawa muatan produk sisa tersebut ke cabang drop-point terdekat pilihan Anda untuk diverifikasi berat nyata fisiknya oleh petugas timbangan kami.</p>
                </div>
            </div>

            <div class="timeline-step-row">
                <div class="step-number-circle">3</div>
                <div class="step-text-details">
                    <h4>Saldo Masuk & Pencairan Keuntungan</h4>
                    <p>Kas keuntungan rupiah digital langsung dicairkan ke saldo akun Anda secara real-time dan siap untuk ditarik kapan saja.</p>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>