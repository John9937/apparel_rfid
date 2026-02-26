<?php
include "db.php";
include "phpqrcode/qrlib.php";

$payload = file_get_contents("php://input");
$event = json_decode($payload, true);

$type = $event['data']['attributes']['type'] ?? null;

if ($type === "checkout_session.payment.paid") {

    $order_id = $event['data']['attributes']['data']['attributes']['payments'][0]['attributes']['metadata']['order_id'] ?? null;

    if ($order_id) {

        mysqli_query($conn, "UPDATE orders SET payment_status='paid'  WHERE id='$order_id'");

        $cart_items = mysqli_query($conn, "SELECT product_id FROM cart");

        while ($item = mysqli_fetch_assoc($cart_items)) {
            $product_id = $item['product_id'];

            mysqli_query($conn, "UPDATE products SET status='out_of_stock' WHERE id='$product_id'");
        }

        mysqli_query($conn, "DELETE FROM cart");

        $token = md5($order_id . time());

        if (!file_exists('qrcodes')) {
            mkdir('qrcodes', 0777, true);
        }

        $order_url = "https://apparelease.fit/order_view.php?token=" . $token;

        $qr_path = "qrcodes/order_" . $order_id . ".png";

        QRcode::png($order_url, $qr_path, QR_ECLEVEL_L, 5);

        mysqli_query($conn, "
            UPDATE orders 
            SET qr_token='$token', qr_code_path='$qr_path' 
            WHERE id='$order_id'
        ");
    }
}

http_response_code(200);