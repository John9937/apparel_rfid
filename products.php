<?php
include 'db.php';


$id = $_GET['id'];

if (empty($id)) {
    die("Product not found.");
}


$sql = "SELECT * FROM products WHERE id = ?";
$query = sqlsrv_query($conn, $sql, [$id]);

if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}

$product = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);


if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?= htmlspecialchars($product['name']) ?></title>
<link rel="stylesheet" href="shop.css">
</head>
<body>

<div class="shop-container">
    <img src="products/<?= htmlspecialchars($product['image']) ?>" style="max-width:400px;">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p><?= htmlspecialchars($product['description']) ?></p>
    <h2>₱<?= number_format($product['price'], 2) ?></h2>
    <p>Status: <strong><?= htmlspecialchars($product['status']) ?></strong></p>
    <a href="shop.php">← Back to Shop</a>
</div>

</body>
</html>
