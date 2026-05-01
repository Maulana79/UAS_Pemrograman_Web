<?php
require_once __DIR__ . '/supabase.php';

// Validasi API Key 
$headers = getallheaders();
$headers = array_change_key_case($headers, CASE_LOWER);
$clientKey = $headers['x-api-key'] ?? $_POST['api_key'] ?? '';
if ($clientKey !== get_env('API_KEY')) jsonError("Akses Ditolak", 401);

$id = $_POST['id'] ?? null;
if (!$id) jsonError("ID Produk diperlukan", 400);

try {
    // 1. Ambil Data Lama
    $stmtOld = $pdo->prepare("SELECT image_url, gallery_images FROM products WHERE id = ?");
    $stmtOld->execute([$id]);
    $oldData = $stmtOld->fetch();
    if (!$oldData) jsonError("Produk tidak ditemukan", 404);

    // 2. Siapkan Data Text Baru
    $name = $_POST['name'];
    $category = $_POST['category'];
    $city = $_POST['city'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $hours = $_POST['hours'];
    $address = $_POST['address'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $long = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    
    // 3. Logika Upload Gambar (Update)
    $mainImageName = $oldData['image_url']; // Default pakai yang lama
    $galleryJson = $oldData['gallery_images']; // Default pakai yang lama
    $newGalleryImages = [];
    $uploadDir = dirname(__DIR__) . '/assets/foto/';
    $hasNewUpload = false;

    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['error'][0] === UPLOAD_ERR_OK) {
        $hasNewUpload = true;
        $totalFiles = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $fileExt = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                $uniqueName = uniqid() . '_upd_' . $i . '.' . $fileExt;
                $targetFile = $uploadDir . $uniqueName;

                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                    if ($i === 0) {
                        $mainImageName = $uniqueName; // Gambar pertama jadi main baru
                    } else {
                        $newGalleryImages[] = $uniqueName; // Sisanya jadi gallery baru
                    }
                }
            }
        }
        // Jika ada upload baru, gallery lama ditimpa total dengan yang baru
        $galleryJson = !empty($newGalleryImages) ? json_encode($newGalleryImages) : null;
        
        // (Opsional: Di sini bisa tambah logika hapus file lama dari server jika mau hemat space)
    }

    // 4. Update Database
    $sql = "UPDATE products SET name=?, category=?, city=?, price=?, description=?, image_url=?, gallery_images=?, hours=?, address=?, latitude=?, longitude=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $category, $city, $price, $desc, $mainImageName, $galleryJson, $hours, $address, $lat, $long, $id]);

    jsonSuccess([], "Produk berhasil diupdate!");

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}