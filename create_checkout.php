<?php

session_start();
include 'db.php';

$secretKey = "sk_test_GET_KEY_FROM_PAYMONGO";

$res = mysqli_query($conn, "SELECT SUM(p.price * c.quantity) AS total FROM cart c 
    JOIN products p ON p.id = c.product_id");

$row = mysqli_fetch_assoc($res);
$totalPeso = $row['total'] ?? 0;

if ($totalPeso <= 0) {
     echo "<script>alert('Cart is empty.');window.history.back();</script>";
    exit;
}

$total = intval($totalPeso * 100);

mysqli_query($conn, "INSERT INTO orders (total_amount, payment_status, created_at)
    VALUES ($totalPeso, 'pending', NOW())
");

$order_id = mysqli_insert_id($conn);

$cart = mysqli_query($conn, "SELECT c.product_id, c.quantity, p.price 
    FROM cart c JOIN products p ON p.id = c.product_id");

while ($item = mysqli_fetch_assoc($cart)) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];

    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES ($order_id, $product_id, $quantity, $price)");
}

$data = [
    "data" => [
        "attributes" => [
            "line_items" => [
                [
                    "currency" => "PHP",
                    "amount" => $total,
                    "name" => "ApparelEase Order",
                    "quantity" => 1
                ]
            ],
            "payment_method_types" => ["gcash", "card", "paymaya"],
            "success_url" => "https://apparelease.fit/qr_receipt.php?order_id=$order_id",
            "cancel_url" => "https://apparelease.fit/shop.php",
            "metadata" => [
                "order_id" => $order_id
            ]
        ]
    ]
];

$ch = curl_init("https://api.paymongo.com/v1/checkout_sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Basic " . base64_encode($secretKey . ":")
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Curl error: " . curl_error($ch);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if (isset($result['data']['attributes']['checkout_url'])) {
    header("Location: " . $result['data']['attributes']['checkout_url']);
    exit;
} else {
    echo "<pre>";
    print_r($result);
}