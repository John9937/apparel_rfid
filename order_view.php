<?php
session_start();

if (!isset($_SESSION['admin'])) {
    echo "<script>alert('Access Denied.');</script>";
    exit;
}

include "db.php";

$token = $_GET['token'] ?? null;

if (!$token) {
    die("Invalid QR.");
}

$order = mysqli_query($conn, "SELECT * FROM orders WHERE qr_token='$token' AND payment_status='paid'");

if (mysqli_num_rows($order) == 0) {
    die("Order not found or unpaid.");
}

$order_data = mysqli_fetch_assoc($order);

$alreadyClaimed = false;

if ($order_data['claimed'] == 1) {
    $alreadyClaimed = true;
}

if (!$alreadyClaimed) {
    mysqli_query($conn, "UPDATE orders SET claimed = 1 WHERE id = '".$order_data['id']."'");
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Order Verification</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #4b2e14, #8b5a2b);
    min-height: 100vh;
    padding: 40px 20px;
}


.receipt {
    position: relative;
    background: rgba(255,255,255,0.95);
    padding: 40px;
    border-radius: 25px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    text-align: center;
}

.receipt h2 {
    color: #7a3e00;
    margin-bottom: 10px;
}

.receipt p {
    margin: 5px 0;
    color: #444;
}

.items {
    margin-top: 30px;
    text-align: left;
}

.item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    background: #f9f6f2;
    padding: 15px;
    border-radius: 15px;
}

.item img {
    width: 90px;
    border-radius: 10px;
    margin-right: 15px;
}

.item-details {
    flex: 1;
}

.receipt {
    margin: 0 auto;
}
.item-details strong {
    color: #7a3e00;
}

.price {
    font-weight: 600;
    color: #333;
}
</style>
</head>

<script>
<?php if ($alreadyClaimed): ?>
    alert("This order has already been claimed.");
<?php else: ?>
    alert("Order successfully verified.");
<?php endif; ?>
</script>

<body>

<div class="receipt">
    <h2>Order #<?php echo $order_data['id']; ?></h2>
    <p><strong>Total:</strong> ₱<?php echo $order_data['total_amount']; ?></p>
    <p><strong>Date:</strong> <?php echo $order_data['created_at']; ?></p>
    
    <div class="items">
        <h3>Items Purchased</h3>

        <?php
        $items = mysqli_query($conn, "SELECT products.name, products.price, products.image,
        order_items.quantity FROM order_items JOIN products ON products.id = order_items.product_id
        WHERE order_items.order_id='".$order_data['id']."'");

        $total = 0;

        while($item = mysqli_fetch_assoc($items)){

            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;

            echo "<div class='item'>";
            echo "<img src='products/".$item['image']."'>";
            echo "<div class='item-details'>";
            echo "<strong>".$item['name']."</strong><br>";
            echo "<span>₱".$item['price']." × ".$item['quantity']."</span><br>";
            echo "<strong style='color:#7a3e00;'>Subtotal: ₱".$subtotal."</strong>";
            echo "</div>";
            echo "</div>";
        }
        
        echo "<hr style='margin:25px 0;'>";
        echo "<div style='text-align:right; font-size:18px;'>";
        echo "<strong>Total: ₱".$total."</strong>";
        echo "</div>";
        ?>
    </div>
</div>

</body>
</html>