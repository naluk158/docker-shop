<?php
session_start();
//cart_remove.php (ลบสินค้าออก)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
    unset($_SESSION['cart'][$id]);
}

header("Location: cart_view.php");
exit;
