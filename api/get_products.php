<?php
require_once __DIR__ . '/supabase.php';
// Tidak butuh login (Public)
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
    jsonSuccess($stmt->fetchAll());
} catch (Exception $e) {
    jsonError($e->getMessage());
}