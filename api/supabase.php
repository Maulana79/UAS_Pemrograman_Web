<?php
// api/supabase.php
// MENGHUBUNGKAN KE MYSQL INFINITYFREE

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/response_helper.php';

try {
    // Ambil data dari .env
    $host = get_env('DB_HOST');
    $db   = get_env('DB_NAME');
    $user = get_env('DB_USER');
    $pass = get_env('DB_PASS');

    // Cek apakah .env terbaca
    if (!$host || !$user) {
        throw new Exception("File .env tidak terbaca atau kosong.");
    }

    // String Koneksi MySQL
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (\PDOException $e) {
    // Tampilkan error jika gagal
    die("<h1>Koneksi MySQL Gagal:</h1> " . $e->getMessage());
}