<?php
// FILE: api/bootstrap.php

// 1. CORS Handling
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. FUNGSI PEMBACA .ENV MANUAL (ROBUST)
function loadEnv($path) {
    if (!file_exists($path)) {
        // Jika file tidak ada, stop script biar ketahuan
        die("❌ Error: File .env tidak ditemukan di: $path <br>Pastikan file .env ada di folder luar folder 'api'");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Hapus spasi di awal/akhir baris
        $line = trim($line);

        // Lewati komentar (#) atau baris kosong
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Pisahkan Key dan Value berdasarkan tanda "=" pertama
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            
            $name  = trim($name);
            $value = trim($value);
            
            // Hapus tanda kutip ( " atau ' ) jika ada di value
            $value = trim($value, "\"'");

            // Simpan ke Environment Variable PHP
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// 3. JALANKAN LOADER
// dirname(__DIR__) artinya: Ambil folder parent dari folder 'api'
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// 4. Helper Function
function get_env($key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
}

// 5. Setting PHP Lainnya
date_default_timezone_set('Asia/Makassar');
error_reporting(E_ALL);

// 6. Polyfill getallheaders & Auth Middleware
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function getLoggedUser($pdo) {
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER);
    $authHeader = $headers['authorization'] ?? ''; 
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, phone FROM customers WHERE auth_token = :token");
            $stmt->execute(['token' => $token]);
            return $stmt->fetch() ?: null;
        } catch (Exception $e) { return null; }
    }
    return null;
}

// 7. Security Check
$publicFiles = ['notification.php', 'maps.php', 'index.php']; 
$currentFile = basename($_SERVER['SCRIPT_FILENAME']);
if (!in_array($currentFile, $publicFiles)) {
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER);
    $clientKey = $headers['x-api-key'] ?? $_GET['api_key'] ?? '';
    $serverKey = get_env('API_KEY');
    if (!empty($serverKey) && $clientKey !== $serverKey) {
        header('Content-Type: application/json');
        http_response_code(401); 
        echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak: API Key Salah']);
        exit; 
    }
}