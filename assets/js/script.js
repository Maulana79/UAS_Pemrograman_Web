document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================================
    // 0. CONFIG PATH GAMBAR
    // ==========================================================
    const IMAGE_BASE_PATH = '../assets/foto/';

    // ==========================================================
    // 1. FUNGSI FETCH DATA
    // ==========================================================
    async function fetchDestinationsFromAPI() {
        try {
            const response = await fetch(`${BASE_API_URL}/get_products.php`, { headers: getHeaders() });
            const result = await response.json();
            return result.status === 'success' ? result.data : [];
        } catch (error) {
            console.error("Error Fetch:", error);
            return [];
        }
    }

    // ==========================================================
    // 2. ROUTER & INIT
    // ==========================================================
    const urlParams = new URLSearchParams(window.location.search);
    const destinationId = urlParams.get('id');
    const categoryElement = document.getElementById('page-category');
    
    // JALANKAN FUNGSI UTAMA
    initPage();

	async function initPage() {
        // 1. Update Navbar DULUAN (Supaya tombol login selalu muncul)
        updateNavbar(); 

        // --- TAMBAHAN: Definisi pathName biar ga error ---
        const pathName = window.location.pathname.toLowerCase(); 
        // -------------------------------------------------

        // 2. Cek Halaman Mana yang Aktif
        if (destinationId) {
            if (pathName.includes('halamanreview.html')) {
                handleReviewPageLoad(destinationId);
            } else if (pathName.includes('halamanpemesanan.html')) {
                handleBookingPageLoad(destinationId);
            } else {
                handleDetailPageLoad(destinationId);
            }
        } else if (categoryElement) {
            // Halaman Kategori (Nature, Adventure, dll)
            handleCategoryPageLoad(categoryElement.dataset.category);
        } else {
            // Halaman Home (Index)
            if (document.getElementById('best-destinations-list')) {
                await loadBestDestinations();
            }
            if (document.getElementById('homepage-reviews')) {
                await loadHomepageReviews();
            }
        }
    }

    // ==========================================================
    // 3. HALAMAN HOME (INDEX)
    // ==========================================================
    async function loadBestDestinations() {
        const container = document.getElementById('best-destinations-list');
        let products = await fetchDestinationsFromAPI();
        const bestProducts = products.slice(0, 5); 

        container.innerHTML = '';
        if (bestProducts.length === 0) {
            container.innerHTML = '<p style="text-align:center; width:100%;">Belum ada data.</p>';
            return;
        }

        bestProducts.forEach(dest => {
            let imgUrl = dest.image_url;
            if (!imgUrl.startsWith('http')) imgUrl = IMAGE_BASE_PATH + imgUrl;

            const detailUrl = `detail.html?id=${dest.id}&category=${dest.category || 'nature'}`;
            
            // --- LOGIKA STOK BARU 
            const stock = dest.stock !== undefined ? parseInt(dest.stock) : 50; // Default 50 jika database belum ada kolom stock
            let buttonHTML = '';
            let overlayHTML = '';

            if (stock <= 0) {
                // KASUS HABIS
                buttonHTML = `<a href="#" class="visit-btn" style="background: #555; cursor: not-allowed; pointer-events: none;">SOLD OUT</a>`;
                overlayHTML = `<div style="position:absolute; top:10px; right:10px; background:red; color:white; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:5px; z-index:2;">HABIS</div>`;
            } else {
                // KASUS ADA
                buttonHTML = `<a href="${detailUrl}" class="visit-btn">VISIT →</a>`;
                // Tampilkan sisa tiket
                overlayHTML = `<div style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.6); color:white; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:5px; z-index:2;">Sisa: ${stock}</div>`;
            }

            const card = document.createElement('div');
            card.className = 'destination-card';
            card.style.position = 'relative'; // Penting buat label sisa tiket

            card.innerHTML = `
                ${overlayHTML}
                <img src="${imgUrl}" alt="${dest.name}" onerror="this.src='../assets/foto/default.jpg'">
                <div class="card-overlay">
                    <h3>${dest.name}</h3>
                    ${buttonHTML}
                </div>
            `;
            container.appendChild(card);
        });
    }

    async function loadHomepageReviews() {
        const container = document.getElementById('homepage-reviews');
        try {
            const res = await fetch(`${BASE_API_URL}/get_reviews.php`, { headers: getHeaders() });
            const json = await res.json();

            container.innerHTML = '';
            if (json.status === 'success' && json.data.length > 0) {
                json.data.forEach(review => {
                    const stars = '⭐'.repeat(parseInt(review.rating));
                    let comment = review.comment || "";
                    if (comment.length > 100) comment = comment.substring(0, 100) + '...';

                    const card = document.createElement('div');
                    card.className = 'review-card';
                    card.innerHTML = `
                        <p>"${comment}"</p>
                        <div class="stars">${stars}</div>
                        <span class="review-destination">
                            <strong>${review.reviewer_name}</strong><br>
                            <small>${review.product_name}</small>
                        </span> 
                    `;
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = '<p style="text-align:center; width:100%;">Belum ada ulasan.</p>';
            }
        } catch (e) { console.error(e); }
    }

    // ==========================================================
    // 4. HALAMAN KATEGORI
    // ==========================================================
    async function handleCategoryPageLoad(categoryKey) {
        const grid = document.getElementById('destination-list');
        const cityFilter = document.getElementById('city-filter');
        
        grid.innerHTML = '<p style="text-align:center; width:100%;">Memuat data...</p>';
        
        let allProducts = await fetchDestinationsFromAPI();
        let categoryProducts = allProducts.filter(p => p.category && p.category.toLowerCase() === categoryKey.toLowerCase());

        const render = (items) => {
            grid.innerHTML = '';
            if (items.length === 0) {
                grid.innerHTML = '<p style="text-align:center; width:100%;">Data tidak ditemukan.</p>';
                return;
            }
            items.forEach(dest => {
                let imgUrl = dest.image_url;
                if (!imgUrl.startsWith('http')) imgUrl = IMAGE_BASE_PATH + imgUrl;

                const detailUrl = `detail.html?id=${dest.id}&category=${categoryKey}`;
                
                // --- LOGIKA STOK BARU 
                const stock = dest.stock !== undefined ? parseInt(dest.stock) : 50; 
                let buttonHTML = '';
                let overlayHTML = '';

                if (stock <= 0) {
                    buttonHTML = `<a href="#" class="visit-btn" style="background: #555; cursor: not-allowed; pointer-events: none;">SOLD OUT</a>`;
                    overlayHTML = `<div style="position:absolute; top:10px; right:10px; background:red; color:white; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:5px; z-index:2;">HABIS</div>`;
                } else {
                    buttonHTML = `<a href="${detailUrl}" class="visit-btn">VISIT →</a>`;
                    overlayHTML = `<div style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.6); color:white; padding:5px 10px; font-size:12px; font-weight:bold; border-radius:5px; z-index:2;">Sisa: ${stock}</div>`;
                }
                // ------------------------------------------

                const card = document.createElement('div');
                card.className = 'destination-card';
                card.style.position = 'relative';
                
                card.innerHTML = `
                    ${overlayHTML}
                    <img src="${imgUrl}" alt="${dest.name}" onerror="this.src='${IMAGE_BASE_PATH}default.jpg'">
                    <div class="card-overlay">
                        <h3>${dest.name}</h3>
                        ${buttonHTML}
                    </div>
                `;
                grid.appendChild(card);
            });
        };

        if (cityFilter) {
            cityFilter.addEventListener('change', () => {
                const city = cityFilter.value.toLowerCase();
                const filtered = city === 'all' 
                    ? categoryProducts 
                    : categoryProducts.filter(p => p.city && p.city.toLowerCase() === city);
                render(filtered);
            });
        }
        render(categoryProducts);
    }

    // ==========================================================
    // 5. HALAMAN DETAIL (DETAIL + MAPS + REVIEW DINAMIS)
    // ==========================================================
    async function handleDetailPageLoad(id) {
        try {
            const res = await fetch(`${BASE_API_URL}/get_product_detail.php?id=${id}`, { headers: getHeaders() });
            const json = await res.json();
            const dest = json.data;

            if (!dest) return;

            // --- TEXT ---
            document.title = dest.name;
            if(document.getElementById('destination-name')) document.getElementById('destination-name').textContent = dest.name;
            if(document.getElementById('destination-tagline')) document.getElementById('destination-tagline').textContent = dest.description || '';
            if(document.getElementById('dest-city')) document.getElementById('dest-city').textContent = (dest.city || '').toUpperCase();
            if(document.getElementById('dest-price')) document.getElementById('dest-price').textContent = formatRupiah(dest.price);
            if(document.getElementById('dest-open')) document.getElementById('dest-open').textContent = dest.hours || '08:00 - 17:00 WITA';
            if(document.getElementById('dest-address')) document.getElementById('dest-address').textContent = dest.address || 'Alamat tersedia di lokasi.';

            // --- GAMBAR GALERI ---
            const mainPhotoPlaceholder = document.getElementById('main-photo-placeholder');
            const thumbnailContainer = document.getElementById('thumbnail-container'); 

            if (mainPhotoPlaceholder && thumbnailContainer) {
                let allImages = [];
                if (dest.image_url) allImages.push(dest.image_url);
                if (dest.gallery_images) {
                    try {
                        const gallery = JSON.parse(dest.gallery_images);
                        if (Array.isArray(gallery)) allImages = allImages.concat(gallery);
                    } catch (e) {}
                }
                if (allImages.length === 0) allImages.push('default.jpg');

                const getFullPath = (img) => img.startsWith('http') ? img : IMAGE_BASE_PATH + img;
                
                const showMain = (src) => {
                    mainPhotoPlaceholder.innerHTML = `<img src="${src}" class="main-gallery-image" style="width:100%; height:400px; object-fit:cover; border-radius:8px;" onerror="this.src='${IMAGE_BASE_PATH}default.jpg'">`;
                };
                showMain(getFullPath(allImages[0]));

                thumbnailContainer.innerHTML = '';
                allImages.slice(0, 5).forEach((img, idx) => {
                    const fullPath = getFullPath(img);
                    const thumb = document.createElement('div');
                    thumb.className = 'thumb-photo' + (idx === 0 ? ' active' : '');
                    thumb.innerHTML = `<img src="${fullPath}" style="width:100%; height:100%; object-fit:cover; cursor:pointer;">`;
                    thumb.onclick = () => {
                        showMain(fullPath);
                        document.querySelectorAll('.thumb-photo').forEach(t => t.classList.remove('active'));
                        thumb.classList.add('active');
                    };
                    thumbnailContainer.appendChild(thumb);
                });
            }

            // --- TOMBOL BOOKING ---
            const cat = urlParams.get('category') || 'nature';
            const ticketBtn = document.getElementById('ticket-link');
            if(ticketBtn) ticketBtn.href = `halamanpemesanan.html?id=${dest.id}&category=${cat}`;

            // --- PETA (EMBED) ---
            const locBox = document.querySelector('.location-box');
            if (document.getElementById('map-frame')) document.getElementById('map-frame').remove();
            if (document.getElementById('btn-map-link')) document.getElementById('btn-map-link').remove();

            if (dest.latitude && dest.longitude && locBox) {
                const iframe = document.createElement('iframe');
                iframe.id = 'map-frame';
                iframe.src = `${BASE_API_URL}/maps.php?id=${dest.id}`;
                iframe.style.cssText = "width:100%; height:250px; border:0; border-radius:8px; margin-top:15px; box-shadow:0 2px 5px rgba(0,0,0,0.1);";
                locBox.appendChild(iframe);
            }

			// --- TOMBOL REVIEW (LOGIKA BARU: PAKSA MUNCUL) ---
            const reviewBtn = document.getElementById('review-link');
            
            if (reviewBtn) {
                // 1. Hapus style 'display:none' biar tombol PASTI MUNCUL
                reviewBtn.removeAttribute('style'); 
                reviewBtn.style.display = 'inline-block'; // Paksa tampil
                reviewBtn.innerText = "TULIS ULASAN";
                
                // Arahkan href ke halaman review (biar kalau diklik kanan open tab tetap jalan)
                reviewBtn.href = `halamanreview.html?id=${dest.id}&category=${cat}`;

                // 2. Pasang Event Klik (Validasi terjadi SAAT KLIK, bukan saat loading)
                reviewBtn.onclick = async (e) => {
                    e.preventDefault(); // Tahan dulu, jangan pindah halaman

                    // Cek Login di Browser
                    if (!localStorage.getItem('auth_token')) {
                        alert("Silakan Login terlebih dahulu untuk menulis ulasan.");
                        window.location.href = 'login.html';
                        return;
                    }

                    // Beri feedback visual
                    const originalText = reviewBtn.innerText;
                    reviewBtn.innerText = "Mengecek data...";
                    reviewBtn.style.opacity = "0.7";
                    
                    try {
                        // Cek ke Backend: Apakah User ini berhak review?
                        const res = await fetch(`${BASE_API_URL}/check_eligibility.php?product_id=${id}`, { headers: getHeaders() });
                        const json = await res.json();

                        if (json.status === 'success' && json.data.eligible === true) {
                            // LOLOS VALIDASI -> Pindah ke Halaman Review
                            window.location.href = reviewBtn.href;
                        } else {
                            // DITOLAK
                            alert("Maaf, Anda harus membeli tiket wisata ini (dan status Lunas) sebelum bisa memberikan ulasan.");
                        }
                    } catch (err) { 
                        console.error(err);
                        alert("Terjadi kesalahan koneksi ke server.");
                    } finally {
                        // Balikkan tombol seperti semula
                        reviewBtn.innerText = originalText;
                        reviewBtn.style.opacity = "1";
                    }
                };
            } else {
                console.error("Tombol review-link tidak ditemukan di HTML!");
            }

            // --- LOAD REVIEW LIST (DINAMIS) ---
            const reviewsContainer = document.querySelector('.reviews-list');
            const reviewStatsLabel = document.querySelector('.review-stats p');

            if (reviewsContainer) {
                reviewsContainer.innerHTML = '<p style="text-align:center; color:#888;">Memuat ulasan...</p>';
                fetch(`${BASE_API_URL}/get_reviews.php?product_id=${id}`, { headers: getHeaders() })
                    .then(res => res.json())
                    .then(json => {
                        if (json.status === 'success') {
                            const reviews = json.data;
                            reviewsContainer.innerHTML = ''; 

                            if (reviews.length === 0) {
                                reviewsContainer.innerHTML = '<p style="text-align:center; color:#888;">Belum ada ulasan.</p>';
                                if(reviewStatsLabel) reviewStatsLabel.textContent = "⭐ 0.0 / 5.0 (Dari 0 Ulasan)";
                                return;
                            }

                            let totalRating = 0;
                            reviews.forEach(r => {
                                totalRating += parseInt(r.rating);
                                const stars = '⭐'.repeat(parseInt(r.rating));
                                const date = new Date(r.created_at).toLocaleDateString('id-ID');
                                
                                reviewsContainer.innerHTML += `
                                    <div class="review-item">
                                        <div class="review-header">
                                            <strong class="reviewer-name" style="color:#007bff;">${r.reviewer_name}</strong>
                                            <span class="review-rating">${stars}</span>
                                        </div>
                                        <p class="review-date" style="font-size:0.8em; color:#999;">${date}</p>
                                        <p class="review-text" style="color:#333;">${r.comment}</p>
                                    </div>`;
                            });

                            const avg = (totalRating / reviews.length).toFixed(1);
                            if(reviewStatsLabel) {
                                reviewStatsLabel.textContent = `⭐ ${avg} / 5.0 (Dari ${reviews.length} Ulasan)`;
                            }
                        }
                    });
            }

        } catch (err) { console.error(err); }
    }

// ==========================================================
    // 5. LOGIKA HALAMAN PEMESANAN (FIX VOUCHER)
    // ==========================================================
    async function handleBookingPageLoad(id) {
        // 1. Cek Login
        if (!localStorage.getItem('auth_token')) {
            alert("Harap login untuk memesan tiket!");
            window.location.href = 'login.html';
            return;
        }

        // 2. Ambil Data Produk
        const res = await fetch(`${BASE_API_URL}/get_product_detail.php?id=${id}`, { headers: getHeaders() });
        const json = await res.json();
        const dest = json.data;

        if (!dest) { alert("Data wisata error"); return; }

        // 3. Isi UI Dasar
        document.getElementById('booking-dest-name').textContent = dest.name;
        document.getElementById('booking-price').textContent = formatRupiah(dest.price);
        document.getElementById('summary-dest-name').textContent = dest.name;
        document.getElementById('summary-price-unit').textContent = formatRupiah(dest.price);

        const userName = localStorage.getItem('user_name');
        if(userName) document.getElementById('booking-name').value = userName;
        document.getElementById('booking-date').min = new Date().toISOString().split("T")[0];

        // 4. Variabel Kalkulasi
        const ticketInput = document.getElementById('booking-tickets');
        const totalDisplay = document.getElementById('summary-total-price');
        
        // Elemen Diskon (Pastikan ID ini ada di HTML)
        const discountRow = document.getElementById('row-discount'); 
        const discountPercentLabel = document.getElementById('discount-percent');
        const discountValueLabel = document.getElementById('summary-discount');

        let currentPrice = parseFloat(dest.price);
        let currentDiscountPercent = 0; 
        let appliedVoucherCode = null;

        // Fungsi Hitung Ulang
        const recalculateTotal = () => {
            const qty = parseInt(ticketInput.value) || 1;
            const grossTotal = qty * currentPrice;
            
            const discountAmount = (grossTotal * currentDiscountPercent) / 100;
            const finalTotal = grossTotal - discountAmount;

            document.getElementById('summary-tickets-count').textContent = qty;
            totalDisplay.textContent = formatRupiah(finalTotal);

            // UI Diskon
            if (currentDiscountPercent > 0 && discountRow) {
                discountRow.style.display = 'flex';
                discountPercentLabel.textContent = currentDiscountPercent;
                discountValueLabel.textContent = `-${formatRupiah(discountAmount)}`;
            } else if (discountRow) {
                discountRow.style.display = 'none';
            }
        };

        // Event Jumlah Tiket
        ticketInput.addEventListener('input', recalculateTotal);
        recalculateTotal(); // Init awal

        // =========================================
        // 🔥 LOGIKA VOUCHER (FIXED)
        // =========================================
        const voucherInput = document.getElementById('voucher-input');
        const btnApply = document.getElementById('btn-apply-voucher');
        const msgBox = document.getElementById('voucher-message');

        // Cek apakah elemen voucher ada di HTML?
        if (voucherInput && btnApply) {
            console.log("Fitur Voucher Aktif"); // Cek Console Browser

            btnApply.addEventListener('click', async (e) => {
                e.preventDefault(); // Mencegah refresh jika tombol ada dalam form
                
                const code = voucherInput.value.trim();
                if(!code) { alert("Masukkan kode dulu!"); return; }

                btnApply.innerText = "Cek...";
                btnApply.disabled = true;
                
                try {
                    const resV = await fetch(`${BASE_API_URL}/check_voucher.php?code=${code}`, { headers: getHeaders() });
                    const jsonV = await resV.json();

                    if (jsonV.status === 'success') {
                        currentDiscountPercent = jsonV.data.discount_percent;
                        appliedVoucherCode = jsonV.data.code;
                        
                        if(msgBox) {
                            msgBox.style.color = 'green';
                            msgBox.textContent = `Sukses! Diskon ${currentDiscountPercent}%`;
                        }
                        alert(`Voucher Berhasil! Hemat ${currentDiscountPercent}%`);
                    } else {
                        currentDiscountPercent = 0;
                        appliedVoucherCode = null;
                        if(msgBox) {
                            msgBox.style.color = 'red';
                            msgBox.textContent = jsonV.message;
                        }
                        alert(jsonV.message);
                    }
                    recalculateTotal(); // Hitung ulang harga setelah voucher
                } catch(err) {
                    console.error(err);
                    alert("Gagal mengecek voucher");
                } finally {
                    btnApply.innerText = "Gunakan";
                    btnApply.disabled = false;
                }
            });
        } else {
            console.warn("Elemen Voucher tidak ditemukan di HTML. Cek ID 'voucher-input' dan 'btn-apply-voucher'");
        }

        // =========================================
        // SUBMIT BOOKING
        // =========================================
        document.getElementById('booking-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            btn.innerText = "Memproses..."; btn.disabled = true;

            try {
                const checkoutRes = await fetch(`${BASE_API_URL}/checkout.php`, {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        product_id: id,
                        quantity: ticketInput.value,
                        visit_date: document.getElementById('booking-date').value,
                        visitor_name: document.getElementById('booking-name').value,
                        voucher_code: appliedVoucherCode
                    })
                });
                const result = await checkoutRes.json();

                if (result.status === 'success') {
                    if (result.data.is_free === true) {
                        alert("Transaksi Berhasil! (Gratis)");
                        window.location.href = "orders.html";
                    } else {
                        window.snap.pay(result.data.token, {
                            onSuccess: () => { alert("Pembayaran Sukses!"); window.location.href = "orders.html"; },
                            onPending: () => { alert("Menunggu Pembayaran"); window.location.href = "orders.html"; },
                            onError: () => { alert("Gagal"); btn.disabled = false; },
                            onClose: () => { btn.disabled = false; btn.innerText = "Konfirmasi & Pesan"; }
                        });
                    }
                } else {
                    alert(result.message);
                    btn.disabled = false;
                }
            } catch (err) { 
                alert("Error System"); 
                console.error(err);
                btn.disabled = false; 
            }
        });
    }

    // ==========================================================
    // 7. HALAMAN REVIEW FORM
    // ==========================================================
    async function handleReviewPageLoad(id) {
        // (Kode form review Anda di sini)
        // Pastikan ID form dan input sesuai dengan HTML halamanreview.html
        const form = document.getElementById('review-form');
        if (form) {
            const userName = localStorage.getItem('user_name');
            if(userName) document.getElementById('reviewer-name').value = userName;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const ratingEl = document.querySelector('input[name="rating"]:checked');
                if(!ratingEl) { alert("Pilih bintang!"); return; }

                const res = await fetch(`${BASE_API_URL}/add_review.php`, {
                    method: 'POST', headers: getHeaders(),
                    body: JSON.stringify({
                        product_id: id,
                        rating: ratingEl.value,
                        comment: document.getElementById('review-text').value
                    })
                });
                const data = await res.json();
                if(data.status === 'success') {
                    alert("Terkirim!");
                    window.history.back();
                } else {
                    alert(data.message);
                }
            });
        }
    }

