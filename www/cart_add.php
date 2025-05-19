<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = max(1, (int) $_POST['quantity']);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header("Location: cart_view.php"); // ไปยังหน้าตะกร้า
    exit;
} else {
    echo "การร้องขอไม่ถูกต้อง";
}
