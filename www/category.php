<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>สินค้าทั้งหมดในหมวด</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <?php
  if (isset($_GET['category'])) {
    $category = $_GET['category'];
    echo '<h4 class="mb-4 text-primary">หมวดหมู่: ' . htmlspecialchars($category) . '</h4>';

    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 'active' AND category = ? ORDER BY created_at ASC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      echo '<div class="row row-cols-1 row-cols-md-3 g-4">';
      while ($row = $result->fetch_assoc()) {
        echo '<div class="col">';
        echo '  <div class="card product-card text-center shadow-sm">';
        echo '    <a href="product_detail.php?id=' . urlencode($row['id']) . '" class="text-decoration-none text-dark">
        echo '    <div class="product-image-wrapper p-3">';
        echo '      <img src="' . htmlspecialchars($row['image_url']) . '" class="img-fluid rounded shadow-sm" alt="' . htmlspecialchars($row['name']) . '">';
        echo '    </div>';
        echo '    </a>';
        echo '    <div class="card-body pt-2">';
        echo '      <h6 class="card-title">' . htmlspecialchars($row['name']) . '</h6>';
        echo '      <p class="card-text">฿ ' . number_format($row['price'], 2) . '<br>' . htmlspecialchars($row['code']) . '</p>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
      }
      echo '</div>';
    } else {
      echo '<p class="text-muted">ไม่มีสินค้าที่แสดงในหมวดนี้</p>';
    }
  } else {
    echo '<p class="text-danger">ไม่พบหมวดหมู่</p>';
  }
  ?>
</div>
</body>
</html>
