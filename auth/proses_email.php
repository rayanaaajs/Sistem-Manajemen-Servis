<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mundur 1 folder untuk memanggil vendor dari dalam folder auth
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Tangkap email dari input form (POST) ATAU dari URL tombol (GET)
$email_tujuan = $_POST['email'] ?? $_GET['email'] ?? '';

if (!empty($email_tujuan)) {
    try {
        // 2. CEK DATABASE: Apakah email ini ada di tabel karyawan?
        $stmt = $pdo->prepare("SELECT id_karyawan, nama_karyawan FROM karyawan WHERE email = :email");
        $stmt->execute(['email' => $email_tujuan]);
        $user = $stmt->fetch();

        // Jika email ditemukan di Supabase
        if ($user) {

            // 3. GENERATE TOKEN & WAKTU EXPIRED (Misal: 30 Menit)
            $token_baru = bin2hex(random_bytes(16));

            // Set zona waktu WIB dan formatkan ke timestamp PostgreSQL
            date_default_timezone_set('Asia/Jakarta');
            $waktu_sekarang = time();
            $expired = date('Y-m-d H:i:s', strtotime('+30 minutes', $waktu_sekarang));

            // 4. SIMPAN TOKEN KE SUPABASE
            $updateStmt = $pdo->prepare("UPDATE karyawan SET token_reset_password = :token, token_expired = :expired WHERE email = :email");
            $updateStmt->execute([
                'token' => $token_baru,
                'expired' => $expired,
                'email' => $email_tujuan
            ]);

            // 5. KIRIM EMAIL DENGAN PHPMAILER
            // Gunakan pembaca environment yang sama dengan koneksi database.
            $appUrl = rtrim(trim($getEnvValue('APP_URL')), '/');
            $smtp_email = trim($getEnvValue('SMTP_EMAIL'));
            $smtp_pass = str_replace(' ', '', trim($getEnvValue('SMTP_PASSWORD')));

            if ($smtp_email === '' || $smtp_pass === '') {
                throw new Exception('SMTP_EMAIL atau SMTP_PASSWORD belum tersedia.');
            }
            if (!filter_var($appUrl, FILTER_VALIDATE_URL) ||
                !in_array(parse_url($appUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
                throw new Exception('APP_URL belum tersedia atau tidak valid.');
            }

            $link_reset = htmlspecialchars(
                $appUrl . '/auth/reset_password.php?token=' . rawurlencode($token_baru),
                ENT_QUOTES,
                'UTF-8'
            );

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_email;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->Timeout = 15;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($smtp_email, 'Admin Singhasari Computer');
            $mail->addAddress($email_tujuan);

            $mail->isHTML(true);
            $mail->Subject = 'Permintaan Reset Kata Sandi';
            $mail->Body    = "Halo " . htmlspecialchars($user['nama_karyawan']) . ",<br><br>
                              Admin telah mengirim link reset kata sandi untuk akun Anda.<br>
                              Klik tautan di bawah ini untuk membuat sandi baru.<br><br>
                              <a href='$link_reset'>$link_reset</a><br><br>
                              Jika Anda tidak meminta ini, abaikan saja email ini.";

            $mail->send();

           if (isset($_POST['email'])) {
                echo "<script>alert('Link reset telah dikirim ke email!'); window.location.href='lupa_password.php';</script>";
            } else {
                // Tangkap ID yang dititipkan dari tombol tadi
                $id_kembali = $_GET['id'] ?? '';
                echo "<script>alert('Link reset teknisi telah dikirim!'); window.location.href='../admin/edit_karyawan.php?id=" . $id_kembali . "';</script>";
            }
        } else {
            echo "<script>alert('Email tidak terdaftar di sistem!'); window.history.back();</script>";
        }
    } catch (\PDOException $e) {
        error_log('Reset password database: ' . $e->getMessage());
        echo "<script>alert('Terjadi kesalahan pada database Supabase!'); window.history.back();</script>";
    } catch (Exception $e) {
        error_log('Reset password PHPMailer: ' . $e->getMessage());
        echo "<script>alert('Gagal mengirim email. Silakan coba lagi.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Email tidak valid!'); window.history.back();</script>";
}
