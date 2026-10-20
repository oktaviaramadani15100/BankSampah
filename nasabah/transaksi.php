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

// PROSES BACKEND: SUBMIT RENCANA SETORAN BARU (MULTI-ITEM)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buat_rencana') {
    $tanggal_setoran = $_POST['tanggal_setoran'];
    $cabang_lokasi   = trim($_POST['cabang_lokasi']);
    
    // Ambil data array dari form
    $items_sampah    = isset($_POST['items']) ? $_POST['items'] : [];

    if (!empty($items_sampah)) {
        try {
            $koneksi->beginTransaction();

            // 1. Generator ID Setoran VARCHAR (Contoh: STR-2026-0001)
            $queryMax = $koneksi->query("SELECT MAX(id_setoran) AS max_id FROM `transaksi_setoran`");
            $idLast = $queryMax->fetch(PDO::FETCH_ASSOC)['max_id'];
            $newId = 'STR-' . date('Y') . '-0001';
            if ($idLast) {
                $noUrut = (int) substr($idLast, 9, 4);
                $noUrut++;
                $newId = 'STR-' . date('Y') . '-' . sprintf('%04d', $noUrut);
            }

            $total_berat_akumulasi = 0;
            $total_penerimaan_estimasi = 0;
            $detail_rows_to_insert = [];

            // 2. Loop data item untuk kalkulasi awal harga master sampah terbaru
            foreach ($items_sampah as $item) {
                $id_sampah       = $item['id_sampah'];
                $perkiraan_berat = floatval($item['berat_kg']);
                
                if ($perkiraan_berat <= 0) continue;

                $stmt_harga = $koneksi->prepare("SELECT harga_perkg FROM sampah WHERE id_sampah = :id_s LIMIT 1");
                $stmt_harga->execute([':id_s' => $id_sampah]);
                $harga_perkg = (int)$stmt_harga->fetchColumn();
                $subtotal = (int)round($perkiraan_berat * $harga_perkg);

                $total_berat_akumulasi += $perkiraan_berat;
                $total_penerimaan_estimasi += $subtotal;

                // Simpan data sementara untuk bulk insert detail
                $detail_rows_to_insert[] = [
                    'id_sampah' => $id_sampah,
                    'berat'     => $perkiraan_berat,
                    'harga'     => $harga_perkg,
                    'subtotal'  => $subtotal
                ];
            }

            // 3. Insert ke induk transaksi_setoran (Status default: 'rencana')
            $stmt_induk = $koneksi->prepare("INSERT INTO transaksi_setoran (id_setoran, id_nasabah, id_petugas, tanggal_setoran, cabang_lokasi, total_berat, total_penerimaan, status) VALUES (:id, :id_nsb, NULL, :tgl, :cabang, :berat, :penerimaan, 'rencana')");
            $stmt_induk->execute([
                ':id'         => $newId,
                ':id_nsb'     => $id_nasabah,
                ':tgl'        => $tanggal_setoran,
                ':cabang'     => $cabang_lokasi,
                ':berat'      => $total_berat_akumulasi,
                ':penerimaan' => $total_penerimaan_estimasi
            ]);

            // 4. Insert semua baris rincian ke detail_setoran
            $stmt_detail = $koneksi->prepare("INSERT INTO detail_setoran (id_setoran, id_sampah, berat_kg, harga_perkg, subtotal) VALUES (:id_setoran, :id_s, :berat, :harga, :subtotal)");
            foreach ($detail_rows_to_insert as $row) {
                $stmt_detail->execute([
                    ':id_setoran' => $newId,
                    ':id_s'       => $row['id_sampah'],
                    ':berat'      => $row['berat'],
                    ':harga'      => $row['harga'],
                    ':subtotal'   => $row['subtotal']
                ]);
            }

            $koneksi->commit();
            $notif = "sukses";
        } catch (Exception $e) {
            $koneksi->rollBack();
            $notif = "gagal";
        }
    } else {
        $notif = "kosong";
    }
}

// AMBIL MASTER SAMPAH UNTUK OPSI FORM SELECT JAVASCRIPT
$master_sampah = [];
try {
    $query_s = $koneksi->query("SELECT id_sampah, nama_sampah, harga_perkg, satuan FROM sampah WHERE status = 'aktif' ORDER BY nama_sampah ASC");
    $master_sampah = $query_s->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Empty
}
?>

<link rel="stylesheet" href="../css/nasabah.css">

