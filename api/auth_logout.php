<?php
require_once __DIR__ . '/bootstrap.php'; // Ini otomatis panggil session_start()
require_once __DIR__ . '/supabase.php';

// Ambil User dari Token (Sistem Lama)
$user = getLoggedUser($pdo);

if ($user) {
    // Hapus token di database (Biar token lama gak laku)
    $stmt = $pdo->prepare("UPDATE customers SET auth_token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
}

// ======================================================
// TAMBAHAN: HAPUS SESSION PHP (SYARAT RUBRIK) ✅
// ======================================================
session_unset();     // Kosongkan variabel $_SESSION
session_destroy();   // Hancurkan file session di server
// ======================================================

jsonSuccess([], "Logout berhasil");