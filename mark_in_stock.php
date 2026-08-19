<?php
include "db.php";

if (!isset($_POST['id'])) {
    echo "ERROR";
    exit;
}

$id = intval($_POST['id']);

if ($id <= 0) {
    echo "ERROR";
    exit;
}

// Update status to in_stock
$result = mysqli_query($conn, "
    UPDATE product_items 
    SET status='in_stock' 
    WHERE id='$id'
");

if ($result) {
    echo "SUCCESS";
} else {
    echo "ERROR";
}