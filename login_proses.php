<?php
session_start();
// Hubungkan ke koneksi database di folder config
require_once 'config/koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validasi jika input kosong
    if (empty($email) || empty($password)) {
        header("Location: login.php?status=gagal");
        exit();
    }

    try {
        /* Kueri SQL Baru: Menggunakan teknik LEFT JOIN dari tabel user pusat 
          ke tabel detail entitas (admin, petugas, nasabah) secara presisi
        */
        $stmt_user = $koneksi->prepare(
            "SELECT u.*, 
                    a.id_admin, a.nama_admin, 
                    p.id_petugas, p.nama_petugas, p.no_hp, p.cabang_lokasi, p.created_at AS petugas_status, 
                    n.id_nasabah, n.nama_lengkap, n.no_whatsapp, n.alamat, n.status_akun AS nasabah_status
             FROM user u
             LEFT JOIN admin a ON u.id_user = a.id_user
             LEFT JOIN petugas p ON u.id_user = p.id_user
             LEFT JOIN nasabah n ON u.id_user = n.id_user
             WHERE u.email = :email LIMIT 1"
        );
        $stmt_user->execute([':email' => $email]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        // Evaluasi Kredensial Akun (Mendukung Plain Text ATAU Enkripsi Hash BCRYPT)
        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
            
            // Ambil role hak akses dari tabel user
            $role = $user['role'];

            // =================================================================
            // SKENARIO A: LOG HISTORI LOGIN UNTUK ROLE ADMIN
            // =================================================================
            if ($role === 'admin') {
                $_SESSION['logged_in']  = true;
                $_SESSION['user_id']    = $user['id_admin'];
                $_SESSION['user_role']  = 'admin';
                $_SESSION['user_name']  = $user['nama_admin'] ?: 'Admin';
                $_SESSION['user_email'] = $user['email'];

                header("Location: admin/dashboard.php");
                exit();
            }

            // =================================================================
            // SKENARIO B: LOG HISTORI LOGIN UNTUK ROLE PETUGAS
            // =================================================================
            if ($role === 'petugas') {
                $_SESSION['logged_in']   = true;
                $_SESSION['user_id']     = $user['id_petugas'];
                $_SESSION['user_role']   = 'petugas';
                $_SESSION['user_name']   = $user['nama_petugas'];
                $_SESSION['user_email']  = $user['email'];
                $_SESSION['user_phone']  = $user['no_hp'] ?? '';
                $_SESSION['user_extra']  = trim($user['cabang_lokasi'] ?? '');
                
                // Asumsi akun petugas lapangan selalu aktif otomatis dari seeder data
                $_SESSION['user_status'] = 'aktif'; 

                header("Location: petugas/dashboard.php");
                exit();
            }

            // =================================================================
            // SKENARIO C: LOG HISTORI LOGIN UNTUK ROLE NASABAH
            // =================================================================
            if ($role === 'warga') {
                $_SESSION['logged_in']   = true;
                $_SESSION['user_id']     = $user['id_nasabah'];
                $_SESSION['user_role']   = 'warga'; 
                $_SESSION['user_name']   = $user['nama_lengkap'];
                $_SESSION['user_email']  = $user['email'];
                $_SESSION['user_phone']  = $user['no_whatsapp'] ?? '';
                $_SESSION['user_extra']  = $user['alamat'] ?? '';
                
                // Ambil status_akun peninjauan ('pending' / 'aktif') dari tabel nasabah
                $_SESSION['user_status'] = strtolower(trim($user['nasabah_status'] ?? 'aktif'));

                // Jika status akun masih pending, kunci rutenya ke halaman approval.php
                if ($_SESSION['user_status'] === 'pending') {
                    header("Location: approval.php");
                } else {
                    header("Location: nasabah/dashboard.php");
                }
                exit();
            }
        }

        // Jika data user tidak ditemukan atau password salah, kembalikan ke gerbang depan
        header("Location: login.php?status=gagal");
        exit();

    } catch (Exception $e) {
        die("Gagal memproses otorisasi basis data: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit();
}