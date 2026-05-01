<?php
require_once __DIR__ . '/supabase.php'; // Koneksi ke MySQL

// 1. Ambil ID dari URL
$id = $_GET['id'] ?? null;

$name = "Lokasi Tidak Ditemukan";
$lat = -0.502106; // Default: Samarinda/Kaltim Center
$lng = 117.153709;
$found = false;

if ($id) {
    try {
        // 2. Ambil Koordinat dari Database
        $stmt = $pdo->prepare("SELECT name, latitude, longitude FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data) {
            $name = $data['name'];
            // Pastikan ada koordinat, kalau null pakai default
            if ($data['latitude'] && $data['longitude']) {
                $lat = $data['latitude'];
                $lng = $data['longitude'];
                $found = true;
            }
        }
    } catch (Exception $e) {
        // Silent error, pakai default
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi: <?php echo htmlspecialchars($name); ?></title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: sans-serif; }
        #map { height: 100%; width: 100%; }
        .error-msg {
            position: absolute; top: 10px; left: 50%; transform: translateX(-50%);
            background: rgba(255,0,0,0.8); color: white; padding: 10px 20px;
            border-radius: 20px; z-index: 9999; font-size: 0.9em;
        }
        .back-btn {
            position: absolute; top: 10px; left: 10px; z-index: 9999;
            background: white; padding: 8px 15px; border-radius: 5px;
            text-decoration: none; color: #333; font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

    <?php if (!$found && $id): ?>
        <div class="error-msg">Koordinat belum diatur untuk wisata ini. Menampilkan lokasi default.</div>
    <?php endif; ?>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Ambil data dari PHP ke JavaScript
        var lat = <?php echo $lat; ?>;
        var lng = <?php echo $lng; ?>;
        var name = "<?php echo $name; ?>";

        // 1. Inisialisasi Peta
        var map = L.map('map').setView([lat, lng], 13);

        // 2. Tambahkan Tile Layer (Gambar Peta)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 3. Tambahkan Marker
        var marker = L.marker([lat, lng]).addTo(map);
        
        // 4. Tambahkan Popup
        marker.bindPopup("<b>" + name + "</b><br>Lokasi Wisata").openPopup();
    </script>
</body>
</html>