<?php
// api/bootstrap.php

// 1. CORS Handling
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. FUNGSI PARSER .ENV MANUAL (Kompatibel dengan InfinityFree)
function loadEnv($path) {
    if (!file_exists($path)) {
        // Silent fail agar tidak error fatal di user
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Hapus spasi kiri kanan
        $line = trim($line);

        // Lewati komentar (#) atau baris kosong
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Pisahkan Key dan Value
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Bersihkan tanda kutip jika ada
            $value = trim($value, "\"'");

            // Simpan ke Global Variable ($_ENV dan $_SERVER)
            // Kita tidak pakai putenv() karena kadang didisable hosting gratis
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env dari folder root (naik satu level dari folder api)
$rootPath = dirname(__DIR__);
loadEnv($rootPath . '/.env');

// 3. Helper Function Ambil Env
function get_env($key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
}

// 4. Setting PHP
date_default_timezone_set('Asia/Makassar');
ini_set('display_errors', 1); // Matikan ini (jadi 0) nanti kalau sudah fix
error_reporting(E_ALL);

// 5. Auth Middleware
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
            // Cek token di tabel customers
            $stmt = $pdo->prepare("SELECT id, name, email, phone FROM customers WHERE auth_token = :token");
            $stmt->execute(['token' => $token]);
            return $stmt->fetch() ?: null;
        } catch (Exception $e) { return null; }
    }
    return null;
}

// 6. Security Check (API Key)
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