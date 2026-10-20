<?php
include 'header.php';
?>

<section
    class="hero-section relative overflow-hidden bg-zinc-950 min-h-screen flex items-center">

    <!-- Background Glow -->
    <div class="absolute -top-60 -right-40 w-[650px] h-[650px] rounded-full bg-lime-500/10 blur-[140px]"></div>

    <div class="absolute -bottom-60 -left-40 w-[500px] h-[500px] rounded-full bg-emerald-500/10 blur-[140px]"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-24 grid lg:grid-cols-2 gap-20 items-center relative z-10 hero-grid">

        <!-- LEFT -->
        <div>

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-lime-500/10 border border-lime-500/20 mb-8">

                <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>

                <span class="text-lime-400 text-sm font-semibold tracking-wide">
                    PLATFORM BANK SAMPAH DIGITAL
                </span>

            </div>

            <!-- Heading -->

            <h1
                class="hero-title text-5xl lg:text-7xl font-black text-white leading-[1.05] tracking-tight">

                Kelola Sampahmu,

                <span class="text-lime-400">

                    Raih Penghasilan

                </span>

                <br>

                Lebih Mudah.

            </h1>

            <!-- Desc -->

            <p
                class="mt-8 text-lg lg:text-xl text-zinc-400 leading-9 max-w-xl">

                Platform digital yang membantu masyarakat menukar sampah menjadi
                uang, memperoleh reward, serta menjaga lingkungan menjadi lebih
                bersih dan berkelanjutan.

            </p>

            <!-- Button -->

            <div class="flex flex-wrap gap-5 mt-10 mobile-stack">

                <a href="login.php"

                    class="px-8 py-4 bg-lime-500 hover:bg-lime-400 text-black rounded-2xl font-bold transition duration-300 shadow-xl hover:scale-105">

                    Mulai Sekarang →

                </a>

                <a href="#fitur"

                    class="px-8 py-4 border border-zinc-700 rounded-2xl text-white hover:border-lime-400 hover:text-lime-400 transition">

                    Pelajari Lebih Lanjut

                </a>

            </div>

            <!-- Statistik -->

            <div class="grid grid-cols-3 gap-8 mt-16">

                <div>

                    <h2 class="text-4xl font-black text-lime-400">

                        10K+

                    </h2>

                    <p class="text-zinc-500 mt-2">

                        Pengguna Aktif

                    </p>

                </div>

                <div>

                    <h2 class="text-4xl font-black text-lime-400">

                        500T

                    </h2>

                    <p class="text-zinc-500 mt-2">

                        Sampah Terkelola

                    </p>

                </div>

                <div>

                    <h2 class="text-4xl font-black text-lime-400">

                        Rp2M

                    </h2>

                    <p class="text-zinc-500 mt-2">

                        Reward Dibagikan

                    </p>

                </div>

            </div>

        </div>

        <!-- RIGHT -->

        <div class="relative">

            <!-- Glow -->

            <div
                class="absolute inset-0 bg-lime-500/20 blur-[120px] rounded-full">
            </div>

            <div
                class="relative rounded-[35px] overflow-hidden border border-zinc-800 shadow-2xl">

                <img

                    src="assets/img/tm.jpg"

                    class="w-full h-[560px] object-cover hover:scale-105 duration-700"

                    alt="Bank Sampah">

            </div>

            <!-- Floating Card -->

            <!-- <div
                class="absolute bottom-6 -left-6 bg-zinc-900/95 border border-zinc-700 rounded-3xl px-6 py-5 shadow-2xl backdrop-blur-sm max-w-[220px] z-20">

                <h3 class="text-white font-bold text-xl">

                    🌱 50+ Bank Sampah

                </h3>

                <p class="text-zinc-400 mt-1 text-sm leading-6">

                    Telah Bergabung Bersama Kami

                </p>

            </div>

            <div
                class="absolute top-6 -right-6 bg-zinc-900/95 border border-zinc-700 rounded-3xl px-6 py-5 shadow-xl backdrop-blur-sm max-w-[180px] z-20">

                <h3 class="text-lime-400 font-black text-3xl">

                    98%

                </h3>

                <p class="text-zinc-400 text-sm leading-6">

                    Kepuasan Pengguna

                </p>

            </div> -->

        </div>

    </div>

