<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel | ApparelEase</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<script>
    function refreshTable(){
        fetch("fetch_products_admin.php")
            .then(response => response.text())
            .then(data => {
                document.getElementById("productTable").innerHTML = data;
            });
    }

   
    setInterval(refreshTable, 2000);
</script>

<body>

<div class="admin-wrapper">

    <div class="admin-header">
        <h1>Product Database</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>RFID UID</th>
                    <th>Status</th>
                    <th>Tag ID</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="productTable">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['description'] ?></td>
                    <td>₱<?= number_format($row['price'],2) ?></td>
                    <td><?= $row['image'] ?></td>
                    <td><?= $row['rfid_uid'] ?></td>
                    <td><span class="status-badge <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                    <td><?= $row['tag_id'] ?></td>
                    <td><?= $row['category'] ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>

            </tbody>

        </table>
        <div class="table-footer">
            <a href="add_product.php" class="add-product-btn">Add Product</a>
            <a href="remove_product.php" class="remove-product-btn">Remove Product</a>
        </div>
    </div>

</div>

</body>
</html>