// ==========================================================
    // 8. UPDATE NAVBAR (FIXED LOGIC)
    // ==========================================================
    function updateNavbar() {
        const navUl = document.querySelector('nav ul');
        if (!navUl) return;

        const token = localStorage.getItem('auth_token');
        const userName = localStorage.getItem('user_name') || 'User';
        const userAvatar = localStorage.getItem('user_avatar');

        // --- PERBAIKAN LOGIKA PATH ---
        // 1. Prefix untuk LINK HALAMAN (HTML ke HTML)
        // Karena semua file HTML (home, login, orders, profile) ada di folder yang SAMA ('pages/'),
        // maka kita TIDAK PERLU prefix '../'. Linknya langsung nama file saja.
        const htmlPrefix = ''; 

        // 2. Prefix untuk GAMBAR (HTML ke Assets)
        // Karena HTML ada di 'pages/' dan gambar di 'assets/', kita perlu mundur satu langkah.
        const imagePrefix = '../'; 
        const avatarBasePath = imagePrefix + 'assets/foto_profil/';

        // Bersihkan tombol lama
        ['nav-auth-item', 'nav-orders-item', 'nav-profile-item'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.remove();
        });

        // Hapus link hardcode
        navUl.querySelectorAll('li a').forEach(link => {
            const h = link.getAttribute('href') || '';
            if (h.includes('login.html') || h.includes('orders.html')) link.parentElement.remove();
        });

        if (token) {
            // 1. Menu Tiket Saya
            const liOrders = document.createElement('li');
            liOrders.id = 'nav-orders-item';
            liOrders.innerHTML = `<a href="${htmlPrefix}orders.html">TIKET SAYA</a>`;
            navUl.appendChild(liOrders);

            // 2. Menu Profil (DENGAN FOTO)
            let avatarHtml = '<i class="fas fa-user-circle" style="margin-right:5px;"></i>'; // Default Icon
            
            if (userAvatar && userAvatar !== 'null' && userAvatar !== 'undefined') {
                // Path gambar: ../assets/foto_profil/namafile.jpg
                const avatarPath = avatarBasePath + userAvatar;
                const timestamp = new Date().getTime(); 
                // Style diperbesar (40px) agar terlihat jelas
                avatarHtml = `<img src="${avatarPath}?v=${timestamp}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; vertical-align:middle; border:2px solid #fff; margin-right:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">`;
            }

            const liProfile = document.createElement('li');
            liProfile.id = 'nav-profile-item';
            // Link profil menggunakan htmlPrefix (kosong)
            liProfile.innerHTML = `<a href="${htmlPrefix}profile.html" style="color:#007bff; font-weight:bold; display:flex; align-items:center;">${avatarHtml} ${userName}</a>`;
            navUl.appendChild(liProfile);

            // 3. Menu Logout
            const liAuth = document.createElement('li');
            liAuth.id = 'nav-auth-item';
            liAuth.innerHTML = `<a href="#" id="btn-logout" style="color:red;">LOGOUT</a>`;
            navUl.appendChild(liAuth);

            document.getElementById('btn-logout').addEventListener('click', (e) => {
                e.preventDefault();
                if(confirm("Keluar?")) {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_name');
                    localStorage.removeItem('user_avatar');
                    localStorage.removeItem('user_role');
                    window.location.href = htmlPrefix + 'home.html';
                }
            });
        } else {
            // Menu Login
            const liAuth = document.createElement('li');
            liAuth.id = 'nav-auth-item';
            liAuth.innerHTML = `<a href="${htmlPrefix}login.html" style="color:#8ac926; font-weight:bold;">LOGIN</a>`;
            navUl.appendChild(liAuth);
        }
    }
    
    // ==========================================================
    // 9. LOGIKA MOBILE MENU
    // ==========================================================
    const menuToggle = document.querySelector('.menu-toggle');
    const navList = document.querySelector('nav ul');
    if (menuToggle && navList) {
        menuToggle.addEventListener('click', () => {
            navList.classList.toggle('slide');
            const icon = menuToggle.querySelector('i');
            if (navList.classList.contains('slide')) {
                icon.classList.remove('fa-bars'); icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times'); icon.classList.add('fa-bars');
            }
        });
    }
    // 10. LOGIKA CONTACT FORM
    const contactForm = document.getElementById('form-contact');
    if (contactForm) {
        // Auto-fill jika user login
        const userName = localStorage.getItem('user_name');
        if (userName) document.getElementById('contact-name').value = userName;

        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = contactForm.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = "Mengirim..."; btn.disabled = true;

            const name = document.getElementById('contact-name').value;
            const email = document.getElementById('contact-email').value;
            const message = document.getElementById('contact-message').value;

            try {
                const res = await fetch(`${BASE_API_URL}/send_message.php`, {
                    method: 'POST',
                    headers: getHeaders(), // Pastikan ini ada (walau guest tetap butuh API Key)
                    body: JSON.stringify({ name, email, message })
                });
                const json = await res.json();

                if (json.status === 'success') {
                    alert(json.message);
                    contactForm.reset();
                } else {
                    alert("Gagal: " + json.message);
                }
            } catch (err) {
                alert("Terjadi kesalahan koneksi.");
                console.error(err);
            } finally {
                btn.innerText = originalText; btn.disabled = false;
            }
        });
    }
});