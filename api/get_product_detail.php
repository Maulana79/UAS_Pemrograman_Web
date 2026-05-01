<?php
require_once __DIR__ . '/supabase.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    jsonError("ID Produk diperlukan", 400);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        jsonError("Produk tidak ditemukan", 404);
    }

    jsonSuccess($product);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}