</section>

<section id="fitur" class="relative py-28 bg-zinc-950 overflow-hidden">

    <!-- Background Glow -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-lime-500/10 blur-[150px] rounded-full"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500/10 blur-[150px] rounded-full"></div>

    <div class="relative max-w-7xl mx-auto px-6">

        <!-- Judul -->
        <div class="text-center max-w-3xl mx-auto">

            <span class="inline-block px-5 py-2 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-400 font-semibold text-sm">
                FITUR UNGGULAN
            </span>

            <h2 class="mt-6 text-5xl font-black text-white leading-tight">
                Semua yang Anda Butuhkan
                <span class="text-lime-400">Dalam Satu Platform</span>
            </h2>

            <p class="mt-6 text-xl text-zinc-400 leading-8">
                Kelola transaksi, pantau saldo, kumpulkan reward,
                dan kelola sampah dengan lebih mudah melalui
                sistem Bank Sampah Digital.
            </p>

        </div>

        <!-- Card -->

        <div class="grid lg:grid-cols-3 gap-8 mt-20 feature-grid">

            <!-- Card 1 -->

            <div class="group bg-zinc-900 border border-zinc-800 rounded-3xl p-8 hover:border-lime-500 transition duration-300 hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(132,204,22,.15)]">

                <div class="w-16 h-16 rounded-2xl bg-lime-500/15 flex items-center justify-center text-3xl mb-8 group-hover:scale-110 duration-300">

                    📊

                </div>

                <h3 class="text-2xl font-bold text-white">

                    Tracking Transaksi

                </h3>

                <p class="mt-5 text-zinc-400 leading-8">

                    Seluruh transaksi setoran dan penarikan dapat
                    dipantau secara realtime dengan riwayat lengkap.

                </p>

            </div>

            <!-- Card 2 -->

            <div class="group bg-zinc-900 border border-zinc-800 rounded-3xl p-8 hover:border-lime-500 transition duration-300 hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(132,204,22,.15)]">

                <div class="w-16 h-16 rounded-2xl bg-lime-500/15 flex items-center justify-center text-3xl mb-8 group-hover:scale-110 duration-300">

                    💰

                </div>

                <h3 class="text-2xl font-bold text-white">

                    Reward Otomatis

                </h3>

                <p class="mt-5 text-zinc-400 leading-8">

                    Setiap kilogram sampah yang Anda setor akan
                    otomatis dikonversi menjadi saldo maupun poin.

                </p>

            </div>

            <!-- Card 3 -->

            <div class="group bg-zinc-900 border border-zinc-800 rounded-3xl p-8 hover:border-lime-500 transition duration-300 hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(132,204,22,.15)]">

                <div class="w-16 h-16 rounded-2xl bg-lime-500/15 flex items-center justify-center text-3xl mb-8 group-hover:scale-110 duration-300">

                    🌍

                </div>

                <h3 class="text-2xl font-bold text-white">

                    Komunitas Peduli Lingkungan

                </h3>

                <p class="mt-5 text-zinc-400 leading-8">

                    Bergabung bersama ribuan masyarakat yang telah
                    mengurangi limbah sekaligus mendapatkan penghasilan.

                </p>

            </div>

        </div>

    </div>

</section>

