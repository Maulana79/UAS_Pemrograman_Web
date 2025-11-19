<?php
require_once __DIR__ . '/supabase.php';

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) jsonError("Order ID diperlukan", 400);

try {
    // Query Kompleks untuk menyatukan data yang terpisah-pisah
    $sql = "
        SELECT 
            t.order_id, t.total_amount, t.status, t.snap_token,
            c.name as customer_name, c.email,
            td.quantity, td.visit_date, td.price_at_purchase,
            p.name as product_name, p.image_url
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        JOIN transaction_details td ON td.transaction_id = t.id
        JOIN products p ON td.product_id = p.id
        WHERE t.order_id = :oid
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['oid' => $orderId]);
    $data = $stmt->fetch();

    if (!$data) jsonError("Transaksi tidak ditemukan", 404);

    jsonSuccess($data);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}