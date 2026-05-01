<?php
// api/checkout.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once __DIR__ . '/supabase.php';
require_once __DIR__ . '/email_helper.php'; // <--- WAJIB TAMBAHKAN INI!

// 1. CEK LOGIN
$user = getLoggedUser($pdo);
if (!$user) jsonError("Akses Ditolak: Login dulu!", 401);

// 2. KONFIGURASI MIDTRANS (HARDCODE)
$serverKey = 'Mid-server-WEYMLYd6JlL_edRTxEN4WRqq'; // Ganti dengan Key Asli
$isProduction = false; 

$apiUrl = $isProduction 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

// 3. INPUT
$input = json_decode(file_get_contents('php://input'), true);
$productId   = $input['product_id'] ?? null;
$quantity    = $input['quantity'] ?? 1;
$visitDate   = $input['visit_date'] ?? date('Y-m-d');
$voucherCode = $input['voucher_code'] ?? null;
$visitorName = $input['visitor_name'] ?? $user['name'];

if (!$productId) jsonError("ID Produk wajib", 400);

try {
    $pdo->beginTransaction();

    // 4. CEK PRODUK
    $stmtProduct = $pdo->prepare("SELECT * FROM products WHERE id = :id FOR UPDATE");
    $stmtProduct->execute(['id' => $productId]);
    $product = $stmtProduct->fetch();

    if (!$product) throw new Exception("Produk tidak ditemukan.");
    if ($product['stock'] < $quantity) throw new Exception("Stok habis!");

    // 5. HITUNG HARGA
    $pricePerItem = (int)$product['price'];
    $grossAmount  = $pricePerItem * $quantity;
    $discountAmt  = 0;
    $finalAmount  = $grossAmount;
    $voucherUsed  = null;

    // Cek Voucher
    if (!empty($voucherCode)) {
        $stmtVoucher = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code FOR UPDATE");
        $stmtVoucher->execute(['code' => $voucherCode]);
        $voucher = $stmtVoucher->fetch();

        if ($voucher && $voucher['used_count'] < $voucher['max_usage']) {
            $discountAmt = ($grossAmount * $voucher['discount_percent']) / 100;
            $finalAmount = $grossAmount - $discountAmt;
            $voucherUsed = $voucher;
        }
    }

    if ($finalAmount < 0) $finalAmount = 0;

    // 6. INSERT DB
	$expireTime = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $initialStatus = ($finalAmount <= 0) ? 'paid' : 'pending';
    
    $orderId = 'TRX-' . time() . '-' . rand(100, 999);
    
    $sqlHeader = "INSERT INTO transactions (order_id, customer_id, total_amount, discount_amount, voucher_code, status, expire_time) 
                  VALUES (:oid, :cid, :total, :disc, :code, :status, :expire)";
    $stmtHeader = $pdo->prepare($sqlHeader);
    $stmtHeader->execute([
        'oid' => $orderId, 
        'cid' => $user['id'], 
        'total' => $finalAmount, 
        'disc' => $discountAmt, 
        'code' => $voucherCode, 
        'status' => $initialStatus,
        'expire' => $expireTime // <--- TAMBAHAN INI
    ]);
    $trxId = $pdo->lastInsertId();

    // Insert Detail
    $sqlDetail = "INSERT INTO transaction_details (transaction_id, product_id, quantity, price_at_purchase, visit_date) 
                  VALUES (:tid, :pid, :qty, :price, :vdate)";
    $pdo->prepare($sqlDetail)->execute([
        'tid' => $trxId, 'pid' => $product['id'], 'qty' => $quantity, 'price' => $pricePerItem, 'vdate' => $visitDate
    ]);

    // Kurangi Stok
    $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id")->execute(['qty' => $quantity, 'id' => $productId]);
    if ($voucherUsed) {
        $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = :vid")->execute(['vid' => $voucherUsed['id']]);
    }

    // --- SKENARIO A: GRATIS (VOUCHER 100%) ---
    if ($finalAmount <= 0) {
        // Commit Database dulu biar data tersimpan
        $pdo->commit();
        
        // KIRIM EMAIL LANGSUNG! (Fitur Tambahan)
        sendEmailTicket(
            $user['email'],
            $visitorName,
            $orderId,
            $product['name'],
            $quantity,
            $visitDate
        );

        jsonSuccess([
            'is_free' => true, 
            'order_id' => $orderId,
            'message' => 'Transaksi Gratis Berhasil (E-Ticket dikirim ke email)'
        ]);
        exit; // Selesai
    }

    // --- SKENARIO B: BAYAR (MIDTRANS) ---
    $midtransItems = [[
        'id' => $product['id'], 'price' => $pricePerItem, 'quantity' => $quantity, 'name' => substr($product['name'], 0, 50)
    ]];
    if ($discountAmt > 0) {
        $midtransItems[] = ['id' => 'DISC', 'price' => -$discountAmt, 'quantity' => 1, 'name' => "Voucher $voucherCode"];
    }

	$params = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => (int)$finalAmount,
        ],
        'item_details' => $midtransItems,
        'customer_details' => [
            'first_name' => $visitorName,
            'email'      => $user['email'],
            'phone'      => $user['phone']
        ],
        // 👇 TAMBAHAN PENTING: ATUR EXPIRED 24 JAM 👇
        'expiry' => [
            'start_time' => date("Y-m-d H:i:s O"), // Waktu sekarang
            'unit'       => 'hour',
            'duration'   => 24 // Hangus dalam 24 jam
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':'),
        'X-Override-Notification: https://turipuloka.42web.io/api/notification.php'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $midtransRes = json_decode($response, true);
    if ($httpCode != 201 || !isset($midtransRes['token'])) {
        throw new Exception("Midtrans Error ($httpCode): " . ($midtransRes['error_messages'][0] ?? 'Unknown'));
    }

    $pdo->prepare("UPDATE transactions SET snap_token = :token WHERE id = :id")
        ->execute(['token' => $midtransRes['token'], 'id' => $trxId]);

    $pdo->commit();

    jsonSuccess([
        'is_free' => false,
        'token' => $midtransRes['token'],
        'order_id' => $orderId,
        'redirect_url' => $midtransRes['redirect_url']
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
}