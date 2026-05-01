-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Waktu pembuatan: 30 Nov 2025 pada 02.42
-- Versi server: 10.6.22-MariaDB
-- Versi PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40458927_tiket_wisata`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `auth_token` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `email`, `password`, `name`, `phone`, `auth_token`, `created_at`, `role`, `avatar`) VALUES
(1, 'lanas9066+admin@gmail.com', '$2y$10$icAza9n9Jhh.lNSfwoZX7eH5qqVujkECYmHt1zISbPg5lkbrYxTBK', 'Admin', '083116275693', '28cf9665fe676b3f7ee20cc8dd755e9fd0bcca18139273248b9ec867485b9de9', '2025-11-22 14:09:08', 'admin', NULL),
(3, 'lanas9066@gmail.com', '$2y$10$ylpzqwjp/stpxcBQwSaWleCJkt2Jqaq8ZCdkM3mX/sNVaSLC3Bpiq', 'Muhammad Maulana', '083116275693', '8d00b1e24d8b795c259453f038e0b5b253d440bf5586535519f3e323789f8573', '2025-11-21 14:47:12', 'user', '3_1764211673.webp'),
(6, '123@gmail.com', '$2y$10$1YYnePzpoUUUHhGwf3ARkuoePRhTjQ7yee6rADFZheG9ErPMDZI2K', 'RJ', '08724789232', 'f7e19622b3aab0d61260e5e33fc10f923ad6e13a98747d9ac0a09f17b2eda97a', '2025-11-23 05:17:20', 'user', NULL),
(7, '2411102441072@umkt.ac.id', '$2y$10$1iNV6kMvStkckUzmSBeB7uSzszXOnbXkQCjhhG8hg0A8.f/gZqKF6', 'Irfan', '082196636162', '927bef370193f0f9b1441ba3b055861d77230381a9c6e923e2815d84ec6c4880', '2025-11-24 09:00:54', 'user', NULL),
(8, 'alfitorayzhadinoval@gmail.com', '$2y$10$.DhJH9nImFlMGmqlzuxu8uxBGHmkAa5EZ/UoGMrr5cl3RfSFo/ZnW', 'ALFITO RAYZHA DINOVAL', '085247293282', 'dc53bd89524a57a6a1480c6a122b71250c43225749a60bb1a268c649334ec019', '2025-11-24 14:12:52', 'user', NULL),
(9, '123456@gmail.com', '$2y$10$pd6Qeg/fYGBpeUNAbbSomeLE.wGGivlGXr/Wn89Pm8eRSQjUmRQem', 'Sofwan', '085248763970', '19dd87826909ec7939965cbc5e7f76950a669120d26b45c276d9deac67c68043', '2025-11-26 06:09:38', 'user', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Muhammad Maulana', 'lanas9066@gmail.com', 'P', '2025-11-22 13:45:35'),
(2, 'Muhammad Maulana', 'lanas9066@gmail.com', 'a', '2025-11-25 13:28:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('lanas9066@gmail.com', '69851565cc6d81f85058839eb42581131bb468aae1e8e09aa9992411ed50fad2', '2025-11-24 06:07:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `hours` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `stock` int(11) DEFAULT 100,
  `image_url` text DEFAULT NULL,
  `gallery_images` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `hours`, `address`, `stock`, `image_url`, `gallery_images`, `latitude`, `longitude`, `created_at`, `category`, `city`) VALUES
(1, 'Pulau Derawan', 'Surga Bawah Laut Kalimantan Timur', '30000.00', '08:00 - 18:00 WITA', 'Berau, Kalimantan Timur', 90, '6925cf66b4f5e_upd_0.jpg', '[\"6925cf66b5b97_upd_1.webp\",\"6925cf66b5ecb_upd_2.jpg\",\"6925cf66b621d_upd_3.jpg\",\"6925cf66b6485_upd_4.jpeg\"]', '2.28489400', '118.24379000', '2025-11-22 05:59:26', 'nature', 'berau'),
(2, 'Taman Nasional Kutai', 'Rumah bagi Orangutan dan Flora Endemik', '25000.00', '07:00 - 16:00 WITA', 'Sanggata, Kutai Timur', 99, 'TamanNasionalKutai.jpg', NULL, '0.35000000', '117.26666700', '2025-11-22 05:59:26', 'nature', 'kutai'),
(3, 'Danau Labuan Cermin', 'Danau Dua Rasa yang Unik', '15000.00', '08:00 - 17:00 WITA', 'Biduk-Biduk, Berau', 100, '6923a890850e8_upd_0.jpeg', '[\"6923a890853ac_upd_1.jpg\",\"6923a89085628_upd_2.jpeg\",\"6923a89085829_upd_3.webp\"]', '1.25000000', '118.68000000', '2025-11-22 05:59:26', 'nature', 'berau'),
(4, 'Kawasan Konservasi Mangrove', 'Ekowisata Hutan Bakau Balikpapan', '10000.00', '07:00 - 18:00 WITA', 'Karang Rejo, Balikpapan', 100, 'HutanMagroveMargomulyo-1.jpeg', NULL, '-1.23550000', '116.84690000', '2025-11-22 05:59:26', 'nature', 'balikpapan'),
(5, 'Goa Haji Mangku', 'Goa dengan Pemandian Air Asin', '25000.00', '08:00 - 17:00 WITA', 'Pulau Kakaban, Berau', 99, 'GuaHajiMangku-1.jpg', NULL, '2.27800000', '118.61500000', '2025-11-22 05:59:26', 'nature', 'berau'),
(6, 'Pantai Manggar Segarasari', 'Destinasi Pantai Favorit Keluarga', '10000.00', '06:00 - 18:00 WITA', 'Manggar, Balikpapan', 98, '6924537195509_upd_0.jpg', '[\"6924537195a7a_upd_1.jpeg\",\"6924537195fe1_upd_2.jpg\",\"69245371965d7_upd_3.webp\"]', '-1.21433100', '116.97479000', '2025-11-22 05:59:26', 'nature', 'balikpapan'),
(7, 'Pulau Kakaban', 'Danau Ubur-ubur Tak Menyengat', '40000.00', '08:00 - 17:00 WITA', 'Kepulauan Derawan, Berau', 97, 'PulauKakaban-1.webp', NULL, '2.15000000', '118.53300000', '2025-11-22 05:59:26', 'nature', 'berau'),
(8, 'Goa Tengkorak', 'Wisata Gua Eksotis', '25000.00', '08:00 - 17:00 WITA', 'Kabupaten Paser', 98, 'GoaTengkorak-1.webp', NULL, '-1.38560000', '116.23390000', '2025-11-22 05:59:26', 'adventure', 'paser'),
(9, 'Museum Mulawarman', 'Jejak Sejarah Kerajaan Tertua', '10000.00', '08:00 - 16:00 WITA', 'Tenggarong', 97, 'museum_mulawarman.jpg', NULL, '-0.41308000', '116.99040000', '2025-11-22 05:59:26', 'culture', 'kutai'),
(10, 'Pantai Lamaru', NULL, '25000.00', '06:00 - 18:00 WITA', 'Balikpapan', 98, 'PantaiLamaru-1.jpeg', NULL, '-1.19994700', '116.97538000', '2025-11-22 11:43:45', 'nature', 'balikpapan'),
(11, 'Pulau Beras Basah', NULL, '40000.00', NULL, 'Kota Bontang, Provinsi Kalimantan Timur', 98, 'PulauBerasBasah-1.jpeg', NULL, '0.06381500', '117.55909700', '2025-11-22 11:58:04', 'nature', 'bontang'),
(12, 'Samarinda Theme Park', NULL, '25000.00', NULL, 'Samarinda', 98, 'samarinda-theme-park.jpg', NULL, '-0.44022200', '117.19417800', '2025-11-22 12:00:01', 'family', 'samarinda'),
(13, 'Pulau Kumala', 'Sebuah pulau kecil di tengah Sungai Mahakam, Kabupaten Kutai Kartanegara, Kalimantan Timur, yang telah diubah menjadi kawasan wisata.', '10000.00', '09:00 - 16:30 WITA', 'Tenggarong, Kutai Kartanegara.', 100, '6924fdbf00e4a_upd_0.webp', '[\"6924fdbf014c5_upd_1.webp\",\"6924fdbf0197c_upd_2.webp\",\"6924fdbf01ee9_upd_3.webp\"]', '-0.42554000', '116.99839000', '2025-11-25 00:50:14', 'culture', 'kutai'),
(14, 'Rumah Adat Lamin Desa Lekaq Kidau', 'Rumah Adat Lamin di Desa Lekaq Kidau adalah rumah panjang khas Suku Dayak, Kalimantan Timur, yang berfungsi sebagai rumah bersama untuk banyak keluarga.', '15000.00', '', 'Sebulu, Kutai Kartanegara', 100, '6924fee8096c0_0.webp', '[\"6924fee80998f_1.webp\",\"6924fee809c93_2.webp\",\"6924fee809f37_3.webp\"]', '-0.30803413', '116.81901097', '2025-11-25 00:57:12', 'culture', 'kutai'),
(15, 'Taman Budaya Kota Samarinda', 'Taman Budaya Samarinda adalah pusat seni dan kebudayaan yang berfungsi sebagai tempat pagelaran seni, pameran, dan pertunjukan budaya Kalimantan Timur. ', '10000.00', '14.00 - 17.00 WITA', 'Kec. Sungai Pinang, Kota Samarinda', 100, '692503f9b12c4_upd_0.webp', '[\"692503f9b15bb_upd_1.webp\",\"692503f9b180b_upd_2.webp\",\"692503f9b1a67_upd_3.webp\"]', '-0.47848110', '117.16334800', '2025-11-25 01:01:24', 'culture', 'samarinda'),
(232, 'Goa Tapak Raja', 'Goa Tapak Raja adalah destinasi wisata alam di Penajam Paser Utara, Kalimantan Timur, yang terkenal karena adanya stalaktit berbentuk tapak kaki raksasa dan diyakini sebagai tempat pertapaan raja di masa kerajaan.', '20000.00', '08:00 - 17:00 WITA', 'Sepaku, Penajam Paser Utara, kota Nusantara', 98, '692507993c983_0.jpeg', '[\"692507993cc9e_1.jpg\",\"692507993cf0d_2.jpeg\",\"692507993d193_3.jpg\",\"692507993d3c6_4.jpeg\"]', '-0.97051696', '116.82318146', '2025-11-25 01:34:17', 'adventure', 'penajam'),
(233, 'Rumah Ulin Arya', 'Rumah Ulin Arya adalah tempat wisata alam terbuka dan mini zoo di Samarinda, Kalimantan Timur, yang menawarkan berbagai wahana rekreasi keluarga.', '70000.00', '08:30 - 16:30 WITA', 'Samarinda, Kalimantan Timur.', 100, '6925088726a3a_0.jpeg', '[\"6925088726f39_1.jpg\",\"69250887273dd_2.webp\",\"6925088727879_3.jpg\",\"6925088727da1_4.jpg\",\"69250887281f8_5.jpg\"]', '-0.38746280', '117.16243782', '2025-11-25 01:38:15', 'family', 'samarinda');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `rating` int(11) DEFAULT NULL
) ;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `customer_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 3, 5, 'Sangat menyenangkan', '2025-11-24 01:34:42'),
(2, 7, 3, 5, 'Tempatnya sangat indah sekali!', '2025-11-24 01:34:42'),
(3, 6, 3, 5, 'Sangat puas', '2025-11-24 01:34:42'),
(4, 6, 3, 5, 'Menyenangkan', '2025-11-24 01:34:42'),
(5, 7, 3, 5, 'Sangat puas', '2025-11-24 01:34:42'),
(6, 11, 3, 5, 'Sangat indah', '2025-11-24 01:36:15'),
(7, 11, 3, 5, 'Nice', '2025-11-24 06:18:16'),
(8, 232, 3, 5, '?', '2025-11-25 15:33:57'),
(9, 12, 3, 5, 'Mantap', '2025-11-27 03:06:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `voucher_code` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `snap_token` text DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expire_time` timestamp NULL DEFAULT NULL,
  `is_redeemed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `customer_id`, `total_amount`, `discount_amount`, `voucher_code`, `status`, `snap_token`, `payment_type`, `created_at`, `updated_at`, `expire_time`, `is_redeemed`) VALUES
(14, 'TRX-1763799550-507', 3, '30000.00', '0.00', NULL, 'paid', 'c8e3168c-e0b3-4683-b548-b9a997ae1e91', NULL, '2025-11-22 08:19:10', '2025-11-25 11:06:41', '2025-11-23 08:19:10', 0),
(15, 'TRX-1763799606-636', 3, '30000.00', '0.00', NULL, 'expired', '71f5b963-9c74-4018-99f6-4e8cbc577471', NULL, '2025-11-22 08:20:06', '2025-11-25 11:20:16', '2025-11-23 08:20:06', 0),
(16, 'TRX-1763800363-390', 3, '30000.00', '0.00', NULL, 'paid', 'a77d5803-511e-405b-86c5-7dbec444221c', NULL, '2025-11-22 08:32:43', '2025-11-25 11:06:41', '2025-11-23 08:32:43', 0),
(17, 'TRX-1763813270-970', 3, '25000.00', '0.00', NULL, 'paid', '44213ef1-1e64-4c3d-93d8-e3b4b39cc0eb', NULL, '2025-11-22 12:07:50', '2025-11-25 11:06:41', '2025-11-23 12:07:50', 0),
(18, 'TRX-1763814426-812', 3, '25000.00', '0.00', NULL, 'expired', 'f719f501-534b-49bd-9837-37399467e348', NULL, '2025-11-22 12:27:06', '2025-11-25 11:20:32', '2025-11-23 12:27:06', 0),
(19, 'TRX-1763815841-799', 3, '25000.00', '0.00', NULL, 'failed', '0ae4f3fd-891b-4049-a594-ab4dcb6ee710', NULL, '2025-11-22 12:50:41', '2025-11-25 11:06:41', '2025-11-23 12:50:41', 0),
(20, 'TRX-1763821472-752', 3, '10000.00', '0.00', NULL, 'expired', '9578283b-da9a-4857-879e-9f1f64cc534d', NULL, '2025-11-22 14:24:32', '2025-11-25 11:20:56', '2025-11-23 14:24:32', 0),
(21, 'TRX-1763821483-161', 3, '10000.00', '0.00', NULL, 'expired', '184d8b43-d1cf-47b3-a5cd-13ff2d7a3a1d', NULL, '2025-11-22 14:24:43', '2025-11-25 11:21:08', '2025-11-23 14:24:43', 0),
(22, 'TRX-1763821501-125', 3, '10000.00', '0.00', NULL, 'paid', '264ef24c-622d-4734-af2c-0a661e74e76a', NULL, '2025-11-22 14:25:01', '2025-11-25 11:06:41', '2025-11-23 14:25:01', 0),
(23, 'TRX-1763821937-862', 3, '0.00', '10000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-22 14:32:17', '2025-11-25 11:06:41', '2025-11-23 14:32:17', 0),
(35, 'TRX-1763823671-544', 3, '100.00', '9900.00', 'HEMAT99', 'paid', 'd0dc4056-1622-436a-ab41-d5b02b6b6df8', NULL, '2025-11-22 15:01:11', '2025-11-25 11:06:41', '2025-11-23 15:01:11', 0),
(36, 'TRX-1763863583-722', 3, '25000.00', '0.00', NULL, 'paid', '95c65d61-552b-4f20-a519-23517c6e6242', NULL, '2025-11-23 02:06:23', '2025-11-25 11:06:41', '2025-11-24 02:06:23', 0),
(37, 'TRX-1763875166-173', 6, '30000.00', '0.00', NULL, 'expired', '18d36dac-271b-4db5-a5aa-c6cc26f3d03f', NULL, '2025-11-23 05:19:26', '2025-11-25 11:21:20', '2025-11-24 05:19:26', 0),
(38, 'TRX-1763875390-858', 6, '0.00', '25000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-23 05:23:10', '2025-11-25 11:06:41', '2025-11-24 05:23:10', 0),
(39, 'TRX-1763898323-178', 3, '0.00', '40000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-23 11:45:23', '2025-11-25 11:06:41', '2025-11-24 11:45:23', 0),
(40, 'TRX-1763946705-422', 3, '0.00', '30000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 01:11:45', '2025-11-25 11:06:41', '2025-11-25 01:11:45', 0),
(41, 'TRX-1763947074-390', 3, '0.00', '30000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 01:17:54', '2025-11-25 11:06:41', '2025-11-25 01:17:54', 0),
(42, 'TRX-1763947913-954', 3, '0.00', '40000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 01:31:53', '2025-11-25 11:06:41', '2025-11-25 01:31:53', 0),
(43, 'TRX-1763948152-305', 3, '0.00', '40000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 01:35:52', '2025-11-25 11:06:41', '2025-11-25 01:35:52', 0),
(44, 'TRX-1763950774-964', 3, '0.00', '25000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 02:19:34', '2025-11-25 11:06:41', '2025-11-25 02:19:34', 0),
(45, 'TRX-1763950957-318', 3, '0.00', '25000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 02:22:37', '2025-11-25 11:06:41', '2025-11-25 02:22:37', 0),
(46, 'TRX-1763952315-409', 3, '10000.00', '0.00', NULL, 'paid', 'c2e947e8-bf60-4e13-b24c-46b60ac172fe', NULL, '2025-11-24 02:45:15', '2025-11-25 11:06:41', '2025-11-25 02:45:15', 0),
(47, 'TRX-1763956052-166', 6, '0.00', '10000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 03:47:32', '2025-11-25 11:06:41', '2025-11-25 03:47:32', 0),
(48, 'TRX-1763964819-777', 3, '0.00', '40000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 06:13:39', '2025-11-25 11:06:41', '2025-11-25 06:13:39', 0),
(49, 'TRX-1763975079-417', 7, '0.00', '25000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-24 09:04:39', '2025-11-25 11:06:41', '2025-11-25 09:04:39', 0),
(50, 'TRX-1763988184-961', 3, '40000.00', '0.00', NULL, 'paid', 'a5626cdb-4881-44d8-98ca-f49dfae5a350', NULL, '2025-11-24 12:43:04', '2025-11-25 11:06:41', '2025-11-25 12:43:04', 0),
(51, 'TRX-1763993665-327', 8, '60000.00', '0.00', NULL, 'paid', '668c8cc5-f2bd-4d3c-9d42-79798565347a', NULL, '2025-11-24 14:14:25', '2025-11-25 11:06:41', '2025-11-25 14:14:25', 0),
(52, 'TRX-1764029661-699', 3, '0.00', '30000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-25 00:14:21', '2025-11-25 11:06:41', '2025-11-26 00:14:21', 0),
(53, 'TRX-1764052161-427', 3, '300.00', '29700.00', 'HEMAT99', 'paid', '78b14110-cd2a-43c6-a2bb-bcf07a3a66b0', NULL, '2025-11-25 06:29:21', '2025-11-25 11:06:41', '2025-11-26 06:29:21', 0),
(54, 'TRX-1764055476-575', 3, '300.00', '29700.00', 'HEMAT99', 'paid', '3871af83-1ba0-4f74-8353-23ca6d430a83', NULL, '2025-11-25 07:24:36', '2025-11-25 11:06:41', '2025-11-26 07:24:36', 0),
(55, 'TRX-1764079660-221', 3, '20000.00', '0.00', NULL, 'paid', 'cf3ae40d-e85b-4a4c-8b6c-43d173e07710', NULL, '2025-11-25 14:07:40', '2025-11-25 14:08:03', '2025-11-27 06:07:40', 0),
(56, 'TRX-1764084873-460', 3, '0.00', '20000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-25 15:34:33', '2025-11-25 15:34:33', '2025-11-27 07:34:33', 0),
(57, 'TRX-1764116045-734', 3, '100.00', '9900.00', 'HEMAT99', 'paid', '15e683e1-b92f-42d0-b5c0-59c1b9ebdd81', NULL, '2025-11-26 00:14:06', '2025-11-26 00:14:29', '2025-11-27 16:14:05', 0),
(58, 'TRX-1764128725-887', 3, '30000.00', '0.00', NULL, 'expired', '5b9710d2-f4b8-432c-9a07-742585cf843d', NULL, '2025-11-26 03:45:25', '2025-11-30 02:18:19', '2025-11-27 19:45:25', 0),
(59, 'TRX-1764137464-939', 9, '30000.00', '0.00', NULL, 'expired', '305e2393-6daf-45ec-9594-95dc0f87f0cb', NULL, '2025-11-26 06:11:04', '2025-11-30 03:29:50', '2025-11-27 22:11:04', 0),
(60, 'TRX-1764212438-444', 3, '0.00', '25000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-27 03:00:38', '2025-11-27 03:00:38', '2025-11-28 19:00:38', 0),
(61, 'TRX-1764480369-130', 3, '0.00', '2500000.00', 'HEMAT100', 'paid', NULL, NULL, '2025-11-30 05:26:09', '2025-11-30 05:26:09', '2025-12-01 21:26:09', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id` bigint(20) NOT NULL,
  `transaction_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(15,2) NOT NULL,
  `visit_date` date NOT NULL,
  `is_redeemed` tinyint(1) DEFAULT 0,
  `redeemed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `transaction_details`
--

INSERT INTO `transaction_details` (`id`, `transaction_id`, `product_id`, `quantity`, `price_at_purchase`, `visit_date`, `is_redeemed`, `redeemed_at`) VALUES
(14, 14, 1, 1, '30000.00', '2025-11-22', 0, NULL),
(15, 15, 1, 1, '30000.00', '2025-11-22', 0, NULL),
(16, 16, 1, 1, '30000.00', '2025-11-22', 0, NULL),
(17, 17, 12, 1, '25000.00', '2025-11-22', 0, NULL),
(18, 18, 10, 1, '25000.00', '2025-11-22', 0, NULL),
(19, 19, 2, 1, '25000.00', '2025-11-22', 0, NULL),
(20, 20, 6, 1, '10000.00', '2025-11-22', 0, NULL),
(21, 21, 6, 1, '10000.00', '2025-11-22', 0, NULL),
(22, 22, 6, 1, '10000.00', '2025-11-22', 0, NULL),
(23, 23, 6, 1, '10000.00', '2025-11-22', 0, NULL),
(35, 35, 9, 1, '10000.00', '2025-11-22', 0, NULL),
(36, 36, 8, 1, '25000.00', '2025-11-25', 0, NULL),
(37, 37, 1, 1, '30000.00', '2025-11-23', 0, NULL),
(38, 38, 2, 1, '25000.00', '2025-11-25', 1, '2025-11-23 05:37:55'),
(39, 39, 7, 1, '40000.00', '2025-11-27', 1, '2025-11-23 11:46:49'),
(40, 40, 1, 1, '30000.00', '2025-11-26', 1, '2025-11-24 01:12:22'),
(41, 41, 1, 1, '30000.00', '2025-11-24', 0, NULL),
(42, 42, 7, 1, '40000.00', '2025-11-28', 0, NULL),
(43, 43, 11, 1, '40000.00', '2025-11-24', 1, '2025-11-24 02:09:47'),
(44, 44, 12, 1, '25000.00', '2025-11-28', 0, NULL),
(45, 45, 12, 1, '25000.00', '2025-11-29', 1, '2025-11-24 02:42:14'),
(46, 46, 9, 1, '10000.00', '2025-11-24', 0, NULL),
(47, 47, 9, 1, '10000.00', '2025-11-24', 0, NULL),
(48, 48, 11, 1, '40000.00', '2025-11-28', 1, '2025-11-24 06:17:25'),
(49, 49, 8, 1, '25000.00', '2025-11-27', 1, '2025-11-24 09:26:26'),
(50, 50, 7, 1, '40000.00', '2025-11-24', 1, '2025-11-24 12:43:54'),
(51, 51, 1, 2, '30000.00', '2025-11-30', 0, NULL),
(52, 52, 1, 1, '30000.00', '2025-11-25', 0, NULL),
(53, 53, 1, 1, '30000.00', '2025-11-25', 1, '2025-11-25 07:17:29'),
(54, 54, 1, 1, '30000.00', '2025-11-25', 1, '2025-11-25 14:09:22'),
(55, 55, 232, 1, '20000.00', '2025-11-27', 0, NULL),
(56, 56, 232, 1, '20000.00', '2025-11-25', 1, '2025-11-25 15:34:43'),
(57, 57, 6, 1, '10000.00', '2025-11-26', 1, '2025-11-26 02:53:03'),
(58, 58, 1, 1, '30000.00', '2025-11-26', 0, NULL),
(59, 59, 1, 1, '30000.00', '2025-11-27', 0, NULL),
(60, 60, 5, 1, '25000.00', '2025-11-27', 0, NULL),
(61, 61, 10, 100, '25000.00', '2025-11-30', 1, '2025-11-30 06:17:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `max_usage` int(11) DEFAULT 100,
  `used_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `discount_percent`, `max_usage`, `used_count`, `created_at`) VALUES
(1, 'HEMAT100', 100, 20, 16, '2025-11-20 04:05:07'),
(2, 'HEMAT99', 99, 10, 4, '2025-11-22 14:39:01');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `token` (`token`),
  ADD KEY `fk_resets_email` (`email`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `fk_transactions_customer` (`customer_id`),
  ADD KEY `fk_transactions_voucher` (`voucher_code`);

--
-- Indeks untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_details_header` (`transaction_id`),
  ADD KEY `fk_details_product` (`product_id`);

--
-- Indeks untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_resets_email` FOREIGN KEY (`email`) REFERENCES `customers` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transactions_voucher` FOREIGN KEY (`voucher_code`) REFERENCES `vouchers` (`code`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `fk_details_header` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
