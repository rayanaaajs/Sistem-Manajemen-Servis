<?php

$envFile = dirname(__DIR__) . '/.env';
$env = [];

if (is_readable($envFile)) {
    $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if ($parsed !== false) {
        $env = $parsed;
    }
}

$getEnvValue = static function (string $key, string $default = '') use ($env): string {
    if (array_key_exists($key, $env) && $env[$key] !== '') {
        return (string) $env[$key];
    }

    $fromGetenv = getenv($key);
    if ($fromGetenv !== false && $fromGetenv !== '') {
        return (string) $fromGetenv;
    }

    if (!empty($_ENV[$key])) {
        return (string) $_ENV[$key];
    }

    if (!empty($_SERVER[$key])) {
        return (string) $_SERVER[$key];
    }

    return $default;
};

$host     = $getEnvValue('DB_HOST');
$port     = $getEnvValue('DB_PORT', '5432');
$dbname   = $getEnvValue('DB_NAME', 'postgres');
$user     = $getEnvValue('DB_USER');
$password = $getEnvValue('DB_PASSWORD');

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
