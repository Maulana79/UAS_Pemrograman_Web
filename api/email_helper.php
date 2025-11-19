<?php
// api/email_helper.php

function sendEmailTicket($toEmail, $toName, $orderId, $productName, $quantity, $visitDate) {
    $apiKey = get_env('BREVO_API_KEY');
    $url = 'https://api.brevo.com/v3/smtp/email';

    // Template Email HTML Sederhana
    $htmlContent = "
        <h1>Pembayaran Berhasil!</h1>
        <p>Halo <b>$toName</b>,</p>
        <p>Terima kasih telah memesan tiket wisata. Berikut detail tiket Anda:</p>
        <table border='1' cellpadding='10' cellspacing='0'>
            <tr><td>Order ID</td><td><strong>$orderId</strong></td></tr>
            <tr><td>Wisata</td><td>$productName</td></tr>
            <tr><td>Jumlah</td><td>$quantity Tiket</td></tr>
            <tr><td>Tanggal</td><td>$visitDate</td></tr>
        </table>
        <p>Tunjukkan Order ID ini di loket masuk.</p>
        <br>
        <small>Sistem Tiket Wisata</small>
    ";

    $data = [
        "sender" => ["name" => "Tiket Wisata", "email" => "no-reply@tiketwisata.com"],
        "to" => [
            ["email" => $toEmail, "name" => $toName]
        ],
        "subject" => "E-Ticket Anda: $orderId",
        "htmlContent" => $htmlContent
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    
    // Kita tidak return error ke user agar tidak mengganggu proses notifikasi
    // Cukup log saja jika mau
    return $response;
}

function sendResetEmail($toEmail, $toName, $token) {
    $apiKey = get_env('BREVO_API_KEY');
    if (!$apiKey) return false;

    $url = 'https://api.brevo.com/v3/smtp/email';

    // URL Frontend untuk reset (Ganti dengan URL asli kamu nanti)
    // Karena belum ada frontend, kita asumsi user copy token manual atau klik link dummy
    $resetLink = "http://localhost:8000/reset_password.html?token=" . $token;

    $htmlContent = "
        <h3>Permintaan Reset Password 🔒</h3>
        <p>Halo <b>$toName</b>,</p>
        <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
        <p>Silakan klik link di bawah ini (berlaku 1 jam):</p>
        <p>
            <a href='$resetLink' style='background:#3498db; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Reset Password Saya</a>
        </p>
        <p>Atau gunakan Token ini secara manual: <b>$token</b></p>
        <br>
        <small>Jika Anda tidak meminta ini, abaikan email ini.</small>
    ";

    $data = [
        "sender" => ["name" => "Tiket Wisata Security", "email" => "no-reply@tiketwisata.com"],
        "to" => [["email" => $toEmail, "name" => $toName]],
        "subject" => "Reset Password Akun Tiket Wisata",
        "htmlContent" => $htmlContent
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'api-key: ' . $apiKey,
        'content-type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}