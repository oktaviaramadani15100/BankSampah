<?php
session_start();
// Proteksi Akses Halaman Petugas
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'petugas') {
    header("Location: ../login.php?role=petugas");
    exit();
}

require_once '../config/koneksi.php'; 

$cabang_petugas = isset($_SESSION['user_extra']) ? trim($_SESSION['user_extra']) : '';
$notif = "";

// A. PROSES BACKEND: FINALISASI TIMBANGAN & UPDATE AKUMULASI TABEL SALDO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalkan_setoran') {
    $id_kunjungan = $_POST['id_kunjungan'];

    try {
        $koneksi->beginTransaction();

        // 1. Ambil hitungan total nominal uang dan id_nasabah dari detail setoran ini
        $sql_calc = "SELECT SUM(ds.subtotal) as total, ts.id_nasabah 
                     FROM detail_setoran ds
                     JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
                     WHERE ds.id_setoran = :id
                     GROUP BY ts.id_setoran";
        $stmt_calc = $koneksi->prepare($sql_calc);
        $stmt_calc->execute([':id' => $id_kunjungan]);
        $res_calc = $stmt_calc->fetch(PDO::FETCH_ASSOC);

        if ($res_calc) {
            $total_pendapatan   = (int)$res_calc['total'];
            $id_nasabah         = $res_calc['id_nasabah'];
            
            $total_masuk_bersih = $total_pendapatan;
            $poin_baru          = (int)($total_masuk_bersih * 0.1); 

            $id_petugas_login = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['id_petugas']) ? $_SESSION['id_petugas'] : null); 

            // 2. Update status setoran MENJADI selesai DAN catat id_petugas penanggung jawab
            $stmt_update = $koneksi->prepare("UPDATE transaksi_setoran 
                                              SET status = 'selesai', 
                                                  id_petugas = :id_petugas 
                                              WHERE id_setoran = :id");
            $stmt_update->execute([
                ':id_petugas' => $id_petugas_login, 
                ':id'         => $id_kunjungan
            ]);

            // 3. SINKRONISASI AKUMULASI SALDO NASABAH
            $sql_saldo = "INSERT INTO saldo (id_nasabah, saldo_tersedia, total_pemasukan, total_pengeluaran, point_reward) 
                          VALUES (:id_w, :masuk, :pemasukan, 0, :poin)
                          ON DUPLICATE KEY UPDATE 
                            saldo_tersedia = saldo_tersedia + :masuk_update,
                            total_pemasukan = total_pemasukan + :pemasukan_update,
                            point_reward = point_reward + :poin_update";
            
            $stmt_saldo = $koneksi->prepare($sql_saldo);
            $stmt_saldo->execute([
                ':id_w'             => $id_nasabah,
                ':masuk'            => $total_masuk_bersih,
                ':pemasukan'        => $total_masuk_bersih,
                ':poin'             => $poin_baru,
                ':masuk_update'     => $total_masuk_bersih,
                ':pemasukan_update' => $total_masuk_bersih,
                ':poin_update'      => $poin_baru
            ]);
        }

        $koneksi->commit();
        header("Location: verifikasi.php?status=timbang_sukses");
        exit();
    } catch (Exception $e) {
        $koneksi->rollBack();
        die("Gagal memproses akumulasi saldo: " . $e->getMessage());
    }
}

// B. ENDPOINT API MINI (AJAX FETCH)
if (isset($_GET['get_detail_id'])) {
    header('Content-Type: application/json');
    $id_req = $_GET['get_detail_id'];
    
    $sql_json = "SELECT ds.id_detail, s.nama_sampah AS nama_produk, ds.berat_kg AS perkiraan_jumlah, ds.harga_perkg AS harga, s.satuan
                 FROM detail_setoran ds
                 JOIN sampah s ON ds.id_sampah = s.id_sampah
                 WHERE ds.id_setoran = :id";
    $stmt_json = $koneksi->prepare($sql_json);
    $stmt_json->execute([':id' => $id_req]);
    $items = $stmt_json->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($items);
    exit(); 
}

