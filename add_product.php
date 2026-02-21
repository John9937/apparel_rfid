<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

if (isset($_POST['add'])) {

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $rfid_uid = $_POST['rfid_uid'];
    $tag_id = $_POST['tag_id'];
    $category = $_POST['category'];
    $status = $_POST['status'];

    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];

    $uploadPath = "products/" . $imageName;

    move_uploaded_file($imageTmp, $uploadPath);

    $sql = "INSERT INTO products 
            (name, description, price, image, rfid_uid, status, tag_id, category)
            VALUES 
            ('$name', '$description', '$price', '$imageName', '$rfid_uid', '$status', '$tag_id', '$category')";

    if (mysqli_query($conn, $sql)) {
        header("Location: admin.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="add_product.css">
</head>
<body>

<div class="container">

    <h2>Add Product</h2>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-grid">

           
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" required>
            </div>

            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>RFID UID</label>
                <input type="text" name="rfid_uid" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="in_stock" selected>in_stock</option>
                    <option value="out_of_stock">out_of_stock</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tag ID</label>
                <input type="text" name="tag_id" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="Men">Men</option>
                    <option value="Women">Women</option>
                    <option value="Accessories">Accessories</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label>Image Filename</label>
                <input type="file" name="image" accept="image/*" required>
            </div>

        </div>

        <div class="buttons">
            <a href="admin.php" class="btn-cancel">Cancel</a>
            <button type="submit" name="add" class="btn-save">Add Product</button>
        </div>

    </form>

</div>

</body>
</html>