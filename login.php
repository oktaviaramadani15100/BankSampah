<?php 
// 1. Memanggil header.php bertema hijau gelap
include 'header.php'; 

// Kredensial otomatis dinonaktifkan / dikosongkan agar pengguna memasukkan akun secara mandiri
$default_email = '';
$default_pass = '';
?>

<link rel="stylesheet" href="css/nasabah.css">

<style>
    /* SOLUSI UTAMA: Mengatur ulang padding atas agar boks tidak tertutup header */
    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        /* Padding atas disesuaikan dengan tinggi h-20 (80px) + 60px ruang napas */
        padding: 140px 16px 48px 16px; 
        background-color: #09090b;
        box-sizing: border-box;
    }

    .auth-card {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 24px;
        padding: 40px;
        max-width: 440px;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* Animasi Glow Hijau Limau Ketika Form Disentuh Kursor (Hover) */
    .auth-card:hover {
        border-color: #8ae61a !important;
        box-shadow: 0 0 25px rgba(138, 230, 26, 0.2);
    }

    .brand-icon-box {
        background-color: #8ae61a;
        padding: 12px;
        border-radius: 16px;
        display: inline-block;
        margin-bottom: 12px;
    }

    .header-text h2 {
        font-size: 24px;
        font-weight: 700;
        color: #f4f4f5;
        margin: 0;
    }

    .header-text p {
        font-size: 13px;
        color: #52525b;
        margin: 4px 0 0 0;
    }

    .form-login {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 28px;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
    }

    .input-field-wrapper {
        position: relative;
        width: 100%;
    }

    .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #52525b;
        display: flex;
        align-items: center;
        pointer-events: none;
    }

    .input-field {
        width: 100%;
        background-color: #050505;
        border: 1px solid #1f1f23;
        border-radius: 12px;
        padding: 14px 16px 14px 46px;
        font-size: 13px;
        color: #f4f4f5;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-field:focus {
        border-color: #8ae61a;
        box-shadow: 0 0 8px rgba(138, 230, 26, 0.1);
    }

    .btn-login-submit {
        width: 100%;
        padding: 14px;
        background-color: #8ae61a;
        border: none;
        color: #09090b;
        font-weight: 700;
        font-size: 14px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 10px;
        box-shadow: 0 4px 12px rgba(138, 230, 26, 0.15);
        transition: all 0.2s ease;
    }

    .btn-login-submit:hover {
        background-color: #7cd015;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(138, 230, 26, 0.25);
    }

    .footer-note {
        margin-top: 24px;
        text-align: center;
        font-size: 11px;
        color: #3f3f46;
        line-height: 1.6;
    }

    .footer-note a {
        color: #52525b;
        text-decoration: none;
    }

    .footer-note a:hover {
        color: #a1a1aa;
        text-decoration: underline;
    }
</style>

<main class="login-wrapper">
    <div class="auth-card">
        
        <div style="text-align: center;">
            <div class="brand-icon-box">
                <span style="font-size: 20px;">🌱</span>
            </div>
            <div class="header-text">
                <h2>Bank Sampah</h2>
                <p>Silakan masuk ke akun Portal Anda</p>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'gagal'): ?>
            <div style="margin-top: 16px; background-color: rgba(244, 63, 94, 0.05); border: 1px solid #7f1d1d; color: #f43f5e; padding: 12px; font-size: 12px; border-radius: 12px; text-align: center;">
                <span>⚠️ Gagal masuk! Email atau Kata Sandi Anda keliru.</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'registrasi_sukses'): ?>
            <div style="margin-top: 16px; background-color: rgba(52, 211, 153, 0.05); border: 1px solid #064e3b; color: #34d399; padding: 12px; font-size: 12px; border-radius: 12px; text-align: center;">
                <span>✓ Pendaftaran berhasil! Silakan masuk dengan akun Anda.</span>
            </div>
        <?php endif; ?>

        <form action="login_proses.php" method="POST" class="form-login">
            
            <div class="input-group">
                <label class="form-label" style="font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase;">Email</label>
                <div class="input-field-wrapper">
                    <span class="input-icon">✉️</span>
                    <input type="email" id="email" name="email" value="<?= $default_email; ?>" placeholder="Masukkan alamat email" class="input-field" required>
                </div>
            </div>

            <div class="input-group">
                <label class="form-label" style="font-size: 11px; font-weight: 600; color: #a1a1aa; text-transform: uppercase;">Kata Sandi</label>
                <div class="input-field-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password" value="<?= $default_pass; ?>" placeholder="••••••••" class="input-field" required>
                </div>
            </div>

            <button type="submit" class="btn-login-submit">
                <span>Masuk Sekarang</span> →
            </button>
        </form>

        <p class="footer-note">
            Dengan masuk, Anda menyetujui <a href="#">Syarat Layanan</a> dan <a href="#">Kebijakan Privasi</a> kami
        </p>
    </div>
</main>

</body>
</html>