<section id="cara-kerja" class="relative py-28 bg-zinc-900 overflow-hidden">

    <!-- Background -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-lime-500/5 blur-[180px] rounded-full"></div>

    <div class="relative max-w-7xl mx-auto px-6">

        <!-- Heading -->

        <div class="text-center max-w-3xl mx-auto">

            <span class="inline-flex px-5 py-2 rounded-full bg-lime-500/10 text-lime-400 border border-lime-500/20 font-semibold">
                CARA KERJA
            </span>

            <h2 class="mt-6 text-5xl font-black text-white">
                Hanya
                <span class="text-lime-400">
                    3 Langkah Mudah
                </span>
            </h2>

            <p class="mt-6 text-xl text-zinc-400 leading-8">

                Menabung sampah kini lebih mudah, cepat,
                dan transparan melalui platform digital kami.

            </p>

        </div>

        <!-- Timeline -->

        <div class="relative mt-24">

            <!-- garis -->
            <div class="hidden lg:block absolute top-10 left-0 right-0 h-1 bg-zinc-800"></div>

            <div class="grid lg:grid-cols-3 gap-10 relative steps-grid">

                <!-- STEP 1 -->

                <div class="relative text-center">

                    <div class="relative z-10 w-20 h-20 mx-auto rounded-full bg-lime-500 text-black flex items-center justify-center text-3xl font-black shadow-xl">

                        1

                    </div>

                    <div class="mt-10 bg-zinc-950 border border-zinc-800 rounded-3xl p-8 hover:border-lime-500 transition duration-300 hover:-translate-y-2">

                        <div class="text-6xl mb-6">
                            ♻️
                        </div>

                        <h3 class="text-2xl font-bold text-white">

                            Pilih Sampah

                        </h3>

                        <p class="mt-5 text-zinc-400 leading-8">

                            Pisahkan sampah berdasarkan kategori
                            seperti plastik, kertas, logam,
                            maupun botol.

                        </p>

                    </div>

                </div>

                <!-- STEP 2 -->

                <div class="relative text-center">

                    <div class="relative z-10 w-20 h-20 mx-auto rounded-full bg-lime-500 text-black flex items-center justify-center text-3xl font-black shadow-xl">

                        2

                    </div>

                    <div class="mt-10 bg-zinc-950 border border-zinc-800 rounded-3xl p-8 hover:border-lime-500 transition duration-300 hover:-translate-y-2">

                        <div class="text-6xl mb-6">
                            🚚
                        </div>

                        <h3 class="text-2xl font-bold text-white">

                            Setor Sampah

                        </h3>

                        <p class="mt-5 text-zinc-400 leading-8">

                            Datang ke Bank Sampah terdekat
                            atau jadwalkan penjemputan
                            apabila tersedia.

                        </p>

                    </div>

                </div>

                <!-- STEP 3 -->

                <div class="relative text-center">

                    <div class="relative z-10 w-20 h-20 mx-auto rounded-full bg-lime-500 text-black flex items-center justify-center text-3xl font-black shadow-xl">

                        3

                    </div>

                    <div class="mt-10 bg-zinc-950 border border-zinc-800 rounded-3xl p-8 hover:border-lime-500 transition duration-300 hover:-translate-y-2">

                        <div class="text-6xl mb-6">
                            💵
                        </div>

                        <h3 class="text-2xl font-bold text-white">

                            Terima Reward

                        </h3>

                        <p class="mt-5 text-zinc-400 leading-8">

                            Nilai sampah otomatis masuk
                            menjadi saldo maupun poin
                            reward Anda.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<section class="py-24 bg-zinc-950">

    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">

            <h2 class="text-5xl font-black text-white">

                Jenis Sampah yang Kami Terima

            </h2>

            <p class="mt-5 text-zinc-400 text-lg">

                Semua kategori memiliki harga sesuai kualitas dan berat.

            </p>

        </div>

        <div class="grid grid-cols-2 lg:grid-cols-6 gap-6">

            <div class="bg-zinc-900 rounded-3xl p-8 text-center border border-zinc-800 hover:border-lime-500 duration-300">

                <div class="text-5xl">🧴</div>

                <h4 class="mt-5 font-bold text-xl text-white">

                    Plastik

                </h4>

            </div>

            <div class="bg-zinc-900 rounded-3xl p-8 text-center border border-zinc-800 hover:border-lime-500">

                <div class="text-5xl">📄</div>

                <h4 class="mt-5 font-bold text-xl text-white">

                    Kertas

                </h4>

            </div>

            <div class="bg-zinc-900 rounded-3xl p-8 text-center border border-zinc-800 hover:border-lime-500">

                <div class="text-5xl">📦</div>

                <h4 class="mt-5 font-bold text-xl text-white">

                    Kardus

                </h4>

            </div>

            <div class="bg-zinc-900 rounded-3xl p-8 text-center border border-zinc-800 hover:border-lime-500">

                <div class="text-5xl">🥫</div>

                <h4 class="mt-5 font-bold text-xl text-white">

                    Kaleng

                </h4>

            </div>

            <div class="bg-zinc-900 rounded-3xl p-8 text-center border border-zinc-800 hover:border-lime-500">

                <div class="text-5xl">🔋</div>

                <h4 class="mt-5 font-bold text-xl text-white">

                    Elektronik

                </h4>

            </div>

            <div class="bg-zinc-900 rounded-3xl p-8 text-center border border-zinc-800 hover:border-lime-500">

                <div class="text-5xl">🍾</div>

                <h4 class="mt-5 font-bold text-xl text-white">

                    Botol

                </h4>

            </div>

        </div>

    </div>

