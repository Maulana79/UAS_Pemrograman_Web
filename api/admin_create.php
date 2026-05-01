<?php
require_once __DIR__ . '/supabase.php';

// Validasi API Key 
$headers = getallheaders();
$headers = array_change_key_case($headers, CASE_LOWER);
$clientKey = $headers['x-api-key'] ?? $_POST['api_key'] ?? '';
if ($clientKey !== get_env('API_KEY')) jsonError("Akses Ditolak", 401);

// Ambil Data Text
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$city = $_POST['city'] ?? '';
$price = $_POST['price'] ?? 0;
$desc = $_POST['description'] ?? '';
$hours = $_POST['hours'] ?? '';
$address = $_POST['address'] ?? '';
$lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
$long = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

if (!$name || !$price) jsonError("Nama dan Harga wajib diisi!", 400);

// --- LOGIKA UPLOAD GAMBAR BARU (MULTIPLE FILES) ---
$mainImageName = 'default.jpg';
$galleryImages = [];
$uploadDir = dirname(__DIR__) . '/assets/foto/';

if (isset($_FILES['images'])) {
    $totalFiles = count($_FILES['images']['name']);
    
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $fileExt = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . $i . '.' . $fileExt; // Tambah index biar unik
            $targetFile = $uploadDir . $uniqueName;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                // File pertama jadi main image, sisanya masuk gallery
                if ($i === 0) {
                    $mainImageName = $uniqueName;
                } else {
                    $galleryImages[] = $uniqueName;
                }
            }
        }
    }
}

// Ubah array gallery jadi JSON string untuk disimpan di DB
$galleryJson = !empty($galleryImages) ? json_encode($galleryImages) : null;

try {
    $sql = "INSERT INTO products (name, category, city, price, description, image_url, gallery_images, hours, address, latitude, longitude, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $category, $city, $price, $desc, $mainImageName, $galleryJson, $hours, $address, $lat, $long]);

    jsonSuccess([], "Produk berhasil ditambahkan dengan galeri!");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}