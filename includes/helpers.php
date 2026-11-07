<?php

function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function badgeStatus($status)
{
    // 1. Class dasar yang dipakai semua kapsul (bentuk, ukuran, font)
    $baseClass = "inline-flex items-center justify-center w-28 py-2 rounded-full text-xs font-semibold transition-colors border-transparent";


    $colorClass = match (strtolower(trim($status))) {
        'selesai', 'aktif' => 'bg-[#E9F2EC] text-[#1F7A45] border-[#1F7A45]',
        'dikerjakan' => 'bg-[#E6F5F9] text-[#0277BD] border-[#0277BD]',
        'diterima' => 'bg-[#FEF9EF] text-[#8A5A00] border-[#8A5A00]',
        'express' => 'bg-[#FFFEFD] text-[#AF3A26] border-[#FFFEFD]',
        'dibatalkan' => 'bg-[#F9ECEA] text-[#742619] border-[#742619]',
        'reguler' => 'bg-[#FFFEFD] text-[#090D1F] border-[#FFFEFD]',
        'admin' => 'bg-[#E8E9EE] text-[#141C43] border-[#141C43]',
        'menunggu persetujuan' => 'bg-[#FEF9EF] text-[#8A5A00] border-[#8A5A00]',
        'menunggu' => 'bg-[#E8E9EE] text-[#595752] border-[#595752]',
        'teknisi' => 'bg-[#FEF0E9] text-[#AF3A26] border-[#AF3A26]',
        'lunas' => 'bg-[#DDEBE3] text-[#1F7A45] border-[#1F7A45]',
        'nonaktif' => 'bg-[#E8E9EE] text-[#595752] border-[#595752]', // (Atau sesuaikan warna di gambar)
        'aman' => 'bg-[#E9F2EC] text-[#1F7A45] border-[#1F7A45]',
        'menipis' => 'bg-[#FEF6E7] text-[#C79B4A] border-[#C79B4A]',
        'belum bayar' => 'bg-[#E8E9EE] text-[#595752] border-[#595752]',
        'habis' => 'bg-[#FEF0E9] text-[#AF3A26] border-[#AF3A26]',


        // Default kalau statusnya gak dikenali
        default => 'bg-gray-100 text-gray-600 border-gray-100'
    };

    // 3. Kembalikan elemen HTML utuh
    return "<span class=\"{$baseClass} {$colorClass}\">" . e($status) . "</span>";
}


if (!function_exists('convertStatusAdmin')) {
    function convertStatusAdmin($status)
    {
        return match (strtolower(trim($status))) {
            'menunggu' => 'Menunggu',

            'sedang dicek' => 'Diterima',

            'menunggu persetujuan' => 'Menunggu Persetujuan',

            'proses servis' => 'Dikerjakan',

            'selesai' => 'Selesai',

            default => $status,
        };
    }
}


//function buat inisial nama di db admin dan teknisi
if (!function_exists('inisialNama')) {
    function inisialNama($nama, $default = '?')
    {
        $nama = trim((string) $nama);
        if ($nama === '') return $default;

        $kata = preg_split('/\s+/', $nama);

        if (count($kata) === 1) {
            // 1 kata: ambil 2 huruf pertama (Ilham -> IL)
            return mb_strtoupper(mb_substr($kata[0], 0, 2));
        }
        // 2 kata atau lebih: huruf pertama kata pertama + kata terakhir (Sari Lestari -> SL)
        return mb_strtoupper(mb_substr($kata[0], 0, 1) . mb_substr(end($kata), 0, 1));
    }
}

// Format tanggal Indonesia: "1 Okt 2026" atau "1 Okt 2026, 09.40"
if (!function_exists('formatTanggal')) {
    function formatTanggal($ts, $denganJam = false)
    {
        if (empty($ts)) return '-';
        $t = strtotime($ts);
        if (!$t) return '-';
        $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $str = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
        return $denganJam ? $str . ', ' . date('H.i', $t) : $str;
    }
}
