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
    if(!$product) {
        die("Ürün bulunamadı.");
    }
} catch(PDOException $e) {
    die("Ürün bilgileri yüklenemedi: " . $e->getMessage());
}

// Form gönderildiğinde
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = htmlspecialchars($_POST['address']);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    
    try {
        // Tasarım verisini al
        $design_data = isset($_POST['design_data']) ? $_POST['design_data'] : '';
        
        // Siparişi veritabanına ekle (sütun 'size' yerine 'description')
        $stmt = $pdo->prepare("INSERT INTO orders 
            (user_id, product_id, design_data, address, phone, status, description) 
            VALUES (?, ?, ?, ?, ?, 'preparing', ?)");
            
        $stmt->execute([
            $user_id,
            $product_id,
            $design_data,
            $address,
            $phone,
            $description
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
    <!-- Mobil cihazlarda responsive görünüm için viewport ayarı -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Özel Yastık Tasarla</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fabric.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.0/fabric.min.js"></script>
    <!-- GSAP (Opsiyonel) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <!-- Font Awesome -->
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
        /* Canvas’ın kapsayıcıya uyumlu olması için */
        #designCanvas {
            display: block;
            max-width: 100%;
        }
    </style>
</head>
<body class="antialiased bg-gray-100 relative">
    <!-- Yükleniyor Overlay'i (varsayılan gizli) -->
    <div id="loadingOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="text-white text-xl animate-pulse">Lütfen bekleyin, resim yükleniyor...</div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-40 mb-8">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="index.php" class="text-2xl font-bold gradient-text">PillowCraft</a>
            
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

    <div class="container mx-auto px-4 py-4">
        <!-- Ürün Başlık -->
        <h1 class="text-3xl font-bold mb-4 text-purple-600">
            <?= htmlspecialchars($product['name']) ?> Tasarla
        </h1>

        <!-- Responsive Flex: mobilde alt alta, md ve üzeri ekranlarda yan yana -->
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Tasarım Alanı -->
            <div class="bg-white rounded-xl shadow-lg p-6 flex-1">
                <h2 class="text-xl font-bold mb-4">Tasarımını Yap</h2>
                
                <!-- Canvas Kapsayıcı (responsive) -->
                <div id="canvas-container" class="border-2 border-dashed border-gray-300 mb-4">
                    <canvas id="designCanvas"></canvas>
                </div>
                
                <!-- Araçlar -->
                <div class="flex gap-4 mb-4 flex-wrap">
                    <!-- Resim yükleme inputu (Gizli) -->
                    <input type="file" id="faceImage" accept="image/*" class="hidden" />
                    
                    <button onclick="document.getElementById('faceImage').click()" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Yüz Resmi Yükle
                    </button>

                    <button onclick="addText()" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                        Metin Ekle
                    </button>

                    <button onclick="removeSelected()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        Seçileni Sil
                    </button>
                </div>
                
                <p class="text-gray-600 text-sm">
                    İsterseniz yüzünüzü (veya istediğiniz görseli) yükleyip ekleyin, metin ekleyin veya boyutlandırın.
                </p>
            </div>

            <!-- Sipariş Formu -->
            <form method="POST" class="bg-white rounded-xl shadow-lg p-6 flex-1 mt-8 md:mt-0" onsubmit="prepareDesignData()">
                <input type="hidden" name="design_data" id="designData">
                
                <h2 class="text-xl font-bold mb-4">Sipariş Bilgileri</h2>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium">Adres Bilgileri</label>
                    <textarea name="address" required 
                        class="w-full p-3 border rounded-lg" rows="4"
                        placeholder="Tam adresinizi yazınız"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium">Telefon Numarası</label>
                    <input type="tel" name="phone" required 
                        pattern="[0-9]{10}" title="5XXXXXXXXX"
                        class="w-full p-3 border rounded-lg"
                        placeholder="5XXXXXXXXX">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium">Açıklama veya Not Bırakın</label>
                    <textarea name="description" class="w-full p-3 border rounded-lg" rows="4" placeholder="Açıklama veya not ekleyin"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-purple-600">
                        Fiyat: <?= number_format($product['base_price'], 2) ?> TL
                    </span>
                    <button type="submit" 
                        class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700">
                        Siparişi Tamamla
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-gray-600 mt-8 py-4">
        <p>&copy; <?= date('Y') ?> PillowCraft. Tüm hakları saklıdır.</p>
    </footer>

<script>
// Fabric.js Canvas Oluşturma
const canvas = new fabric.Canvas('designCanvas', {
    backgroundColor: '#ffffff'
});

// Orijinal boyut oranı: 600x400 (3:2)
const ORIGINAL_WIDTH = 600;
const ORIGINAL_HEIGHT = 400;
const ASPECT_RATIO = ORIGINAL_HEIGHT / ORIGINAL_WIDTH;

// Canvas’ı kapsayıcıya göre yeniden boyutlandıran fonksiyon
function resizeCanvas() {
    const container = document.getElementById('canvas-container');
    const containerWidth = container.clientWidth;
    const newWidth = containerWidth;
    const newHeight = containerWidth * ASPECT_RATIO;
    canvas.setWidth(newWidth);
    canvas.setHeight(newHeight);
    
    // Arkaplan resmi varsa yeniden boyutlandır
    if(canvas.backgroundImage) {
        canvas.backgroundImage.scaleToWidth(newWidth);
        canvas.backgroundImage.scaleToHeight(newHeight);
    }
    canvas.renderAll();
}

// Sayfa yüklendiğinde ve pencere yeniden boyutlandırıldığında canvas’ı güncelle
window.addEventListener('load', () => {
    resizeCanvas();
    // Ürün Resmini Arkaplan Olarak Ayarla (Base64)
    const productImageBase64 = "data:image/jpeg;base64,<?= htmlspecialchars($product['image']) ?>";
    fabric.Image.fromURL(productImageBase64, function(img) {
        const canvasWidth = canvas.getWidth();
        const canvasHeight = canvas.getHeight();
        img.scaleToWidth(canvasWidth);
        img.scaleToHeight(canvasHeight);
        canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
            originX: 'left',
            originY: 'top'
        });
    });
});
window.addEventListener('resize', resizeCanvas);

// Yüz Resmi Yükleme İşlemi
const faceInput = document.getElementById('faceImage');
const loadingOverlay = document.getElementById('loadingOverlay');

faceInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Yükleme sırasında overlay'i göstererek dokunmaları engelle
    loadingOverlay.classList.remove('hidden');

    const formData = new FormData();
    formData.append('face_file', file);

    fetch('face-upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const faceUrl = data.faceUrl;
            fabric.Image.fromURL(faceUrl, function(img) {
                img.scaleToWidth(200);
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
                // Yükleme tamamlandığında overlay'i gizle
                loadingOverlay.classList.add('hidden');
            });
        } else {
            alert('Yüz işlenemedi: ' + data.message);
            loadingOverlay.classList.add('hidden');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Hata oluştu.');
        loadingOverlay.classList.add('hidden');
    });
});

// Metin Ekleme
function addText() {
    const textbox = new fabric.Textbox('Metninizi yazın', {
        left: 50,
        top: 50,
        fontSize: 24,
        fill: '#000000',
        fontFamily: 'Arial'
    });
    canvas.add(textbox);
    canvas.setActiveObject(textbox);
}

// Seçili Nesneyi Silme
function removeSelected() {
    const activeObject = canvas.getActiveObject();
    if(activeObject) {
        canvas.remove(activeObject);
    }
}

// Form gönderilirken design_data Hazırlama
function prepareDesignData() {
    const designData = JSON.stringify(canvas.toJSON());
    document.getElementById('designData').value = designData;
}
</script>

</body>
</html>
