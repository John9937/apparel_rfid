<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: remove_product.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remove Product</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-wrapper">
    <div class="admin-header">
        <h1>Remove Product</h1>
        <a href="admin.php" class="logout-btn">Back</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td>₱<?= number_format($row['price'],2) ?></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>"
                        class="remove-product-btn"
                        onclick="return confirm('Delete this product?')">
                        Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>