<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Ensure UTF-8 encoding for the session
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['notifications'])) {
    $notifications = json_decode($_POST['notifications'], true);
    
    if (!empty($notifications)) {
        // Check if email is set in session
        if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
            echo 'Lỗi: Không tìm thấy địa chỉ email người nhận';
            exit;
        }

        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'monkeystore.hotro.4335@gmail.com';
            $mail->Password = 'ofkv yzxx ovkt jgqw';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Set UTF-8 encoding
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Recipients
            $mail->setFrom('monkeystore.hotro.4335@gmail.com', 'Hệ thống giám sát cảm biến');
            $mail->addAddress($_SESSION['email']);

            // Secure headers
            $mail->addCustomHeader('X-Content-Type-Options: nosniff');
            $mail->addCustomHeader('X-Frame-Options: DENY');
            $mail->addCustomHeader('X-XSS-Protection: 1; mode=block');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Cảnh báo từ hệ thống cảm biến';

            // HTML email template
            $mail->Body = '
            <!DOCTYPE html>
            <html lang="vi">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Cảnh báo cảm biến</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
                    .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    .header { background: #007bff; color: #ffffff; padding: 20px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px; }
                    .header h1 { margin: 0; font-size: 24px; }
                    .content { padding: 20px; }
                    .notification-list { list-style: none; padding: 0; }
                    .notification-list li { padding: 10px; border-bottom: 1px solid #eee; font-size: 16px; }
                    .notification-list li:last-child { border-bottom: none; }
                    .notification-list li::before { content: "🔔 "; }
                    .footer { background: #343a40; color: #ffffff; padding: 10px; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; font-size: 14px; }
                    .footer a { color: #ffffff; text-decoration: none; }
                    @media (max-width: 600px) {
                        .container { width: 100%; margin: 10px; }
                        .header h1 { font-size: 20px; }
                        .notification-list li { font-size: 14px; }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Thông báo từ cảm biến</h1>
                    </div>
                    <div class="content">
                        <p>Xin chào,</p>
                        <p>Dưới đây là các cảnh báo mới từ hệ thống giám sát cảm biến của bạn:</p>
                        <ul class="notification-list">';
            foreach ($notifications as $notification) {
                $mail->Body .= '<li>' . htmlspecialchars($notification, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $mail->Body .= '
                        </ul>
                        <p>Vui lòng kiểm tra hệ thống để xử lý các cảnh báo này.</p>
                    </div>
                    <div class="footer">
                        <p>Hệ thống giám sát cảm biến | <a href="mailto:monkeystore.hotro.4335@gmail.com">Liên hệ hỗ trợ</a></p>
                        <p>&copy; ' . date('Y') . ' Monkey Store. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->AltBody = "Thông báo từ cảm biến:\n\n";
            foreach ($notifications as $notification) {
                $mail->AltBody .= "- " . strip_tags($notification) . "\n";
            }
            $mail->AltBody .= "\nVui lòng kiểm tra hệ thống để xử lý các cảnh báo này.\n\nHệ thống giám sát cảm biến\nLiên hệ: monkeystore.hotro.4335@gmail.com";

            $mail->send();
            echo 'Email đã được gửi thành công';
        } catch (Exception $e) {
            echo "Lỗi khi gửi email: {$mail->ErrorInfo}";
        }
    } else {
        echo 'Không có thông báo để gửi';
    }
} else {
    echo 'Yêu cầu không hợp lệ';
}
?>