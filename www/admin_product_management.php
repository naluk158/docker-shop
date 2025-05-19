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
  <title>Admin - จัดการสินค้า | KETA-SHOP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin_style.css" rel="stylesheet"> <!-- สไตล์แยกไฟล์ -->

<?php if (isset($_SESSION['username'])): ?>
  <div class="d-flex align-items-center">
    <span class="me-3">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
    <a href="logout.php" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
  </div>
<?php endif; ?>

</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 sidebar d-flex flex-column p-3">
      <h4 class="text-center mb-4">🔧 Admin Panel</h4>
      <a href="admin_product_management.php">📦 สินค้าทั้งหมด</a>
      <?php
      $categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
      while ($cat = $categories->fetch_assoc()) {
        $catName = htmlspecialchars($cat['category']);
        echo "<a href='admin_product_management.php?category=" . urlencode($catName) . "'>📂 " . $catName . "</a>";
      }
      ?>
      <hr>
      <a href="add_products.html">➕ เพิ่มสินค้า</a>
      <a href="#">🧾 รายงานการขาย</a>
      <a href="#">👥 จัดการผู้ใช้</a>
      <a href="logout.php">🚪 ออกจากระบบ</a>
    </nav>

    <!-- Main Content -->
    <main class="col-md-10 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3>📦 รายการสินค้า</h3>
          <h5 class="text-muted">
            <?= isset($_GET['category']) ? "📂 หมวดหมู่: " . htmlspecialchars($_GET['category']) : "🗂️ ทั้งหมด" ?>
          </h5>
        </div>
        <a href="add_products.html" class="btn btn-success">➕ เพิ่มสินค้า</a>
      </div>

      <table class="table table-bordered table-hover bg-white">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>รูปภาพ</th>
            <th>ชื่อสินค้า</th>
            <th>รหัส</th>
            <th>ราคา</th>
            <th>หมวดหมู่</th>
            <th>สถานะ</th>
            <th>การจัดการ</th>
          </tr>
        </thead>
        <tbody>
        <?php
        if (isset($_GET['category'])) {
          $category = $conn->real_escape_string($_GET['category']);
          $sql = "SELECT * FROM products WHERE category = '$category' ORDER BY id DESC";
        } else {
          $sql = "SELECT * FROM products ORDER BY id DESC";
        }

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td><img src="' . htmlspecialchars($row['image_url']) . '" width="60" height="60" class="rounded"></td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['code']) . '</td>';
            echo '<td>฿ ' . number_format($row['price'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>' . ($row['status'] === 'active' ? '<span class="badge bg-success">เปิด</span>' : '<span class="badge bg-secondary">ปิด</span>') . '</td>';
            echo '<td>';
            echo '<a href="edit_product.php?id=' . $row['id'] . '" class="btn btn-sm btn-warning">✏️ แก้ไข</a> ';
            echo '<a href="delete_product.php?id=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'ยืนยันการลบสินค้านี้?\')">🗑️ ลบ</a>';
            echo '</td>';
            echo '</tr>';
          }
        } else {
          echo '<tr><td colspan="8" class="text-center text-muted">ไม่มีสินค้าในหมวดนี้</td></tr>';
        }
        ?>
        </tbody>
      </table>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
