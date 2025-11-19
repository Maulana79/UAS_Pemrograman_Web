<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata Nusantara</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <?php require_once 'api/bootstrap.php'; ?>
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="<?php echo get_env('MIDTRANS_CLIENT_KEY'); ?>"></script>
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-blue-600 text-white p-6 shadow-lg">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold">Portal Tiket Wisata</h1>
            <p class="text-blue-100">Jelajahi keindahan alam dan budaya.</p>
        </div>
    </header>

    <main class="container mx-auto p-6">
        <div id="loading" class="text-center text-gray-500 mt-10">Memuat data wisata...</div>
        
        <div id="ticket-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            </div>
    </main>

    <div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg w-11/12 md:w-2/3 h-3/4 relative flex flex-col">
            <button onclick="closeMap()" class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded z-[9999]">X</button>
            <h3 id="mapTitle" class="text-xl font-bold mb-2">Lokasi</h3>
            <div id="map" class="flex-grow w-full rounded border border-gray-300"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map = null;
        let marker = null;

        // 1. Fetch Data Wisata saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            fetch('api/get_products.php')
                .then(response => response.json())
                .then(res => {
                    const listContainer = document.getElementById('ticket-list');
                    document.getElementById('loading').style.display = 'none';

                    if(res.status === 'success') {
                        res.data.forEach(item => {
                            // Render Card HTML
                            const card = `
                                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                                    <div class="h-48 bg-gray-300 w-full object-cover flex items-center justify-center text-gray-500">
                                        ${item.image_url ? `<img src="${item.image_url}" class="w-full h-full object-cover">` : '<span>No Image</span>'}
                                    </div>
                                    <div class="p-5">
                                        <h2 class="text-xl font-bold text-gray-800">${item.name}</h2>
                                        <p class="text-gray-600 mt-2 text-sm h-16 overflow-hidden">${item.description || 'Tidak ada deskripsi.'}</p>
                                        <div class="mt-4 flex justify-between items-center">
                                            <span class="text-green-600 font-bold text-lg">Rp ${parseInt(item.price).toLocaleString('id-ID')}</span>
                                        </div>
                                        <div class="mt-4 flex gap-2">
                                            <button onclick="showMap('${item.name}', ${item.latitude}, ${item.longitude})" 
                                                class="flex-1 bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300 transition">
                                                📍 Lokasi
                                            </button>
                                            <button onclick="buyTicket(${item.id})" 
                                                class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                                                Booking
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            listContainer.innerHTML += card;
                        });
                    }
                });
        });

        // 2. Fungsi Menampilkan Peta
        function showMap(name, lat, lng) {
            const modal = document.getElementById('mapModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('mapTitle').innerText = "Lokasi: " + name;

            // Hapus map lama jika ada
            if (map) {
                map.remove();
            }

            // Validasi koordinat
            if (!lat || !lng) {
                document.getElementById('map').innerHTML = '<div class="flex items-center justify-center h-full">Koordinat tidak tersedia</div>';
                return;
            }

            // Render Map Baru
            setTimeout(() => {
                map = L.map('map').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup(name).openPopup();
            }, 100); // Delay sedikit agar container siap
        }

        function closeMap() {
            document.getElementById('mapModal').classList.add('hidden');
            document.getElementById('mapModal').classList.remove('flex');
        }

        // 3. Fungsi Beli Tiket (Midtrans)
        async function buyTicket(id) {
            try {
                // Panggil API backend kita untuk dapat token
                const response = await fetch('api/payment_gateway.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, quantity: 1 }) 
                });
                
                const result = await response.json();

                if (result.token) {
                    // Munculkan Popup Midtrans
                    window.snap.pay(result.token, {
                        onSuccess: function(result){ alert("Pembayaran Berhasil!"); console.log(result); },
                        onPending: function(result){ alert("Menunggu Pembayaran!"); console.log(result); },
                        onError: function(result){ alert("Pembayaran Gagal!"); console.log(result); },
                        onClose: function(){ alert('Anda menutup popup tanpa menyelesaikan pembayaran'); }
                    });
                } else {
                    alert('Gagal membuat transaksi: ' + (result.error || 'Unknown Error'));
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi.');
            }
        }
    </script>
</body>
</html>