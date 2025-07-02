<?php
session_start();
include('includes/db.php');

$error = '';
$success = '';

// Token kontrolü
if(isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch();

    if(!$reset_request) {
        $error = "Geçersiz veya süresi dolmuş link!";
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if($password !== $password_confirm) {
        $error = "Şifreler eşleşmiyor!";
    } elseif(strlen($password) < 8) {
        $error = "Şifre en az 8 karakter olmalı!";
    } else {
        // Şifreyi güncelle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $reset_request['email']]);
        
        // Token'i sil
        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
        
        $success = "Şifreniz başarıyla güncellendi!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Şifre Yenile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-50 to-indigo-50 min-h-screen flex items-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Yeni Şifre Belirle</h2>
            
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

            <?php if(!$error || $success): ?>
            <form method="POST">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre</label>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                        <input 
                            type="password" 
                            name="password_confirm" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                    >
                        Şifreyi Güncelle
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>