<?php
require_once __DIR__ . '/supabase.php';

// Security Check
$headers = getallheaders();
$clientKey = $headers['X-Api-Key'] ?? $_GET['api_key'] ?? '';
if ($clientKey !== get_env('API_KEY')) jsonError("Akses Ditolak", 401);

try {
    // Query MySQL Complex Join
    // Kita ambil: ID Order, Nama User, Nama Wisata, Total, Status, Tanggal
    $sql = "
        SELECT 
            t.id, t.order_id, t.total_amount, t.status, t.created_at,
            c.name as customer_name, c.email as customer_email,
            p.name as product_name, td.quantity, td.visit_date
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        JOIN transaction_details td ON td.transaction_id = t.id
        JOIN products p ON td.product_id = p.id
        ORDER BY t.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll();

    jsonSuccess($data);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}