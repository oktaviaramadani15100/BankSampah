<?php 
// 1. Memanggil header.php bertema hijau gelap
include 'header.php'; 

// 2. Menentukan role registrasi (?role=nasabah atau ?role=petugas)
$role = isset($_GET['role']) ? $_GET['role'] : 'nasabah';
if (!in_array($role, ['nasabah', 'petugas'])) {
    $role = 'nasabah';
}

// 3. Data mockup dinamis untuk value input
$mock_name = ($role === 'petugas') ? 'Oktavia ramadani' : 'Dias Anugrah Anggraini';
$mock_phone = '081234567890';
$mock_address = 'Depok, Jawa Barat';
$mock_email = ($role === 'petugas') ? 'okta@gmail.com' : 'dias@gmail.com';
?>

<link rel="stylesheet" href="css/nasabah.css">

<style>
    /* Styling Khusus Lembar Autentikasi Registrasi Bertema Gelap & Efek Glow */
    .register-wrapper {
    /* Menggunakan 100vh penuh agar background tetap menutup satu halaman */
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    
    /* Perbaikan: Padding atas diubah menjadi 140px sebagai bantalan jarak dari header fixed */
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

    /* Animasi Glow Hijau Limau Ketika Form Disentuh */
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

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        font-size: 11px;
        font-weight: 600;
        color: #52525b;
        margin: 24px 0;
    }

    .step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #050505;
        border: 1px solid #1f1f23;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .step-circle.active {
        background-color: #8ae61a;
        color: #09090b;
        font-weight: bold;
        border-color: #8ae61a;
    }

    .step-line {
        width: 48px;
        height: 1px;
        background-color: #1f1f23;
    }

    .form-register {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .input-container {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #52525b;
    }

    .input-field {
        width: 100%;
        background-color: #050505;
        border: 1px solid #1f1f23;
        border-radius: 12px;
        padding: 12px 14px 12px 42px;
        font-size: 13px;
        color: #f4f4f5;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }

    .input-field:focus {
        border-color: #8ae61a;
    }

    .bonus-box {
        background-color: rgba(138, 230, 26, 0.02);
        border: 1px solid #1a3a06;
        padding: 16px;
        border-radius: 16px;
        font-size: 12px;
    }

    .bonus-box p {
        margin: 0 0 6px 0;
    }

    .footer-link {
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #1f1f23;
        text-align: center;
        font-size: 12px;
        color: #52525b;
    }

    .footer-link a {
        color: #8ae61a;
        text-decoration: none;
        font-weight: 600;
    }

    .footer-link a:hover {
        text-decoration: underline;
    }
</style>

<main class="register-wrapper">
    <div class="auth-card">
        
        <div style="text-align: center;">
            <div class="brand-icon-box">
                <span style="font-size: 20px;">🌱</span>
            </div>
            <div class="header-text">
                <h2>Daftar Bank Sampah</h2>
                <p>Mulai mengubah sampah menjadi penghasilan hari ini</p>
            </div>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-warning" style="margin-top: 16px; background-color: rgba(244, 63, 94, 0.05); border-color: #7f1d1d; color: #f43f5e;">
                <?php if ($_GET['status'] === 'email_terdaftar'): ?>
                    <span>⚠️ Gagal! Alamat email sudah digunakan oleh akun lain.</span>
                <?php elseif ($_GET['status'] === 'password_salah'): ?>
                    <span>⚠️ Gagal! Kata sandi dan Konfirmasi kata sandi tidak cocok.</span>
                <?php elseif ($_GET['status'] === 'sistem_error'): ?>
                    <span>⚠️ Gagal! Terjadi kesalahan internal pada kueri basis data kelompok.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="step-indicator">
            <div style="text-align: center;"><div class="step-circle active">1</div><span style="color:#fff; display:block; margin-top:4px;">Data Diri</span></div>
            <div class="step-line" style="margin-top: -14px;"></div>
            <div style="text-align: center;"><div class="step-circle">2</div><span style="display:block; margin-top:4px;">Akun</span></div>
            <div class="step-line" style="margin-top: -14px;"></div>
            <div style="text-align: center;"><div class="step-circle">3</div><span style="display:block; margin-top:4px;">Verifikasi</span></div>
        </div>

        <form action="register_proses.php" method="POST" class="form-register">
            <input type="hidden" name="role" value="<?= $role; ?>">

            <div class="input-container">
                <label class="form-label">Nama Lengkap</label>
                <span class="input-icon">👤</span>
                <input type="text" name="nama_lengkap" value="<?= $mock_name; ?>" class="input-field" required>
            </div>

            <div class="input-container">
                <label class="form-label">Nomor WhatsApp</label>
                <span class="input-icon">📞</span>
                <input type="text" name="no_whatsapp" value="<?= $mock_phone; ?>" class="input-field" required>
            </div>

           <?php if ($role === 'petugas'): ?>
                <div class="input-container">
                    <label class="form-label">Lokasi Penempatan Cabang</label>
                    <span class="input-icon">📍</span>
                    <select name="lokasi_penempatan" class="input-field" style="padding-left:42px; appearance:none; cursor:pointer;" required>
                        <option value="" disabled>-- Pilih Wilayah Cabang --</option>
                        <option value="Bank Sampah Pusat">Bank Sampah Pusat</option>
                        <option value="Bank Sampah Jakarta Timur" selected>Bank Sampah Jakarta Timur</option>
                        <option value="Bank Sampah Jakarta Barat">Bank Sampah Jakarta Barat</option>
                        <option value="Bank Sampah Jakarta Selatan">Bank Sampah Jakarta Selatan</option>
                        <option value="Bank Sampah Jakarta Utara">Bank Sampah Jakarta Utara</option>
                        <option value="Bank Sampah Jakarta Pusat">Bank Sampah Jakarta Pusat</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="input-container">
                <label class="form-label">Alamat Lengkap</label>
                <span class="input-icon" style="top:38px;">🏠</span>
                <textarea name="alamat_lengkap" rows="2" class="input-field" style="padding-left:42px; padding-top:10px;" required><?= $mock_address; ?></textarea>
            </div>

            <div class="input-container">
                <label class="form-label">Email</label>
                <span class="input-icon">✉️</span>
                <input type="email" name="email" value="<?= $mock_email; ?>" class="input-field" required>
            </div>

            <div class="input-container">
                <label class="form-label">Kata Sandi</label>
                <span class="input-icon">🔒</span>
                <input type="password" name="password" placeholder="Masukan kata sandi baru" class="input-field" required>
            </div>

            <div class="input-container">
                <label class="form-label">Konfirmasi Kata Sandi</label>
                <span class="input-icon">🔒</span>
                <input type="password" name="konfirmasi_password" placeholder="Ulangi kata sandi" class="input-field" required>
            </div>

            <?php if ($role === 'nasabah'): ?>
                <div class="bonus-box">
                    <p style="font-weight: 700; color: #fff;">🎁 Bonus Pendaftaran:</p>
                    <p style="color: var(--lime-primary);">✓ Rp 50.000 untuk setoran pertama</p>
                    <p style="color: var(--lime-primary);">✓ 500 poin reward gratis</p>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-daftar-premium" style="
    width: 100%; padding: 14px; font-size: 14px; font-weight: 800; background-color: #8ae61a; color: #09090b; border: none;
    border-radius: 12px; cursor: pointer;  box-shadow: 0 4px 14px rgba(138, 230, 26, 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
" onmouseover="this.style.backgroundColor='#7cd015'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(138, 230, 26, 0.35)';" onmouseout="this.style.backgroundColor='#8ae61a'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(138, 230, 26, 0.15)';">
    Daftar Sekarang 
</button>
        </form>

        
    </div>
</main>
</body>
</html>