<?php
session_start();
// Proteksi Akses Halaman Nasabah
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'warga') {
    header("Location: ../login.php");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

$id_nasabah = $_SESSION['user_id'];
$notif = "";
$msg_error = "";

// 1. AMBIL SALDO TERSEDIA AKTIF SEBAGAI BATAS MAKSIMAL KLAIM PENARIKAN
$saldo_tersedia = 0;
try {
    $stmt_s = $koneksi->prepare("SELECT saldo_tersedia FROM saldo WHERE id_nasabah = :id");
    $stmt_s->execute([':id' => $id_nasabah]);
    $saldo_tersedia = (int)$stmt_s->fetchColumn() ?? 0;
} catch (Exception $e) {
    // Default 0
}

// 2. PROSES FORM INPUT KLAIM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tarik_dana') {
    $jumlah_dana = (int)$_POST['jumlah_dana'];
    $metode      = trim($_POST['metode']);

    if ($jumlah_dana <= 0) {
        $notif = "gagal";
        $msg_error = "Nominal penarikan dana harus bernilai positif!";
    } elseif ($jumlah_dana > $saldo_tersedia) {
        $notif = "gagal";
        $msg_error = "Saldo Anda tidak mencukupi untuk melakukan penarikan nominal ini!";
    } else {
        try {
            // Generator ID Pencairan VARCHAR (Contoh: WD-2026-0001)
            $queryMax = $koneksi->query("SELECT MAX(id_pencairan) AS max_id FROM `pencairan`");
            $idLast = $queryMax->fetch(PDO::FETCH_ASSOC)['max_id'];
            $newId = 'WD-' . date('Y') . '-0001';
            if ($idLast) {
                $noUrut = (int) substr($idLast, 8, 4);
                $noUrut++;
                $newId = 'WD-' . date('Y') . '-' . sprintf('%04d', $noUrut);
            }

            // Insert data klaim status 'menunggu' untuk diproses petugas di kasir lapangan
            $stmt = $koneksi->prepare("INSERT INTO pencairan (id_pencairan, id_nasabah, id_petugas, jumlah_dana, metode, status, tanggal_pengajuan, tanggal_verifikasi) VALUES (:id, :id_nsb, NULL, :dana, :metode, 'menunggu', NOW(), NULL)");
            $stmt->execute([
                ':id' => $newId,
                ':id_nsb' => $id_nasabah,
                ':dana' => $jumlah_dana,
                ':metode' => $metode
            ]);

            $notif = "sukses";
            $saldo_tersedia -= $jumlah_dana;
        } catch (Exception $e) {
            $notif = "gagal";
            $msg_error = "Terjadi kendala sistem pada server basis data kelompok.";
        }
    }
}
?>

<!-- Link Jalur Hubung Kustom CSS Nasabah -->
<link rel="stylesheet" href="../css/nasabah.css">

