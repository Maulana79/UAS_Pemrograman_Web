<?php
require_once __DIR__ . '/supabase.php';

try {
    $stmtSum = $pdo->query("SELECT SUM(total_amount) as omzet FROM transactions WHERE status = 'paid'");
    $omzet = $stmtSum->fetch()['omzet'] ?? 0;

    $stmtSold = $pdo->query("SELECT SUM(quantity) as terjual FROM transactions WHERE status = 'paid' AND DATE(created_at) = CURRENT_DATE");
    $terjualHariIni = $stmtSold->fetch()['terjual'] ?? 0;

    $stmtRecent = $pdo->query("
        SELECT t.order_id, t.customer_name, t.total_amount, t.status, t.created_at 
        FROM transactions t 
        ORDER BY t.created_at DESC 
        LIMIT 5
    ");
    $recent = $stmtRecent->fetchAll();

    jsonSuccess([
        'total_revenue' => (int)$omzet,
        'tickets_sold_today' => (int)$terjualHariIni,
        'recent_transactions' => $recent
    ]);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}