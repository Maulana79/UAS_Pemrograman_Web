<?php
require_once __DIR__ . '/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError("Method not allowed", 405);

$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? null;

if (!$orderId) jsonError("Order ID wajib diisi", 400);

try {
    $pdo->beginTransaction();

    // 1. Ambil Data Lengkap (Join 3 Tabel)
    $sql = "
        SELECT 
            t.id as trx_id, t.status, t.order_id,
            c.name as customer_name,
            td.id as detail_id, td.is_redeemed, td.visit_date, td.quantity,
            p.name as product_name
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        JOIN transaction_details td ON td.transaction_id = t.id
        JOIN products p ON td.product_id = p.id
        WHERE t.order_id = :oid
        FOR UPDATE
    "; // FOR UPDATE mengunci baris agar tidak di-scan 2x bersamaan

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['oid' => $orderId]);
    $ticket = $stmt->fetch();

    if (!$ticket) throw new Exception("Tiket tidak ditemukan!");

    // 2. Validasi
    if ($ticket['status'] !== 'paid') {
        throw new Exception("Tiket belum lunas! Status: " . $ticket['status']);
    }

    if ($ticket['is_redeemed']) {
        throw new Exception("Tiket SUDAH DIPAKAI sebelumnya!");
    }

    // 3. Redeem (Update di tabel detail)
    $update = $pdo->prepare("UPDATE transaction_details SET is_redeemed = TRUE, redeemed_at = NOW() WHERE id = :did");
    $update->execute(['did' => $ticket['detail_id']]);

    $pdo->commit();

    jsonSuccess([
        'message' => 'AKSES DITERIMA',
        'visitor' => $ticket['customer_name'],
        'ticket' => $ticket['product_name'],
        'quantity' => $ticket['quantity'],
        'visit_date' => $ticket['visit_date']
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonError($e->getMessage(), 400);
}