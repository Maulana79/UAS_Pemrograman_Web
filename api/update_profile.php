<?php
require_once __DIR__ . '/supabase.php';

$user = getLoggedUser($pdo);
if (!$user) jsonError("Unauthorized", 401);

$input = json_decode(file_get_contents('php://input'), true);
$name  = $input['name'] ?? $user['name']; // Pakai nama lama jika tidak diisi
$phone = $input['phone'] ?? $user['phone'];
$password = $input['password'] ?? null;

try {
    if ($password) {
        // Jika user kirim password baru, hash ulang
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $hashed, $user['id']]);
    } else {
        // Update info saja tanpa password
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $user['id']]);
    }

    jsonSuccess([], "Profil berhasil diperbarui");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}