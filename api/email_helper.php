<?php
// api/email_helper.php

// ==========================================
// KONFIGURASI PENGIRIM
// ==========================================
define('BREVO_API_KEY_CONST', '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'); // <--- PASTE KEY KAMU
define('SENDER_EMAIL_CONST', '2411102441043@umkt.ac.id'); // <--- EMAIL KAMU
define('SENDER_NAME_CONST', 'Wisata Borneo Admin');
// ==========================================

function sendEmailTicket($toEmail, $toName, $orderId, $productName, $quantity, $visitDate) {
    
    // 1. GENERATE URL QR CODE (Otomatis)
    // Kita pakai layanan gratis goqr.me atau qrserver
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $orderId;

    // 2. TEMPLATE EMAIL (Sekarang ada gambarnya)
    $htmlContent = "
    <div style='font-family: Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
            
            <div style='background-color: #8ac926; padding: 25px; text-align: center; color: #fff;'>
                <h1 style='margin: 0; font-size: 24px; letter-spacing: 1px;'>E-TICKET WISATA</h1>
                <p style='margin: 5px 0 0; opacity: 0.9;'>Wisata Borneo Timur</p>
            </div>

            <div style='padding: 30px;'>
                <p>Halo <strong>$toName</strong>,</p>
                <p>Pembayaran lunas! Berikut adalah tiket masuk Anda:</p>
                
                <div style='background-color: #f8f9fa; border: 2px dashed #8ac926; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center;'>
                    
                    <div style='margin-bottom: 15px;'>
                        <img src='$qrCodeUrl' alt='QR Code' style='width: 150px; height: 150px; border: 5px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                    </div>
                    
                    <div style='font-size: 18px; font-weight: bold; color: #333; letter-spacing: 2px; margin-bottom: 15px;'>
                        $orderId
                    </div>

                    <table style='width: 100%; text-align: left; font-size: 14px; color: #555;'>
                        <tr>
                            <td style='padding: 5px 0; border-bottom: 1px solid #eee;'>Destinasi</td>
                            <td style='padding: 5px 0; border-bottom: 1px solid #eee; font-weight: bold; text-align: right;'>$productName</td>
                        </tr>
                        <tr>
                            <td style='padding: 5px 0; border-bottom: 1px solid #eee;'>Tanggal</td>
                            <td style='padding: 5px 0; border-bottom: 1px solid #eee; font-weight: bold; text-align: right;'>$visitDate</td>
                        </tr>
                        <tr>
                            <td style='padding: 5px 0;'>Jumlah</td>
                            <td style='padding: 5px 0; font-weight: bold; text-align: right;'>$quantity Orang</td>
                        </tr>
                    </table>
                </div>

                <p style='font-size: 13px; color: #777; text-align: center;'>
                    Tunjukkan <strong>QR Code</strong> di atas kepada petugas loket untuk dipindai (Scan).
                </p>
                
                <div style='text-align: center; margin-top: 25px;'>
                    <a href='http://turipuloka.42web.io/pages/orders.html' style='display: inline-block; padding: 12px 30px; background-color: #1e2a38; color: #fff; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 14px;'>Lihat Tiket di Website</a>
                </div>
            </div>

            <div style='background-color: #eee; color: #888; padding: 15px; text-align: center; font-size: 11px;'>
                &copy; " . date('Y') . " Wisata Borneo Timur.<br>
                Simpan email ini sebagai bukti pembayaran yang sah.
            </div>
        </div>
    </div>
    ";
    
    return sendBrevoEmail($toEmail, $toName, "✅ E-Ticket: $productName ($orderId)", $htmlContent);
}

function sendResetEmail($toEmail, $toName, $token) {
    // Link Reset
    $resetLink = "http://turipuloka.42web.io/reset_password.html?token=" . $token;

    $htmlContent = "
    <div style='font-family: Arial, sans-serif; padding: 40px 20px; background-color: #f4f4f4;'>
        <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
            <h2 style='color: #333;'>Reset Password 🔒</h2>
            <p style='color: #555;'>Halo <strong>$toName</strong>,</p>
            <p style='color: #555; margin-bottom: 30px;'>Klik tombol di bawah ini untuk membuat password baru:</p>
            
            <a href='$resetLink' style='background-color: #007bff; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Reset Password Saya</a>
            
            <p style='margin-top: 30px; font-size: 13px; color: #999;'>Atau salin link ini:<br><a href='$resetLink' style='color: #007bff;'>$resetLink</a></p>
        </div>
    </div>
    ";

    return sendBrevoEmail($toEmail, $toName, "🔒 Reset Password Wisata", $htmlContent);
}

// --- FUNGSI PENGIRIM UTAMA ---
function sendBrevoEmail($toEmail, $toName, $subject, $htmlContent) {
    $apiKey = BREVO_API_KEY_CONST;
    $url = 'https://api.brevo.com/v3/smtp/email';

    $data = [
        "sender" => ["name" => SENDER_NAME_CONST, "email" => SENDER_EMAIL_CONST],
        "to" => [["email" => $toEmail, "name" => $toName]],
        "subject" => $subject,
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