<?php
require_once __DIR__ . '/supabase.php';

try {
    // 1. Hitung Total Pendapatan (Omzet)
    // Ini tetap ambil dari tabel transactions karena total_amount masih ada di sana
    $stmtSum = $pdo->query("SELECT SUM(total_amount) as omzet FROM transactions WHERE status = 'paid'");
    $omzet = $stmtSum->fetch()['omzet'] ?? 0;

    // 2. Hitung Tiket Terjual Hari Ini (PERBAIKAN DI SINI)
    // Kita harus JOIN ke tabel transaction_details karena 'quantity' ada di sana
    $sqlSold = "
        SELECT SUM(td.quantity) as terjual 
        FROM transaction_details td
        JOIN transactions t ON td.transaction_id = t.id
        WHERE t.status = 'paid' 
        AND DATE(t.created_at) = CURRENT_DATE
    ";
    $stmtSold = $pdo->query($sqlSold);
    $terjualHariIni = $stmtSold->fetch()['terjual'] ?? 0;

    // 3. List 5 Transaksi Terakhir
    // Kita ambil nama customer dari tabel customers (JOIN lagi)
    $stmtRecent = $pdo->query("
        SELECT t.order_id, c.name as customer_name, t.total_amount, t.status, t.created_at 
        FROM transactions t 
        JOIN customers c ON t.customer_id = c.id
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
    // Tampilkan error asli jika ada masalah query lagi
    if (ini_get('display_errors')) {
        die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
    }
    jsonError("Gagal memuat dashboard.", 500);
}