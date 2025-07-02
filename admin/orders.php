<?php
require_once '../includes/db.php';
require_once '../includes/admin-auth-check.php';

// Arama ve filtre parametreleri
$searchTerm   = isset($_GET['q'])      ? trim($_GET['q'])      : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// 1) AJAX isteği: Tek bir siparişin tüm detaylarını çek (orders + users + products), LEFT JOIN ile
if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['order_id'])) {
    $orderId = (int) $_GET['order_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT
                o.id               AS order_id,
                o.user_id,
                o.design_id,
                o.product_id,
                o.quantity,
                o.status,
                o.tracking_code,
                o.created_at,
                o.address,
                o.phone           AS order_phone,
                o.design_data,
                o.description     AS customer_note,
                
                u.full_name       AS user_name,
                u.email           AS user_email,
                u.phone           AS user_phone,
                
                p.name            AS product_name,
                p.base_price      AS product_base_price,
                p.description     AS product_description
            FROM orders o
            LEFT JOIN users u    ON o.user_id    = u.id
            LEFT JOIN products p ON o.product_id = p.id
            WHERE o.id = :orderId
            LIMIT 1
        ");
        $stmt->execute([':orderId' => $orderId]);
        $orderDetail = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderDetail) {
            echo json_encode([
                'success' => false,
                'message' => 'Sipariş bulunamadı.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data'    => $orderDetail
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veritabanı hatası: ' . $e->getMessage()
        ]);
        exit;
    }
}

// 2) Form ile siparişin durumunu / takip kodunu güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id      = $_POST['order_id'];
    $new_status    = $_POST['status'];
    $tracking_code = $_POST['tracking_code'] ?? '';

    try {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = :status,
                tracking_code = :tracking_code
            WHERE id = :order_id
        ");
        $stmt->execute([
            ':status'        => $new_status,
            ':tracking_code' => $tracking_code,
            ':order_id'      => $order_id
        ]);

        $_SESSION['success'] = "Sipariş #$order_id güncellendi!";
        header("Location: orders.php");
        exit();
    } catch (PDOException $e) {
        die("Güncelleme hatası: " . $e->getMessage());
    }
}

