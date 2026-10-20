<?php
// Konfigurasi Parameter Koneksi Database Master Kelompok
$host     = "127.0.0.1";
$port     = "3307";
$username = "root";
$password = "";
$dbname   = "db_banksampah"; // Variabel yang benar adalah $dbname

// Perbaikan: Mengubah $body menjadi $dbname agar database db_banksampah terpilih
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Inisialisasi Koneksi PDO Baru
    $koneksi = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    die("Gagal memproses otorisasi basis data: " . $e->getMessage());
}
?>