<?php
// api/admin_stats.php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/supabase.php';

// 1. Cek Apakah User adalah ADMIN
$user = getLoggedUser($pdo);
if (!$user || $user['role'] !== 'admin') {
    jsonError("Akses Ditolak. Khusus Admin.", 403);
}

try {
    // A. RINGKASAN ATAS (Total Pendapatan & Jumlah Transaksi Sukses)
    $stmtSummary = $pdo->query("SELECT 
        SUM(total_amount) as total_revenue,
        COUNT(*) as total_transactions
        FROM transactions 
        WHERE status = 'paid'
    ");
    $summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

    // B. DATA UNTUK GRAFIK (Tiket Terlaris)
    // Menghitung jumlah tiket yang terjual per Produk
    $stmtChart = $pdo->query("SELECT 
        p.name as product_name, 
        SUM(td.quantity) as total_sold
        FROM transaction_details td
        JOIN transactions t ON td.transaction_id = t.id
        JOIN products p ON td.product_id = p.id
        WHERE t.status = 'paid'
        GROUP BY p.name
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    // C. TRANSAKSI TERBARU (5 Terakhir)
    $stmtRecent = $pdo->query("SELECT 
        t.order_id, c.name as customer_name, t.total_amount, t.status, t.created_at
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $recentOrders = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess([
        'revenue' => $summary['total_revenue'] ?? 0,
        'transactions' => $summary['total_transactions'] ?? 0,
        'chart_data' => $chartData,
        'recent_orders' => $recentOrders
    ]);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}