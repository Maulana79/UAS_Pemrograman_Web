<?php
require_once __DIR__ . '/supabase.php';

// 1. Cek Login (Wajib)
$user = getLoggedUser($pdo);
if (!$user) jsonError("Unauthorized", 401);

try {
    // 2. Ambil Transaksi User
    // Kita ambil transaksi yang statusnya BUKAN 'pending' (atau tampilkan semua juga boleh)
    $sql = "
        SELECT 
            t.order_id, t.total_amount, t.status, t.created_at, t.snap_token,
            p.name as product_name, p.image_url,
            td.quantity, td.visit_date, td.is_redeemed
        FROM transactions t
        JOIN transaction_details td ON td.transaction_id = t.id
        JOIN products p ON td.product_id = p.id
        WHERE t.customer_id = :cid
        ORDER BY t.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cid' => $user['id']]);
    $orders = $stmt->fetchAll();

    jsonSuccess($orders);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}