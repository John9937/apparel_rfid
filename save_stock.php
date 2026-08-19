<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 🔹 Get values safely
    $rfid = mysqli_real_escape_string($conn, $_POST['rfid_uid'] ?? '');
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id'] ?? '');
    $size = mysqli_real_escape_string($conn, $_POST['size'] ?? '');

    // ================= VALIDATION =================
    if (empty($rfid) || empty($product_id) || empty($size)) {
        echo "ERROR: Missing fields";
        exit;
    }

    // ================= CHECK DUPLICATE RFID =================
    $check = mysqli_query($conn, "
        SELECT id FROM product_items WHERE rfid_uid='$rfid'
    ");

    if (mysqli_num_rows($check) > 0) {
        echo "EXISTS";
        exit;
    }

    // ================= INSERT STOCK =================
    $insert = mysqli_query($conn, "
        INSERT INTO product_items (rfid_uid, product_id, size, status)
        VALUES ('$rfid', '$product_id', '$size', 'in_stock')
    ");

    if ($insert) {
        echo "SUCCESS";
    } else {
        echo "ERROR: " . mysqli_error($conn);
    }
}
?>