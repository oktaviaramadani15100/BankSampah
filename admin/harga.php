<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

include 'header.php';
require_once '../config/koneksi.php';

$notif = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_harga') {
    $id_sampah  = $_POST['id_sampah'];
    $harga      = (int)$_POST['harga'];
    $satuan     = trim($_POST['satuan']);

    try {
        $stmt = $koneksi->prepare("UPDATE sampah SET harga_perkg = :harga, satuan = :sat WHERE id_sampah = :id");
        $stmt->execute([
            ':harga' => $harga,
            ':sat' => $satuan,
            ':id'    => $id_sampah
        ]);
        $notif = "sukses";
    } catch (Exception $e) {
        $notif = "gagal";
    }
}


try {
    $stmt = $koneksi->query("SELECT s.*, k.nama_kategori AS kategori FROM sampah s LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori ORDER BY k.nama_kategori ASC, s.nama_sampah ASC");
    $list_sampah = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Gagal memuat database sampah: " . $e->getMessage());
}
?>


<link rel="stylesheet" href="assets/css/responsive.css">


<style>


header, .admin-header {
    position: fixed !important;
    top: 20;
    left: 0;
    width: 100% !important;
    z-index: 999;
    box-sizing: border-box;
    background-color: #050505;
    border-bottom: 1px solid var(--border-color);
    height: 75px; 
}


@media (max-width: 991px) {
    header, .admin-header {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
    box-sizing: border-box;

    padding: 110px 20px 40px 20px; 
}


@media (max-width: 767px) {
    .dashboard-container {

        padding-top: 5px; 
    }
}
:root {
    --bg-main: #09090b;
    --bg-card: #0e0e0e;
    --bg-darker: #050505;
    --border-color: #1f1f23;
    --text-primary: #f4f4f5;
    --text-secondary: #a1a1aa;
    --text-muted: #52525b;
    --lime-primary: #8ae61a;
    --lime-hover: #7cd015;
    --lime-glow: rgba(138, 230, 26, 0.25);
    --emerald-alert: #34d399;
    --rose-alert: #f43f5e;
    --amber-alert: #ffc107;
}



header, .admin-header {
    position: fixed !important;
    top: 20;
    left: 0;
    width: 100% !important;
    z-index: 999;
    box-sizing: border-box;
    background-color: #050505;
    border-bottom: 1px solid var(--border-color);
    height: 70px; 
}


@media (max-width: 991px) {
    header, .admin-header {
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
    box-sizing: border-box;

    padding: 110px 20px 40px 20px; 
}


@media (max-width: 767px) {
    .dashboard-container {
     
        padding-top: 100px; 
    }
}

.page-header {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 16px;
    margin-bottom: 8px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: #fff;
    margin: 0;
}

.page-header p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 4px 0 0 0;
}

.form-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
}


.alert {
    padding: 14px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 12px;
    line-height: 1.5;
    box-sizing: border-box;
}

.alert-sukses {
    background-color: rgba(52, 211, 153, 0.05);
    border: 1px solid rgba(52, 211, 153, 0.2);
    color: var(--emerald-alert);
}

.alert-warning {
    background-color: rgba(244, 63, 94, 0.05);
    border: 1px solid rgba(244, 63, 94, 0.2);
    color: var(--rose-alert);
}


.table-wrapper {
    width: 100%;
    overflow-x: auto;
    background-color: rgba(255, 255, 255, 0.01);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-sizing: border-box;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 13px;
    min-width: 600px;
}

.custom-table th {
    padding: 16px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.05em;
    background-color: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid var(--border-color);
}

.custom-table td {
    padding: 16px;
    border-bottom: 1px solid rgba(31, 31, 35, 0.6);
    color: #d4d4d8;
    vertical-align: middle;
}

.custom-table tr:last-child td {
    border-bottom: none;
}

.custom-table tbody tr {
    transition: all 0.25s ease-in-out;
}

.custom-table tbody tr:hover td {
    background-color: rgba(138, 230, 26, 0.01) !important;
    border-top-color: var(--lime-primary);
    border-bottom-color: var(--lime-primary);
    box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.05);
}


.btn-action-primary {
    background-color: var(--lime-primary);
    color: var(--bg-main);
    font-weight: 700;
    padding: 12px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 13px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(138, 230, 26, 0.15);
    transition: all 0.2s ease-in-out;
}

.btn-action-primary:hover {
    background-color: var(--lime-hover);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px var(--lime-glow);
}


.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(4px);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}

.modal-content-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    width: 100%;
    max-width: 440px;
    border-radius: 20px;
    padding: 24px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    transition: border-color 0.3s, box-shadow 0.3s;
}

