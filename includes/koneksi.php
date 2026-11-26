<?php

$envFile = dirname(__DIR__) . '/.env';

if (!is_readable($envFile)) {
    exit('File .env tidak ditemukan.');
}

$env = parse_ini_file($envFile, false, INI_SCANNER_RAW);

if ($env === false) {
    exit('File .env tidak valid.');
}

$host = $env['DB_HOST'] ?? '';
$port = $env['DB_PORT'] ?? '5432';
$dbname = $env['DB_NAME'] ?? 'postgres';
$user = $env['DB_USER'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    exit('Koneksi database gagal.');
}