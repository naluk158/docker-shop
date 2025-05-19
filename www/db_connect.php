<?php
$host = "mariadb";
$user = "shopuser";
$pass = "shoppass";
$db = "shop_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $conn->connect_error);
}
?>
