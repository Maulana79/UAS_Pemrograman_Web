<?php
require_once __DIR__ . '/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError("Method not allowed", 405);

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';
$newPass = $input['new_password'] ?? '';

if (!$token || !$newPass) jsonError("Token dan Password Baru wajib diisi", 400);
if (strlen($newPass) < 6) jsonError("Password minimal 6 karakter", 400);

try {
    // 1. Cek Token di Database
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();

    if (!$resetRequest) {
        jsonError("Token tidak valid atau sudah kadaluarsa.", 400);
    }

    // 2. Cek Expired (Misal: Token cuma berlaku 1 Jam / 3600 detik)
    $createdAt = strtotime($resetRequest['created_at']);
    if (time() - $createdAt > 3600) {
        // Hapus token expired
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$resetRequest['email']]);
        jsonError("Token sudah kadaluarsa, silakan request ulang.", 400);
    }

    // 3. Update Password User
    $hashedPass = password_hash($newPass, PASSWORD_BCRYPT);
    
    // Update password di tabel customers berdasarkan email dari token
    $stmtUpdate = $pdo->prepare("UPDATE customers SET password = ? WHERE email = ?");
    $stmtUpdate->execute([$hashedPass, $resetRequest['email']]);

    // 4. Hapus Token (Agar tidak bisa dipakai lagi)
    $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$resetRequest['email']]);

    jsonSuccess([], "Password berhasil diubah! Silakan login dengan password baru.");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}