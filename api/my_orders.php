<?php
// api/my_orders.php

require_once __DIR__ . '/supabase.php';
require_once __DIR__ . '/email_helper.php'; 

// 1. Cek Login
$user = getLoggedUser($pdo);
if (!$user) jsonError("Unauthorized", 401);

// 2. KONFIGURASI MIDTRANS
$serverKey = 'Mid-server-WEYMLYd6JlL_edRTxEN4WRqq'; 
$isProduction = false; 
$baseUrl = $isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

try {
    // A. Ambil Transaksi User yang masih PENDING
    $stmtPending = $pdo->prepare("SELECT order_id FROM transactions WHERE customer_id = ? AND status = 'pending'");
    $stmtPending->execute([$user['id']]);
    $pendingOrders = $stmtPending->fetchAll(PDO::FETCH_COLUMN);

    // B. Cek ke Midtrans satu per satu (Jemput Bola)
    if (!empty($pendingOrders)) {
        foreach ($pendingOrders as $oid) {
            // Request status ke Midtrans API
            $ch = curl_init("$baseUrl/v2/$oid/status");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':')
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $statusData = json_decode($response, true);

            // Jika ada respon valid dari Midtrans
            if (isset($statusData['transaction_status'])) {
                $trxStatus = $statusData['transaction_status'];
                $newStatus = 'pending';

                // Mapping Status Midtrans -> Database Kita
                if ($trxStatus == 'settlement' || $trxStatus == 'capture') {
                    $newStatus = 'paid';
                } else if ($trxStatus == 'expire') {
                    $newStatus = 'expired'; 
                } else if ($trxStatus == 'cancel' || $trxStatus == 'deny') {
                    $newStatus = 'failed';
                }

                // JIKA STATUS BERUBAH DARI PENDING (JADI PAID / EXPIRED / FAILED)
                if ($newStatus != 'pending') {
                    
                    // KASUS 1: PAID (Lunas)
                    if ($newStatus == 'paid') {
                        // 1. Update Status di Database
                        $pdo->prepare("UPDATE transactions SET status = 'paid', updated_at = NOW() WHERE order_id = ?")
                            ->execute([$oid]);

                        // 2. Ambil Data Lengkap untuk Email
                        $stmtEmail = $pdo->prepare("
                            SELECT c.email, c.name, p.name as product_name, td.quantity, td.visit_date
                            FROM transactions t
                            JOIN customers c ON t.customer_id = c.id
                            JOIN transaction_details td ON td.transaction_id = t.id
                            JOIN products p ON td.product_id = p.id
                            WHERE t.order_id = ?
                        ");
                        $stmtEmail->execute([$oid]);
                        $dataEmail = $stmtEmail->fetch();

                        // 3. Kirim Email
                        if ($dataEmail) {
                            sendEmailTicket(
                                $dataEmail['email'],
                                $dataEmail['name'],
                                $oid,
                                $dataEmail['product_name'],
                                $dataEmail['quantity'],
                                $dataEmail['visit_date']
                            );
                        }
                    } 
                    // KASUS 2: GAGAL ATAU KADALUARSA (Expired/Failed)
                    // ---> INI BAGIAN YANG TADI HILANG <---
                    else { 
                        // Update status jadi 'expired' atau 'failed' sesuai respon Midtrans
                        $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE order_id = ?")
                            ->execute([$newStatus, $oid]);
                    }
                }
            }
        }
    }

    // C. Ambil Data Terbaru untuk Ditampilkan ke User
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