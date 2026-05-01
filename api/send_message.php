<?php
require_once __DIR__ . '/supabase.php';

$user = getLoggedUser($pdo); if (!$user) jsonError("Login dulu", 401);
// 1. Ambil Input
$input = json_decode(file_get_contents('php://input'), true);
$name    = $input['name'] ?? '';
$email   = $input['email'] ?? '';
$message = $input['message'] ?? '';

// 2. Validasi
if (!$name || !$email || !$message) {
    jsonError("Nama, Email, dan Pesan wajib diisi!", 400);
}

try {
    // 3. Simpan ke Database
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);

    jsonSuccess([], "Pesan terkirim!");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}