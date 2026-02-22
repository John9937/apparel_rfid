<?php
include 'db.php';

$sql = "
SELECT MIN(id) AS id, name, description, price, image
FROM products
WHERE 1=1
";

/* SEARCH */
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $_GET['search'];
    $sql .= " AND name LIKE '%$search%'";
}

/* CATEGORY */
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = $_GET['category'];
    $sql .= " AND category = '$category'";
}

/* MIN PRICE */
if (isset($_GET['min']) && $_GET['min'] != "") {
    $min = $_GET['min'];
    $sql .= " AND price >= $min";
}

/* MAX PRICE */
if (isset($_GET['max']) && $_GET['max'] != "") {
    $max = $_GET['max'];
    $sql .= " AND price <= $max";
}

$sql .= " GROUP BY name, description, price, image";

$products = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($products)) {
    echo '
        <div class="product-card">
            <img src="products/'.$row['image'].'">
            <h3>'.$row['name'].'</h3>
            <p>'.$row['description'].'</p>
            <strong>₱'.number_format($row['price'],2).'</strong>
        </div>
    ';
}
?>
