<?php
session_start();
include('includes/db.php');

// Veritabanından ürünleri çek
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products LIMIT 3");
    $products = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Ürünler yüklenirken hata oluştu";
}
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PillowCraft - Özel Baskılı Yastıklar</title>
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
        .parallax-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .card-hover {
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .sticky-nav {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
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
            <a href="/" class="text-2xl font-bold gradient-text">PillowCraft</a>
            <div class="flex items-center gap-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-4">
                        <a href="kullanici/profil.php" class="flex items-center gap-2 text-gray-600 hover:text-purple-600 hover-underline">
                            <i class="fas fa-user-circle"></i>
                            <span>Profilim</span>
                        </a>
                        <a href="siparislerim.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                            Siparişlerim
                        </a>
                        <a href="includes/logout.php" class="text-red-600 hover:text-red-700 hover-underline">
                            Çıkış Yap
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-6">
                        <a href="giris.php" class="text-gray-600 hover:text-purple-600 hover-underline">Giriş Yap</a>
                        <a href="kayit.php" class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-3 rounded-xl hover:scale-105 transition-transform shadow-lg">
                            Kayıt Ol
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center overflow-hidden">
        <div class="absolute inset-0 parallax-bg bg-[url('assets/hero-bg.jpg')] opacity-30"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl text-center mx-auto" data-aos="zoom-in">
                <h1 class="text-5xl md:text-8xl font-bold mb-8 gradient-text leading-tight">
                    Hayalindeki Yastık<br>Şimdi Çok Yakın!
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 mb-12">
                    %100 Pamuk Kumaş | Suya Dayanıklı Baskı | 48 Saatte Teslimat
                </p>
                <a href="#populer-tasarimlar" class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 text-white px-12 py-5 rounded-full text-xl hover:scale-105 transition-transform shadow-xl relative overflow-hidden group">
                    <span class="relative z-10">Hemen Tasarla <i class="ml-3 fas fa-arrow-right"></i></span>
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                </a>
            </div>
        </div>
    </section>

    <!-- Özellikler -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-12">
                <div class="bg-white p-8 rounded-3xl card-hover" data-aos="fade-up">
                    <div class="w-20 h-20 bg-purple-100 rounded-2xl mb-6 flex items-center justify-center hover:rotate-[360deg] transition-transform">
                        <i class="fas fa-paint-brush text-4xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Sınırsız Tasarım</h3>
                    <p class="text-gray-600 leading-relaxed">AI destekli tasarım aracımızla mükemmel yastığını oluştur</p>
                </div>
                <div class="bg-white p-8 rounded-3xl card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-20 h-20 bg-green-100 rounded-2xl mb-6 flex items-center justify-center hover:rotate-[360deg] transition-transform">
                        <i class="fas fa-shipping-fast text-4xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Şimşek Hızında Teslimat</h3>
                    <p class="text-gray-600 leading-relaxed">Siparişini 48 saatte kapına getirme garantisi</p>
                </div>
                <div class="bg-white p-8 rounded-3xl card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-20 h-20 bg-blue-100 rounded-2xl mb-6 flex items-center justify-center hover:rotate-[360deg] transition-transform">
                        <i class="fas fa-award text-4xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Premium Kalite</h3>
                    <p class="text-gray-600 leading-relaxed">Ekolojik baskı teknolojisi ve hypoallergenic kumaş</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popüler Ürünler -->
    <section id="populer-tasarimlar" class="py-24">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4 gradient-text" data-aos="fade-up">En Çok Beğenilen Tasarımlar</h2>
            <!-- Yeni "Bütün Tasarımlar Arasında Seç" Butonu -->
            <div class="text-center mb-12">
                <a href="/tumtasarımlar.php" class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 text-white px-8 py-3 rounded-full hover:scale-105 transition-transform">
                    Bütün Tasarımlar Arasında Seç
                </a>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($products as $product): ?>
                    <div class="bg-white rounded-3xl overflow-hidden card-hover" data-aos="flip-up">
                        <div class="relative overflow-hidden group">
                            <?php $imageSrc = "data:image/jpeg;base64," . $product['image']; ?>
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
            </div>
        </div>
    </section>

    <!-- Nasıl Çalışır? -->
    <section class="py-24 bg-gradient-to-b from-white to-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 gradient-text" data-aos="fade-up">4 Adımda Sipariş</h2>
            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center" data-aos="zoom-in">
                    <div class="w-24 h-24 bg-purple-100 rounded-2xl mb-6 mx-auto flex items-center justify-center hover:scale-110 transition-transform">
                        <i class="fas fa-mouse-pointer text-4xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">1. Tasarım Seç</h3>
                    <p class="text-gray-600">Hazır tasarımlardan seç veya kendin oluştur</p>
                </div>
                <div class="text-center" data-aos="zoom-in" data-aos-delay="100">
                    <div class="w-24 h-24 bg-green-100 rounded-2xl mb-6 mx-auto flex items-center justify-center hover:scale-110 transition-transform">
                        <i class="fas fa-palette text-4xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">2. Kişiselleştir</h3>
                    <p class="text-gray-600">Renk ve boyut seçeneklerini belirle</p>
                </div>
                <div class="text-center" data-aos="zoom-in" data-aos-delay="200">
                    <div class="w-24 h-24 bg-blue-100 rounded-2xl mb-6 mx-auto flex items-center justify-center hover:scale-110 transition-transform">
                        <i class="fas fa-credit-card text-4xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">3. Güvenli Ödeme</h3>
                    <p class="text-gray-600">256-bit SSL ile güvenli ödeme yap</p>
                </div>
                <div class="text-center" data-aos="zoom-in" data-aos-delay="300">
                    <div class="w-24 h-24 bg-pink-100 rounded-2xl mb-6 mx-auto flex items-center justify-center hover:scale-110 transition-transform">
                        <i class="fas fa-gift text-4xl text-pink-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">4. Kapında Teslim</h3>
                    <p class="text-gray-600">Hızlı kargo ile teslim al</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
        // AOS Animasyonları
        AOS.init({
            duration: 1000,
            once: true,
            easing: 'ease-in-out',
        });

        // GSAP Animasyonları
        gsap.registerPlugin(ScrollTrigger);
        
        // Parallax efekt
        gsap.utils.toArray('.parallax-bg').forEach(bg => {
            gsap.to(bg, {
                yPercent: 20,
                ease: "none",
                scrollTrigger: {
                    trigger: bg,
                    scrub: true
                }, 
            });
        });

        // Navbar scroll efekti
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 100) {
                nav.classList.add('shadow-lg', 'py-4');
            } else {
                nav.classList.remove('shadow-lg', 'py-4');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
