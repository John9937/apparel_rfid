<?php
include "db.php";

$id = (int)$_POST['id'];

mysqli_query($conn, "DELETE FROM product_items WHERE id = $id");

echo "SUCCESS";