<?php
session_start();
$host = '92.113.22.48';
$dbname = 'u859419507_111';
$user = 'u859419507_111';
$pass = ']kd7?rIZUDg7';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>