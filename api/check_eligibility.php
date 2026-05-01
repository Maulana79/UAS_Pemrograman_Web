<?php
require_once __DIR__ . '/supabase.php';

$headers = getallheaders();
$clientKey = $headers['X-Api-Key'] ?? $_GET['api_key'] ?? '';

// 1. Cek Login
$user = getLoggedUser($pdo);
if (!$user) {
    echo json_encode(['status' => 'success', 'data' => ['eligible' => false, 'reason' => 'Belum Login']]);
    exit;
}

$productId = $_GET['product_id'] ?? null;
if (!$productId) {
    echo json_encode(['status' => 'success', 'data' => ['eligible' => false]]);
    exit;
}

try {
    // 2. HITUNG JUMLAH TRANSAKSI SUKSES (Beli Berapa Kali?)
    // Kita hitung berdasarkan Transaction ID yang unik
    $sqlCountBuy = "
        SELECT COUNT(DISTINCT t.id) as total_bought
        FROM transaction_details td
        JOIN transactions t ON td.transaction_id = t.id
        WHERE t.customer_id = :cid 
          AND td.product_id = :pid 
          AND t.status = 'paid'
    ";
    $stmtBuy = $pdo->prepare($sqlCountBuy);
    $stmtBuy->execute(['cid' => $user['id'], 'pid' => $productId]);
    $bought = $stmtBuy->fetch()['total_bought'];

    // 3. HITUNG JUMLAH REVIEW YANG SUDAH DIBUAT (Review Berapa Kali?)
    $sqlCountReview = "SELECT COUNT(*) as total_reviewed FROM reviews WHERE customer_id = ? AND product_id = ?";
    $stmtRev = $pdo->prepare($sqlCountReview);
    $stmtRev->execute([$user['id'], $productId]);
    $reviewed = $stmtRev->fetch()['total_reviewed'];

    // 4. BANDINGKAN
    // Jika jumlah review masih kurang dari jumlah beli, BOLEH review lagi.
    if ($bought > $reviewed) {
        echo json_encode([
            'status' => 'success', 
            'data' => ['eligible' => true, 'remaining' => ($bought - $reviewed)]
        ]);
    } else {
        // Jika review sudah sama atau lebih banyak dari beli, TOLAK.
        echo json_encode([
            'status' => 'success', 
            'data' => [
                'eligible' => false, 
                'reason' => "Anda sudah menggunakan semua kesempatan review ($reviewed review dari $bought transaksi)."
            ]
        ]);
    }

} catch (Exception $e) {
    jsonError($e->getMessage());
}