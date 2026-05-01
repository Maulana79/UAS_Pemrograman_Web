<?php
require_once __DIR__ . '/supabase.php';

// 1. Cek Login
$user = getLoggedUser($pdo);
if (!$user) jsonError("Unauthorized", 401);

// 2. Ambil Data dari $_POST (Bukan JSON lagi karena pakai FormData)
$name  = $_POST['name'] ?? $user['name'];
$phone = $_POST['phone'] ?? $user['phone'];
$password = $_POST['password'] ?? null;

try {
    $updateFields = [];
    $params = [];

    // A. Update Data Teks
    $sql = "UPDATE customers SET name = ?, phone = ?";
    $params[] = $name;
    $params[] = $phone;

    // B. Update Password (Jika diisi)
    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_BCRYPT);
    }

    // C. Update Foto Profil (Jika ada upload)
    $newAvatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/assets/foto_profil/';
        
        // Validasi Ekstensi
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowedExt)) {
            // Nama file unik: ID_TIMESTAMP.ext
            $fileName = $user['id'] . '_' . time() . '.' . $fileExt;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                $sql .= ", avatar = ?";
                $params[] = $fileName;
                $newAvatar = $fileName; // Simpan untuk respon balik
            }
        }
    }

    // D. Eksekusi Query
    $sql .= " WHERE id = ?";
    $params[] = $user['id'];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // E. Kirim Data Terbaru ke Frontend
    jsonSuccess([
        'message' => 'Profil berhasil diperbarui',
        'name' => $name,
        'avatar' => $newAvatar // Kirim nama file baru
    ]);

} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}