// C. QUERY DATA ANTREAN VERIFIKASI UTAMA (STATUS: 'diproses')
$list_verifikasi = [];
try {
    $sql_list = "SELECT ts.id_setoran AS id_kunjungan, ts.created_at, n.nama_lengkap, 
                        SUM(ds.subtotal) as estimasi_total,
                        GROUP_CONCAT(CONCAT(s.nama_sampah, ' (', ds.berat_kg, ' ', s.satuan, ')') SEPARATOR ', ') as ringkasan
                 FROM transaksi_setoran ts
                 JOIN nasabah n ON ts.id_nasabah = n.id_nasabah
                 JOIN detail_setoran ds ON ts.id_setoran = ds.id_setoran
                 JOIN sampah s ON ds.id_sampah = s.id_sampah
                 WHERE ts.status = 'diproses' AND ts.cabang_lokasi = :cabang
                 GROUP BY ts.id_setoran
                 ORDER BY ts.created_at DESC";
    $stmt_list = $koneksi->prepare($sql_list);
    $stmt_list->execute([':cabang' => $cabang_petugas]);
    $list_verifikasi = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_verifikasi = [];
}

if (isset($_GET['status']) && $_GET['status'] === 'timbang_sukses') {
    $notif = "sukses";
}

include 'header.php';
?>

<link rel="stylesheet" href="../css/petugas.css">

