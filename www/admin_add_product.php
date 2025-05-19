<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = $_POST['name'];
    $price       = $_POST['price'];
    $code        = $_POST['code'];
    $description = $_POST['description'];
    $category    = $_POST['category'];
    $status      = $_POST['status'];

    // อัปโหลดรูป
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $image = $_FILES["image"];
    $image_name = basename($image["name"]);
    $image_ext  = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($image_ext, $allowed_ext)) {
        die("❌ ไม่อนุญาตให้อัปโหลดไฟล์ประเภทนี้");
    }

    $unique_name = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $image_name);
    $target_file = $upload_dir . $unique_name;

    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        $sql = "INSERT INTO products (name, price, code, image_url, description, category, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsssss", $name, $price, $code, $target_file, $description, $category, $status);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>✅ เพิ่มสินค้าเรียบร้อย</p>";
            echo "<a href='add_products.html'>➕ เพิ่มสินค้าใหม่อีก</a> | <a href='index.php'>🏠 กลับหน้าหลัก</a>";
        } else {
            echo "<p style='color:red;'>❌ เกิดข้อผิดพลาดในการบันทึก: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>❌ ไม่สามารถอัปโหลดรูปภาพได้</p>";
    }

    $conn->close();
} else {
    header("Location: add_product.php");
    exit();
}
?>
