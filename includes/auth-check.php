<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /giris.php');
    exit();
}
?>