<style>
    /* Perbaikan Jarak Atas Kontainer */
    .dashboard-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 60px 20px 40px 20px;
        display: flex;
        flex-direction: column;
        gap: 30px;
        box-sizing: border-box;
    }

    /* Kotak Formulir Premium Bertema Gelap */
    .form-card-box {
        background-color: #0e0e0e;
        border: 1px solid #1f1f23;
        border-radius: 24px;
        padding: 40px;
        max-width: 850px;
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-card-box:hover {
        border-color: #8ae61a !important;
        box-shadow: 0 0 25px rgba(138, 230, 26, 0.15);
    }

    .form-grid-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    @media (min-width: 640px) {
        .form-grid-layout {
            grid-template-columns: 1fr 1fr;
        }
    }

    .input-group-wrapper {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-label-custom {
        font-size: 11px;
        font-weight: 600;
        color: #a1a1aa;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .input-custom-field {
        width: 100%;
        background-color: #050505;
        border: 1px solid #1f1f23;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 13px;
        color: #f4f4f5;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-custom-field:focus {
        border-color: #8ae61a;
        box-shadow: 0 0 8px rgba(138, 230, 26, 0.1);
    }

    .input-custom-field option {
        background-color: #0e0e0e;
        color: #e4e4e7;
    }

    /* Gaya Area Pembungkus Baris Komoditas */
    .dynamic-rows-section {
        border-top: 1px dashed #1f1f23;
        padding-top: 20px;
        margin-bottom: 25px;
    }

    .row-item-sampah {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 15px;
        align-items: center;
        background: #070709;
        padding: 16px;
        border: 1px solid #16161a;
        border-radius: 14px;
        margin-bottom: 12px;
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .btn-remove-row {
        background: rgba(244, 63, 94, 0.1);
        border: 1px solid rgba(244, 63, 94, 0.2);
        color: #f43f5e;
        padding: 12px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-remove-row:hover {
        background: #f43f5e;
        color: #fff;
    }

    .btn-add-item {
        background: #161622;
        border: 1px dashed #3a3a4f;
        color: #e4e4e7;
        padding: 12px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        width: 100%;
        margin-bottom: 25px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-add-item:hover {
        border-color: #8ae61a;
        color: #8ae61a;
        background: rgba(138, 230, 26, 0.02);
    }

    /* Tombol Submit Utama */
    .btn-submit-rencana {
        width: 100%;
        padding: 16px;
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
        box-shadow: 0 4px 12px rgba(138, 230, 26, 0.15);
        transition: all 0.2s ease;
    }

    .btn-submit-rencana:hover {
        background-color: #7cd015;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(138, 230, 26, 0.25);
    }
</style>

<div class="dashboard-container">
    
    <div class="welcome-section">
        <div>
            <h1 style="font-size: 26px; font-weight: 700; color: #fff; margin: 0;">Setoran Mandiri</h1>
            <p style="font-size: 13px; color: #52525b; margin: 4px 0 0 0;">Buat rencana jadwal pengantaran komoditas sampah Anda ke lokasi cabang</p>
        </div>
    </div>

    <?php if ($notif === 'sukses'): ?>
        <div style="background-color: rgba(52, 211, 153, 0.05); border: 1px solid #064e3b; color: #34d399; padding: 16px; border-radius: 12px; font-size: 13px; font-weight: 600;">
            ✓ <strong>Rencana Berhasil Dibuat!</strong> Silakan bawa muatan muatan sampah Anda ke cabang tujuan sesuai tanggal pilihan Anda untuk diverifikasi petugas.
        </div>
    <?php elseif ($notif === 'gagal'): ?>
        <div style="background-color: rgba(244, 63, 94, 0.05); border: 1px solid #7f1d1d; color: #f43f5e; padding: 16px; border-radius: 12px; font-size: 13px; font-weight: 600;">
            ⚠️ <strong>Gagal Memproses!</strong> Terjadi kendala internal koneksi server database kelompok. Coba beberapa saat lagi.
        </div>
    <?php elseif ($notif === 'kosong'): ?>
        <div style="background-color: rgba(251, 191, 36, 0.05); border: 1px solid #78350f; color: #fbbf24; padding: 16px; border-radius: 12px; font-size: 13px; font-weight: 600;">
            ⚠️ <strong>Input Kosong!</strong> Minimal harus memasukkan satu jenis komoditas sampah dengan muatan yang valid.
        </div>
    <?php endif; ?>

    <div class="form-card-box">
        <form action="transaksi.php" method="POST">
            <input type="hidden" name="action" value="buat_rencana">

            <div class="form-grid-layout">
                <div class="input-group-wrapper">
                    <label class="form-label-custom">Tanggal Kedatangan</label>
                    <input type="date" name="tanggal_setoran" class="input-custom-field" min="<?= date('Y-m-d'); ?>" required>
                </div>
                
                <div class="input-group-wrapper">
                    <label class="form-label-custom">Cabang Tujuan</label>
                    <select name="cabang_lokasi" class="input-custom-field" required>
                        <option value="" disabled selected>-- Pilih Lokasi Cabang --</option>
                        <option value="Bank Sampah Pusat">Bank Sampah Pusat</option>
                        <option value="Bank Sampah Jakarta Timur">Bank Sampah Jakarta Timur</option>
                        <option value="Bank Sampah Jakarta Barat">Bank Sampah Jakarta Barat</option>
                        <option value="Bank Sampah Jakarta Selatan">Bank Sampah Jakarta Selatan</option>
                        <option value="Bank Sampah Jakarta Utara">Bank Sampah Jakarta Utara</option>
                        <option value="Bank Sampah Jakarta Pusat">Bank Sampah Jakarta Pusat</option>
                    </select>
                </div>
            </div>

            <div class="dynamic-rows-section">
                <div class="form-label-custom" style="margin-bottom: 12px; display: block;">Daftar Rincian Komoditas Sampah Bawaan</div>
                
                <div id="wrapper-item-rows">
                    <div class="row-item-sampah">
                        <div class="input-group-wrapper">
                            <select name="items[0][id_sampah]" class="input-custom-field" required>
                                <option value="" disabled selected>-- Pilih Komoditas --</option>
                                <?php foreach ($master_sampah as $ms): ?>
                                    <option value="<?= $ms['id_sampah']; ?>">
                                        <?= htmlspecialchars($ms['nama_sampah']); ?> (Rp <?= number_format($ms['harga_perkg'], 0, ',', '.'); ?>/<?= $ms['satuan']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group-wrapper">
                            <input type="number" step="0.01" name="items[0][berat_kg]" placeholder="Estimasi Jumlah" class="input-custom-field" required>
                        </div>
                        <div>
                            <button type="button" class="btn-remove-row" onclick="hapusBarisItem(this)">🗑️</button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-add-item" id="btn-tambah-baris">
                    ➕ Tambah Komoditas Sampah Lainnya
                </button>
            </div>

            <button type="submit" class="btn-submit-rencana">
                🚀 <span>Daftarkan Jadwal Rencana Setoran Bersama</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Konversi master_sampah PHP ke array JSON JavaScript
    const masterSampahOptions = <?= json_encode($master_sampah); ?>;
    const wrapperItemRows = document.getElementById('wrapper-item-rows');
    const btnTambahBaris = document.getElementById('btn-tambah-baris');
    
    let rowIndex = 1; // Counter index array element form

    btnTambahBaris.addEventListener('click', function() {
        // Render element select options secara dinamis
        let selectOptions = '<option value="" disabled selected>-- Pilih Komoditas --</option>';
        masterSampahOptions.forEach(s => {
            const formatHarga = parseInt(s.harga_perkg).toLocaleString('id-ID');
            selectOptions += `<option value="${s.id_sampah}">${s.nama_sampah} (Rp ${formatHarga}/${s.satuan})</option>`;
        });

        // Buat struktur baris baru
        const divRow = document.createElement('div');
        divRow.className = 'row-item-sampah';
        divRow.innerHTML = `
            <div class="input-group-wrapper">
                <select name="items[${rowIndex}][id_sampah]" class="input-custom-field" required>
                    ${selectOptions}
                </select>
            </div>
            <div class="input-group-wrapper">
                <input type="number" step="0.01" name="items[${rowIndex}][berat_kg]" placeholder="Estimasi Jumlah" class="input-custom-field" required>
            </div>
            <div>
                <button type="button" class="btn-remove-row" onclick="hapusBarisItem(this)">🗑️</button>
            </div>
        `;

        wrapperItemRows.appendChild(divRow);
        rowIndex++;
    });

    function hapusBarisItem(button) {
        const totalRows = wrapperItemRows.querySelectorAll('.row-item-sampah').length;
        if (totalRows > 1) {
            button.closest('.row-item-sampah').remove();
        } else {
            alert('Gagal menghapus! Rencana setoran minimal harus berisi 1 baris item komoditas.');
        }
    }
</script>
</body>
</html>