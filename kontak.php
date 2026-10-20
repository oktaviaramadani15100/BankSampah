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
        padding: 140px 24px 100px 24px;
        max-width: 1100px;
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

    .contact-columns-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 32px;
    }

    @media (min-width: 768px) {
        .contact-columns-layout {
            grid-template-columns: 5fr 7fr;
        }
    }

    .info-sidebar-box {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 24px;
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .info-item-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .info-icon-square {
        width: 44px;
        height: 44px;
        background-color: #050505;
        border: 1px solid #1f1f23;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8ae61a;
        font-size: 16px;
        flex-shrink: 0;
    }

    .info-text-group h5 {
        margin: 0;
        font-size: 13px;
        color: #ffffff;
        font-weight: 700;
    }

    .info-text-group p {
        margin: 2px 0 0 0;
        font-size: 12px;
        color: #a1a1aa;
    }

    .form-inputs-card {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        padding: 36px;
        border-radius: 24px;
    }

    .form-grid-flex {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 16px;
    }

    .contact-field-style {
        width: 100%;
        background-color: #050505;
        border: 1px solid #1f1f23;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 13px;
        color: #ffffff;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }

    .contact-field-style:focus {
        border-color: #8ae61a;
    }

    .btn-send-message {
        width: 100%;
        padding: 14px;
        background-color: #8ae61a;
        border: none;
        color: #09090b;
        font-weight: 700;
        font-size: 13px;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(138, 230, 26, 0.1);
        transition: background-color 0.2s;
    }

    .btn-send-message:hover {
        background-color: #7cd015;
    }
</style>

<div class="landing-page-body">
    <main class="section-wrapper">
        <div class="section-title-center">
            <h1>Hubungi Layanan Kontak</h1>
            <p>Punya pertanyaan, kendala sistem e-wallet, atau rencana kemitraan operasional? Hubungi kami</p>
        </div>

        <div class="contact-columns-layout">
            <div class="info-sidebar-box">
                <div class="info-item-row">
                    <div class="info-icon-square">📍</div>
                    <div class="info-text-group">
                        <h5>Alamat Kantor Konsol</h5>
                        <p>Kawasan Operasional Jakarta Timur, DKI Jakarta, Indonesia</p>
                    </div>
                </div>
                <div class="info-item-row">
                    <div class="info-icon-square">💬</div>
                    <div class="info-text-group">
                        <h5>WhatsApp Layanan Hub</h5>
                        <p>0895-3144-0967</p>
                    </div>
                </div>
            </div>
            
            <div class="form-inputs-card">
                <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="form-grid-flex">
                        <input type="text" placeholder="Nama Lengkap" class="contact-field-style" required>
                        <input type="email" placeholder="Alamat Email" class="contact-field-style" required>
                    </div>
                    <textarea placeholder="Tuliskan isi pertanyaan atau detail kendala sistem Anda di sini..." class="contact-field-style" style="height: 130px; resize: none;" required></textarea>
                    <button type="submit" class="btn-send-message">Kirim Pesan Kotak Hub</button>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>