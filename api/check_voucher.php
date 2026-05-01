<?php
require_once __DIR__ . '/supabase.php';

$headers = getallheaders();
$clientKey = $headers['X-Api-Key'] ?? $_GET['api_key'] ?? '';
if ($clientKey !== get_env('API_KEY')) jsonError("Akses Ditolak", 401);

$code = $_GET['code'] ?? '';

if (!$code) jsonError("Kode voucher kosong", 400);

try {
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ?");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch();

    if (!$voucher) {
        jsonError("Kode voucher tidak ditemukan", 404);
    }

    if ($voucher['used_count'] >= $voucher['max_usage']) {
        jsonError("Kuota voucher sudah habis", 400);
    }

    jsonSuccess([
        'code' => $voucher['code'],
        'discount_percent' => (int)$voucher['discount_percent']
    ], "Voucher Valid!");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}