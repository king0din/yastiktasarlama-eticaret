<?php
include('../includes/auth-check.php');
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<div class="space-y-4">
    <?php foreach($orders as $order): ?>
    <div class="p-4 bg-white shadow rounded">
        <h3 class="font-bold">Sipariş #<?= $order['id'] ?></h3>
        <p>Durum: <?= $order['status'] ?></p>
    </div>
    <?php endforeach; ?>
</div>