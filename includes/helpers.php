<?php
// Membungkus output data yang berasal dari input pengguna sebelum
// dicetak ke HTML, untuk mencegah XSS (Cross-Site Scripting).
function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk bikin badge/kapsul status otomatis
function badgeStatus($status) {
    // 1. Class dasar yang dipakai semua kapsul (bentuk, ukuran, font)
    $baseClass = "inline-flex items-center justify-center px-6 py-2 rounded-full text-xs font-semibold transition-colors border-transparent";

    // 2. Tentukan warna berdasarkan nama status
    // (Warna HEX disesuaikan bayangan dari gambarmu)
    $colorClass = match(strtolower(trim($status))) {
        // Hijau
        'selesai', 'aktif' => 'bg-[#E9F2EC] text-[#1F7A45] border-[#1F7A45]',
        // Biru
        'dikerjakan' => 'bg-[#E6F5F9] text-[#0277BD] border-[#0277BD]',
        // Kuning/Coklat
        'diterima' => 'bg-[#FEF9EF] text-[#8A5A00] border-[#8A5A00]',
        // Merah (Tampilan Outline/Border)
        'express' => 'bg-[#FFFEFD] text-[#AF3A26] border-[#FFFEFD]',
        // Merah Pudar
        'dibatalkan' => 'bg-[#F9ECEA] text-[#742619] border-[#742619]',
        // Hitam/Gelap (Outline)
        'reguler' => 'bg-[#FFFEFD] text-[#090D1F] border-[#FFFEFD]',
        // Abu-abu keunguan
        'admin' => 'bg-[#E8E9EE] text-[#141C43] border-[#141C43]',
        // Abu-abu
        'menunggu' => 'bg-[#E8E9EE] text-[#595752] border-[#595752]',
        // Orange/Peach
        'teknisi' => 'bg-[#FEF0E9] text-[#AF3A26] border-[#AF3A26]',
        
        // Default kalau statusnya gak dikenali
        default => 'bg-gray-100 text-gray-600 border-gray-100'
    };

    // 3. Kembalikan elemen HTML utuh
    return "<span class=\"{$baseClass} {$colorClass}\">" . htmlspecialchars($status) . "</span>";
}