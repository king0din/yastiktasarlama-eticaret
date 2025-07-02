<?php
session_start();

// Tüm oturum verilerini temizle
$_SESSION = array();

// Oturum çerezi sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Oturumu tamamen yok et
session_destroy();

// Giriş sayfasına yönlendir
header("Location: ../giris.php");
exit();
?>