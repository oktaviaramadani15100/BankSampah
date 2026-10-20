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
        max-width: 760px;
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

    .accordion-group-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .accordion-item-box {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 16px;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .accordion-item-box:hover {
        border-color: #3f3f46;
    }

    .accordion-trigger-btn {
        width: 100%;
        padding: 20px 24px;
        background: none;
        border: none;
        text-align: left;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        outline: none;
    }

    .accordion-arrow-span {
        font-size: 11px;
        color: #52525b;
        transition: transform 0.3s ease;
    }

    .accordion-hidden-panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0, 1, 0, 1);
        background-color: #050505;
        color: #a1a1aa;
        font-size: 13px;
        line-height: 1.6;
    }

    .accordion-hidden-panel p {
        padding: 20px 24px;
        margin: 0;
    }

    /* Kondisi aktif dikontrol lewat JavaScript */
    .accordion-item-box.active {
        border-color: #8ae61a;
    }

    .accordion-item-box.active .accordion-hidden-panel {
        max-height: 300px;
        transition: max-height 0.3s cubic-bezier(1, 0, 1, 0);
    }

    .accordion-item-box.active .accordion-arrow-span {
        transform: rotate(180deg);
        color: #8ae61a;
    }
</style>

<div class="landing-page-body">
    <main class="section-wrapper">
        <div class="section-title-center">
            <h1>Pertanyaan Sering Diajukan (FAQ)</h1>
            <p>Temukan jawaban instan seputar regulasi operasional sistem e-wallet bank sampah</p>
        </div>

        <div class="accordion-group-list">
            <div class="accordion-item-box">
                <button class="accordion-trigger-btn">
                    <span>Apa saja jenis komoditas sampah yang bisa ditukar jadi saldo?</span>
                    <span class="accordion-arrow-span">▼</span>
                </button>
                <div class="accordion-hidden-panel">
                    <p>Kami menerima komoditas anorganik bernilai jual tinggi, seperti botol plastik bekas (PET/PP), kardus tebal, kertas HVS sisa, kaleng aluminium minuman, besi tua rongsok, kaca jendela bekas, hingga minyak jelantah sisa dapur.</p>
                </div>
            </div>

            <div class="accordion-item-box">
                <button class="accordion-trigger-btn">
                    <span>Berapa minimal limit penarikan dana tabungan digital?</span>
                    <span class="accordion-arrow-span">▼</span>
                </button>
                <div class="accordion-hidden-panel">
                    <p>Batas minimal penarikan dana tabungan digital sangat rendah, yaitu mulai dari Rp 1.000 rupiah saja. Pihak nasabah dibebaskan menarik dana keuntungan kapan saja melalui petugas kasir.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Logika Ekspansi Accordion FAQ Murni
    document.querySelectorAll('.accordion-trigger-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const currentItem = btn.parentElement;
            currentItem.classList.toggle('active');
        });
    });
</script>

</body>
</html>