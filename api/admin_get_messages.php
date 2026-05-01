<?php
require_once __DIR__ . '/supabase.php';

$headers = getallheaders();
$clientKey = $headers['X-Api-Key'] ?? $_GET['api_key'] ?? '';
if ($clientKey !== get_env('API_KEY')) jsonError("Akses Ditolak", 401);

try {
    // Ambil pesan urut dari yang terbaru
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
    jsonSuccess($stmt->fetchAll());
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}