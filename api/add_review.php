<?php
require_once __DIR__ . '/supabase.php';

// 1. Wajib Login
$user = getLoggedUser($pdo);
if (!$user) jsonError("Silakan login untuk memberikan ulasan.", 401);

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;
$rating    = $input['rating'] ?? 0;
$comment   = $input['comment'] ?? '';

if (!$productId || $rating < 1) jsonError("Rating wajib diisi!", 400);

try {
        // 1. HITUNG JUMLAH BELI
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

        if ($bought == 0) {
            jsonError("Anda belum pernah membeli tiket wisata ini.", 403);
        }

        // 2. HITUNG JUMLAH REVIEW SEKARANG
        $sqlCountReview = "SELECT COUNT(*) as total_reviewed FROM reviews WHERE customer_id = ? AND product_id = ?";
        $stmtRev = $pdo->prepare($sqlCountReview);
        $stmtRev->execute([$user['id'], $productId]);
        $reviewed = $stmtRev->fetch()['total_reviewed'];

        // 3. CEK KUOTA
        if ($reviewed >= $bought) {
            jsonError("Kuota review habis! Anda membeli $bought kali dan sudah mereview $reviewed kali.", 400);
        }

        // 4. Simpan (Lolos Validasi)
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, customer_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$productId, $user['id'], $rating, $comment]);

        jsonSuccess([], "Ulasan berhasil dikirim!");

    } catch (Exception $e) {
        jsonError($e->getMessage(), 500);
	}