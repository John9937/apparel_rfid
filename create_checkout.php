<?php
session_start();
include 'db.php';

$res = mysqli_query($conn, "
SELECT SUM(p.price * c.quantity) AS total 
FROM cart c 
JOIN products p ON p.id = c.product_id
");

$row = mysqli_fetch_assoc($res);
$totalPeso = $row['total'] ?? 0;

if ($totalPeso <= 0) {
    echo "<script>alert('Cart is empty.');window.history.back();</script>";
    exit;
}


mysqli_query($conn, "
INSERT INTO orders (total_amount, payment_status, created_at, claimed)
VALUES ($totalPeso, 'pending', NOW(), 0)
");

$order_id = mysqli_insert_id($conn);


$cart = mysqli_query($conn, "SELECT c.product_id, c.quantity, p.price FROM cart c 
JOIN products p ON p.id = c.product_id");

while ($item = mysqli_fetch_assoc($cart)) {
    mysqli_query($conn, "
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES ($order_id, {$item['product_id']}, {$item['quantity']}, {$item['price']})
    ");
}


header("Location: payment.php?order_id=$order_id");
exit;