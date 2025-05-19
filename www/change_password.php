<?php
session_start();
include 'db_connect.php';

// ถ้ายังไม่ได้ login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_pass = trim($_POST['old_password']);
    $new_pass = trim($_POST['new_password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if ($new_pass !== $confirm_pass) {
        $error = "รหัสผ่านใหม่ไม่ตรงกัน";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && password_verify($old_pass, $row['password'])) {
            $new_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update->bind_param("ss", $new_hashed, $username);
            if ($update->execute()) {
                $success = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
            } else {
                $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
            }
        } else {
            $error = "รหัสผ่านเดิมไม่ถูกต้อง";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เปลี่ยนรหัสผ่าน | KETA-SHOP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .checklist {
      font-size: 0.9em;
      margin-top: -5px;
    }
    .checklist li {
      list-style: none;
    }
    .checklist .valid {
      color: green;
    }
    .checklist .invalid {
      color: red;
    }
  </style>
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow" style="min-width: 400px;">
    <h4 class="mb-3 text-center">🔒 เปลี่ยนรหัสผ่าน</h4>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3">
        <label class="form-label">รหัสผ่านเดิม</label>
        <input type="password" name="old_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">รหัสผ่านใหม่</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <ul id="password-checklist" class="checklist mb-3">
        <li id="length" class="invalid">❌ อย่างน้อย 8 ตัวอักษร</li>
        <li id="number" class="invalid">❌ มีตัวเลขอย่างน้อย 1 ตัว</li>
        <li id="uppercase" class="invalid">❌ มีตัวอักษรพิมพ์ใหญ่</li>
        <li id="special" class="invalid">❌ มีอักขระพิเศษ (!@#$%^&*)</li>
        <li id="match" class="invalid">❌ รหัสผ่านตรงกัน</li>
      </ul>
      <button type="submit" class="btn btn-primary w-100">บันทึก</button>
    </form>
    <a href="index.php" class="btn btn-link mt-3 w-100">⬅️ กลับหน้าหลัก</a>
  </div>
</div>

<script>
  const newPassword = document.querySelector('input[name="new_password"]');
  const confirmPassword = document.querySelector('input[name="confirm_password"]');

  function validatePasswordStrength() {
    const val = newPassword.value;
    document.getElementById("length").className = val.length >= 8 ? "valid" : "invalid";
    document.getElementById("number").className = /\d/.test(val) ? "valid" : "invalid";
    document.getElementById("uppercase").className = /[A-Z]/.test(val) ? "valid" : "invalid";
    document.getElementById("special").className = /[!@#$%^&*]/.test(val) ? "valid" : "invalid";
    document.getElementById("match").className = (val === confirmPassword.value && val !== "") ? "valid" : "invalid";
  }

  newPassword.addEventListener("input", validatePasswordStrength);
  confirmPassword.addEventListener("input", validatePasswordStrength);
</script>
</body>
</html>
