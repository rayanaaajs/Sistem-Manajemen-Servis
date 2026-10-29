<?php

require_once __DIR__ . '/koneksi.php';

$stmt = $pdo->query('SELECT current_database() AS database_name');
$result = $stmt->fetch();

echo "Koneksi ke Supabase berhasil!<br>";
echo "Database: " . htmlspecialchars($result['database_name']);