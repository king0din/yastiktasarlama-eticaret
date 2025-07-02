<?php
require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// base_price kolonunu price olarak döndürelim:
$stmt = $pdo->prepare("SELECT id, name, base_price AS price, description 
                       FROM products 
                       WHERE id = ?");
$stmt->execute([$id]);
$design = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($design);
exit;
