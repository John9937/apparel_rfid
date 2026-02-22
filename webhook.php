<?php
include 'db.php';

$payload = file_get_contents("php://input");
$event = json_decode($payload, true);

if ($event['data']['attributes']['type'] == "checkout_session.payment.paid") {

    $reference = $event['data']['attributes']['data']['attributes']['line_items'][0]['name'];
    $order_id = str_replace("ApparelEase Order #", "", $reference);


    mysqli_query($conn, "UPDATE orders SET payment_status='paid' WHERE id='$order_id'");


    $cartItems = mysqli_query($conn, "SELECT product_id FROM cart");

    while ($row = mysqli_fetch_assoc($cartItems)) {
        $pid = $row['product_id'];
        mysqli_query($conn, "UPDATE products SET status='out_of_stock' WHERE id='$pid'");
    }


    mysqli_query($conn, "DELETE FROM cart");
}