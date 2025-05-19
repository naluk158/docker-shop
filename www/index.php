<?php
session_start();
$timeout_duration = 900; // 15 นาที

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

// ตรวจสอบ timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
  session_unset();
  session_destroy();
  header("Location: login.php?error=หมดเวลาการใช้งาน");
  exit();
}

$_SESSION['last_activity'] = time(); // อัปเดตเวลา
?>

<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>KETA-SHOP - สินค้าใหม่</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg bg-white border-bottom">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="index.php">KETA-SHOP</a>
    <a class="nav-link" href="index.php">ทั้งหมด</a>

    <form class="d-flex search-bar mx-5 w-100" method="get" action="index.php">
      <input class="form-control" type="search" name="search" placeholder="ค้นหาสินค้าที่ต้องการที่นี่..." aria-label="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button class="btn btn-warning ms-2 w-50" type="submit">🔍</button>
    </form>

    <ul class="navbar-nav ms-auto align-items-center">
      <?php if (isset($_SESSION['username'])): ?>
        <li class="nav-item dropdown mx-2">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            👤 <?= htmlspecialchars($_SESSION['username']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="change_password.php">🔐 เปลี่ยนรหัสผ่าน</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">🚪 ออกจากระบบ</a></li>
          </ul>
        </li>
      <?php else: ?>
        <li class="nav-item mx-2"><a class="nav-link" href="login.php">👤 เข้าสู่ระบบ</a></li>
      <?php endif; ?>
      <li class="nav-item mx-2"><a class="nav-link" href="#">🛒</a></li>
    </ul>
  </div>
</nav>

<!-- Content -->
<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <aside class="col-md-2 category-sidebar p-4">
      <h5 class="mb-3">หมวดหมู่สินค้า</h5>
      <ul class="list-unstyled">
        <li><strong>หมวดเสื้อ</strong>
          <ul><li>เสื้อแขนยาว</li><li>เสื้อแฟชั่น</li></ul>
        </li>
        <li><strong>หมวดกางเกง</strong>
          <ul><li>กางเกงขาสั้น</li><li>กางเกงขายาว</li></ul>
        </li>
        <li><strong>เครื่องประดับ</strong>
          <ul><li>สร้อยคอ</li><li>กระเป๋า</li></ul>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="col-md-9 py-4 px-4">
      <p class="text-muted">
        คีตะช็อป จำหน่ายสินค้าแฟชั่น ชาย หญิง มีหลากหลายแบรนด์ให้เลือกสรรมากมาย 
        <span class="text-danger fw-bold">การันตีของแท้</span> 
        พร้อมโปรโมชั่นส่วนลด สามารถสั่งซื้อออนไลน์ได้ง่าย ๆ ในราคาพิเศษจากร้านคีตะช็อป
      </p>
      <h5 class="mb-3">สินค้าแนะนำ</h5>

<?php
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
  $search = '%' . $conn->real_escape_string($_GET['search']) . '%';
  echo '<h5 class="mt-4 mb-3 text-danger">🔍 ผลลัพธ์สำหรับ: "' . htmlspecialchars($_GET['search']) . '"</h5>';
  echo '<div class="row row-cols-1 row-cols-md-3 g-4">';

  $stmt = $conn->prepare("SELECT * FROM products WHERE status = 'active' AND (name LIKE ? OR code LIKE ?) ORDER BY created_at DESC");
  $stmt->bind_param("ss", $search, $search);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo '<div class="col">';
      echo '  <div class="card product-card text-center shadow-sm">';
      echo '    <a href="product_detail.php?id=' . urlencode($row['id']) . '" class="text-decoration-none text-dark">';
      echo '      <div class="product-image-wrapper p-3">';
      echo '        <img src="' . htmlspecialchars($row['image_url']) . '" class="img-fluid rounded shadow-sm" alt="' . htmlspecialchars($row['name']) . '">';
      echo '      </div>';
      echo '    </a>';
      echo '    <div class="card-body pt-2">';
      echo '      <h6 class="card-title">' . htmlspecialchars($row['name']) . '</h6>';
      echo '      <p class="card-text">฿ ' . number_format($row['price'], 2) . '<br>' . htmlspecialchars($row['code']) . '</p>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }
  } else {
    echo '<div class="col"><p class="text-muted">ไม่พบสินค้าที่ค้นหา</p></div>';
  }

  echo '</div>';
} else {
  $categories = ['เสื้อผ้า', 'กางเกง', 'เครื่องประดับ'];

  foreach ($categories as $category) {
    echo '<h5 class="mt-4 mb-3 text-primary">หมวด: ' . htmlspecialchars($category) . '</h5>';
    echo '<div class="row row-cols-1 row-cols-md-3 g-4">';

    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 'active' AND category = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo '<div class="col">';
        echo '  <div class="card product-card text-center shadow-sm">';
        echo '    <a href="product_detail.php?id=' . urlencode($row['id']) . '" class="text-decoration-none text-dark">';
        echo '      <div class="product-image-wrapper p-3">';
        echo '        <img src="' . htmlspecialchars($row['image_url']) . '" class="img-fluid rounded shadow-sm" alt="' . htmlspecialchars($row['name']) . '">';
        echo '      </div>';
        echo '    </a>';
        echo '    <div class="card-body pt-2">';
        echo '      <h6 class="card-title">' . htmlspecialchars($row['name']) . '</h6>';
        echo '      <p class="card-text">฿ ' . number_format($row['price'], 2) . '<br>' . htmlspecialchars($row['code']) . '</p>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
      }
    } else {
      echo '<div class="col"><p class="text-muted">ไม่มีสินค้าที่แสดง</p></div>';
    }

    echo '</div>';
  }
}
?>
    </main>
  </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white mt-5">
  <div class="container py-4">
    <div class="row">
      <div class="col-md-4">
        <h5>KETA-SHOP</h5>
        <p>ร้านขายเสื้อผ้าและเครื่องประดับแฟชั่น พร้อมบริการจัดส่งทั่วประเทศ</p>
      </div>
      <div class="col-md-4">
        <h6>เมนู</h6>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">หน้าหลัก</a></li>
          <li><a href="#" class="text-white text-decoration-none">สินค้าใหม่</a></li>
          <li><a href="#" class="text-white text-decoration-none">เกี่ยวกับเรา</a></li>
          <li><a href="#" class="text-white text-decoration-none">ติดต่อเรา</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6>ติดต่อ</h6>
        <p>โทร: 099-123-4567<br>อีเมล: info@keta-shop.com</p>
        <p>ที่อยู่: 123 ถนนแฟชั่น เขตบางรัก กรุงเทพฯ</p>
      </div>
    </div>
    <div class="text-center mt-3">
      <small>© 2025 KETA-SHOP. สงวนลิขสิทธิ์.</small>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
