<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$id = (int)$_GET['id'];

// OPTIONAL: delete image file too
$query = mysqli_query($conn, "SELECT image FROM products WHERE id=$id");
$data = mysqli_fetch_assoc($query);

if ($data && file_exists("products/" . $data['image'])) {
    unlink("products/" . $data['image']);
}

// DELETE FROM DATABASE
mysqli_query($conn, "DELETE FROM products WHERE id=$id");

header("Location: add_product.php?deleted=1");
exit;