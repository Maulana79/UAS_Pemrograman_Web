<?php
require_once __DIR__ . '/supabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError("Method not allowed", 405);

$input = json_decode(file_get_contents('php://input'), true);
$name  = $input['name'] ?? '';
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';
$pass  = $input['password'] ?? '';

// Validasi Simple
if (!$name || !$email || !$pass) jsonError("Nama, Email, dan Password wajib diisi", 400);
if (strlen($pass) < 6) jsonError("Password minimal 6 karakter", 400);

try {
    // 1. Cek Email Kembar
    $stmtCheck = $pdo->prepare("SELECT id FROM customers WHERE email = :email");
    $stmtCheck->execute(['email' => $email]);
    if ($stmtCheck->fetch()) {
        jsonError("Email sudah terdaftar!", 400);
    }

    // 2. Hash Password (Keamanan)
    $hashedPass = password_hash($pass, PASSWORD_BCRYPT);

    // 3. Insert Customer Baru
    $sql = "INSERT INTO customers (name, email, phone, password) VALUES (:name, :email, :phone, :pass)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'pass' => $hashedPass
    ]);

    jsonSuccess([], "Registrasi berhasil! Silakan login.");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}