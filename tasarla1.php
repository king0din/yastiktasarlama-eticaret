<?php
session_start();
include('includes/db.php');
include('includes/auth-check.php');


// Ürün ve kullanıcı bilgilerini al
$product_id = isset($_GET['product']) ? intval($_GET['product']) : 1;
$user_id = $_SESSION['user_id'];

// Ürün bilgilerini çek
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} catch(PDOException $e) {
    die("Ürün bilgileri yüklenemedi: " . $e->getMessage());
}

// Form gönderildiğinde
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = htmlspecialchars($_POST['address']);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    
    try {
        // Tasarım verisini al
        $design_data = isset($_POST['design_data']) ? $_POST['design_data'] : '';
        
        // Siparişi veritabanına ekle
        $stmt = $pdo->prepare("INSERT INTO orders 
            (user_id, product_id, design_data, address, phone, status) 
            VALUES (?, ?, ?, ?, ?, 'preparing')");
            
        $stmt->execute([
            $user_id,
            $product_id,
            $design_data,
            $address,
            $phone
        ]);
        
        // Başarılıysa siparişler sayfasına yönlendir
        header("Location: siparislerim.php");
        exit();
        
    } catch(PDOException $e) {
        die("Sipariş kaydedilemedi: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Özel Yastık Tasarla</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.0/fabric.min.js"></script>
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
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Tasarım Alanı -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Tasarımını Yap</h2>
            <div class="flex gap-4 mb-4">
                <input type="file" id="imageUpload" accept="image/*" class="hidden">
                <button onclick="document.getElementById('imageUpload').click()" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                    Resim Yükle
                </button>
                <button onclick="addText()" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                    Metin Ekle
                </button>
            </div>
            <canvas id="designCanvas" width="800" height="500" class="border-2 border-dashed border-gray-300"></canvas>
        </div>

        <!-- Sipariş Formu -->
        <form method="POST" class="bg-white rounded-xl shadow-lg p-6" onsubmit="prepareDesignData()">
            <input type="hidden" name="design_data" id="designData">
            
            <h2 class="text-2xl font-bold mb-4">Sipariş Bilgileri</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-medium">Adres Bilgileri</label>
                    <textarea name="address" required 
                        class="w-full p-3 border rounded-lg" rows="4"
                        placeholder="Tam adresinizi yazınız"></textarea>
                </div>
                
                <div>
                    <div class="mb-4">
                        <label class="block mb-2 font-medium">Telefon Numarası</label>
                        <input type="tel" name="phone" required 
                            pattern="[0-9]{10}" title="5XXXXXXXXX"
                            class="w-full p-3 border rounded-lg"
                            placeholder="5XXXXXXXXX">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block mb-2 font-medium">Ürün Boyutu</label>
                        <select name="size" class="w-full p-3 border rounded-lg">
                            <?php foreach(json_decode($product['sizes']) as $size): ?>
                                <option value="<?= $size ?>"><?= $size ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" 
                class="mt-6 bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700">
                Siparişi Tamamla (<?= $product['base_price'] ?> TL)
            </button>
        </form>
    </div>

    <script>
        // Canvas Setup
        const canvas = new fabric.Canvas('designCanvas', {
            backgroundColor: '#ffffff'
        });

        // Resim Yükleme
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                fabric.Image.fromURL(event.target.result, function(img) {
                    img.scaleToWidth(400);
                    canvas.add(img);
                    canvas.setActiveObject(img);
                });
            };
            reader.readAsDataURL(file);
        });

        // Metin Ekleme
        function addText() {
            const text = new fabric.Textbox('Metninizi yazın', {
                left: 100,
                top: 100,
                fontSize: 30,
                fill: '#000000',
                fontFamily: 'Arial'
            });
            canvas.add(text);
            canvas.setActiveObject(text);
        }

        // Tasarım Verisini Hazırlama
        function prepareDesignData() {
            const designData = JSON.stringify(canvas.toJSON());
            document.getElementById('designData').value = designData;
        }
    </script>
</body>
</html>