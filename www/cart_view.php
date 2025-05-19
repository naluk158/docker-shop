<?php
session_start();
include 'db_connect.php';

$cart = $_SESSION['cart'] ?? [];
$products = [];

$total = 0;

// โหลดรายละเอียดสินค้าจากฐานข้อมูล
if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");

    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $cart[$row['id']];
        $row['total_price'] = $row['quantity'] * $row['price'];
        $products[] = $row;
        $total += $row['total_price'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ตะกร้าสินค้า | KETA-SHOP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h3 class="mb-4">🛒 ตะกร้าสินค้าของคุณ</h3>

  <?php if (empty($products)): ?>
    <div class="alert alert-info">ยังไม่มีสินค้าในตะกร้า</div>
    <a href="index.php" class="btn btn-primary">⬅️ เลือกซื้อสินค้า</a>
  <?php else: ?>
    <form method="post" action="cart_update.php">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>สินค้า</th>
            <th width="120">ราคา/ชิ้น</th>
            <th width="100">จำนวน</th>
            <th width="120">รวม</th>
            <th width="80">ลบ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                <small class="text-muted">รหัส: <?= htmlspecialchars($p['code']) ?></small>
              </td>
              <td>฿ <?= number_format($p['price'], 2) ?></td>
              <td>
                <input type="number" name="quantities[<?= $p['id'] ?>]" value="<?= $p['quantity'] ?>" min="1" class="form-control">
              </td>
              <td>฿ <?= number_format($p['total_price'], 2) ?></td>
              <td>
                <a href="cart_remove.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger">✕</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="d-flex justify-content-between align-items-center">
        <h5>💵 ยอดรวม: ฿ <?= number_format($total, 2) ?></h5>
        <div>
          <a href="index.php" class="btn btn-secondary">⬅️ เลือกสินค้าเพิ่ม</a>
          <button type="submit" class="btn btn-warning">🔁 อัปเดตตะกร้า</button>
          <a href="checkout.php" class="btn btn-success">✅ ดำเนินการชำระเงิน</a>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
