<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mundur 1 folder untuk memanggil vendor dari dalam folder auth
require '../includes/koneksi.php';
require '../vendor/autoload.php';

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
            $link_reset = "http://localhost:8000/auth/reset_password.php?token=" . $token_baru;

            // --- PEMBACA .ENV ANTI GAGAL ---
            $env_file = __DIR__ . '/../.env';
            $smtp_email = '';
            $smtp_pass = '';

            if (file_exists($env_file)) {
                $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) continue; // Abaikan baris komentar
                    $parts = explode('=', $line, 2);
                    if (count($parts) == 2) {
                        $key = trim($parts[0]);
                        $value = trim(trim($parts[1]), '"\''); // Hapus otomatis tanda kutip
                        if ($key === 'SMTP_EMAIL') $smtp_email = $value;
                        if ($key === 'SMTP_PASSWORD') $smtp_pass = $value;
                    }
                }
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            
            // Gunakan variabel yang ditarik manual dari brankas
            $mail->Username   = $smtp_email; 
            $mail->Password   = $smtp_pass; 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Alamat Pengirim & Penerima
            $mail->setFrom($smtp_email, 'Admin Singhasari Computer');
            $mail->addAddress($email_tujuan); 

            // Mengambil email dan sandi dari brankas .env
            $mail->Username   = $smtp_email;
            $mail->Password   = $smtp_pass;

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Pastikan pengirim juga mengambil dari .env
            $mail->setFrom($smtp_email, 'Admin Singhasari Computer');

            // Ubah menjadi dinamis! Email akan dikirim ke alamat yang diinputkan
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
                echo "<script>alert('Link reset teknisi telah dikirim!'); window.location.href='../admin/edit_karyawan.php';</script>";
            }
        } else {
            echo "<script>alert('Email tidak terdaftar di sistem!'); window.history.back();</script>";
        }
    } catch (\PDOException $e) {
        echo "<script>alert('Terjadi kesalahan pada database Supabase!'); window.history.back();</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal mengirim email: {$mail->ErrorInfo}'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Email tidak valid!'); window.history.back();</script>";
}
