<?php
// File: api/notification.php

require_once __DIR__ . '/supabase.php';
require_once __DIR__ . '/email_helper.php'; // Import helper email

// Ambil Server Key untuk (Opsional) validasi signature
$serverKey = get_env('MIDTRANS_SERVER_KEY');

try {
    // 1. Ambil JSON dari Midtrans
    $json = file_get_contents('php://input');
    $notif = json_decode($json, true);

    if (!$notif) throw new Exception("Invalid JSON");

    $transactionStatus = $notif['transaction_status'];
    $paymentType = $notif['payment_type'];
    $orderId = $notif['order_id'];
    $fraudStatus = $notif['fraud_status'] ?? '';

    // 2. Tentukan Status Database
    $status = 'pending';
    if ($transactionStatus == 'capture') {
        if ($paymentType == 'credit_card') {
            $status = ($fraudStatus == 'challenge') ? 'challenge' : 'paid';
        }
    } else if ($transactionStatus == 'settlement') {
        $status = 'paid';
    } else if ($transactionStatus == 'pending') {
        $status = 'pending';
    } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
        $status = 'failed';
    }

    // 3. Update Status di Tabel Transactions
    $stmt = $pdo->prepare("UPDATE transactions SET status = :status, payment_type = :type, updated_at = NOW() WHERE order_id = :oid");
    $stmt->execute([
        'status' => $status,
        'type' => $paymentType,
        'oid' => $orderId
    ]);

    // --- FITUR 1: JIKA PAID -> KIRIM EMAIL ---
    if ($status == 'paid') {
        // Ambil data lengkap untuk isi email (Join 4 Tabel)
        $sqlEmail = "
            SELECT 
                c.email, c.name, 
                p.name as product_name, 
                td.quantity, td.visit_date
            FROM transactions t
            JOIN customers c ON t.customer_id = c.id
            JOIN transaction_details td ON td.transaction_id = t.id
            JOIN products p ON td.product_id = p.id
            WHERE t.order_id = :oid
            LIMIT 1
        ";
        
        $stmtEm = $pdo->prepare($sqlEmail);
        $stmtEm->execute(['oid' => $orderId]);
        $data = $stmtEm->fetch();

        if ($data) {
            // Panggil fungsi dari email_helper.php
            sendEmailTicket(
                $data['email'], 
                $data['name'], 
                $orderId, 
                $data['product_name'], 
                $data['quantity'], 
                $data['visit_date']
            );
        }
    }

    // --- FITUR 2: JIKA FAILED -> BALIKIN STOK ---
    if ($status == 'failed') {
        // Ambil item yang dibeli di transaksi ini
        $sqlRestore = "
            SELECT td.product_id, td.quantity 
            FROM transaction_details td
            JOIN transactions t ON td.transaction_id = t.id
            WHERE t.order_id = :oid
        ";
        $stmtRestore = $pdo->prepare($sqlRestore);
        $stmtRestore->execute(['oid' => $orderId]);
        $items = $stmtRestore->fetchAll();

        // Loop setiap item dan kembalikan stoknya
        foreach ($items as $item) {
            $pdo->prepare("UPDATE products SET stock = stock + :qty WHERE id = :pid")
                ->execute(['qty' => $item['quantity'], 'pid' => $item['product_id']]);
        }
    }

    // Wajib return 200 OK ke Midtrans
    http_response_code(200);
    echo json_encode(['message' => 'Notification processed', 'status' => $status]);

} catch (Exception $e) {
    // Jika error server, return 500 agar Midtrans mencoba kirim ulang nanti
    error_log("Notification Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}