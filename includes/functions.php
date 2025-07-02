function sendNotification($orderId, $message) {
    // Kullanıcı bilgilerini al
    $user = getUserByOrderId($orderId);
    
    // E-posta gönderimi
    $to = $user['email'];
    $subject = "Sipariş Durum Güncellemesi (#$orderId)";
    $headers = "From: bildirim@yastiksitesi.com";
    
    // HTML e-posta şablonu
    $body = "
    <html>
    <body>
        <h1>Siparişinizde Güncelleme!</h1>
        <p>$message</p>
        <a href='https://siteniz.com/siparislerim'>Detayları Görüntüle</a>
    </body>
    </html>
    ";
    
    mail($to, $subject, $body, $headers);
    
    // SMS entegrasyonu (Örnek: Twilio)
    sendSMS($user['phone'], $message);
}