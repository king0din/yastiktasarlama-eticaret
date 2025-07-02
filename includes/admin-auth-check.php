<?php

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../giris.php");
    exit();
}
?>