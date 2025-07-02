<?php
session_start();
include('includes/db.php');

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Kullanıcıyı bul
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user) {
        // Benzersiz token oluştur
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Token'i veritabanına kaydet
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires_at]);
        
        // E-posta gönder (Örnek, gerçekte PHPMailer kullanın)
        $reset_link = "https://özelyastıktasarlama.shop/sifre-yenile.php?token=$token";
        $subject = "Şifre Sıfırlama Talebi";
        $message = "Şifrenizi sıfırlamak için linke tıklayın: $reset_link";
        mail($email, $subject, $message);
        
        $success = "Şifre sıfırlama linki e-posta adresinize gönderildi!";
    } else {
        $error = "Bu e-posta ile kayıtlı kullanıcı bulunamadı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Şifremi Unuttum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-orange-50 to-pink-50 min-h-screen flex items-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Şifremi Unuttum</h2>
            
            <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $success ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                        <input 
                            type="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-orange-500 to-pink-500 text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                    >
                        Link Gönder
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="giris.php" class="text-orange-600 hover:underline">Giriş Yap</a>
            </div>
        </div>
    </div>
</body>
</html>