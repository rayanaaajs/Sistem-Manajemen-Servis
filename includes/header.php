<?php

// Kembalikan deklarasi variabel $__rel yang sempat terhapus
$__jobsheetRoot = dirname(__DIR__);
$__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
$__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__jobsheetRoot))), '/');
$base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Singhasari Computer</title>
    
    <!-- Google Fonts: Full Poppins (Weights: 300, 400, 500, 600, 700, 800, 900) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Custom Style CSS -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body class="bg-brand-cream font-sans text-gray-800 flex min-h-screen">

    <!-- ================= SIDEBAR ================= -->
    <!-- Overlay Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-60 bg-tosca-active text-white flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
        <!-- Logo Area -->
        <div class="flex flex-col items-center justify-center pt-10 pb-8">
            <!-- Sisipin $base biar path gambar gak error -->
            <img src="<?= $base ?>assets/img/logo-text.png" alt="Singhasari Computer Logo" class="w-35 mb-3">
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-5 mt-4 space-y-2">
            <!-- Link Dashboard -->
            <a href="<?= $base ?>dashboard.php" class="flex items-center gap-4 px-4 py-3.5 active:bg-orange bg-orange rounded-xl text-white font-medium transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.4em" height="1.4em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 11l2.5-2.5c1.017-1.017 1.526-1.526 2.137-1.638a2 2 0 0 1 .726 0c.611.112 1.12.62 2.137 1.638s1.526 1.526 2.137 1.638c.24.045.486.045.726 0c.611-.112 1.12-.621 2.137-1.638L21 3M3 15v6m6-8v8m6-5v5m6-12v12" />
                </svg>
                Dashboard
            </a>

            <!-- Link Karyawan -->
            <a href="<?= $base ?>karyawan.php" class="flex items-center gap-4 px-4 py-3.5 text-gray-300 hover:text-white hover:bg-white/10 active:bg-orange rounded-xl transition-all font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.4em" height="1.4em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h8v1a4 4 0 0 1-8 0zm0 0h8V5a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3zm7 14H4a2 2 0 0 1-2-2a4 4 0 0 1 4-4h4M9 2v2M3.5 7h11m5 15v-1.968A4.5 4.5 0 0 0 22 16c0-1.767-1.018-3.264-2.5-4v2.5a2 2 0 1 1-4 0V12c-1.482.736-2.5 2.233-2.5 4a4.5 4.5 0 0 0 2.5 4.032V22" />
                </svg>
                Karyawan
            </a>

            <!-- Link Antrean -->
            <a href="<?= $base ?>antrean.php" class="flex items-center gap-4 px-4 py-3.5 text-gray-300 hover:text-white hover:bg-white/10 active:bg-orange rounded-xl transition-all font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.4em" height="1.4em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75" />
                </svg>
                Antrean
            </a>

            <!-- Link Pelanggan -->
            <a href="<?= $base ?>pelanggan.php" class="flex items-center gap-4 px-4 py-3.5 text-gray-300 hover:text-white hover:bg-white/10 active:bg-orange  rounded-xl transition-all font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.4em" height="1.4em" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <g fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="7" r="5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 14h.352a3 3 0 0 1 2.976 2.628l.391 3.124A2 2 0 0 1 18.734 22H5.266a2 2 0 0 1-1.985-2.248l.39-3.124A3 3 0 0 1 6.649 14H7" />
                    </g>
                </svg>
                Pelanggan
            </a>

            <!-- Link Sparepart -->
            <a href="<?= $base ?>sparepart.php" class="flex items-center gap-4 px-4 py-3.5 text-gray-300 hover:text-white hover:bg-white/10 active:bg-orange  rounded-xl transition-all font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 16 16">
                    <path d="M0 0h16v16H0z" fill="none" />
                    <path fill="currentColor" d="M5.669 1a.498.498 0 0 0-.667.47v1.816c0 .275-.225.5-.5.5a.5.5 0 0 1-.5-.5V1.47A.5.5 0 0 0 3.335 1a3.5 3.5 0 0 0-2.333 3.287c0 1.359.79 2.589 2 3.162V13.5c0 .827.673 1.5 1.5 1.5s1.5-.673 1.5-1.5V7.449c1.21-.573 2-1.803 2-3.162A3.5 3.5 0 0 0 5.669 1m-.333 5.644a.5.5 0 0 0-.334.472v6.385c0 .275-.225.5-.5.5a.5.5 0 0 1-.5-.5V7.116a.5.5 0 0 0-.334-.472a2.5 2.5 0 0 1-1.666-2.356c0-.792.383-1.523 1-1.987v.987c0 .827.673 1.5 1.5 1.5s1.5-.673 1.5-1.5v-.987c.617.464 1 1.195 1 1.987c0 1.058-.67 2.005-1.666 2.356M13.5 8h-.498V4.118l.447-.895a.5.5 0 0 0 .027-.382l-.5-1.5a.5.5 0 0 0-.475-.342h-2a.5.5 0 0 0-.475.342l-.5 1.5a.5.5 0 0 0 .027.382l.447.895V8h-.5a.5.5 0 0 0-.5.5v4c0 1.379 1.121 2.5 2.5 2.5s2.5-1.121 2.5-2.5v-4a.5.5 0 0 0-.5-.5m-2.638-5.999h1.279l.32.962l-.407.814a.5.5 0 0 0-.053.224v4h-1v-4a.5.5 0 0 0-.052-.224l-.407-.814zm2.14 10.499c0 .827-.673 1.5-1.5 1.5s-1.5-.673-1.5-1.5V9h3z" />
                </svg>
                Sparepart
            </a>
        </nav>

        <!-- Copyright Footer Sidebar -->
        <div class="p-6 text-center text-[12px] text-gray-300 font-light mb-4">
            Copyright <?= date('Y') ?><br>© Singhasari Computer
        </div>
    </aside>

    <!-- ================= MAIN CONTENT WRAPPER ================= -->
    <main class="flex-1 ml-0 md:ml-60 flex flex-col transition-all duration-300">
        
        <!-- TOP NAVBAR -->
        <header class="w-full flex flex-col md:flex-row md:items-center justify-between px-6 md:px-8 py-4 md:py-6 gap-4 md:gap-0">
            
            <!-- Area Kiri Header (Hamburger, Tanggal & Greeting) -->
            <div class="flex items-center gap-4 flex-1">
                <!-- Tombol Hamburger Mobile -->
                <button id="btn-toggle" class="md:hidden p-2 bg-tosca-active rounded-lg  border border-gray-200 text-white-light focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div>
                    <p class="text-sm md:text-md text-white-dark-active font-light mb-0.5">Sabtu, 3 Oktober 2026</p>
                    <h1 class="text-3xl md:text-5xl font-bold text-blue-darker tracking-tight">Halo, Admin</h1>
                </div>
            </div>

            <!-- Area Kanan Header (Search & Profil) -->
            <div class="flex items-center gap-4 md:gap-6 justify-between md:justify-end">
                <!-- Search Bar -->
                <div class="relative flex-1 md:flex-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari nomor nota" class="w-full md:w-75 pl-11 pr-4 py-2.5 bg-white-light border border-gray-300 text-sm rounded-full focus:outline-none focus:ring-1 focus:ring-sidebar focus:border-transparent transition-all">
                </div>

                <!-- Admin Profile Pill -->
                <div class="flex items-center gap-3 bg-tosca-dark text-white p-1.5 md:pr-5 py-1.5 rounded-full cursor-pointer hover:bg-opacity-90 transition-all shrink-0">
                    <div class="w-8 h-8 rounded-full bg-orange flex items-center justify-center font-bold text-xs">
                        AD
                    </div>
                    <span class="font-medium text-sm tracking-wide hidden md:block">Admin</span>
                </div>
            </div>
        </header>

        <!-- AREA KONTEN HALAMAN -->
        <div class="px-6 md:px-8 pb-10">