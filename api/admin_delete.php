<?php
require_once __DIR__ . '/supabase.php';

$headers = getallheaders();
$clientKey = $headers['X-Api-Key'] ?? $_POST['api_key'] ?? ''; // Cek POST juga
// Cek juga JSON input kalau fetch kirim JSON
$input = json_decode(file_get_contents('php://input'), true);
$clientKeyJSON = $headers['X-Api-Key'] ?? '';

if ($clientKey !== get_env('API_KEY') && $clientKeyJSON !== get_env('API_KEY')) {
    jsonError("Akses Ditolak", 401);
}

$id = $_POST['id'] ?? $input['id'] ?? null;

if (!$id) jsonError("ID Produk diperlukan", 400);

try {
    // Cek dulu data (untuk hapus gambar kalau perlu)
    /*
    $stmtCheck = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmtCheck->execute([$id]);
    $data = $stmtCheck->fetch();
    if($data && $data['image_url'] != 'default.jpg') {
        @unlink(dirname(__DIR__) . '/foto/' . $data['image_url']);
    }
    */

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    jsonSuccess([], "Produk berhasil dihapus!");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}