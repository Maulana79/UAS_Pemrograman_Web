<?php
require_once __DIR__ . '/supabase.php';

$headers = getallheaders();
$clientKey = $headers['X-Api-Key'] ?? $_GET['api_key'] ?? '';
if ($clientKey !== get_env('API_KEY')) jsonError("Akses Ditolak", 401);

try {
    // Ambil semua user kecuali admin passwordnya (Privacy)
    $stmt = $pdo->query("SELECT id, name, email, phone, role, created_at FROM customers ORDER BY created_at DESC");
    jsonSuccess($stmt->fetchAll());
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}