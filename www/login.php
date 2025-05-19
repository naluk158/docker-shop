<?php
session_start();
include 'db_connect.php';

// ถ้าล็อกอินแล้วและไม่มี timeout ให้ไปหน้าอื่นเลย (หลีกเลี่ยง redirect ซ้ำที่ login.php)
if (isset($_SESSION['username']) && !isset($_GET['timeout'])) {
    header("Location: redirect_by_role.php");
    exit;
}

// ตรวจสอบการส่งฟอร์ม login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['last_activity'] = time();

            header("Location: redirect_by_role.php");
            exit;
        } else {
            $error = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "ไม่พบชื่อผู้ใช้นี้";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เข้าสู่ระบบ | KETA-SHOP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="min-width: 350px;">
      <h4 class="mb-3 text-center">🔐 เข้าสู่ระบบ</h4>
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['timeout'])): ?>
        <div class="alert alert-warning">หมดเวลาการใช้งาน กรุณาเข้าสู่ระบบใหม่</div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label for="username" class="form-label">ชื่อผู้ใช้</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">รหัสผ่าน</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        <div class="mt-3 text-center">
          <a href="register.php">ยังไม่มีบัญชี? สมัครสมาชิก</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
