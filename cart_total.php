<?php
include 'db.php';

$sql = "SELECT IFNULL(SUM(products.price * cart.quantity), 0) AS total
        FROM cart
        JOIN products ON products.id = cart.product_id";

$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

echo number_format($row['total'], 2);
