<?php
// Mendeteksi nama file halaman yang sedang diakses saat ini
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bank Sampah</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <style>
        html{
            scroll-behavior:smooth;
        }

        body{
            background:#09090b;
        }

        .glass{
            backdrop-filter: blur(18px);
            background:rgba(24,24,27,.75);
        }

        /* CONTAINER NAVIGASI TOMBOL AUTH DENGAN INDIKATOR SLIDING */
        .auth-nav-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background-color: #0e0e0e;
            border: 1px solid #1f1f23;
            padding: 4px;
            border-radius: 14px;
            overflow: hidden;
        }

        .nav-btn-auth {
            position: relative;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 24px;
            border-radius: 10px;
            z-index: 2; /* Memaksa teks berada di atas kotak hijau */
            color: #a1a1aa;
            text-decoration: none;
            transition: color 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 90px; /* Lebar statis agar pergeseran kotak seimbang */
        }

        /* Ketika tombol aktif, teks berubah menjadi hitam secara halus */
        .nav-btn-auth.auth-active {
            color: #09090b !important;
        }

        .nav-btn-auth:hover:not(.auth-active) {
            color: #ffffff;
        }

        /* KOTAK HIJAU AKSI MELUNCUR (SLIDING SLIDER) */
        .sliding-bg-indicator {
            position: absolute;
            top: 4px;
            left: 4px;
            height: calc(100% - 8px);
            width: 90px; /* Sama dengan lebar tombol */
            background-color: #8ae61a;
            border-radius: 10px;
            z-index: 1;
            box-shadow: 0 8px 20px rgba(138, 230, 26, 0.25);
            /* Kurva Bezier kustom untuk efek elastis dan meluncur super halus */
            transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* LOGIKA PERGESERAN POSISI KOTAK HIJAU */
        /* Jika halaman aktif adalah register.php, geser kotak hijau ke kanan sebesar width tombol + gap padding */
        .auth-nav-wrapper.state-register .sliding-bg-indicator {
            transform: translateX(90px);
        }
        
        /* Jika sedang di index.php (bukan login/register), sembunyikan kotak hijau secara halus */
        .auth-nav-wrapper.state-hidden .sliding-bg-indicator {
            opacity: 0;
            scale: 0.9;
        }
    </style>

</head>

<body class="bg-zinc-950 text-white">

<header class="fixed top-0 left-0 right-0 z-50 glass border-b border-zinc-800">

    <div class="max-w-7xl mx-auto">

        <div class="h-20 px-6 flex items-center justify-between">

           
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-lime-500 flex items-center justify-center shadow-lg">
                    <span class="text-2xl">🌱</span>
                </div>
                <div>
                    <h2 class="font-black text-xl">Bank Sampah</h2>
                    <p class="text-xs text-zinc-400">Digital Platform</p>
                </div>
            </a>

           
            <nav class="hidden lg:flex items-center gap-10">
                <a href="index.php" class="text-zinc-300 hover:text-lime-400 duration-300">Home</a>
                <a href="#fitur" class="text-zinc-300 hover:text-lime-400 duration-300">Fitur</a>
                <a href="#cara-kerja" class="text-zinc-300 hover:text-lime-400 duration-300">Cara Kerja</a>
                <a href="#faq" class="text-zinc-300 hover:text-lime-400 duration-300">FAQ</a>
                <a href="#kontak" class="text-zinc-300 hover:text-lime-400 duration-300">Kontak</a>
            </nav>

            <div class="hidden lg:flex items-center">
                <?php
                // Menentukan kelas pembungkus berdasarkan halaman aktif untuk memicu geser CSS
                $wrapper_state = 'state-login';
                if ($current_page === 'register.php') {
                    $wrapper_state = 'state-register';
                } elseif ($current_page !== 'login.php' && $current_page !== 'register.php') {
                    $wrapper_state = 'state-hidden';
                }
                ?>
                <div class="auth-nav-wrapper <?= $wrapper_state; ?>">
                    <div class="sliding-bg-indicator"></div>

                    <a href="login.php" class="nav-btn-auth <?= ($current_page === 'login.php') ? 'auth-active' : ''; ?>">
                        Masuk
                    </a>

                    <a href="register.php" class="nav-btn-auth <?= ($current_page === 'register.php') ? 'auth-active' : ''; ?>">
                        Daftar
                    </a>
                </div>
            </div>

           
            <button id="menuBtn" class="lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

        </div>

    </div>

   
    <div id="mobileMenu" class="hidden lg:hidden border-t border-zinc-800 bg-zinc-900">
        <div class="flex flex-col p-6 space-y-5">
            <a href="index.php">Home</a>
            <a href="fitur.php">Fitur</a>
            <a href="cara-kerja.php">Cara Kerja</a>
            <a href="faq.php">FAQ</a>
            <a href="kontak.php">Kontak</a>
            <hr class="border-zinc-700">

            <a href="login.php" class="text-center py-3 rounded-xl border transition-all duration-300 <?= ($current_page === 'login.php') ? 'bg-lime-500 text-black font-bold' : 'text-zinc-300 border-zinc-700' ?>">
                Masuk
            </a>

            <a href="register.php" class="text-center py-3 rounded-xl transition-all duration-300 <?= ($current_page === 'register.php') ? 'bg-lime-500 text-black font-bold' : 'border border-zinc-700 text-zinc-300' ?>">
                Daftar
            </a>
        </div>
    </div>

</header>

<script>
    // Logika buka-tutup menu mobile hf
    const menuBtn = document.getElementById('menuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    if(menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>