<?php
include 'db.php';

$rfid_uid = $_POST['rfid_uid'];

if (empty($rfid_uid)) {
    echo "NO RFID";
    exit;
}

/* ===================== FIX 1: GET FROM product_items ===================== */
$product_query = "
SELECT 
    product_items.id AS item_id,
    product_items.product_id,
    product_items.status,
    products.price
FROM product_items
JOIN products ON products.id = product_items.product_id
WHERE product_items.rfid_uid='$rfid_uid'
LIMIT 1
";

$product_result = mysqli_query($conn, $product_query);

if (!$product_result) {
    die(mysqli_error($conn));
}

if (mysqli_num_rows($product_result) == 0) {
    mysqli_query($conn, "UPDATE settings SET last_error='RFID_NOT_REGISTERED' WHERE id=1");
    exit;
}

$product = mysqli_fetch_assoc($product_result);

/* ===================== STATUS CHECK ===================== */
// ✅ FIRST: already scanned
if ($product['status'] === 'in_cart') {
    mysqli_query($conn, "UPDATE settings SET last_error='ALREADY_SCANNED' WHERE id=1");
    echo "ALREADY SCANNED";
    exit;
}

// ✅ THEN: general availability
if ($product['status'] !== 'in_stock') {
    mysqli_query($conn, "UPDATE settings SET last_error='RFID_UNAVAILABLE' WHERE id=1");
    exit;
}
/* ===================== TOTAL ===================== */
$total_query = "SELECT IFNULL(SUM(products.price), 0) AS total FROM cart 
    JOIN products ON products.id = cart.product_id";

$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
$current_total = $total_data['total'];

/* ===================== BUDGET ===================== */
$budget_query = "SELECT budget FROM settings LIMIT 1";
$budget_result = mysqli_query($conn, $budget_query);
$budget_data = mysqli_fetch_assoc($budget_result);
$budget = $budget_data['budget'];

if ($budget <= 0) {
    mysqli_query($conn, "UPDATE settings SET last_error='SET_BUDGET_FIRST' WHERE id=1");
    echo "SET BUDGET FIRST";
    exit;
}

if (($current_total + $product['price']) > $budget) {
    mysqli_query($conn, "UPDATE settings SET last_error='BUDGET_EXCEEDED' WHERE id=1");
    echo "BUDGET EXCEEDED";
    exit;
}

/* ===================== FIX 2: INSERT CORRECT PRODUCT ID ===================== */
$insert_query = "INSERT INTO cart (product_id, quantity) VALUES ({$product['product_id']}, 1)";

if (!mysqli_query($conn, $insert_query)) {
    die(mysqli_error($conn));
}

/* ===================== FIX 3: UPDATE STATUS ===================== */
$update_query = "
UPDATE product_items 
SET status='in_cart' 
WHERE id='{$product['item_id']}'
";

mysqli_query($conn, $update_query);

echo "ITEM_ADDED";