// 3) Listeleme: Arama + Durum filtresi
try {
    $sql = "
        SELECT 
            o.id AS order_id,
            o.created_at,
            o.status,
            o.tracking_code,
            o.design_data, 
            u.full_name AS user_name,
            u.email     AS user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE 1=1
    ";

    $params = [];

    if ($searchTerm !== '') {
        $sql .= "
            AND (
                o.id LIKE :stExact
                OR u.full_name LIKE :stLike
                OR u.email LIKE :stLike
                OR o.tracking_code LIKE :stLike
            )
        ";
        $params[':stExact'] = $searchTerm;
        $params[':stLike']  = '%' . $searchTerm . '%';
    }

    if ($statusFilter !== '') {
        $sql .= " AND o.status = :statusFilter ";
        $params[':statusFilter'] = $statusFilter;
    }

    $sql .= " ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Siparişler yüklenemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fabric.js (tasarım önizleme) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.0/fabric.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="flex flex-col md:flex-row">

    <!-- Admin Sidebar -->
    <?php include '../includes/admin-sidebar.php'; ?>

    <main class="flex-1 p-4 md:p-8">
        <!-- Geri Butonu -->
        <button 
            onclick="window.history.back()" 
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 mb-4">
            ← Geri
        </button>

        <!-- Başarı Mesajı -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold mb-4">Sipariş Yönetimi</h2>
        
        <!-- Arama ve Durum Filtre Formu -->
        <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-2 items-start sm:items-end">
            <div>
                <label for="q" class="block mb-1 font-medium text-sm text-gray-700">Arama</label>
                <input 
                    type="text" 
                    id="q" 
                    name="q"
                    class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring w-64"
                    placeholder="ID, müşteri adı, e-mail, takip kodu..."
                    value="<?= htmlspecialchars($searchTerm) ?>"
                >
            </div>

            <div>
                <label for="status" class="block mb-1 font-medium text-sm text-gray-700">Durum</label>
                <select 
                    id="status"
                    name="status"
                    class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring w-48"
                >
                    <option value="">Tümü</option>
                    <option value="preparing" <?= $statusFilter === 'preparing' ? 'selected' : '' ?>>Hazırlanıyor</option>
                    <option value="printing"  <?= $statusFilter === 'printing'  ? 'selected' : '' ?>>Baskıda</option>
                    <option value="shipped"   <?= $statusFilter === 'shipped'   ? 'selected' : '' ?>>Kargoda</option>
                    <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Teslim Edildi</option>
                </select>
            </div>

            <div class="pt-6 sm:pt-0">
                <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                    Filtrele
                </button>
            </div>
        </form>

        <!-- Sipariş Tablosu (Responsive) -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full min-w-max text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Sipariş ID</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Durum</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Takip Kodu</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">Detay</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-700">İşlemler</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <?php if(count($orders) === 0): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Aramanıza/filtrenize uygun sipariş bulunamadı.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">#<?= htmlspecialchars($order['order_id']) ?></td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">
                                    <?= htmlspecialchars($order['user_name'] ?? 'Kullanıcı Yok') ?>
                                </div>
                                <div class="text-gray-600">
                                    <?= htmlspecialchars($order['user_email'] ?? '') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" class="flex gap-2 items-center">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status" class="p-1 border rounded text-sm">
                                        <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Hazırlanıyor</option>
                                        <option value="printing" <?= $order['status'] === 'printing' ? 'selected' : '' ?>>Baskıda</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Kargoda</option>
                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Teslim Edildi</option>
                                    </select>
                            </td>
                            <td class="px-6 py-4">
                                <input type="text" name="tracking_code"
                                       value="<?= htmlspecialchars($order['tracking_code'] ?? '') ?>"
                                       class="p-1 border rounded text-sm w-24"
                                       placeholder="Takip Kodu">
                            </td>
                            <td class="px-6 py-4">
                                <button type="button"
                                        class="bg-purple-500 text-white px-3 py-1 rounded text-sm hover:bg-purple-600"
                                        onclick="showOrderDetail(<?= $order['order_id'] ?>)">
                                    Siparişi Göster
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                    <button type="submit"
                                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                        Güncelle
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- MODAL: Sipariş Detayları -->
<div id="orderDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div 
        class="bg-white rounded-lg shadow-lg p-4 w-full max-w-3xl relative overflow-y-auto max-h-[90vh]"
        style="scroll-behavior: smooth;"
    >
        <!-- Kapatma butonu (sağ üst) -->
        <button 
            class="absolute top-2 right-3 text-gray-600 hover:text-black text-2xl font-bold" 
            onclick="closeOrderDetailModal()"
        >
            &times;
        </button>

        <h2 class="text-xl font-bold mb-4 text-gray-800">Sipariş Detayları</h2>
        
        <div id="orderDetailContent" class="space-y-4 text-sm text-gray-700">
            <!-- AJAX ile doldurulacak içerik -->
        </div>
        
        <!-- Modal alt kısmı: Tasarım önizleme ve indirme butonu -->
        <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2">
            <button 
                onclick="downloadDesign()"
                id="downloadBtn"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 hidden"
            >
                Tasarımı İndir
            </button>
            <button 
                onclick="closeOrderDetailModal()" 
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
            >
                Kapat
            </button>
        </div>
    </div>
</div>

<script>
    // Global: Tüm fabric görüntüleri için crossOrigin ayarı
    fabric.Image.prototype.crossOrigin = 'anonymous';

    // Global değişkenler: modalFabricCanvas global olarak tanımlandı
    let modalFabricCanvas = null;
    let currentOrderId = null;
    const orderDetailModal   = document.getElementById('orderDetailModal');
    const orderDetailContent = document.getElementById('orderDetailContent');
    const downloadBtn        = document.getElementById('downloadBtn');

    // "Siparişi Göster" butonu -> AJAX isteği -> Detay modal
    function showOrderDetail(orderId) {
        currentOrderId = orderId;
        
        orderDetailContent.innerHTML = '<div>Yükleniyor...</div>';

        // AJAX ile detay çek
        fetch(`orders.php?action=details&order_id=${orderId}`)
            .then(response => response.json())
            .then(resp => {
                if (!resp.success) {
                    orderDetailContent.innerHTML = `<div class="text-red-500 font-bold">Hata: ${resp.message}</div>`;
                    return;
                }
                const o = resp.data;
                // Toplam fiyatı adet * ürünün taban fiyatı şeklinde hesapla
                const totalPrice = (Number(o.quantity) * Number(o.product_base_price)).toFixed(2);

                // Modal içeriğini oluştur
                orderDetailContent.innerHTML = createOrderDetailHTML(o, totalPrice);
                
                // Eğer design_data varsa, Fabric.js canvas'ı oluşturup global modalFabricCanvas'e ata
                if(o.design_data) {
                    try {
                        const modalCanvasElem = document.getElementById('modalCanvas');
                        if(modalCanvasElem) {
                            modalFabricCanvas = new fabric.Canvas('modalCanvas');
                            const designJSON = JSON.parse(o.design_data);
                            modalFabricCanvas.loadFromJSON(designJSON, function() {
                                modalFabricCanvas.renderAll();
                                downloadBtn.classList.remove('hidden'); // İndir butonunu göster
                            }, function(o, object) {
                                if (object && object.type === 'image' && object.getSrc()) {
                                    let src = object.getSrc();
                                    if (!src.includes('proxy.php?url=')) {
                                        object.setSrc('proxy.php?url=' + encodeURIComponent(src), function() {
                                            modalFabricCanvas.renderAll();
                                        });
                                    }
                                    object.set({ crossOrigin: 'anonymous' });
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Tasarım datası parse hatası:', e);
                    }
                } else {
                    downloadBtn.classList.add('hidden');
                }
            })
            .catch(err => {
                console.error(err);
                orderDetailContent.innerHTML = '<div class="text-red-500 font-bold">Sunucu hatası.</div>';
            });
        
        orderDetailModal.classList.remove('hidden');
    }

    // Modal kapatma
    function closeOrderDetailModal() {
        orderDetailModal.classList.add('hidden');
    }

    // Detay içeriğini oluşturma (HTML)
    // totalPrice parametresi hesaplanmış değeri alır
    function createOrderDetailHTML(o, totalPrice) {
        return `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Sipariş Bilgileri -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Sipariş Bilgileri</h3>
                <ul class="space-y-1">
                    <li><strong>ID:</strong> #${o.order_id}</li>
                    <li><strong>Durum:</strong> ${o.status}</li>
                    <li><strong>Takip Kodu:</strong> ${o.tracking_code ?? ''}</li>
                    <li><strong>Oluşturma:</strong> ${o.created_at}</li>
                    <li><strong>Adres:</strong> ${o.address}</li>
                    <li><strong>Sipariş Telefon:</strong> ${o.order_phone}</li>
                    <li><strong>Adet:</strong> ${o.quantity}</li>
                    <li><strong>Toplam Fiyat:</strong> ${totalPrice} TL</li>
                    <li><strong>Müşteri Notu:</strong> ${o.customer_note ?? ''}</li>
                </ul>
            </div>
            <!-- Ürün Bilgileri -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Ürün Bilgileri</h3>
                <ul class="space-y-1">
                    <li><strong>Ürün ID:</strong> ${o.product_id}</li>
                    <li><strong>Ürün Adı:</strong> ${o.product_name ?? 'Ürün Yok'}</li>
                    <li><strong>Taban Fiyat:</strong> ${o.product_base_price} TL</li>
                    <li><strong>Ürün Açıklaması:</strong> ${o.product_description ?? ''}</li>
                </ul>
            </div>
            <!-- Kullanıcı Bilgileri -->
            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Kullanıcı Bilgileri</h3>
                <ul class="space-y-1">
                    <li><strong>Ad Soyad:</strong> ${o.user_name ?? 'Kullanıcı Yok'}</li>
                    <li><strong>E-posta:</strong> ${o.user_email ?? ''}</li>
                    <li><strong>Üye Telefon:</strong> ${o.user_phone ?? ''}</li>
                </ul>
            </div>
        </div>
        ${ o.design_data ? `
        <div class="mt-4">
            <h3 class="font-semibold text-gray-800 mb-2">Tasarım Önizleme</h3>
            <p class="text-gray-500 text-sm">
                Eğer tasarım verisi mevcutsa, aşağıdaki alanda Fabric.js ile görüntülenecektir.
            </p>
            <div class="border border-dashed border-gray-300 mt-2 p-2">
                <canvas id="modalCanvas" width="400" height="450" class="border rounded w-full h-auto"></canvas>
            </div>
        </div>
        ` : '' }
        `;
    }

    // Tasarımı PNG olarak indir (eski sürüm gibi; proxy ayarı vs. uygulanıyor)
    function downloadDesign() {
        if (!modalFabricCanvas) return;
        try {
            modalFabricCanvas.discardActiveObject();
            modalFabricCanvas.renderAll();
            const dataURL = modalFabricCanvas.toDataURL({ format: 'png', quality: 1 });
            const link = document.createElement('a');
            link.href = dataURL;
            link.download = 'siparis_' + currentOrderId + '_tasarim.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (e) {
            console.error('Download error:', e);
            alert('Tasarım indirilemedi. Lütfen sunucu CORS ayarlarını kontrol edin.');
        }
    }
</script>

</body>
</html>
