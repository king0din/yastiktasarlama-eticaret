<?php
session_start();
include('includes/db.php');

// Kullanıcı zaten giriş yapmışsa yönlendir
if(isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Kullanıcıyı veritabanında ara
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Giriş başarılıysa
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }
} // <-- Dikkat: Eksik parantez burada eklenmeli
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-purple-50 min-h-screen flex items-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Giriş Yap</h2>
            
            <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-purple-500 to-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                    >
                        Giriş Yap
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="kayit.php" class="text-purple-600 hover:underline">Hesabın yok mu? Kayıt Ol</a>
                <span class="mx-2">•</span>
                <a href="sifremi-unuttum.php" class="text-gray-600 hover:underline">Şifremi Unuttum</a>
            </div>
        </div>
    </div>
</body>
</html>