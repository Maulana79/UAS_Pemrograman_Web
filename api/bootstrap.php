<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            $value = trim($value, '"\'');

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$rootPath = dirname(__DIR__);
loadEnv($rootPath . '/.env');

function get_env($key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
}

date_default_timezone_set('Asia/Makassar'); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$publicFiles = [
    'notification.php', 
    'maps.php',         
    'index.php'        
];

$currentFile = basename($_SERVER['SCRIPT_FILENAME']);

if (!in_array($currentFile, $publicFiles)) {
    
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER);
    
    $clientKey = $headers['x-api-key'] ?? $_GET['api_key'] ?? '';
    $serverKey = get_env('API_KEY');

    if (!empty($serverKey)) {
        if ($clientKey !== $serverKey) {
            header('Content-Type: application/json');
            http_response_code(401); 
            echo json_encode([
                'status' => 'error',
                'message' => 'Akses Ditolak: API Key Salah atau Tidak Ditemukan.'
            ]);
            exit; 
        }
    }
}

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

    if (!$authHeader) {
        return null;
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE auth_token = :token");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (Exception $e) {
        return null;
    }
}