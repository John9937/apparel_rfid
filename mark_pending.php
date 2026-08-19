<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";
include "phpqrcode/qrlib.php";

/* ===================== VALIDATE INPUT ===================== */
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    die("Missing order ID");
}

$order_id = intval($_POST['order_id']);

/* ===================== CHECK ORDER EXISTS ===================== */
$check = mysqli_query($conn, "SELECT * FROM orders WHERE id='$order_id'");

if (!$check) {
    die("SQL Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($check) == 0) {
    die("Order not found");
}

/* ===================== GENERATE TOKEN ===================== */
$token = md5($order_id . time());

/* ===================== CREATE QR FOLDER ===================== */
if (!file_exists('qrcodes')) {
    if (!mkdir('qrcodes', 0777, true)) {
        die("Failed to create QR folder");
    }
}

/* ===================== GENERATE QR ===================== */
$order_url = "https://apparelease.fit/order_view.php?token=" . $token;

$qr_path = "qrcodes/order_" . $order_id . ".png";

QRcode::png($order_url, $qr_path, QR_ECLEVEL_L, 5);

/* ===================== UPDATE ORDER ===================== */
$update = mysqli_query($conn, "
    UPDATE orders 
    SET 
        payment_status = 'waiting_verification',
        qr_token = '$token',
        qr_code_path = '$qr_path'
    WHERE id = '$order_id'
");

if (!$update) {
    die("Update failed: " . mysqli_error($conn));
}

/* ===================== REDIRECT ===================== */
header("Location: qr_receipt.php?order_id=$order_id");
exit;