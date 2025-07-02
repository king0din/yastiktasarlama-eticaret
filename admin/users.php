<?php
require_once '../includes/db.php';
require_once '../includes/admin-auth-check.php';

// Kullanıcı yasaklama/işlemler
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    
    if($action == 'ban') {
        $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
    } elseif($action == 'unban') {
        $stmt = $pdo->prepare("UPDATE users SET banned = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
    } elseif($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
}

// Tüm kullanıcıları çek
$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex">
        <?php include '../includes/admin-sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <h2 class="text-2xl font-bold mb-6">Kullanıcı Yönetimi</h2>
        
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Ad Soyad</th>
                        <th class="px-6 py-3 text-left">E-posta</th>
                        <th class="px-6 py-3 text-left">Durum</th>
                        <th class="px-6 py-3 text-left">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4"><?= $user['id'] ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($user['full_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-6 py-4">
                            <?php if($user['banned']): ?>
                                <span class="text-red-500">Yasaklı</span>
                            <?php else: ?>
                                <span class="text-green-500">Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <?php if($user['banned']): ?>
                                    <button type="submit" name="action" value="unban" 
                                            class="bg-green-500 text-white px-3 py-1 rounded">
                                        Yasak Kaldır
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="ban" 
                                            class="bg-red-500 text-white px-3 py-1 rounded">
                                        Yasakla
                                    </button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="delete" 
                                        class="bg-gray-500 text-white px-3 py-1 rounded"
                                        onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>