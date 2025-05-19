<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$errors = [];
$name = $phone = $address = $payment_method = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $payment_method = $_POST["payment_method"];

    if (empty($name)) $errors[] = "กรุณากรอกชื่อผู้รับ";
    if (empty($phone)) $errors[] = "กรุณากรอกเบอร์โทร";
    if (empty($address)) $errors[] = "กรุณากรอกที่อยู่";
    if (empty($payment_method)) $errors[] = "กรุณาเลือกวิธีชำระเงิน";

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO orders (customer_name, phone, address, payment_method, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $phone, $address, $payment_method);
            $stmt->execute();
            $order_id = $stmt->insert_id;

            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $stmt_product = $conn->prepare("SELECT price FROM products WHERE id = ?");
                $stmt_product->bind_param("i", $product_id);
                $stmt_product->execute();
                $result = $stmt_product->get_result();
                if ($row = $result->fetch_assoc()) {
                    $price = $row['price'];
                    $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
                    $stmt_item->execute();
                }
            }

            $conn->commit();
            unset($_SESSION['cart']);
            header("Location: order_success.php?order_id=$order_id");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "เกิดข้อผิดพลาดในการสั่งซื้อ: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ชำระเงิน - KETA-SHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #qr-container {
            display: none;
            margin-top: 15px;
        }
        #qr-container img {
            max-width: 200px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="mb-4">📦 ยืนยันการสั่งซื้อ</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">ชื่อผู้รับ</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ที่อยู่สำหรับจัดส่ง</label>
            <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($address) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">ช่องทางการชำระเงิน</label>
            <select name="payment_method" id="payment_method" class="form-select" required onchange="toggleQR()">
                <option value="">-- กรุณาเลือก --</option>
                <option value="โอนเงิน" <?= $payment_method == 'โอนเงิน' ? 'selected' : '' ?>>โอนเงิน</option>
                <option value="ชำระปลายทาง" <?= $payment_method == 'ชำระปลายทาง' ? 'selected' : '' ?>>ชำระปลายทาง</option>
                <option value="พร้อมเพย์" <?= $payment_method == 'พร้อมเพย์' ? 'selected' : '' ?>>พร้อมเพย์ (QR Code)</option>
            </select>
        </div>

        <div id="qr-container" class="text-center">
            <label class="form-label">สแกน QR เพื่อชำระเงิน</label>
            <br>
            <img src="img/qr-code.png" alt="QR Code">
        </div>

        <h5 class="mt-4">รายการสินค้า</h5>
        <ul class="list-group mb-3">
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $product_id => $qty):
                $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()):
                    $subtotal = $row['price'] * $qty;
                    $total += $subtotal;
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($row['name']) ?> x <?= $qty ?>
                <span>฿ <?= number_format($subtotal, 2) ?></span>
            </li>
            <?php endif; endforeach; ?>
            <li class="list-group-item d-flex justify-content-between fw-bold">
                รวมทั้งหมด <span>฿ <?= number_format($total, 2) ?></span>
            </li>
        </ul>

        <button type="submit" class="btn btn-success btn-lg w-100">✅ ยืนยันและสั่งซื้อ</button>
    </form>
</div>

<script>
function toggleQR() {
    const method = document.getElementById("payment_method").value;
    const qr = document.getElementById("qr-container");
    qr.style.display = (method === "พร้อมเพย์") ? "block" : "none";
}
document.addEventListener("DOMContentLoaded", toggleQR);
</script>
</body>
</html>
