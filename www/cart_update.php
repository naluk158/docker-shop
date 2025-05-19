<?php
session_start();
//cart_update.php (จัดการอัปเดตจำนวน)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        $product_id = (int) $product_id;
        $qty = max(1, (int) $qty);
        $_SESSION['cart'][$product_id] = $qty;
    }
}

header("Location: cart_view.php");
exit;
