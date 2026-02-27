<?php
include "db.php";
include "phpqrcode/qrlib.php";

$order_id = $_POST['order_id'];

/* Generate unique token */
$token = md5($order_id . time());

/* Create QR directory if not exists */
if (!file_exists('qrcodes')) {
    mkdir('qrcodes', 0777, true);
}

/* Create QR link */
$order_url = "https://apparelease.fit//order_view.php?token=" . $token;

/* Define QR file path */
$qr_path = "qrcodes/order_" . $order_id . ".png";

/* Generate QR image */
QRcode::png($order_url, $qr_path, QR_ECLEVEL_L, 5);

/* Update order */
mysqli_query($conn, "
UPDATE orders 
SET payment_status = 'waiting_verification',
    qr_token = '$token',
    qr_code_path = '$qr_path'
WHERE id = '$order_id'
");

/* Redirect to qr_receipt page */
header("Location: qr_receipt.php?order_id=$order_id");
exit;