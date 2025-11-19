<?php
require_once __DIR__ . '/supabase.php';
require_once __DIR__ . '/email_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError("Method not allowed", 405);

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';

if (!$email) jsonError("Email wajib diisi", 400);

try {
    // 1. Cek apakah email terdaftar?
    $stmt = $pdo->prepare("SELECT id, name FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Security Best Practice: Jangan bilang "Email tidak ditemukan"
        // Bilang "Jika email terdaftar, kami telah mengirim link." (Mencegah User Enumeration)
        jsonSuccess([], "Jika email terdaftar, link reset telah dikirim.");
    }

    // 2. Generate Token
    $token = bin2hex(random_bytes(32));

    // 3. Hapus token lama milik email ini (agar tidak numpuk)
    $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

    // 4. Simpan Token Baru
    $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)")
        ->execute([$email, $token]);

    // 5. Kirim Email
    sendResetEmail($email, $user['name'], $token);

    jsonSuccess([], "Link reset password telah dikirim ke email Anda.");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}