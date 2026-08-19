<?php
include "db.php";

$order_id = intval($_GET['order_id']);

$res = mysqli_query($conn, "SELECT payment_status FROM orders WHERE id='$order_id'");
$row = mysqli_fetch_assoc($res);

echo json_encode([
    "status" => $row['payment_status']
]);