<?php
session_start();

// Dosya yollarını ayarla
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth-check.php';

// Kullanıcı ve sipariş ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: siparislerim.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Veritabanı bağlantısını kontrol et
    if(!isset($pdo)) {
        throw new Exception("Veritabanı bağlantısı kurulamadı");
    }

    // Sipariş detaylarını çek
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.created_at,
            o.status,
            o.tracking_code,
            o.design_data,
            o.address,
            o.phone,
            p.name AS product_name,
            p.base_price AS product_price
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.id = :order_id AND o.user_id = :user_id
    ");
    
    $stmt->execute([
        ':order_id' => $order_id,
        ':user_id' => $user_id
    ]);
    
    $order = $stmt->fetch();

    if(!$order) {
        throw new Exception("Sipariş bulunamadı veya erişim izniniz yok");
    }

    // Tasarım verisini decode et
    $design_data = json_decode($order['design_data'], true);

} catch(PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
} catch(Exception $e) {
    die($e->getMessage());
}

// Durum renklendirme fonksiyonu
function statusColor($status) {
    $statusColors = [
        'preparing' => 'bg-yellow-100 text-yellow-800',
        'printing' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-green-100 text-green-800',
        'delivered' => 'bg-gray-100 text-gray-800'
    ];
    return $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
}

// Durum metni fonksiyonu
function statusText($status) {
    $statusTexts = [
        'preparing' => 'Hazırlanıyor',
        'printing' => 'Baskıda',
        'shipped' => 'Kargoda',
        'delivered' => 'Teslim Edildi'
    ];
    return $statusTexts[$status] ?? 'Bilinmeyen Durum';
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

    <div class="max-w-7xl mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-6">Sipariş Detayları</h2>

            <!-- Temel Bilgiler -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Genel Bilgiler</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-gray-600">Sipariş Tarihi:</dt>
                            <dd><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></dd>
                        </div>
                        <div>
                            <dt class="text-gray-600">Ürün:</dt>
                            <dd><?= htmlspecialchars($order['product_name']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-gray-600">Toplam Tutar:</dt>
                            <dd><?= number_format($order['product_price'], 2) ?> TL</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Teslimat Bilgileri</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-gray-600">Adres:</dt>
                            <dd class="whitespace-pre-wrap"><?= htmlspecialchars($order['address']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-gray-600">Telefon:</dt>
                            <dd><?= htmlspecialchars($order['phone']) ?></dd>
                        </div>
                        <?php if(!empty($order['tracking_code'])): ?>
                        <div>
                            <dt class="text-gray-600">Kargo Takip No:</dt>
                            <dd>
                                <a href="https://www.araskargo.com.tr/tr/?code=<?= htmlspecialchars($order['tracking_code']) ?>" 
                                   target="_blank"
                                   class="text-purple-600 hover:underline">
                                    <?= htmlspecialchars($order['tracking_code']) ?>
                                </a>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Tasarım Önizleme -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4">Tasarım Önizleme</h3>
                <div class="border-2 border-dashed border-gray-200 rounded-lg p-4">
                    <canvas id="designPreview" width="800" height="500"></canvas>
                </div>
            </div>

            <!-- İşlem Butonları -->
            <div class="flex gap-4">
                <a href="siparislerim.php" 
                   class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200">
                    ← Geri Dön
                </a>
                <button onclick="window.print()" 
                        class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
                    Yazdır
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.0/fabric.min.js"></script>
    <script>
        // Tasarımı yükle
        const canvas = new fabric.Canvas('designPreview');
        <?php if(!empty($design_data)): ?>
            canvas.loadFromJSON(<?= json_encode($design_data) ?>, () => {
                canvas.renderAll();
                // Dilerseniz zoom veya konum ayarı yapabilirsiniz:
                canvas.setZoom(0.8);
            });
        <?php endif; ?>
    </script>
</body>
</html>
