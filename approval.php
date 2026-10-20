<?php
session_start();
// Proteksi halaman: Jika belum login, kembalikan ke login.php
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'header.php';

$role   = $_SESSION['user_role'];
$nama   = $_SESSION['user_name'];
$email  = $_SESSION['user_email'];
$phone  = $_SESSION['user_phone'];
$extra  = $_SESSION['user_extra']; // Alamat (nasabah) atau Lokasi (Petugas)
$status = $_SESSION['user_status'];
?>

<main class="min-h-[calc(100vh-73px)] flex items-center justify-center px-4 py-12 bg-zinc-950">
    <div class="max-w-xl w-full bg-zinc-900/40 border border-zinc-900 rounded-3xl p-6 md:p-10 space-y-8 shadow-2xl backdrop-blur-sm">
        
        <div class="text-center space-y-2">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-100">Proses Verifikasi Akun</h2>
            <p class="text-xs text-zinc-500">Pantau tahapan aktivasi akun Bank Sampah Anda</p>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
            <div class="space-y-2">
                <div class="h-1.5 w-full bg-lime-600 rounded-full"></div>
                <p class="text-lime-500 text-[11px]">1. Data Pribadi</p>
            </div>
            <div class="space-y-2">
                <div class="h-1.5 w-full bg-lime-600 rounded-full"></div>
                <p class="text-lime-500 text-[11px]">2. Status Akun</p>
            </div>
            <div class="space-y-2">
                <div class="h-1.5 w-full <?= $status === 'aktif' ? 'bg-lime-600' : 'bg-zinc-800' ?> rounded-full"></div>
                <p class="<?= $status === 'aktif' ? 'text-lime-500' : 'text-zinc-500' ?> text-[11px]">3. Konfirmasi</p>
            </div>
        </div>

        <div class="bg-zinc-950/60 border border-zinc-900 rounded-2xl p-5 space-y-3">
            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider pb-2 border-b border-zinc-900">
                📋 Ringkasan Data Registrasi
            </h3>
            <div class="grid grid-cols-3 gap-y-2 text-xs">
                <span class="text-zinc-500">Nama Lengkap</span>
                <span class="col-span-2 text-zinc-200 font-medium">: <?= htmlspecialchars($nama); ?></span>

                <span class="text-zinc-500">Email Sistem</span>
                <span class="col-span-2 text-zinc-200 font-medium">: <?= htmlspecialchars($email); ?></span>

                <span class="text-zinc-500">No. Kontak</span>
                <span class="col-span-2 text-zinc-200 font-medium">: <?= htmlspecialchars($phone); ?></span>

                <?php if ($role === 'petugas'): ?>
                    <span class="text-zinc-500">Penempatan</span>
                    <span class="col-span-2 text-lime-500 font-bold">: <?= htmlspecialchars($extra); ?></span>
                <?php else: ?>
                    <span class="text-zinc-500">Alamat Rumah</span>
                    <span class="col-span-2 text-zinc-200 font-medium">: <?= htmlspecialchars($extra); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-3">
            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider">🔒 Status Otorisasi</h3>
            
            <?php if ($status === 'pending'): ?>
                <div class="p-5 bg-amber-950/20 border border-amber-900/60 rounded-2xl flex items-start space-x-4">
                    <div class="text-2xl text-amber-500 mt-0.5 animate-bounce">⏳</div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-amber-400">Akun Belum Aktif</p>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Mohon maaf, akun Anda saat ini masih berada dalam antrean peninjauan. Harap menunggu konfirmasi persetujuan dari <span class="text-zinc-200 font-semibold">Administrator</span> untuk membuka hak akses penuh.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-5 bg-emerald-950/20 border border-emerald-900/60 rounded-2xl flex items-start space-x-4">
                    <div class="text-2xl text-emerald-500 mt-0.5">✓</div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-emerald-400">Akun Berhasil Diaktivasi</p>
                        <p class="text-xs text-zinc-400">Selamat! Berkas data Anda telah divalidasi oleh admin.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="pt-2">
            <?php if ($status === 'pending'): ?>
                <button type="button" class="w-full py-3.5 bg-zinc-900 border border-zinc-800 text-zinc-600 font-bold rounded-xl text-sm cursor-not-allowed text-center">
                    Menunggu Verifikasi Admin...
                </button>
                <p class="text-center text-[10px] text-zinc-600 mt-2">Halaman ini akan terbuka otomatis setelah status akun diperbarui.</p>
            
            <?php else: ?>
                <?php if ($role === 'nasabah'): ?>
                    <div class="mb-5 p-5 bg-zinc-900/60 border border-zinc-900 rounded-2xl space-y-2.5 text-xs">
                        <p class="font-bold text-lime-500 tracking-wide">🎁 Keuntungan Member Bank Sampah:</p>
                        <p class="text-zinc-400">✨ Dapat menukar botol/kertas menjadi saldo tabungan digital digital.</p>
                        <p class="text-zinc-400">✨ Bonus pencairan instan tanpa potongan administrasi bulanan.</p>
                        <p class="text-zinc-400">✨ Akses grafik kalkulator konversi berat sampah secara real-time.</p>
                    </div>
                    <a href="nasabah/dashboard.php" class="w-full block py-3.5 bg-lime-600 text-zinc-950 font-bold rounded-xl text-sm text-center shadow-lg hover:bg-lime-500 transition-all">
                        Masuk ke Dashboard nasabah →
                    </a>

                <?php elseif ($role === 'petugas'): ?>
                    <div class="mb-5 p-5 bg-zinc-900/60 border border-zinc-900 rounded-2xl space-y-1">
                        <p class="font-bold text-lime-500 text-sm">💼 Petugas Siap Bekerja!</p>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Hak akses operator Anda telah dibuka. Anda kini berwenang untuk mencatat data setoran sampah nasabah dan memvalidasi pengajuan saldo di tim lapangan.
                        </p>
                    </div>
                    <a href="petugas/dashboard.php" class="w-full block py-3.5 bg-lime-600 text-zinc-950 font-bold rounded-xl text-sm text-center shadow-lg hover:bg-lime-500 transition-all">
                        Masuk ke Dashboard Petugas →
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>