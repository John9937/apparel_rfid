<?php
include 'db.php';

$sql = "SELECT 
p.id,
p.name,
p.description,
p.price,
p.image,
COUNT(pi.id) as stock,
GROUP_CONCAT(DISTINCT pi.color) as colors
FROM products p
LEFT JOIN product_items pi ON p.id = pi.product_id
GROUP BY p.id";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $_GET['search'];
    $sql .= " AND name LIKE '%$search%'";
}

if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = $_GET['category'];
    $sql .= " AND category = '$category'";
}

if (isset($_GET['min']) && $_GET['min'] != "") {
    $min = $_GET['min'];
    $sql .= " AND price >= $min";
}

if (isset($_GET['max']) && $_GET['max'] != "") {
    $max = $_GET['max'];
    $sql .= " AND price <= $max";
}

$sql .= " GROUP BY name, description, price, image";

$products = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($products)) {

    $name = htmlspecialchars($row['name'], ENT_QUOTES);
    $desc = htmlspecialchars($row['description'], ENT_QUOTES);
    $price = number_format($row['price'], 2);
    $image = htmlspecialchars($row['image'], ENT_QUOTES);

    echo '
        <div class="product-card"
            onclick="openProductModal(
                \''.$name.'\',
                \''.$desc.'\',
                \''.$price.'\',
                \''.$image.'\',
                \'N/A\',
                \'N/A\'
            )">

            <img src="products/'.$image.'">
            <h3>'.$name.'</h3>
            <p>'.$desc.'</p>
            <strong>₱'.$price.'</strong>
        </div>
    ';
}
?>