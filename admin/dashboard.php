<?php
require_once '../includes/db.php';
require_once '../includes/admin-auth-check.php';

// İstatistikler için veriler
$stmt = $pdo->query("SELECT
    (SELECT COUNT(*) FROM orders) AS total_orders,
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT SUM(total_price) FROM orders) AS total_revenue");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex">
        <?php include '../includes/admin-sidebar.php'; ?>

        <!-- Ana İçerik -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">Admin Paneli</h1>
            
            <!-- İstatistik Kartları -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Toplam Sipariş</h3>
                    <p class="text-2xl font-bold"><?= $stats['total_orders'] ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Toplam Kullanıcı</h3>
                    <p class="text-2xl font-bold"><?= $stats['total_users'] ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Toplam Ciro</h3>
                    <p class="text-2xl font-bold"><?= number_format($stats['total_revenue'], 2) ?> TL</p>
                </div>
            </div>

            <!-- Hızlı Erişim Butonları -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <a href="orders.php" class="bg-blue-500 text-white p-4 rounded-lg text-center hover:bg-blue-600">
                    Sipariş Yönetimi
                </a>
                <a href="designs.php" class="bg-green-500 text-white p-4 rounded-lg text-center hover:bg-green-600">
                    Tasarım Yönetimi
                </a>
                <a href="users.php" class="bg-purple-500 text-white p-4 rounded-lg text-center hover:bg-purple-600">
                    Kullanıcı Yönetimi
                </a>
                <a href="../index.php" class="bg-gray-500 text-white p-4 rounded-lg text-center hover:bg-gray-600">
                    Siteye Dön
                </a>
            </div>
        </div>
    </div>
</body>
</html>