<style>
    /* -------------------------------------------------------------
       KUSTOMISASI UTAMA & WARNA VARIABEL (TEMA DARK + NEON GLOW)
    ---------------------------------------------------------------- */
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
     
        padding-top: 200px; 
    }
}
    :root {
        --neon-green: #8ae61a;
        --neon-glow: rgba(138, 230, 26, 0.25);
        --dark-bg: #0d0d11;
        --card-bg: #0f0e13;
        --border-color: #1f1f2a;
        --text-muted: #71717a;
    }

    .container-main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #e4e4e7;
    }

    .row-layout {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 30px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .row-layout {
            grid-template-columns: 1fr;
        }
    }

    /* -------------------------------------------------------------
       PAGE HEADER & ALERT COMPONENTS
    ---------------------------------------------------------------- */
    .page-header h1 {
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 5px 0;
        letter-spacing: -0.02em;
        color: #fff;
    }

    .page-header p {
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 25px;
    }

    .alert-sukses {
        background: rgba(138, 230, 26, 0.08);
        border: 1px solid rgba(138, 230, 26, 0.3);
        color: var(--neon-green);
        padding: 16px;
        border-radius: 12px;
        font-size: 13px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .info-banner {
        background: #14141d;
        border: 1px solid var(--border-color);
        padding: 20px;
        border-radius: 14px;
        margin-bottom: 20px;
    }

    .banner-title {
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 4px;
    }

    /* -------------------------------------------------------------
       CARD ANTREAN (KIRI) & HOVER EFFECTS
    ---------------------------------------------------------------- */
    .list-wrapper {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .card-antrean {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 20px;
        border-radius: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-antrean:hover {
        transform: translateY(-3px);
        border-color: var(--neon-green) !important;
        box-shadow: 0 0 20px var(--neon-glow), 0 6px 20px rgba(0,0,0,0.4);
    }

    .card-body-left h3 {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 6px 0;
    }

    .summary-text {
        font-size: 12px;
        color: var(--text-muted);
        margin: 0 0 10px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 380px;
    }

    .badge-status {
        background: rgba(255, 193, 7, 0.08);
        border: 1px solid rgba(255, 193, 7, 0.2);
        color: #ffc107;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .card-body-right {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .price-text {
        font-size: 18px;
        font-weight: 800;
        color: var(--neon-green);
        font-family: monospace;
    }

    .time-text {
        font-size: 11px;
        color: var(--text-muted);
    }

    /* -------------------------------------------------------------
       PANEL VALIDASI (KANAN)
    ---------------------------------------------------------------- */
    .panel-box {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 28px;
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0,0,0,0.5);
        min-height: 380px;
        transition: all 0.3s ease;
    }

    .empty-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--text-muted);
        font-style: italic;
        font-size: 13px;
        border: 1px dashed var(--border-color);
        padding: 40px;
    }

    .panel-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 18px;
        margin-bottom: 22px;
    }

    .panel-tag {
        font-size: 10px;
        background: rgba(138, 230, 26, 0.1);
        color: var(--neon-green);
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .panel-header h3 {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        margin: 10px 0 4px 0;
    }

    #panel-id-kunjungan {
        font-size: 12px;
        color: var(--text-muted);
        font-family: monospace;
        margin: 0;
    }

    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: #a1a1aa;
        margin-bottom: 12px;
        display: block;
    }

    /* Checkbox list container */
    .checkbox-container {
        max-height: 240px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .checkbox-item {
        background: #07070a;
        border: 1px solid var(--border-color);
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .checkbox-item:hover {
        border-color: #52525b;
        background: #111116;
    }

    /* -------------------------------------------------------------
       TOTAL BOX & BUTTONS
    ---------------------------------------------------------------- */
    .total-box {
        background: linear-gradient(135deg, #111a08, #070c03);
        border: 1px solid rgba(138, 230, 26, 0.2);
        padding: 18px;
        border-radius: 12px;
        margin-top: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    #panel-grand-total {
        font-size: 22px;
        font-weight: 800;
        color: var(--neon-green);
        font-family: monospace;
    }

    .alert-warning {
        background: rgba(255, 193, 7, 0.04);
        border-left: 3px solid #ffc107;
        color: #d4d4d8;
        padding: 12px;
        font-size: 11px;
        border-radius: 4px;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .btn-submit-disabled {
        width: 100%;
        background: #16161f;
        border: 1px solid #2d2d3d;
        color: #52525b;
        padding: 14px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 12px;
        cursor: not-allowed;
        transition: all 0.2s ease;
    }

    .btn-submit-active {
        width: 100%;
        background: var(--neon-green);
        color: #000;
        border: none;
        padding: 14px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 4px 15px rgba(138, 230, 26, 0.2);
    }

    .btn-submit-active:hover {
        background: #9cf51d;
        box-shadow: 0 0 20px rgba(138, 230, 26, 0.5);
        transform: translateY(-1px);
    }
</style>

<div class="container-main">
    <div class="row-layout">
        
        <div class="col-kiri">
            <div class="page-header">
                <h1>Verifikasi Setoran</h1>
                <p>Periksa dan validasi data muatan timbangan nyata dari lapangan</p>
            </div>

            <?php if ($notif === 'sukses'): ?>
                <div class="alert alert-sukses">
                    ✓ <strong>Setoran Muatan Terkonfirmasi!</strong> Nominal uang hasil timbangan resmi diakumulasikan ke E-Wallet saldo digital nasabah.
                </div>
            <?php endif; ?>

            <div class="info-banner">
                <div class="banner-title">Transaksi Menunggu Verifikasi</div>
                <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">
                    Terdapat <strong style="color: #fff;"><?= count($list_verifikasi); ?></strong> transaksi antrean aktif di cabang ini. Klik baris kartu untuk memproses peninjauan.
                </p>
            </div>

            <div class="list-wrapper">
                <?php if (empty($list_verifikasi)): ?>
                    <div class="empty-state" style="background:#09090b; border: 1px dashed #222; text-align:center; padding:40px 20px; font-size:13px; color:var(--text-muted); border-radius:14px; font-style:italic;">
                        Tidak ada antrean setoran penimbangan yang menunggu verifikasi di pos ini.
                    </div>
                <?php else: foreach ($list_verifikasi as $row): ?>
                    <div 
                        onclick="muatDetailVerifikasi('<?= $row['id_kunjungan']; ?>', '<?= htmlspecialchars($row['nama_lengkap']); ?>', '<?= number_format($row['estimasi_total'], 0, ',', '.'); ?>')"
                        class="card-antrean"
                        id="card-<?= $row['id_kunjungan']; ?>"
                    >
                        <div class="card-body-left">
                            <h3>ID: <?= $row['id_kunjungan']; ?> — <?= htmlspecialchars($row['nama_lengkap']); ?></h3>
                            <p class="summary-text" title="<?= htmlspecialchars($row['ringkasan']); ?>"><?= htmlspecialchars($row['ringkasan']); ?></p>
                            <span class="badge-status">⏳ Menunggu Validasi Petugas</span>
                        </div>
                        <div class="card-body-right">
                            <span class="price-text">Rp <?= number_format($row['estimasi_total'], 0, ',', '.'); ?></span>
                            <span class="time-text"><?= date('H:i A', strtotime($row['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="col-kanan" style="margin-top: 68px;">
            <div id="panel-kosong" class="panel-box empty-panel">
                <p>💡 Pilih salah satu kartu antrean di sebelah kiri untuk meninjau lembar kesesuaian fisik komoditas timbangan nasabah.</p>
            </div>

            <div id="panel-detail" class="panel-box" style="display:none;">
                <div class="panel-header">
                    <span class="panel-tag">Lembar Validasi</span>
                    <h3 id="panel-nama-nasabah">Nasabah</h3>
                    <p id="panel-id-kunjungan">ID: KJN-2026-0000</p>
                </div>

                <form action="verifikasi.php" method="POST" onsubmit="return validasiSebelumKirim()">
                    <input type="hidden" name="action" value="finalkan_setoran">
                    <input type="hidden" id="input-hidden-id" name="id_kunjungan" value="">

                    <div class="form-group">
                        <label class="form-label">Ceklis Kesesuaian Fisik Komoditas:</label>
                        <div id="wrapper-checkbox-list" class="checkbox-container"></div>
                    </div>

                    <div class="total-box">
                        <span style="font-weight: 600; color: #a1a1aa;">Total Dana Cair</span>
                        <span id="panel-grand-total">Rp 0</span>
                    </div>

                    <div class="alert alert-warning">
                        <span>Pastikan Anda telah memeriksa kesesuaian item dan mencentang seluruh list di atas sebelum melepas dana digital ke dompet nasabah.</span>
                    </div>

                    <button type="submit" id="btn-konfirmasi-setoran" class="btn-submit-disabled" disabled>
                        Konfirmasi & Cairkan Saldo
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    function muatDetailVerifikasi(idKunjungan, namaNasabah, grandTotal) {
        document.querySelectorAll('.card-antrean').forEach(c => {
            c.style.borderColor = 'var(--border-color)';
            c.style.backgroundColor = 'var(--card-bg)';
            c.style.boxShadow = 'none';
        });
        const activeCard = document.getElementById('card-' + idKunjungan);
        activeCard.style.borderColor = 'var(--neon-green)';
        activeCard.style.backgroundColor = 'rgba(138, 230, 26, 0.02)';
        activeCard.style.boxShadow = '0 0 15px rgba(138, 230, 26, 0.1)';

        document.getElementById('panel-kosong').style.display = 'none';
        document.getElementById('panel-detail').style.display = 'block';

        document.getElementById('panel-nama-nasabah').textContent = namaNasabah;
        document.getElementById('panel-id-kunjungan').textContent = "ID Kunjungan: " + idKunjungan;
        document.getElementById('panel-grand-total').textContent = "Rp " + grandTotal;
        document.getElementById('input-hidden-id').value = idKunjungan;

        const containerCheckbox = document.getElementById('wrapper-checkbox-list');
        containerCheckbox.innerHTML = `<div style="text-align:center; padding:30px; font-size:13px; color:var(--text-muted);">Menghubungkan database...</div>`;

        fetch(`verifikasi.php?get_detail_id=${idKunjungan}`)
            .then(response => response.json())
            .then(items => {
                containerCheckbox.innerHTML = ''; 
                items.forEach((item) => {
                    const div = document.createElement('div');
                    div.className = "checkbox-item";
                    
                    div.innerHTML = `
                        <input type="checkbox" id="item-${item.id_detail}" class="ceklis-verifikasi" style="margin-top:3px; width: 15px; height: 15px; accent-color: var(--neon-green); cursor:pointer;">
                        <label for="item-${item.id_detail}" style="font-size:12px; color:#a1a1aa; cursor:pointer; flex:1; line-height: 1.4;">
                            <strong style="color:#fff; display:block; font-size:13px; margin-bottom:2px;">${item.nama_produk}</strong>
                            Muatan Nyata: <span style="color:var(--neon-green); font-weight:bold;">${parseFloat(item.perkiraan_jumlah)} ${item.satuan}</span> • Nilai: <span style="color:#fff; font-weight:600;">Rp ${(item.perkiraan_jumlah * item.harga).toLocaleString('id-ID')}</span>
                        </label>
                    `;
                    div.addEventListener('click', function(e) {
                        if (e.target.type !== 'checkbox' && e.target.tagName !== 'LABEL') {
                            const chk = this.querySelector('input[type="checkbox"]');
                            chk.checked = !chk.checked;
                            triggerCheckState();
                        }
                    });
                    containerCheckbox.appendChild(div);
                });
                
                document.querySelectorAll('.ceklis-verifikasi').forEach(cb => {
                    cb.addEventListener('change', triggerCheckState);
                });
                triggerCheckState(); 
            });
    }

    function triggerCheckState() {
        const allCheckboxes = document.querySelectorAll('.ceklis-verifikasi');
        const checkedBoxes  = document.querySelectorAll('.ceklis-verifikasi:checked');
        const btnSubmit     = document.getElementById('btn-konfirmasi-setoran');

        if (allCheckboxes.length > 0 && allCheckboxes.length === checkedBoxes.length) {
            btnSubmit.disabled = false;
            btnSubmit.className = "btn-submit-active";
            btnSubmit.style.cursor = "pointer";
        } else {
            btnSubmit.disabled = true;
            btnSubmit.className = "btn-submit-disabled";
            btnSubmit.style.cursor = "not-allowed";
        }
    }

    function validasiSebelumKirim() {
        return confirm('Konfirmasi semua muatan barang nasabah? Dana nominal hasil timbangan akan langsung dikirim dan menambah total e-wallet saldo digital nasabah.');
    }
</script>
</body>
</html>