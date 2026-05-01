<?php
require_once __DIR__ . '/supabase.php';

$user = getLoggedUser($pdo);
if (!$user) jsonError("Unauthorized", 401);

try {
    // PERBAIKAN: Tambahkan kolom 'avatar' di sini!
    $stmt = $pdo->prepare("SELECT name, email, phone, role, avatar FROM customers WHERE id = ?");
    $stmt->execute([$user['id']]);
    $data = $stmt->fetch();

    jsonSuccess($data);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}