<?php
include 'db.php';

$name   = $_POST['name'];
$action = $_POST['action'];

if ($name === '' || $action === '') {
    exit;
}


if ($action === 'decrease') {

    $sql = "SELECT cart.id AS cart_id, cart.product_id, products.tag_id
            FROM cart
            JOIN products ON products.id = cart.product_id
            WHERE products.name = '$name'
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {

        $cartId = (int)$row['cart_id'];
        $productId = (int)$row['product_id'];
        $tagId = $row['tag_id'];

      
        mysqli_query($conn, "DELETE FROM cart WHERE id = $cartId");

       
        mysqli_query($conn, "UPDATE products SET status='in_stock' WHERE id=$productId");

        echo $tagId;
        exit;
    }
}



if ($action === 'remove') {

    $tagIds = [];

    $sql = "SELECT cart.id AS cart_id, cart.product_id, products.tag_id
            FROM cart
            JOIN products ON products.id = cart.product_id
            WHERE products.name = '$name'";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {

        $cartId = (int)$row['cart_id'];
        $productId = (int)$row['product_id'];
        $tagIds[] = $row['tag_id'];

     
        mysqli_query($conn, "DELETE FROM cart WHERE id = $cartId");

        
        mysqli_query($conn, "UPDATE products SET status='in_stock' WHERE id=$productId");
    }

    echo implode(",", $tagIds);
    exit;
}


echo "OK";
