<?php
session_start();
// Proteksi Akses Halaman Petugas
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'petugas') {
    header("Location: ../login.php?role=petugas");
    exit();
}

// Hubungkan ke koneksi database di folder config
require_once '../config/koneksi.php'; 

// Bersihkan data session lokasi cabang petugas agar sinkron
$cabang_lokasi = isset($_SESSION['user_extra']) ? trim($_SESSION['user_extra']) : '';

// 1. QUERY INDEKS ANTREAN: Ambil setoran status rencana di cabang petugas aktif
$sql_kunjungan = "SELECT ts.id_setoran AS id_kunjungan, ts.tanggal_setoran AS tanggal_kunjungan, n.nama_lengkap 
                  FROM transaksi_setoran ts
                  JOIN nasabah n ON ts.id_nasabah = n.id_nasabah
                  WHERE ts.status = 'rencana' AND ts.cabang_lokasi = :cabang_pos
                  ORDER BY ts.created_at ASC";
$stmt_kunjungan = $koneksi->prepare($sql_kunjungan);
$stmt_kunjungan->execute([':cabang_pos' => $cabang_lokasi]);
$daftar_kunjungan = $stmt_kunjungan->fetchAll(PDO::FETCH_ASSOC);

// 2. QUERY ITEM DETAIL BATCH: Ambil item sampah bawaan
$sql_items = "SELECT ds.id_setoran AS id_kunjungan, ds.id_detail, ds.id_sampah, ds.berat_kg AS perkiraan_jumlah, ds.harga_perkg AS harga, s.nama_sampah AS nama_produk, k.nama_kategori AS kategori, s.satuan
              FROM detail_setoran ds
              JOIN sampah s ON ds.id_sampah = s.id_sampah
              LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori
              JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
              WHERE ts.status = 'rencana' AND ts.cabang_lokasi = :cabang_pos";
$stmt_items = $koneksi->prepare($sql_items);
$stmt_items->execute([':cabang_pos' => $cabang_lokasi]);
$all_mapped_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Format ke bentuk JSON Array agar mudah dibaca oleh client-side JavaScript
$json_items = [];
foreach ($all_mapped_items as $item) {
    $json_items[$item['id_kunjungan']][] = $item;
}

