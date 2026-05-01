<?php
require_once __DIR__ . '/supabase.php';

$productId = $_GET['product_id'] ?? null;

try {
    if ($productId) {
        // LOGIKA 1: Review Per Produk (Untuk Halaman Detail)
        $sql = "
            SELECT r.rating, r.comment, r.created_at, c.name as reviewer_name
            FROM reviews r
            JOIN customers c ON r.customer_id = c.id
            WHERE r.product_id = :pid
            ORDER BY r.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['pid' => $productId]);
    } else {
        // LOGIKA 2: Review Global Terbaru (Untuk Halaman Depan)
        // Ambil 3 review terakhir dari semua wisata
        $sql = "
            SELECT r.rating, r.comment, c.name as reviewer_name, p.name as product_name
            FROM reviews r
            JOIN customers c ON r.customer_id = c.id
            JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC
            LIMIT 3
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $reviews = $stmt->fetchAll();
    jsonSuccess($reviews);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}