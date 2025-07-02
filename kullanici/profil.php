<?php
session_start();
include('../includes/db.php');
include('../includes/auth-check.php');

// Kullanıcı bilgilerini çek
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

// Profil Güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $user_id]);
        $success = "Profil bilgileriniz güncellendi!";
        header("Refresh:2");
    } catch(PDOException $e) {
        $error = "Bu e-posta zaten kullanımda!";
    }
}

// Şifre Güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed_password, $user_id]);
            $success = "Şifreniz başarıyla güncellendi!";
        } else {
            $error = "Yeni şifreler eşleşmiyor!";
        }
    } else {
        $error = "Mevcut şifreniz yanlış!";
    }
}

// Çıkış Yap
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../giris.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PillowCraft - Profil Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        @keyframes gradient-wave {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .gradient-text {
            background: linear-gradient(45deg, #6366f1, #ec4899, #f59e0b);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient-wave 5s ease infinite;
        }

        .input-field {
            @apply w-full p-4 border-2 border-gray-300 rounded-xl bg-white 
                   focus:border-purple-500 focus:ring-4 focus:ring-purple-100 
                   transition-all shadow-sm placeholder-gray-400;
        }

        .btn-primary {
            @apply bg-gradient-to-r from-purple-600 to-pink-600 text-white 
                   px-8 py-4 rounded-xl hover:scale-105 transition-transform 
                   font-semibold shadow-lg hover:shadow-xl;
        }

        .btn-secondary {
            @apply bg-gradient-to-r from-gray-600 to-gray-700 text-white 
                   px-8 py-4 rounded-xl hover:scale-105 transition-transform 
                   font-semibold shadow-lg hover:shadow-xl;
        }

        .input-icon {
            @apply absolute right-4 top-1/2 -translate-y-1/2 text-gray-400;
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <!-- Navbar -->
    <nav class="sticky top-0 bg-white shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="text-2xl font-bold gradient-text">PillowCraft</a>
            <div class="flex items-center gap-6">
                <a href="../" class="flex items-center gap-2 text-gray-600 hover:text-purple-600">
                    <i class="fas fa-home mr-1"></i>
                    <span class="hidden md:inline">Ana Sayfa</span>
                </a>
                <a href="siparislerim.php" class="bg-gray-100 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-box-open mr-2"></i>Siparişlerim
                </a>
                <a href="?logout" class="text-red-600 hover:text-red-700">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden md:inline">Çıkış Yap</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-12 min-h-screen">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Başlık -->
            <div class="text-center mb-12" data-aos="fade-up">
                <h1 class="text-4xl font-bold gradient-text mb-4">Profil Yönetimi</h1>
                <p class="text-gray-600">Hesap bilgilerinizi buradan güncelleyebilirsiniz</p>
            </div>

            <!-- Mesajlar -->
            <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-lg" data-aos="fade-up">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-red-700"><?= $error ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8 rounded-lg" data-aos="fade-up">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-green-700"><?= $success ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Profil Formu -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8" data-aos="fade-up">
                <form method="POST">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Ad Soyad -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ad Soyad</label>
                            <input type="text" name="full_name" 
                                   value="<?= htmlspecialchars($user['full_name']) ?>" 
                                   class="input-field pl-4 pr-12"
                                   placeholder="Adınız ve soyadınız">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                        
                        <!-- E-posta -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                            <input type="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" 
                                   class="input-field pl-4 pr-12"
                                   placeholder="ornek@email.com">
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                        
                        <!-- Telefon -->
                        <div class="relative md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                            <input type="tel" name="phone" 
                                   value="<?= htmlspecialchars($user['phone']) ?>" 
                                   class="input-field pl-4 pr-12"
                                   pattern="[0-9]{10}" 
                                   placeholder="05XX XXX XX XX">
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                    </div>

                    <!-- Güncelle Butonu -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit" name="update_profile" 
                                class="btn-primary">
                            <i class="fas fa-save mr-2"></i>Profili Güncelle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Şifre Formu -->
            <div class="bg-white rounded-2xl shadow-xl p-8" data-aos="fade-up" data-aos-delay="100">
                <form method="POST">
                    <h2 class="text-2xl font-bold gradient-text mb-6">Şifre Değişikliği</h2>
                    <div class="space-y-6">
                        <!-- Mevcut Şifre -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mevcut Şifre</label>
                            <input type="password" name="current_password" 
                                   class="input-field pl-4 pr-12"
                                   placeholder="••••••••">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                        
                        <!-- Yeni Şifre -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Yeni Şifre</label>
                            <input type="password" name="new_password" 
                                   class="input-field pl-4 pr-12"
                                   placeholder="En az 8 karakter"
                                   minlength="8">
                            <i class="fas fa-key input-icon"></i>
                        </div>
                        
                        <!-- Şifre Tekrar -->
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Şifre Tekrar</label>
                            <input type="password" name="confirm_password" 
                                   class="input-field pl-4 pr-12"
                                   placeholder="Şifrenizi tekrar girin"
                                   minlength="8">
                            <i class="fas fa-redo input-icon"></i>
                        </div>
                    </div>

                    <!-- Şifre Değiştir Butonu -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit" name="update_password" 
                                class="btn-secondary">
                            <i class="fas fa-lock mr-2"></i>Şifreyi Değiştir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-quad'
        });
    </script>
</body>
</html>