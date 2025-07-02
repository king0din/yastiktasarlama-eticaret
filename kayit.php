<?php
session_start();
include('includes/db.php');

// Kullanıcı zaten giriş yapmışsa yönlendir
if(isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validasyonlar
    if(empty($full_name) || empty($email) || empty($password)) {
        $error = "Lütfen tüm alanları doldurun!";
    } elseif($password !== $password_confirm) {
        $error = "Şifreler eşleşmiyor!";
    } elseif(strlen($password) < 8) {
        $error = "Şifre en az 8 karakter olmalı!";
    } else {
        // E-posta kontrolü
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            $error = "Bu e-posta zaten kullanımda!";
        } else {
            // Kullanıcıyı kaydet
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            
            if($stmt->execute([$full_name, $email, $hashed_password])) {
                $success = "Kayıt başarılı! Giriş yapabilirsiniz.";
            } else {
                $error = "Kayıt sırasında bir hata oluştu!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Kayıt Ol</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-50 to-blue-50 min-h-screen flex items-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Kayıt Ol</h2>
            
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad</label>
                        <input 
                            type="text" 
                            name="full_name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                        <input 
                            type="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                        <input 
                            type="password" 
                            name="password_confirm" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-green-500 text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                    >
                        Kayıt Ol
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="giris.php" class="text-blue-600 hover:underline">Zaten hesabın var mı? Giriş Yap</a>
            </div>
        </div>
    </div>
</body>
</html>