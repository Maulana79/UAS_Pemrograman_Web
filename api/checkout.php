<?php
require_once __DIR__ . '/supabase.php';

/**
 * ENDPOINT CHECKOUT (PEMESANAN TIKET)
 * Fitur: Login Wajib, Cek Stok, Kode Voucher, Integrasi Midtrans
 */

// --- 1. CEK OTENTIKASI USER ---
$user = getLoggedUser($pdo);
if (!$user) {
    jsonError("Akses Ditolak: Anda harus login terlebih dahulu!", 401);
}

// --- 2. KONFIGURASI MIDTRANS ---
$serverKey = get_env('MIDTRANS_SERVER_KEY');
$isProduction = filter_var(get_env('MIDTRANS_IS_PRODUCTION'), FILTER_VALIDATE_BOOLEAN);
$apiUrl = $isProduction 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

// --- 3. AMBIL INPUT DARI FRONTEND ---
$input = json_decode(file_get_contents('php://input'), true);

$productId   = $input['product_id'] ?? null;
$quantity    = $input['quantity'] ?? 1;
$visitDate   = $input['visit_date'] ?? date('Y-m-d');
$voucherCode = $input['voucher_code'] ?? null; // Input Kode Voucher (Opsional)

// Validasi Input
if (!$productId) jsonError("Product ID wajib diisi", 400);
if ($quantity < 1) jsonError("Jumlah tiket minimal 1", 400);
if ($visitDate < date('Y-m-d')) jsonError("Tanggal kunjungan tidak boleh masa lalu", 400);

try {
    // --- 4. MULAI TRANSAKSI DATABASE ---
    $pdo->beginTransaction();

    // --- 5. CEK PRODUK & STOK ---
    $stmtProduct = $pdo->prepare("SELECT * FROM products WHERE id = :id FOR UPDATE");
    $stmtProduct->execute(['id' => $productId]);
    $product = $stmtProduct->fetch();

    if (!$product) throw new Exception("Produk wisata tidak ditemukan.");
    if ($product['stock'] < $quantity) throw new Exception("Stok tiket habis! Tersisa: " . $product['stock']);

    // --- 6. HITUNG HARGA & VOUCHER ---
    $pricePerItem = (int)$product['price'];
    $grossAmount  = $pricePerItem * $quantity; // Harga kotor sebelum diskon
    $discountAmt  = 0;
    $finalAmount  = $grossAmount;              // Harga bersih setelah diskon
    $voucherUsed  = null;                      // Simpan data voucher jika valid

    // Logika Cek Voucher
    if (!empty($voucherCode)) {
        $stmtVoucher = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code FOR UPDATE");
        $stmtVoucher->execute(['code' => $voucherCode]);
        $voucher = $stmtVoucher->fetch();

        // Validasi Voucher
        if (!$voucher) {
            throw new Exception("Kode voucher '$voucherCode' tidak ditemukan!");
        }
        if ($voucher['used_count'] >= $voucher['max_usage']) {
            throw new Exception("Kuota voucher '$voucherCode' sudah habis!");
        }

        // Hitung Diskon (Persen)
        $discountAmt = ($grossAmount * $voucher['discount_percent']) / 100;
        $finalAmount = $grossAmount - $discountAmt;
        $voucherUsed = $voucher;
    }

    // --- 7. PERSIAPAN DATA ORDER ID ---
    $orderId = 'TRX-' . time() . '-' . rand(100, 999);

    // --- 8. INSERT HEADER TRANSAKSI ---
    // Simpan juga kode voucher dan jumlah diskonnya
    $sqlHeader = "INSERT INTO transactions (order_id, customer_id, total_amount, discount_amount, voucher_code, status) 
                  VALUES (:oid, :cid, :total, :disc, :code, 'pending') 
                  RETURNING id";
    
    $stmtHeader = $pdo->prepare($sqlHeader);
    $stmtHeader->execute([
        'oid'   => $orderId,
        'cid'   => $user['id'],
        'total' => $finalAmount, // Yang disimpan adalah harga final yang harus dibayar
        'disc'  => $discountAmt,
        'code'  => $voucherCode
    ]);
    $trxId = $stmtHeader->fetch()['id'];

    // --- 9. INSERT DETAIL ITEM ---
    $sqlDetail = "INSERT INTO transaction_details (transaction_id, product_id, quantity, price_at_purchase, visit_date) 
                  VALUES (:tid, :pid, :qty, :price, :vdate)";
    $stmtDetail = $pdo->prepare($sqlDetail);
    $stmtDetail->execute([
        'tid'   => $trxId,
        'pid'   => $product['id'],
        'qty'   => $quantity,
        'price' => $pricePerItem, // Harga satuan asli (sebelum diskon)
        'vdate' => $visitDate
    ]);

    // --- 10. KURANGI STOK PRODUK ---
    $stmtStock = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
    $stmtStock->execute(['qty' => $quantity, 'id' => $productId]);

    // --- 11. UPDATE KUOTA VOUCHER (Jika Dipakai) ---
    if ($voucherUsed) {
        $stmtUpdVoucher = $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = :vid");
        $stmtUpdVoucher->execute(['vid' => $voucherUsed['id']]);
    }

    // --- 12. SIAPKAN ITEM DETAILS UNTUK MIDTRANS ---
    $midtransItems = [
        [
            'id'       => $product['id'],
            'price'    => $pricePerItem,
            'quantity' => $quantity,
            'name'     => substr($product['name'], 0, 50)
        ]
    ];

    // Trik Midtrans: Tambahkan item dengan harga MINUS sebagai diskon
    if ($discountAmt > 0) {
        $midtransItems[] = [
            'id'       => 'VOUCHER-' . $voucherCode,
            'price'    => -$discountAmt, // Harga Negatif
            'quantity' => 1,
            'name'     => "Diskon Voucher ($voucherCode)"
        ];
    }

    // --- 13. REQUEST KE MIDTRANS (cURL) ---
    $midtransParams = [
        'transaction_details' => [
            'order_id'     => $orderId,
            'gross_amount' => $finalAmount, // Harus sama persis dengan total item_details
        ],
        'item_details' => $midtransItems,
        'customer_details' => [
            'first_name' => $user['name'],
            'email'      => $user['email'],
            'phone'      => $user['phone']
        ],
        'custom_field1' => $visitDate
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($midtransParams));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':')
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $midtransResponse = json_decode($result, true);

    if ($httpCode != 201 || !isset($midtransResponse['token'])) {
        $errorMsg = $midtransResponse['error_messages'][0] ?? 'Gagal koneksi payment gateway';
        throw new Exception($errorMsg);
    }

    // --- 14. SIMPAN SNAP TOKEN & COMMIT ---
    $pdo->prepare("UPDATE transactions SET snap_token = ? WHERE id = ?")
        ->execute([$midtransResponse['token'], $trxId]);

    $pdo->commit();

    // --- 15. RESPONSE SUKSES ---
    jsonSuccess([
        'token'          => $midtransResponse['token'],
        'order_id'       => $orderId,
        'redirect_url'   => $midtransResponse['redirect_url'],
        'original_price' => $grossAmount,
        'discount'       => $discountAmt,
        'final_price'    => $finalAmount
    ], "Order berhasil dibuat.");

} catch (Exception $e) {
    // Rollback jika error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[Checkout Error] " . $e->getMessage());
    jsonError($e->getMessage(), 500);
}