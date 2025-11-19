<?php
// FILE: api/supabase.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/response_helper.php';

try {
    // Ambil dari .env via bootstrap.php
    $host = get_env('SUPABASE_HOST');
    $port = get_env('SUPABASE_PORT');
    $db   = get_env('SUPABASE_DB');
    $user = get_env('SUPABASE_USER');
    $pass = get_env('SUPABASE_PASS');

    // Debugging: Jika masih error, uncomment baris ini untuk lihat apakah env terbaca
    // if (!$host) die("❌ Error: .env tidak terbaca. Cek api/bootstrap.php");

    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (\PDOException $e) {
    die("<h1>Koneksi Gagal:</h1> " . $e->getMessage());
}