</section>

<section class="py-28 bg-zinc-900">

    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">

            <span class="inline-block px-5 py-2 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-400 font-semibold">
                TESTIMONI
            </span>

            <h2 class="text-5xl font-black text-white mt-6">

                Apa Kata Mereka?

            </h2>

            <p class="text-zinc-400 text-lg mt-4">

                Ribuan masyarakat telah bergabung bersama Bank Sampah Digital.

            </p>

        </div>

        <div class="grid lg:grid-cols-3 gap-8">

            <div class="bg-zinc-950 rounded-3xl border border-zinc-800 p-8 hover:border-lime-500 transition">

                <div class="text-yellow-400 text-xl">★★★★★</div>

                <p class="mt-6 text-zinc-400 leading-8">

                    "Saya jadi lebih semangat memilah sampah karena sekarang bisa menjadi tabungan."

                </p>

                <div class="mt-8">

                    <h4 class="font-bold text-white">

                        Andi Saputra

                    </h4>

                    <p class="text-zinc-500">

                        Nasabah

                    </p>

                </div>

            </div>

            <div class="bg-zinc-950 rounded-3xl border border-zinc-800 p-8 hover:border-lime-500 transition">

                <div class="text-yellow-400 text-xl">★★★★★</div>

                <p class="mt-6 text-zinc-400 leading-8">

                    "Proses setor cepat, saldo langsung masuk dan bisa dipantau melalui sistem."

                </p>

                <div class="mt-8">

                    <h4 class="font-bold text-white">

                        Siti Rahma

                    </h4>

                    <p class="text-zinc-500">

                        nasabah

                    </p>

                </div>

            </div>

            <div class="bg-zinc-950 rounded-3xl border border-zinc-800 p-8 hover:border-lime-500 transition">

                <div class="text-yellow-400 text-xl">★★★★★</div>

                <p class="mt-6 text-zinc-400 leading-8">

                    "Website sangat mudah digunakan bahkan untuk orang tua."

                </p>

                <div class="mt-8">

                    <h4 class="font-bold text-white">

                        Budi Hartono

                    </h4>

                    <p class="text-zinc-500">

                        Petugas

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<section id="faq" class="py-28 bg-zinc-950">

    <div class="max-w-5xl mx-auto px-6">

        <div class="text-center">

            <h2 class="text-5xl font-black text-white">

                Pertanyaan yang Sering Ditanyakan

            </h2>

            <p class="text-zinc-400 mt-5 text-lg">

                Temukan jawaban dari pertanyaan umum mengenai Bank Sampah.

            </p>

        </div>

        <div class="mt-16 space-y-6">

            <details class="bg-zinc-900 rounded-2xl border border-zinc-800 p-6">

                <summary class="cursor-pointer text-xl font-bold text-white">

                    Bagaimana cara mulai menabung sampah?

                </summary>

                <p class="mt-4 text-zinc-400">

                    Daftar akun, datang ke Bank Sampah terdekat,
                    kemudian setor sampah yang telah dipilah.

                </p>

            </details>

            <details class="bg-zinc-900 rounded-2xl border border-zinc-800 p-6">

                <summary class="cursor-pointer text-xl font-bold text-white">

                    Apakah semua sampah diterima?

                </summary>

                <p class="mt-4 text-zinc-400">

                    Tidak, hanya kategori yang memiliki nilai jual.

                </p>

            </details>

            <details class="bg-zinc-900 rounded-2xl border border-zinc-800 p-6">

                <summary class="cursor-pointer text-xl font-bold text-white">

                    Bagaimana saldo dicairkan?

                </summary>

                <p class="mt-4 text-zinc-400">

                    Saldo dapat dicairkan sesuai kebijakan Bank Sampah.

                </p>

            </details>

        </div>

    </div>

