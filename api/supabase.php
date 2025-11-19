<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/response_helper.php';

try {
    $host = get_env('SUPABASE_HOST');
    $port = get_env('SUPABASE_PORT');
    $db   = get_env('SUPABASE_DB');
    $user = get_env('SUPABASE_USER');
    $pass = get_env('SUPABASE_PASS');

    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (\PDOException $e) {
    error_log("Database Error: " . $e->getMessage()); 
    jsonError("Koneksi database gagal.", 500);
}