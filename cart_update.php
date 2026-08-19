<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

$productId = $_POST['product_id'] ?? '';
$action    = $_POST['action'] ?? '';

if ($productId === '' || $action === '') {
    echo "NO_INPUT";
    exit;
}

$productId = (int)$productId;

/* ================= DECREASE ================= */
if ($action === 'decrease') {

    // get one cart row
    $result = mysqli_query($conn, "
        SELECT id FROM cart WHERE product_id = $productId LIMIT 1
    ");

    if (mysqli_num_rows($result) == 0) {
        echo "NOT_FOUND";
        exit;
    }

    $row = mysqli_fetch_assoc($result);
    $cartId = $row['id'];

    // 🔥 get ONE actual item
    $item = mysqli_query($conn, "
        SELECT id, rfid_uid FROM product_items 
        WHERE product_id = $productId 
        AND status='in_cart'
        ORDER BY id ASC
        LIMIT 1
    ");

    if ($item && mysqli_num_rows($item) > 0) {
        $itemRow = mysqli_fetch_assoc($item);
        $rfid = $itemRow['rfid_uid'];

        mysqli_query($conn, "
            UPDATE product_items 
            SET status='in_stock' 
            WHERE id = {$itemRow['id']}
        ");
    }

    // delete cart row
    mysqli_query($conn, "DELETE FROM cart WHERE id = $cartId");

   echo "REMOVED_ONE:$rfid";
    exit;
}
/* ================= REMOVE ================= */
if ($action === 'remove') {

    // 🔥 get ALL cart items first
    $items = mysqli_query($conn, "
        SELECT id, rfid_uid FROM product_items 
        WHERE product_id = $productId 
        AND status='in_cart'
        ORDER BY id ASC
    ");

    $rfids = [];

    while ($row = mysqli_fetch_assoc($items)) {
        $itemId = $row['id'];
        $rfids[] = $row['rfid_uid'];

        mysqli_query($conn, "
            UPDATE product_items 
            SET status='in_stock' 
            WHERE id = $itemId
        ");
    }

    // 🔥 delete cart rows
    mysqli_query($conn, "
        DELETE FROM cart WHERE product_id = $productId
    ");

    echo "REMOVED_ALL:" . implode(",", $rfids);
    exit;
}

echo "INVALID_ACTION";