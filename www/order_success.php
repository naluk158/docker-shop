<?php
session_start();

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คำสั่งซื้อสำเร็จ - KETA-SHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5 text-center">
    <div class="alert alert-success">
        <h2>🎉 สั่งซื้อสำเร็จแล้ว!</h2>
        <p>ขอบคุณที่สั่งซื้อสินค้ากับเรา</p>
        <h4>เลขที่คำสั่งซื้อของคุณคือ: <span class="text-primary">#<?= htmlspecialchars($order_id) ?></span></h4>
    </div>
    <a href="index.php" class="btn btn-primary">🛍️ กลับไปหน้าแรก</a>
</div>
</body>
</html>