.modal-content-box:hover {
    border-color: var(--lime-primary);
    box-shadow: 0 0 20px var(--lime-glow);
}
    .custom-table tbody tr {
        transition: all 0.25s ease-in-out;
    }
    .custom-table tbody tr:hover td {
        background-color: rgba(138, 230, 26, 0.02) !important;
        border-top-color: #8ae61a;
        border-bottom-color: #8ae61a;
        box-shadow: inset 0 0 10px rgba(138, 230, 26, 0.1);
    }
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(4px);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="dashboard-container">
    
    <div class="page-header">
        <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Pengaturan Nilai Tukar Harga</h1>
        <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Kelola nominal rupiah dan acuan satuan komoditas sampah secara real-time</p>
    </div>


    <?php if ($notif === 'sukses'): ?>
        <div class="alert alert-sukses">
            ✓ Tarif harga produk sampah berhasil diperbarui. Perubahan langsung diterapkan pada sisi nasabah.
        </div>
    <?php elseif ($notif === 'gagal'): ?>
        <div class="alert alert-warning" style="background-color: rgba(244, 63, 94, 0.05); border-color: #7f1d1d; color: #f43f5e;">
            ⚠️ Gagal memperbarui harga produk. Sila periksa kembali server database Anda.
        </div>
    <?php endif; ?>


    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Nama Komoditas</th>
                    <th>Satuan Acuan</th>
                    <th>Harga Beli Aplikasi</th>
                    <th style="text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_sampah as $row): ?>
                    <tr>
                        <td>
                            <span style="background-color: #050505; border: 1px solid #1f1f23; color: #a1a1aa; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 4px; text-transform: uppercase;">
                                <?= htmlspecialchars($row['kategori'] ?? ''); ?>
                            </span>
                        </td>
                        <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['nama_sampah']); ?></td>
                        <td style="font-family: monospace; color: #52525b; text-transform: uppercase;"><?= htmlspecialchars($row['satuan']); ?></td>
                        <td style="font-weight: bold; color: #8ae61a; font-family: monospace;">Rp <?= number_format($row['harga_perkg'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;">
                            <button 
                                type="button"
                                class="btn-ubah-tarif"
                                style="background: #050505; border: 1px solid #1f1f23; color: #ccc; padding: 6px 12px; font-weight: bold; font-size: 11px; border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                                data-id="<?= $row['id_sampah']; ?>"
                                data-nama="<?= htmlspecialchars($row['nama_sampah']); ?>"
                                data-harga="<?= $row['harga_perkg']; ?>"
                                data-satuan="<?= htmlspecialchars($row['satuan']); ?>"
                            >
                                ⚙️ Ubah Tarif
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="modal-harga" class="modal-backdrop-custom" style="display: none;">
    <div style="background: #0e0e0e; border: 1px solid #1f1f23; width: 100%; max-width: 420px; border-radius: 20px; padding: 24px; box-sizing: border-box; display: flex; flex-direction: column; gap: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1f1f23; padding-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Ubah Tarif Harga Sampah</h3>
            <button type="button" onclick="closeHargaModal()" style="background: none; border: none; color: #52525b; font-size: 14px; font-weight: bold; cursor: pointer;">✕</button>
        </div>
        
        <form action="harga.php" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="action" value="update_harga">
            <input type="hidden" id="form-id" name="id_sampah" value="">

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label class="form-label">Nama Produk (Terkunci)</label>
                <input type="text" id="form-nama" style="width: 100%; padding: 12px; background: #050505; border: 1px solid #1f1f23; border-radius: 10px; color: #52525b; font-size: 13px; cursor: not-allowed; outline: none;" readonly>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label class="form-label" style="color: #a1a1aa;">Harga Baru (Rp)</label>
                    <input type="number" id="form-harga" name="harga" style="width: 100%; padding: 12px; background: #050505; border: 1px solid #1f1f23; border-radius: 10px; color: #fff; font-size: 13px; font-weight: bold; outline: none;" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label class="form-label" style="color: #a1a1aa;">Satuan Ukuran</label>
                    <select id="form-satuan" name="satuan" style="width: 100%; padding: 12px; background: #050505; border: 1px solid #1f1f23; border-radius: 10px; color: #fff; font-size: 13px; outline: none; cursor: pointer;" required>
                        <option value="kg">kg</option>
                        <option value="botol">botol</option>
                        <option value="unit">unit</option>
                        <option value="set">set</option>
                        <option value="lembar">lembar</option>
                        <option value="biji">biji</option>
                        <option value="liter">liter</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeHargaModal()" style="padding: 10px 16px; background: transparent; border: 1px solid #1f1f23; color: #a1a1aa; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-action-primary" style="background-color: #8ae61a; color: #09090b; padding: 10px 16px; border: none; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer;">Simpan Tarif</button>
            </div>
        </form>
    </div>
</div>

<script>
const mHarga = document.getElementById('modal-harga');
const fId = document.getElementById('form-id');
const fNama = document.getElementById('form-nama');
const fHarga = document.getElementById('form-harga');
const fSatuan = document.getElementById('form-satuan');


document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-ubah-tarif')) {
        mHarga.style.display = 'flex';
        
        fId.value = e.target.getAttribute('data-id');
        fNama.value = e.target.getAttribute('data-nama');
        fHarga.value = e.target.getAttribute('data-harga');
        fSatuan.value = e.target.getAttribute('data-satuan');
    }
});

function closeHargaModal() {
    mHarga.style.display = 'none';
}
</script>
</body>
</html>