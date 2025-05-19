<?php
include 'db_connect.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ไม่พบสินค้าที่คุณค้นหา";
    exit;
}

$id = (int) $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "ไม่พบสินค้าที่คุณค้นหา";
    exit;
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> | รายละเอียดสินค้า</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <a href="javascript:history.back()" class="btn btn-secondary mb-3">⬅️ กลับ</a>

  <div class="row g-4">
    <div class="col-md-5">
      <img src="<?= htmlspecialchars($product['image_url']) ?>" class="img-fluid rounded shadow" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="col-md-7">
      <h3><?= htmlspecialchars($product['name']) ?></h3>
      <p class="text-muted">รหัสสินค้า: <?= htmlspecialchars($product['code']) ?></p>
      <h4 class="text-success mb-3">฿ <?= number_format($product['price'], 2) ?></h4>

      <?php if (!empty($product['description'])): ?>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      <?php else: ?>
        <p class="text-muted">ไม่มีรายละเอียดสินค้า</p>
      <?php endif; ?>

      <p><strong>หมวดหมู่:</strong> <?= htmlspecialchars($product['category']) ?></p>
      <p><strong>สถานะ:</strong> <?= $product['status'] == 'active' ? 'พร้อมขาย' : 'ไม่พร้อมขาย' ?></p>

      <form action="cart_add.php" method="post" class="mt-4">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="number" name="quantity" value="1" min="1" class="form-control mb-2" style="width: 120px;">
        <button type="submit" class="btn btn-primary">🛒 เพิ่มลงตะกร้า</button>
      </form>
    </div>
  </div>

  <hr class="my-5">

  <h5 class="mb-4 text-primary">🛍️ สินค้าอื่นในหมวดเดียวกัน</h5>
  <div class="row row-cols-1 row-cols-md-4 g-4">
    <?php
    $category = $product['category'];
    $related_stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND status = 'active' LIMIT 4");
    $related_stmt->bind_param("si", $category, $id);
    $related_stmt->execute();
    $related_result = $related_stmt->get_result();

    if ($related_result->num_rows > 0) {
      while ($row = $related_result->fetch_assoc()) {
        echo '<div class="col">';
        echo '  <div class="card h-100 text-center">';
        echo '    <a href="product_detail.php?id=' . $row['id'] . '" class="text-decoration-none text-dark">';
        echo '      <img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top p-3" alt="' . htmlspecialchars($row['name']) . '">';
        echo '      <div class="card-body">';
        echo '        <h6 class="card-title">' . htmlspecialchars($row['name']) . '</h6>';
        echo '        <p class="card-text text-success">฿ ' . number_format($row['price'], 2) . '</p>';
        echo '      </div>';
        echo '    </a>';
        echo '  </div>';
        echo '</div>';
      }
    } else {
      echo '<p class="text-muted">ไม่มีสินค้าที่เกี่ยวข้อง</p>';
    }
    ?>
  </div>
</div>
</body>
</html>
