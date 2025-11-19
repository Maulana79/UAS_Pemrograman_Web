<?php
require_once __DIR__ . '/supabase.php';

// 1. Cek Login
$user = getLoggedUser($pdo);
if (!$user) jsonError("Unauthorized", 401);

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;
$rating    = $input['rating'] ?? 0;
$comment   = $input['comment'] ?? '';

if (!$productId || $rating < 1 || $rating > 5) {
    jsonError("Data rating tidak valid (Bintang 1-5)", 400);
}

try {
    // 2. Validasi: User HARUS pernah beli produk ini & status PAID
    // Kita cek di tabel transaction_details yang join ke transactions
    $sqlCheck = "
        SELECT td.id 
        FROM transaction_details td
        JOIN transactions t ON td.transaction_id = t.id
        WHERE t.customer_id = :cid 
          AND td.product_id = :pid 
          AND t.status = 'paid'
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->execute([
        'cid' => $user['id'],
        'pid' => $productId
    ]);
    
    if ($stmt->rowCount() == 0) {
        jsonError("Anda belum pernah membeli produk ini (atau pembayaran belum lunas).", 403);
    }

    // 3. Cek apakah sudah pernah review sebelumnya? (Opsional, biar ga spam)
    $checkReview = $pdo->prepare("SELECT id FROM reviews WHERE customer_id = ? AND product_id = ?");
    $checkReview->execute([$user['id'], $productId]);
    if ($checkReview->rowCount() > 0) {
        jsonError("Anda sudah memberikan ulasan untuk produk ini.", 400);
    }

    // 4. Simpan Review
    $sqlInsert = "INSERT INTO reviews (product_id, customer_id, rating, comment, created_at) VALUES (:pid, :cid, :rating, :comment, NOW())";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        'pid' => $productId,
        'cid' => $user['id'],
        'rating' => $rating,
        'comment' => $comment
    ]);

    jsonSuccess([], "Terima kasih atas ulasan Anda!");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}