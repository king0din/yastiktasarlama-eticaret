<?php
session_start();
include('includes/db.php');

try {
    // Tüm ürünleri getiriyoruz.
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ürünler yüklenirken hata oluştu.";
}
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PillowCraft - Bütün Tasarımlar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .card-hover {
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        .sticky-nav {
            backdrop-filter: blur(10px);
            background-color: rgba(255,255,255,0.9);
        }
        .hover-underline {
            position: relative;
            display: inline-block;
        }
        .hover-underline::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #6366f1;
            transition: width 0.3s ease;
        }
        .hover-underline:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navbar -->
    <nav class="sticky-nav fixed w-full top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Sol kısım: Marka ve Ana Sayfa -->
        <div class="flex items-center gap-4">
            <a href="/" class="text-2xl font-bold gradient-text">PillowCraft</a>
            <a href="index.php" class="flex items-center text-gray-600 hover:text-purple-600 hover:underline">
                <i class="fas fa-home mr-1"></i>
                <span>Ana Sayfa</span>
            </a>
        </div>
        <!-- Sağ kısım: Kullanıcı giriş/çıkış veya profil butonları -->
        <div class="flex items-center gap-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="flex items-center gap-4">
                    <a href="kullanici/profil.php" class="flex items-center gap-2 text-gray-600 hover:text-purple-600 hover:underline">
                        <i class="fas fa-user-circle"></i>
                        <span>Profilim</span>
                    </a>
                    <a href="siparislerim.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                        Siparişlerim
                    </a>
                    <a href="includes/logout.php" class="text-red-600 hover:text-red-700 hover:underline">
                        Çıkış Yap
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-6">
                    <a href="giris.php" class="text-gray-600 hover:text-purple-600 hover:underline">Giriş Yap</a>
                    <a href="kayit.php" class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-3 rounded-xl hover:scale-105 transition-transform shadow-lg">
                        Kayıt Ol
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>



    <!-- Ana Başlık -->
    <section class="pt-24 pb-12">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 gradient-text" data-aos="fade-up">
                Bütün Tasarımlar
            </h2>
            <?php if(isset($error)): ?>
                <p class="text-center text-red-500"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <!-- Ürünler Grid -->
            <div class="grid md:grid-cols-3 gap-8">
                <?php if(isset($products) && count($products) > 0): ?>
                    <?php foreach($products as $product): ?>
                        <div class="bg-white rounded-3xl overflow-hidden card-hover" data-aos="flip-up">
                            <div class="relative overflow-hidden group">
                                <?php 
                                    // Resmi base64 koduyla gösteriyoruz
                                    $imageSrc = "data:image/jpeg;base64," . $product['image']; 
                                ?>
                                <img src="<?= htmlspecialchars($imageSrc) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="w-full h-80 object-cover transform group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="p-8">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-2xl font-bold"><?= htmlspecialchars($product['name']) ?></h3>
                                    <span class="bg-purple-100 text-purple-600 px-3 py-1 rounded-full text-sm">%30 İndirim</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="text-2xl font-bold text-purple-600"><?= number_format($product['base_price'], 2) ?> TL</span>
                                        <span class="line-through text-gray-400 ml-2"><?= number_format($product['base_price'] * 1.3, 2) ?> TL</span>
                                    </div>
                                    <a href="tasarla.php?product=<?= $product['id'] ?>" 
                                       class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-3 rounded-xl hover:scale-105 transition-transform">
                                        Özelleştir
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-600">Henüz bir tasarım eklenmemiş.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer (index.php ile uyumlu) -->
    <footer class="bg-gray-900 text-white pt-24 pb-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <h4 class="text-2xl font-bold mb-6 gradient-text">PillowCraft</h4>
                    <p class="text-gray-400 leading-relaxed">Her yastık bir hikaye taşır. Senin hikayenle şekillenen özel tasarımlar.</p>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-6">Koleksiyonlar</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Sevgililer Özel</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Çocuk Odası</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Ofis Tasarımları</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-6">Destek</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Sık Sorulan Sorular</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">İade Politikası</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Gizlilik Politikası</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-6">İletişim</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2 text-gray-400">
                            <i class="fas fa-map-marker-alt"></i>
                            İstanbul, Türkiye
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <i class="fas fa-phone"></i>
                            0850 123 45 67
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <i class="fas fa-envelope"></i>
                            info@pillowcraft.com
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center">
                <div class="flex justify-center gap-6 mb-6">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-instagram text-2xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-facebook text-2xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fab fa-tiktok text-2xl"></i>
                    </a>
                </div>
                <p class="text-gray-400">&copy; 2024 PillowCraft. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // AOS Animasyonlarını başlatıyoruz
        AOS.init({
            duration: 1000,
            once: true,
            easing: 'ease-in-out'
        });

        // GSAP ve ScrollTrigger ile isteğe bağlı efektler ekleyebilirsiniz.
    </script>
</body>
</html>