<style>
    /* Perbaikan Jarak Atas Kontainer Agar Tidak Tertelan Header Sticky */
    .dashboard-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 60px 20px 40px 20px;
        display: flex;
        flex-direction: column;
        gap: 28px;
        box-sizing: border-box;
    }

    /* KOTAK HIJAU GRADASI SALDO ELEGAN */
    .card-saldo-neon {
        max-width: 600px;
        width: 100%;
        margin: 0 auto;
        background: linear-gradient(135deg, #8ae61a, #5cb00d);
        border: none;
        border-radius: 24px;
        padding: 26px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(138, 230, 26, 0.25);
        box-sizing: border-box;
        transition: transform 0.3s ease;
    }
    .card-saldo-neon:hover {
        transform: translateY(-2px);
    }
    .card-saldo-neon .label-saldo {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #09090b;
        opacity: 0.7;
        margin: 0;
    }
    .card-saldo-neon .value-saldo {
        font-size: 36px;
        font-weight: 900;
        color: #09090b;
        margin: 8px 0 0 0;
        letter-spacing: -0.02em;
    }

    /* FORM CARD CONTAINER */
    .form-card-box {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 24px;
        padding: 40px;
        max-width: 600px;
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-card-box:hover {
        border-color: #8ae61a !important;
        box-shadow: 0 0 25px rgba(138, 230, 26, 0.15);
    }

    .input-custom-field {
        width: 100%;
        background-color: #050505;
        border: 1px solid #1f1f23;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 14px;
        color: #f4f4f5;
        outline: none;
        box-sizing: border-box;
        font-weight: bold;
        transition: border-color 0.2s;
    }
    .input-custom-field:focus {
        border-color: #8ae61a;
    }

    /* GRID RADIO METODE PEMBAYARAN KUSTOM */
    .radio-grid-methods {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-top: 8px;
    }
    @media (min-width: 480px) {
        .radio-grid-methods {
            grid-template-columns: 1fr 1fr; /* Membagi dua pilihan baris */
        }
    }

    .radio-method-label {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        background-color: #050505;
        border: 1px solid #1f1f23;
        padding: 14px 16px;
        border-radius: 14px;
        cursor: pointer;
        color: #a1a1aa;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        user-select: none;
    }

    /* Menyembunyikan input radio asli */
    .radio-method-label input[type="radio"] {
        display: none;
    }

    /* Desain Kotak Lingkaran Indikator Kustom */
    .radio-custom-dot {
        width: 16px;
        height: 16px;
        border: 2px solid #3f3f46;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        flex-shrink: 0;
    }

    /* Style Ketika Kartu Dipilih (Checked State) */
    .radio-method-label:has(input[type="radio"]:checked) {
        border-color: #8ae61a;
        color: #fff;
        background-color: rgba(138, 230, 26, 0.02);
    }

    .radio-method-label:has(input[type="radio"]:checked) .radio-custom-dot {
        border-color: #8ae61a;
    }

    .radio-method-label:has(input[type="radio"]:checked) .radio-custom-dot::after {
        content: "";
        position: absolute;
        width: 8px;
        height: 8px;
        background-color: #8ae61a;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .method-icon {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    .btn-submit-pencairan {
        width: 100%;
        padding: 15px;
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
        margin-top: 24px;
        box-shadow: 0 4px 12px rgba(138, 230, 26, 0.15);
        transition: all 0.2s ease;
    }
    .btn-submit-pencairan:hover {
        background-color: #7cd015;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(138, 230, 26, 0.25);
    }
</style>

<div class="dashboard-container">
    <div class="welcome-section">
        <div>
            <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Tarik Dana Tabungan</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Cairkan akumulasi keuntungan digital Anda menjadi uang fisik atau saldo dompet digital secara instan</p>
        </div>
    </div>

    <!-- BOX TABUNGAN GRADASI HIJAU LIMAU NEON -->
    <div class="card-saldo-neon">
        <p class="label-saldo">Saldo Dompet Tersedia Saat Ini</p>
        <p class="value-saldo">Rp <?= number_format($saldo_tersedia, 0, ',', '.'); ?></p>
    </div>

    <?php if ($notif === 'sukses'): ?>
        <div style="background-color: rgba(52, 211, 153, 0.05); border: 1px solid #064e3b; color: #34d399; padding: 16px; border-radius: 12px; font-size: 13px; font-weight: 600; max-width: 600px; margin: 0 auto;">
            ✓ <strong>Pengajuan Berhasil Terkirim!</strong> Berkas klaim Anda telah masuk ke dalam antrean kas keluar. Silakan temui petugas kasir cabang untuk serah terima dana tunai fisik.
        </div>
    <?php elseif ($notif === 'gagal'): ?>
        <div style="background-color: rgba(244, 63, 94, 0.05); border: 1px solid #7f1d1d; color: #f43f5e; padding: 16px; border-radius: 12px; font-size: 13px; font-weight: 600; max-width: 600px; margin: 0 auto;">
            ⚠️ <strong>Gagal Menarik Dana!</strong> <?= htmlspecialchars($msg_error); ?>
        </div>
    <?php endif; ?>

    <!-- BOX FORM UTAMA -->
    <div class="form-card-box">
        <form action="pencairan.php" method="POST">
            <input type="hidden" name="action" value="tarik_dana">

            <!-- Input Nominal -->
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;">
                <label class="form-label" style="color: #a1a1aa;">Nominal Pencairan (Rupiah)</label>
                <input type="number" name="jumlah_dana" min="1000" max="<?= $saldo_tersedia; ?>" placeholder="Masukkan nominal angka (Contoh: 50000)" class="input-custom-field" required>
            </div>

            <!-- Input Radio Grid Pilihan Metode Pembayaran Finansial -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label class="form-label" style="color: #a1a1aa;">Metode Penarikan Finansial</label>
                
                <div class="radio-grid-methods">
                    <!-- Opsi 1: Cash -->
                    <label class="radio-method-label">
                        <input type="radio" name="metode" value="Cash" checked>
                        <span class="radio-custom-dot"></span>
                        <span class="method-icon" style="color: #ffc107;"><i class="fas fa-money-bill-wave"></i></span>
                        <span>Uang Tunai Fisik (Cash)</span>
                    </label>

                    <!-- Opsi 2: DANA -->
                    <label class="radio-method-label">
                        <input type="radio" name="metode" value="DANA">
                        <span class="radio-custom-dot"></span>
                        <span class="method-icon" style="color: #29b6f6;"><i class="fas fa-mobile-alt"></i></span>
                        <span>Dompet Digital DANA</span>
                    </label>

                    <!-- Opsi 3: OVO -->
                    <label class="radio-method-label">
                        <input type="radio" name="metode" value="OVO">
                        <span class="radio-custom-dot"></span>
                        <span class="method-icon" style="color: #7e57c2;"><i class="fas fa-wallet"></i></span>
                        <span>Dompet Digital OVO</span>
                    </label>

                    <!-- Opsi 4: GoPay -->
                    <label class="radio-method-label">
                        <input type="radio" name="metode" value="Gopay">
                        <span class="radio-custom-dot"></span>
                        <span class="method-icon" style="color: #26a69a;"><i class="fas fa-coins"></i></span>
                        <span>Dompet Digital GoPay</span>
                    </label>

                    <!-- Opsi 5: Transfer Bank -->
                    <label class="radio-method-label" style="grid-column: span 1;">
                        <input type="radio" name="metode" value="Transfer Bank">
                        <span class="radio-custom-dot"></span>
                        <span class="method-icon" style="color: #f4f4f5;"><i class="fas fa-university"></i></span>
                        <span>Transfer Rekening Bank</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit-pencairan">
                💵 <span>Ajukan Klaim Kas Keluar</span>
            </button>
        </form>
    </div>
</div>

</body>
</html>