// 3. Ambil semua master data sampah untuk opsi pengganti dinamis di baris tabel
$query_master_sampah = $koneksi->query("SELECT s.id_sampah, s.nama_sampah AS nama_produk, s.harga_perkg AS harga, s.satuan, k.nama_kategori AS kategori 
                                        FROM sampah s 
                                        LEFT JOIN kategori_sampah k ON s.id_kategori = k.id_kategori 
                                        ORDER BY k.nama_kategori ASC, s.nama_sampah ASC");
$master_sampah = $query_master_sampah->fetchAll(PDO::FETCH_ASSOC);

// 4. LOG TIMBANGAN TERAKHIR CABANG
try {
    $sql_terakhir = "SELECT n.nama_lengkap, s.nama_sampah AS nama_produk, ds.berat_kg AS perkiraan_jumlah, ds.subtotal AS total_nominal 
                     FROM detail_setoran ds
                     JOIN transaksi_setoran ts ON ds.id_setoran = ts.id_setoran
                     JOIN sampah s ON ds.id_sampah = s.id_sampah
                     JOIN nasabah n ON ts.id_nasabah = n.id_nasabah
                     WHERE ts.cabang_lokasi = :cabang_pos AND ts.status = 'selesai'
                     ORDER BY ts.tanggal_setoran DESC LIMIT 2";
    $stmt_terakhir = $koneksi->prepare($sql_terakhir);
    $stmt_terakhir->execute([':cabang_pos' => $cabang_lokasi]);
    $daftar_terakhir = $stmt_terakhir->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $daftar_terakhir = [];
}

include 'header.php';
?>

<link rel="stylesheet" href="../css/petugas.css">

<style>
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
    /* -------------------------------------------------------------
       KUSTOMISASI UTAMA & WARNA VARIABEL (TEMA DARK + NEON GLOW)
    ---------------------------------------------------------------- */
    :root {
        --neon-green: #8ae61a;
        --neon-glow: rgba(138, 230, 26, 0.2);
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
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .row-layout {
            grid-template-columns: 1fr;
        }
    }

    /* -------------------------------------------------------------
       PAGE HEADER & FORM STYLING
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

    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #a1a1aa;
        margin-bottom: 8px;
    }

    .input-custom {
        width: 100%;
        background: #07070a;
        border: 1px solid var(--border-color);
        color: #fff;
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .input-custom:focus {
        outline: none;
        border-color: var(--neon-green);
        box-shadow: 0 0 10px var(--neon-glow);
    }

    /* -------------------------------------------------------------
       MAIN WORKSPACE CARD & GLOW ANIMATION
    ---------------------------------------------------------------- */
    .main-workspace-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 28px;
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0,0,0,0.4);
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .main-workspace-card:hover {
        border-color: var(--neon-green);
        box-shadow: 0 0 25px var(--neon-glow), 0 10px 30px rgba(0, 0, 0, 0.6);
    }

    /* -------------------------------------------------------------
       TABEL RINCIAN KOMODITAS
    ---------------------------------------------------------------- */
    .tabel-timbang {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .tabel-timbang th {
        background: #13131a;
        padding: 14px;
        color: #a1a1aa;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--border-color);
    }

    .tabel-timbang td {
        padding: 14px;
        border-bottom: 1px solid #16161f;
        vertical-align: middle;
    }

    .select-row-sampah {
        width: 100%;
        background: #07070a;
        border: 1px solid var(--border-color);
        color: #fff;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 13px;
    }

    .input-row-berat {
        width: 100%;
        background: #07070a;
        border: 1px solid var(--border-color);
        color: var(--neon-green);
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 13px;
        text-align: center;
        font-weight: 700;
    }

    /* -------------------------------------------------------------
       PAPAN KALKULASI
    ---------------------------------------------------------------- */
    .papan-kalkulasi {
        background: linear-gradient(135deg, #111a08, #070c03);
        border: 1px solid rgba(138, 230, 26, 0.2);
        padding: 22px;
        border-radius: 12px;
        margin-top: 25px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* -------------------------------------------------------------
       SIDEBAR & SCROLLABLE SMALL TABLE
    ---------------------------------------------------------------- */
    .sidebar-card-box {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 24px;
        border-radius: 18px;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }

    .sidebar-card-box:hover {
        border-color: var(--neon-green);
        box-shadow: 0 0 20px rgba(138, 230, 26, 0.1);
    }

    .sidebar-card-box h3 {
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 0;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-scroll-wrapper {
        max-height: 250px;
        overflow-y: auto;
        border: 1px solid #16161f;
        border-radius: 10px;
        background: #07070a;
    }

    /* Kustomisasi Scrollbar Keren */
    .table-scroll-wrapper::-webkit-scrollbar {
        width: 6px;
    }
    .table-scroll-wrapper::-webkit-scrollbar-track {
        background: #07070a;
    }
    .table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #22222f;
        border-radius: 10px;
    }
    .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: var(--neon-green);
    }

    .tabel-harga-kecil {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .tabel-harga-kecil th {
        background: #111116;
        padding: 10px 12px;
        color: #71717a;
        font-weight: 600;
        text-align: left;
        position: sticky;
        top: 0;
        z-index: 2;
        border-bottom: 1px solid #16161f;
    }

    .tabel-harga-kecil td {
        padding: 10px 12px;
        border-bottom: 1px solid #111116;
        color: #d4d4d8;
    }

    .tabel-harga-kecil tr:hover td {
        background: #0f0e13;
        color: #fff;
    }

    /* -------------------------------------------------------------
       BUTTON COMPONENT
    ---------------------------------------------------------------- */
    .btn-submit-neon {
        width: 100%;
        background: var(--neon-green);
        color: #000;
        font-weight: 700;
        padding: 15px;
        border-radius: 12px;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 4px 15px rgba(138, 230, 26, 0.2);
    }

    .btn-submit-neon:hover {
        background: #9cf51d;
        box-shadow: 0 0 20px rgba(138, 230, 26, 0.5);
        transform: translateY(-2px);
    }
</style>

<div class="container-main">
    <div class="row-layout">
        
        <div class="col-kiri">
            <div class="page-header">
                <h1>Input Penimbangan</h1>
                <p>Catat timbangan muatan masal pada unit cabang resmi: <strong><?= htmlspecialchars($cabang_lokasi); ?></strong></p>
            </div>

            <div class="main-workspace-card">
                <form action="proses_timbang_batch.php" method="POST">
                    <input type="hidden" name="cabang_lokasi" value="<?= htmlspecialchars($cabang_lokasi); ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div class="form-group">
                            <label>Lokasi Cabang Kerja</label>
                            <input type="text" class="input-custom" value="<?= htmlspecialchars($cabang_lokasi); ?>" style="color:#52525b; cursor:not-allowed;" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nama Nasabah (Antrean Jadwal)</label>
                            <select id="pilih_kunjungan" name="id_kunjungan" class="input-custom" style="cursor:pointer;" required>
                                <option value="" disabled selected>-- Pilih Jadwal Nasabah --</option>
                                <?php foreach($daftar_kunjungan as $kjn) { 
                                    $tanggal_rapi = date('d M Y', strtotime($kjn['tanggal_kunjungan']));
                                ?>
                                    <option value="<?= $kjn['id_kunjungan'] ?>">
                                        <?= htmlspecialchars($kjn['nama_lengkap']) ?> [<?= $kjn['id_kunjungan'] ?>] - <?= $tanggal_rapi ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Rincian Komoditas Muatan</label>
                    </div>
                    
                    <div style="width:100%; overflow-x:auto; border:1px solid var(--border-color); border-radius:12px; background:#07070a;">
                        <table class="tabel-timbang">
                            <thead>
                                <tr>
                                    <th>Nama Produk Barang</th>
                                    <th style="width:130px; text-align:center;">Berat/Unit (Kg)</th>
                                    <th style="width:140px;">Harga Beli</th>
                                    <th style="width:140px; text-align:right;">Subtotal</th>
                                </tr>
                            </thead> 
                            <tbody id="wrapper-row-barang" style="color:#d4d4d8;">
                                <tr>
                                    <td colspan="4" style="padding:40px; text-align:center; color:var(--text-muted); font-style:italic;">
                                        💡 Silakan tentukan antrean nama nasabah di atas terlebih dahulu untuk memuat data item barang bawaan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="papan-kalkulasi">
                        <div>
                            <p style="font-size:10px; color:#6b8e39; margin:0; text-transform:uppercase; letter-spacing:0.05em;">Total Akumulasi Nilai Tabungan</p>
                            <p id="total_grand_display" style="font-size:32px; font-weight:800; color:var(--neon-green); margin:4px 0 0 0;">Rp 0</p>
                        </div>
                        <span id="total_item_count" style="font-size:11px; background:rgba(138, 230, 26, 0.08); color:var(--neon-green); border:1px solid rgba(138, 230, 26, 0.2); padding:6px 12px; border-radius:20px; font-weight:600;">
                            0 Item Terkalkulasi
                        </span>
                    </div>

                    <button type="submit" class="btn-submit-neon" style="margin-top: 10px;">
                        💾 Simpan & Finalisasi Penimbangan
                    </button>
                </form>
            </div>
        </div>

        <div class="col-sidebar" style="margin-top: 68px;">
            
            <div class="sidebar-card-box">
                <h3>📊 Daftar Harga Sampah saat Ini</h3>
                <div class="table-scroll-wrapper">
                    <table class="tabel-harga-kecil">
                        <thead>
                            <tr>
                                <th>Komoditas</th>
                                <th style="text-align:right;">Harga Beli / Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($master_sampah as $ms): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($ms['nama_produk']) ?></td>
                                    <td style="text-align:right; color:var(--neon-green); font-weight:600; font-family:monospace;">
                                        Rp <?= number_format($ms['harga'], 0, ',', '.') ?><span style="color:#52525b; font-size:10px; font-weight:400;">/<?= htmlspecialchars($ms['satuan']) ?></span>
                                    </td>
                                </tr>
                            </tbody>
                            <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <div class="sidebar-card-box">
                <h3>⏱️ Log Timbangan Terakhir Cabang</h3>
                <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                    <?php foreach($daftar_terakhir as $log): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #16161f; padding-bottom:10px;">
                            <div>
                                <p style="color:#fff; font-weight:600; margin:0;"><?= htmlspecialchars($log['nama_lengkap']) ?></p>
                                <p style="color:var(--text-muted); font-size:11px; margin:2px 0 0 0;"><?= htmlspecialchars($log['nama_produk']) ?></p>
                            </div>
                            <span style="color:var(--neon-green); font-weight:700; font-family:monospace;">Rp <?= number_format($log['total_nominal'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($daftar_terakhir)): ?>
                        <p style="color:var(--text-muted); font-style:italic; margin:0; text-align:center; padding:10px 0;">Belum ada timbangan selesai hari ini.</p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>

    </div>
</div>

<script>
    const dbMappedItems = <?= json_encode($json_items); ?>;
    const masterSampahOptions = <?= json_encode($master_sampah); ?>;

    const pilihKunjungan = document.getElementById('pilih_kunjungan');
    const wrapperRowBarang = document.getElementById('wrapper-row-barang');
    const totalGrandDisplay = document.getElementById('total_grand_display');
    const totalItemCount = document.getElementById('total_item_count');

    pilihKunjungan.addEventListener('change', function() {
        const idKjn = this.value;
        const items = dbMappedItems[idKjn] || [];
        
        wrapperRowBarang.innerHTML = ''; 

        if(items.length === 0) {
            wrapperRowBarang.innerHTML = `<tr><td colspan="4" style="padding:20px; text-align:center; color:#f43f5e; font-weight:600;">⚠️ Gagal menarik detail komoditas rencana kunjungan.</td></tr>`;
            return;
        }

        items.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = "row-item-barang";
            
            let selectOptions = '';
            masterSampahOptions.forEach(s => {
                const selected = (s.id_sampah == item.id_sampah) ? 'selected' : '';
                selectOptions += `<option value="${s.id_sampah}" data-harga="${s.harga}" ${selected}>${s.kategori} - ${s.nama_produk}</option>`;
            });

            tr.innerHTML = `
                <td>
                    <input type="hidden" name="items[${index}][id_detail]" value="${item.id_detail}">
                    <select name="items[${index}][id_sampah]" class="select-row-sampah">
                        ${selectOptions}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${index}][berat]" value="${parseFloat(item.perkiraan_jumlah).toFixed(2)}" class="input-row-berat">
                </td>
                <td style="color:#a1a1aa; font-family:monospace; font-weight:500;">
                    Rp <span class="label-row-harga">${parseInt(item.harga).toLocaleString('id-ID')}</span>
                </td>
                <td style="text-align:right; font-weight:700; color:var(--neon-green); font-family:monospace;">
                    Rp <span class="label-row-subtotal">0</span>
                </td>
            `;
            wrapperRowBarang.appendChild(tr);
        });

        bindRealtimeCalculationEvents();
        hitungGrandTotal();
    });

    function bindRealtimeCalculationEvents() {
        document.querySelectorAll('.input-row-berat').forEach(b => b.addEventListener('input', hitungGrandTotal));
        document.querySelectorAll('.select-row-sampah').forEach(s => s.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const hargaBaru = parseFloat(opt.getAttribute('data-harga')) || 0;
            this.closest('tr').querySelector('.label-row-harga').textContent = hargaBaru.toLocaleString('id-ID');
            hitungGrandTotal();
        }));
    }

    function hitungGrandTotal() {
        let grandTotal = 0;
        const rows = document.querySelectorAll('.row-item-barang');

        rows.forEach(row => {
            const sSelect = row.querySelector('.select-row-sampah');
            const opt = sSelect.options[sSelect.selectedIndex];
            const harga = parseFloat(opt.getAttribute('data-harga')) || 0;
            
            const berat = parseFloat(row.querySelector('.input-row-berat').value) || 0;
            const subtotal = berat * harga;

            grandTotal += subtotal;
            row.querySelector('.label-row-subtotal').textContent = subtotal.toLocaleString('id-ID');
        });

        totalGrandDisplay.textContent = "Rp " + grandTotal.toLocaleString('id-ID');
        totalItemCount.textContent = rows.length + " Komoditas Sukses Terhitung";
    }
</script>
</body>
</html>