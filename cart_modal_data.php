<?php
include 'db.php';

$sql = "SELECT 
    p.id AS product_id,
    p.name,
    p.image,
    p.price,
    COUNT(c.id) AS qty
FROM cart c
JOIN products p ON p.id = c.product_id
GROUP BY p.id";

$result = mysqli_query($conn, $sql);

$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $subtotal = $row['price'] * $row['qty'];
    $total += $subtotal;

    $items[] = [
        "product_id" => $row['product_id'],
        "name" => $row['name'],
        "image" => $row['image'],
        "price" => $row['price'],
        "qty" => $row['qty']
    ];
}

echo json_encode([
    "total" => $total,
    "items" => $items
]);