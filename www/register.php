<?php
include 'db_connect.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // ตรวจสอบค่าว่าง
  if (empty($username) || empty($password) || empty($confirm_password)) {
    $errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
  }

  // ตรวจสอบความยาวรหัสผ่าน
  if (strlen($password) < 6) {
    $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
  }

  // ตรวจสอบรหัสผ่านตรงกัน
  if ($password !== $confirm_password) {
    $errors[] = "รหัสผ่านไม่ตรงกัน";
  }

  // ตรวจสอบชื่อผู้ใช้ซ้ำ
  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $errors[] = "ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว";
  }
  $stmt->close();

  // ถ้าไม่มี error ให้บันทึกข้อมูล
  if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    if ($stmt->execute()) {
      header("Location: login.php?success=สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ");
      exit();
    } else {
      $errors[] = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
    }
    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>สมัครสมาชิก | KETA-SHOP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow">
          <div class="card-body">
            <h4 class="card-title text-center mb-4">สมัครสมาชิก</h4>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" name="username" id="username" class="form-control" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" id="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
              </div>
            </form>

            <p class="mt-3 text-center">
              มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
