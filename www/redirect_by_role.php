<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ตรวจ timeout
if (time() - $_SESSION['last_activity'] > 900) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
} else {
    $_SESSION['last_activity'] = time();
}

// redirect ตามบทบาท
if ($_SESSION['role'] === 'admin') {
    header("Location: admin_product_management.php");
} else {
    header("Location: index.php");
}
exit;

