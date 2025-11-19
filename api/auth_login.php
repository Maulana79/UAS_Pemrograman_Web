<?php
require_once __DIR__ . '/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError("Method not allowed", 405);

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$pass  = $input['password'] ?? '';

if (!$email || !$pass) jsonError("Email dan Password wajib diisi", 400);

try {
    // 1. Cari User by Email
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // 2. Verifikasi Password
    if (!$user || !password_verify($pass, $user['password'])) {
        jsonError("Email atau Password salah!", 401);
    }

    // 3. Generate Token Baru (Random String)
    $token = bin2hex(random_bytes(32)); // Contoh: a1b2c3...

    // 4. Simpan Token ke Database
    $update = $pdo->prepare("UPDATE customers SET auth_token = :token WHERE id = :id");
    $update->execute(['token' => $token, 'id' => $user['id']]);

    // 5. Return Token ke User
    jsonSuccess([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ], "Login berhasil");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}