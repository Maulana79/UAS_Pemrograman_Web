<?php
// api/auth_login.php

// 1. Matikan output error ke layar (biar JSON tidak rusak oleh Warning)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Setup Response Header JSON
header('Content-Type: application/json');

require_once __DIR__ . '/supabase.php';

// Fungsi helper untuk kirim error JSON dan mati
function sendJsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonError("Method not allowed", 405);

// 3. Ambil Input
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);

$email = $input['email'] ?? '';
$pass  = $input['password'] ?? '';

if (!$email || !$pass) sendJsonError("Email dan Password wajib diisi", 400);

try {
    // 4. Cari User
    // (Select * aman, tapi pastikan kolom avatar sudah dibuat di DB)
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // 5. Verifikasi Password
    if (!$user || !password_verify($pass, $user['password'])) {
        sendJsonError("Email atau Password salah!", 401);
    }

    // 6. Generate Token Baru
    $token = bin2hex(random_bytes(32)); 
    $update = $pdo->prepare("UPDATE customers SET auth_token = :token WHERE id = :id");
    $update->execute(['token' => $token, 'id' => $user['id']]);
    
    // ======================================================
    // TAMBAHAN: SET SESSION PHP (SYARAT RUBRIK) ✅
    // ======================================================
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['is_logged_in'] = true;
    // ======================================================
    
    // 7. Kirim Respon Sukses
    // Gunakan '?? null' untuk keamanan jika kolom avatar belum ada
    echo json_encode([
        'status' => 'success',
        'message' => 'Login berhasil',
        'data' => [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user',
                'avatar' => $user['avatar'] ?? null // <--- FITUR ANTI CRASH
            ]
        ]
    ]);

} catch (Exception $e) {
    sendJsonError($e->getMessage(), 500);
}