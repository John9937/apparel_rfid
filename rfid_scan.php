<?php
include 'db.php';


$rfid_uid = $_POST['rfid_uid'];

if (empty($rfid_uid)) {
    echo "NO_RFID";
    exit;
}

$product_query = "
    SELECT id, price, status
    FROM products
    WHERE rfid_uid='$rfid_uid'
";

$product_result = mysqli_query($conn, $product_query);

if (!$product_result) {
    die(mysqli_error($conn));
}

if (mysqli_num_rows($product_result) == 0) {
    echo "RFID_NOT_FOUND";
    exit;
}

$product = mysqli_fetch_assoc($product_result);

if ($product['status'] !== 'in_stock') {
    echo "ITEM_UNAVAILABLE";
    exit;
}


$total_query = "
    SELECT IFNULL(SUM(products.price), 0) AS total
    FROM cart
    JOIN products ON products.id = cart.product_id
";

$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
$current_total = $total_data['total'];


$budget_query = "SELECT budget FROM settings LIMIT 1";
$budget_result = mysqli_query($conn, $budget_query);
$budget_data = mysqli_fetch_assoc($budget_result);
$budget = $budget_data['budget'];


if (($current_total + $product['price']) > $budget) {
    echo "BUDGET_EXCEEDED";
    exit;
}


$insert_query = "
    INSERT INTO cart (product_id, quantity)
    VALUES ({$product['id']}, 1)
";

if (!mysqli_query($conn, $insert_query)) {
    die(mysqli_error($conn));
}


$update_query = "
    UPDATE products
    SET status='in_cart'
    WHERE id={$product['id']}
";

mysqli_query($conn, $update_query);

echo "ITEM_ADDED";
