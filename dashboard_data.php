<?php
include "db.php";
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
// COUNTS
$data = [];

// total products
$data['totalProducts'] = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM products
"))['total'];

// in stock
$data['inStock'] = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM product_items WHERE status='in_stock'
"))['total'];

// out of stock
$data['outStock'] = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM product_items WHERE status='out_of_stock'
"))['total'];

// in cart
$data['inCart'] = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM product_items WHERE status='in_cart'
"))['total'];

// ================= ALL PRODUCTS =================
$products = [];
$res = mysqli_query($conn, "
SELECT 
    p.id,
    p.name,
    p.price,

    SUM(CASE WHEN pi.size='Small' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS small,
    SUM(CASE WHEN pi.size='Medium' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS medium,
    SUM(CASE WHEN pi.size='Large' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS large,

    COUNT(pi.id) AS total_registered,
    SUM(CASE WHEN pi.status='in_stock' THEN 1 ELSE 0 END) AS available_total

FROM products p
LEFT JOIN product_items pi ON pi.product_id = p.id
GROUP BY p.id
");

while($row = mysqli_fetch_assoc($res)){
    $products[] = $row;
}

$data['products'] = $products;

// IN STOCK LIST
$inStockList = [];
$res = mysqli_query($conn, "
    SELECT pi.rfid_uid, pi.size, p.name 
    FROM product_items pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.status='in_stock'
    ORDER BY pi.id DESC
");

while($row = mysqli_fetch_assoc($res)){
    $inStockList[] = $row;
}
$data['inStockList'] = $inStockList;


// OUT OF STOCK LIST
$outStockList = [];
$res = mysqli_query($conn, "
    SELECT pi.rfid_uid, pi.size, p.name 
    FROM product_items pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.status='out_of_stock'
    ORDER BY pi.id DESC
");

while($row = mysqli_fetch_assoc($res)){
    $outStockList[] = $row;
}
$data['outStockList'] = $outStockList;


// IN CART LIST
$inCartList = [];
$res = mysqli_query($conn, "
    SELECT pi.rfid_uid, pi.size, p.name 
    FROM product_items pi
    JOIN products p ON p.id = pi.product_id
    WHERE pi.status='in_cart'
    ORDER BY pi.id DESC
");

while($row = mysqli_fetch_assoc($res)){
    $inCartList[] = $row;
}
$data['inCartList'] = $inCartList;

echo json_encode($data);