<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Lokasi</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        #map { height: 500px; width: 100%; border-radius: 8px; }
    </style>
</head>
<body>

    <h2>Lokasi Pengiriman</h2>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([-6.175392, 106.827153], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = L.marker([-6.175392, 106.827153]).addTo(map);
        marker.bindPopup("<b>Lokasi Tujuan</b><br>Jakarta Pusat.").openPopup();

        map.on('click', function(e) {
            alert("Koordinat: " + e.latlng.lat + ", " + e.latlng.lng);
        });
    </script>
</body>
</html>