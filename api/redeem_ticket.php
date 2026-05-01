<?php
// api/redeem_ticket.php

// 1. LOAD DEPENDENCIES
require_once __DIR__ . '/bootstrap.php'; 
require_once __DIR__ . '/supabase.php';

// Set Timezone
date_default_timezone_set('Asia/Makassar');

// 2. CEK ADMIN 
$user = getLoggedUser($pdo);
if (!$user) {
    jsonError("Silakan login terlebih dahulu", 401);
}
if ($user['role'] !== 'admin') {
    jsonError("AKSES DITOLAK: Hanya Admin yang bisa melakukan scan!", 403);
}

// 3. AMBIL INPUT
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError("Method not allowed", 405);
$input = json_decode(file_get_contents('php://input'), true);
$orderId = $input['order_id'] ?? null;
$mode    = $input['mode'] ?? 'check'; 

if (!$orderId) jsonError("Order ID wajib diisi", 400);

try {
    // Mulai Transaksi Database
    $pdo->beginTransaction();

    // 4. QUERY SESUAI KODEMU (Join transaction_details)
    $sql = "
        SELECT 
            t.id as trx_id, t.status, t.order_id,
            c.name as customer_name,
            td.id as detail_id, td.is_redeemed, td.visit_date, td.quantity,
            p.name as product_name
        FROM transactions t
        JOIN customers c ON t.customer_id = c.id
        JOIN transaction_details td ON td.transaction_id = t.id
        JOIN products p ON td.product_id = p.id
        WHERE t.order_id = :oid
        FOR UPDATE
    "; 

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['oid' => $orderId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- VALIDASI UMUM ---
    if (!$ticket) throw new Exception("Tiket tidak ditemukan!");

    if ($ticket['status'] !== 'paid') {
        throw new Exception("Tiket belum lunas! Status: " . $ticket['status']);
    }

    // --- VALIDASI TANGGAL ---
    $today = date('Y-m-d');
    $visitDate = $ticket['visit_date'];
    $dateError = null;

    if ($today > $visitDate) {
        $dateError = "⛔ KADALUARSA: Tiket tgl $visitDate. Sekarang tgl $today.";
    } else if ($today < $visitDate) {
        $dateError = "⛔ BELUM BERLAKU: Tiket baru bisa dipakai tgl $visitDate.";
    }

    // ==========================================================
    // MODE 1: CHECK (Hanya Melihat Data)
    // ==========================================================
    if ($mode === 'check') {
        $pdo->rollBack(); // Lepas lock database

        $isValid = true;
        $statusMsg = "TIKET VALID (SIAP MASUK)";

        // Cek Error tapi jangan throw exception (biar admin bisa baca errornya)
        if ($dateError) {
            $isValid = false;
            $statusMsg = $dateError;
        } else if ($ticket['is_redeemed']) {
            $isValid = false;
            $statusMsg = "⛔ SUDAH DIPAKAI SEBELUMNYA";
        }

        jsonSuccess([
            'valid'         => $isValid,
            'message'       => $statusMsg,
            'visitor'       => $ticket['customer_name'],
            'ticket'        => $ticket['product_name'],
            'quantity'      => $ticket['quantity'], 
            'visit_date'    => $ticket['visit_date'],
            'redeem_status' => (bool)$ticket['is_redeemed']
        ]);
    }

    // ==========================================================
    // MODE 2: EXECUTE (Proses Redeem / Update DB)
    // ==========================================================
    else if ($mode === 'execute') {
        
        // Cek Ulang Validasi (Strict)
        if ($ticket['is_redeemed']) {
            throw new Exception("⛔ DITOLAK: Tiket SUDAH DIPAKAI sebelumnya.");
        }
        if ($dateError) {
            throw new Exception($dateError);
        }

        // Update Database 
        $update = $pdo->prepare("UPDATE transaction_details SET is_redeemed = TRUE, redeemed_at = NOW() WHERE id = :did");
        $update->execute(['did' => $ticket['detail_id']]);

        $pdo->commit(); // Simpan perubahan

        jsonSuccess([
            'message'    => 'AKSES DITERIMA',
            'visitor'    => $ticket['customer_name'],
            'ticket'     => $ticket['product_name'],
            'quantity'   => $ticket['quantity'],
            'visit_date' => $ticket['visit_date']
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonError($e->getMessage(), 400);
}