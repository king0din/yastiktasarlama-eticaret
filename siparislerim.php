<?php

session_start();

// Dosya yollarını düzelt
$base_dir = __DIR__;
require_once $base_dir . '/includes/db.php';
require_once $base_dir . '/includes/auth-check.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit();
}

try {
    // Veritabanı bağlantısını kontrol et
    if(!isset($pdo)) {
        throw new Exception("Veritabanı bağlantısı kurulamadı");
    }

    // Siparişleri çek
    $stmt = $pdo->prepare("SELECT 
        id,
        created_at,
        status,
        tracking_code 
        FROM orders 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC");
    
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
} catch(Exception $e) {
    die("Genel hata: " . $e->getMessage());
}

// Durum renklendirme fonksiyonu
function getStatusBadge($status) {
    $statusClasses = [
        'preparing' => 'bg-yellow-100 text-yellow-800',
        'printing' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-green-100 text-green-800',
        'delivered' => 'bg-gray-100 text-gray-800'
    ];
    
    $statusTexts = [
        'preparing' => 'Hazırlanıyor',
        'printing' => 'Baskıda',
        'shipped' => 'Kargoda',
        'delivered' => 'Teslim Edildi'
    ];

    $class = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
    $text = $statusTexts[$status] ?? 'Bilinmeyen Durum';
    
    return '<span class="px-3 py-1 rounded-full text-sm '.$class.'">'.$text.'</span>';
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
    <style>
        .gradient-text {
            background: linear-gradient(45deg, #6366f1, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="text-2xl font-bold gradient-text">PillowCraft</a>
            
            <div class="flex items-center gap-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-4">
                        <a href="kullanici/profil.php" class="flex items-center gap-2 text-gray-600 hover:text-purple-600">
                            <i class="fas fa-user-circle"></i>
                            <span>Profilim</span>
                        </a>
                        <a href="siparislerim.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">
                            Siparişlerim
                        </a>
                        <a href="includes/logout.php" class="text-red-600 hover:text-red-700">
                            Çıkış Yap
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-4">
                        <a href="giris.php" class="text-gray-600 hover:text-purple-600">Giriş Yap</a>
                        <a href="kayit.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                            Kayıt Ol
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6">
            <h1 class="text-3xl font-bold mb-6 text-purple-600">Siparişlerim</h1>
            
            <?php if(empty($orders)): ?>
                <div class="text-center py-12">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p class="text-gray-600 mb-4">Henüz siparişiniz bulunmamaktadır.</p>
                    <a href="index.php#populer-tasarimlar" class="text-purple-600 hover:text-purple-700 font-medium">
                        Yeni Sipariş Oluştur <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($orders as $order): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="font-semibold">Sipariş #<?= $order['id'] ?></span>
                                        <?= getStatusBadge($order['status']) ?>
                                    </div>
                                    <p class="text-gray-600 text-sm">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                                    </p>
                                    <?php if(!empty($order['tracking_code'])): ?>
                                        <p class="text-gray-600 text-sm mt-2">
                                            <i class="fas fa-truck mr-2"></i>
                                            Kargo Takip No: 
                                            <a href="#" class="text-purple-600 hover:underline">
                                                <?= $order['tracking_code'] ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <a href="siparis-detay.php?id=<?= $order['id'] ?>" 
                                   class="text-purple-600 hover:text-purple-700 whitespace-nowrap">
                                    Detayları Gör <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>