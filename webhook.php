<?php
include "db.php";

$payload = file_get_contents("php://input");
$event = json_decode($payload, true);

$type = $event['data']['attributes']['type'] ?? null;

if ($type === "checkout_session.payment.paid") {

    $order_id = $event['data']['attributes']['data']['attributes']['payments'][0]['attributes']['metadata']['order_id'] ?? null;

    if ($order_id) {

     
        mysqli_query($conn, "UPDATE orders SET payment_status='paid' WHERE id='$order_id'");

        $cart_items = mysqli_query($conn, "SELECT product_id FROM cart");

        while ($item = mysqli_fetch_assoc($cart_items)) {

            $product_id = $item['product_id'];

            mysqli_query($conn, "UPDATE products SET status='out_of_stock'WHERE id='$product_id'");
        }

        mysqli_query($conn, "DELETE FROM cart");
    }
}

http_response_code(200);