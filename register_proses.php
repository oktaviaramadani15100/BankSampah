<?php
session_start();
// Hubungkan berkas konfigurasi koneksi database kelompok
require_once 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role                = trim($_POST['role']);
    $nama_lengkap        = trim($_POST['nama_lengkap']);
    $no_whatsapp         = trim($_POST['no_whatsapp']);
    $alamat_lengkap      = trim($_POST['alamat_lengkap']);
    $email               = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password            = trim($_POST['password']);
    $konfirmasi_password = trim($_POST['konfirmasi_password']);

    // 1. Validasi kecocokan entitas password
    if ($password !== $konfirmasi_password) {
        header("Location: register.php?role=$role&status=password_salah");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // 2. Periksa keunikan email pada tabel pusat user
        $checkEmail = $koneksi->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
        $checkEmail->execute([':email' => $email]);

        if ($checkEmail->fetchColumn() > 0) {
            header("Location: register.php?role=$role&status=email_terdaftar");
            exit();
        }

        // 3. Generator Fungsi ID Berbasis String VARCHAR
        $generateId = function (PDO $pdo, string $prefix, string $table, string $idColumn): string {
            $queryMax = $pdo->query("SELECT MAX($idColumn) AS max_id FROM `$table`");
            $idLast = $queryMax->fetch(PDO::FETCH_ASSOC)['max_id'];
            $newId = $prefix . '001';
            if ($idLast) {
                $noUrut = (int) substr($idLast, 3, 3);
                $noUrut++;
                $newId = $prefix . sprintf('%03d', $noUrut);
            }
            return $newId;
        };

        $koneksi->beginTransaction();

        // 4. INSERT INTO TABEL USER PUSAT (Sesuai skema database baru)
        $userId = $generateId($koneksi, 'USR', 'user', 'id_user');
        $stmtUser = $koneksi->prepare("INSERT INTO user (id_user, email, password, role, status) VALUES (:id, :email, :pass, :role, 'aktif')");
        $stmtUser->execute([
            ':id' => $userId,
            ':email' => $email,
            ':pass' => $password_hash,
            ':role' => ($role === 'petugas') ? 'petugas' : 'warga'
        ]);

        // 5. INSERT INTO TABEL DETAIL PROFILE MASING-MASING ROLE
        if ($role === 'petugas') {
            $lokasi_penempatan = trim($_POST['lokasi_penempatan']);
            $petugasId = $generateId($koneksi, 'PTG', 'petugas', 'id_petugas');
            
            $stmtProfile = $koneksi->prepare("INSERT INTO petugas (id_petugas, id_user, nama_petugas, no_hp, cabang_lokasi) VALUES (:id, :user_id, :nama, :wa, :lokasi)");
            $stmtProfile->execute([
                ':id' => $petugasId,
                ':user_id' => $userId,
                ':nama' => $nama_lengkap,
                ':wa' => $no_whatsapp,
                ':lokasi' => $lokasi_penempatan
            ]);
        } else {
            $nasabahId = $generateId($koneksi, 'NSB', 'nasabah', 'id_nasabah');
            
            // Kolom diubah dari 'alamat_lengkap' menjadi 'alamat' sesuai rancangan tabel baru nasabah
            $stmtProfile = $koneksi->prepare("INSERT INTO nasabah (id_nasabah, id_user, nama_lengkap, no_whatsapp, alamat, status_akun) VALUES (:id, :user_id, :nama, :wa, :alamat, 'pending')");
            $stmtProfile->execute([
                ':id' => $nasabahId,
                ':user_id' => $userId,
                ':nama' => $nama_lengkap,
                ':wa' => $no_whatsapp,
                ':alamat' => $alamat_lengkap
            ]);
        }

        $koneksi->commit();

        header("Location: login.php?role=$role&status=registrasi_sukses");
        exit();

    } catch (PDOException $e) {
        $koneksi->rollBack();
        header("Location: register.php?role=$role&status=sistem_error");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>