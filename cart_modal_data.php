<?php
include 'db.php';

$sql = "SELECT products.name, products.image, products.price, COUNT(*) AS qty
        FROM cart
        JOIN products ON products.id = cart.product_id
        GROUP BY products.name, products.image, products.price";

$result = mysqli_query($conn, $sql);

$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['subtotal'] = $row['price'] * $row['qty'];
    $total += $row['subtotal'];
    $items[] = $row;
}

echo json_encode([
    'total' => $total,
    'items' => $items
]);
