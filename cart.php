<?php
include 'db.php';

$sql = "SELECT products.name,products.price,products.image,SUM(cart.quantity) AS qty
        FROM cart JOIN products ON products.id = cart.product_id 
        GROUP BY products.name, products.price, products.image";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Your Cart</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f8;
}
.cart-item {
    background: white;
    padding: 15px;
    margin: 20px auto;
    width: 400px;
    border-radius: 10px;
}
.cart-item img {
    width: 100px;
}
</style>
</head>

<body>

<h2 style="text-align:center;">Your Cart</h2>

<?php
$total = 0;

while ($row = mysqli_fetch_assoc($result)):
    $subtotal = $row['price'] * $row['qty'];
    $total += $subtotal;
?>
<div class="cart-item">
    <img src="products/<?= htmlspecialchars($row['image']) ?>">
    <h3><?= htmlspecialchars($row['name']) ?></h3>
    <p>Quantity: <?= $row['qty'] ?></p>
    <p>Subtotal: ₱<?= number_format($subtotal, 2) ?></p>
</div>
<?php endwhile; ?>

<h3 style="text-align:center;">
    Total: ₱<?= number_format($total, 2) ?>
</h3>

</body>
</html>
