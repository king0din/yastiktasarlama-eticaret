<?php
// Hata mesajlarını aktif et
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../includes/db.php';
require_once '../includes/admin-auth-check.php';

// Tasarım ekleme/silme/güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Silme işlemi
    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_POST['design_id']]);
    } 
    // Ekleme / Güncelleme
    else {
        $name        = $_POST['name'] ?? '';
        $base_price  = $_POST['price'] ?? 0;
        $description = $_POST['description'] ?? '';
        $designId    = $_POST['design_id'] ?? null;

        // Eğer dosya yüklenmişse base64'e çevir
        $imageBase64 = null;
        if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] != '') {
            $imageData   = file_get_contents($_FILES['image']['tmp_name']);
            $imageBase64 = base64_encode($imageData);
        }

        if (isset($_POST['edit']) && $designId) {
            // Düzenleme işlemi
            if ($imageBase64) {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET name = ?, base_price = ?, description = ?, image = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $base_price, $description, $imageBase64, $designId]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET name = ?, base_price = ?, description = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $base_price, $description, $designId]);
            }
        } else {
            // Yeni ekleme işlemi
            $sizes = $_POST['sizes'] ?? ''; // Varsayılan olarak boş değer

            $stmt = $pdo->prepare("INSERT INTO products (name, base_price, description, image, sizes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $base_price, $description, $imageBase64, $sizes]);
        }
    }
}

// Tüm tasarımları çek
$designs = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tasarım Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include '../includes/admin-sidebar.php'; ?>
    
        <div class="flex-1 p-8">
            <h2 class="text-2xl font-bold mb-6">Tasarım Yönetimi</h2>
            
            <!-- Yeni Tasarım Formu -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4" enctype="multipart/form-data">
                    <input type="text" name="name" placeholder="Tasarım Adı" required class="p-2 border rounded">
                    <input type="number" name="price" placeholder="Fiyat" step="0.01" required class="p-2 border rounded">
                    <textarea name="description" placeholder="Açıklama" class="p-2 border rounded"></textarea>
                    
                    <input type="file" name="image" accept="image/*" class="p-2 border rounded">

                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Ekle</button>
                </form>
            </div>

            <!-- Tasarım Listesi -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">ID</th>
                            <th class="px-6 py-3 text-left">Ad</th>
                            <th class="px-6 py-3 text-left">Fiyat</th>
                            <th class="px-6 py-3 text-left">Resim</th>
                            <th class="px-6 py-3 text-left">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($designs as $design): ?>
                        <tr>
                            <td class="px-6 py-4"><?= $design['id'] ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($design['name']) ?></td>
                            <td class="px-6 py-4">
                                <?= number_format($design['base_price'], 2) ?> TL
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($design['image'])): ?>
                                    <img src="data:image/jpeg;base64,<?= $design['image'] ?>" alt="Resim" class="w-16 h-16 object-cover rounded" />
                                <?php else: ?>
                                    <span class="text-gray-500">Resim yok</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="design_id" value="<?= $design['id'] ?>">
                                    <button type="submit" name="delete" class="bg-red-500 text-white px-3 py-1 rounded">
                                        Sil
                                    </button>
                                    <button type="button"
                                            onclick="openEditModal(<?= $design['id'] ?>)"
                                            class="bg-blue-500 text-white px-3 py-1 rounded">
                                        Düzenle
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Düzenleme Modalı -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-96 mx-auto mt-24">
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="design_id" id="editDesignId">
                <input type="hidden" name="edit" value="1">
                
                <div class="space-y-4">
                    <input type="text" name="name" id="editName" required 
                           class="w-full p-2 border rounded" placeholder="Tasarım Adı">
                    <input type="number" name="price" id="editPrice" step="0.01" required 
                           class="w-full p-2 border rounded" placeholder="Fiyat">
                    <textarea name="description" id="editDescription" 
                              class="w-full p-2 border rounded" placeholder="Açıklama"></textarea>

                    <label class="block">
                        <span class="text-gray-700 text-sm">Yeni Resim (İsteğe bağlı)</span>
                        <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm p-2 border rounded" />
                    </label>

                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Kaydet</button>
                        <button type="button" onclick="closeEditModal()" 
                                class="bg-gray-500 text-white px-4 py-2 rounded">İptal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    // API'den gelen verileri modal input'larına aktarır ve modal'ı açar
    function openEditModal(id) {
        fetch('get-design.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editDesignId').value = data.id;
                document.getElementById('editName').value = data.name;
                document.getElementById('editPrice').value = data.price;
                document.getElementById('editDescription').value = data.description;
                
                // Modal'ı görünür yap
                document.getElementById('editModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Düzenleme verisi çekilirken hata oluştu:', error);
            });
    }

    // Modal kapatma
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    </script>
</body>
</html>
