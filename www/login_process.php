<?php
session_start();
include 'db_connect.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();
  if (password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time(); // ใช้สำหรับ timeout

    if ($user['role'] === 'admin') {
      header("Location: admin_product_managetment.php");
    } else {
      header("Location: index.php");
    }
    exit();
  }
}

header("Location: login.php?error=ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
exit();