</section>

<section class="py-28 bg-gradient-to-r from-lime-500 to-emerald-500">

    <div class="max-w-6xl mx-auto px-6">

        <div class="text-center">

            <h2 class="text-5xl font-black text-black">

                Mulai Menabung Sampah Hari Ini

            </h2>

            <p class="text-black/80 text-xl mt-6">

                Bersama kita ciptakan lingkungan yang bersih,
                sekaligus memperoleh penghasilan tambahan.

            </p>

            <div class="mt-10">

                <a href="register.php"
                    class="px-10 py-5 rounded-2xl bg-black text-white font-bold hover:scale-105 transition">

                    Daftar Sekarang

                </a>

            </div>

        </div>

    </div>

</section>

<footer id="kontak" class="bg-zinc-950 border-t border-zinc-800">

    <div class="max-w-7xl mx-auto px-6 py-20">

        <div class="grid lg:grid-cols-4 gap-12 footer-grid">

            <div>

                <h3 class="text-3xl font-black text-white">

                    🌱 Bank Sampah

                </h3>

                <p class="text-zinc-400 mt-6 leading-8">

                    Platform digital untuk membantu masyarakat mengelola sampah menjadi sesuatu yang bernilai.

                </p>

            </div>

            <div>

                <h4 class="font-bold text-white text-xl mb-6">

                    Menu

                </h4>

                <ul class="space-y-3 text-zinc-400">

                    <li><a href="#">Home</a></li>

                    <li><a href="#fitur">Fitur</a></li>

                    <li><a href="#cara-kerja">Cara Kerja</a></li>

                    <li><a href="#faq">FAQ</a></li>

                </ul>

            </div>

            <div>

                <h4 class="font-bold text-white text-xl mb-6">

                    Kontak

                </h4>

                <ul class="space-y-3 text-zinc-400">

                    <li>📍 Jakarta</li>

                    <li>📞 0812-3456-7890</li>

                    <li>✉ info@banksampah.id</li>

                </ul>

            </div>

            <div>

                <h4 class="font-bold text-white text-xl mb-6">

                    Ikuti Kami

                </h4>

                <div class="flex gap-4 text-3xl">

                    <a href="#">📘</a>

                    <a href="#">📷</a>

                    <a href="#">▶</a>

                    <a href="#">💬</a>

                </div>

            </div>

        </div>

        <div class="border-t border-zinc-800 mt-16 pt-8 text-center text-zinc-500">

            © <?= date('Y') ?> Bank Sampah. All Rights Reserved.

        </div>

    </div>

</footer>

</body>

</html>