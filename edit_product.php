<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

$id = (int)$_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $imageName = $data['image'];

    if(isset($_FILES['image']) && $_FILES['image']['name'] != ""){
        $imageName = $_FILES['image']['name'];
        $tempName = $_FILES['image']['tmp_name'];
        move_uploaded_file($tempName, "products/" . $imageName);
    }

    mysqli_query($conn, "
        UPDATE products SET
        name='$name',
        description='$description',
        price='$price',
        image='$imageName',
        category='$category'
        WHERE id=$id
    ");

    header("Location: add_product.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product | ApparelEase</title>
    <link rel="stylesheet" href="edit_product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="edit-container">

    <h1>Edit Product</h1>

    <form method="POST" enctype="multipart/form-data" class="edit-form">

        <div class="form-grid">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?= $data['name'] ?>" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" value="<?= $data['price'] ?>" required>
            </div>

            <div class="form-group full">
                <label>Description</label>
                <textarea name="description" required><?= $data['description'] ?></textarea>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="Men" 
                        <?= $data['category'] == 'Men' ? 'selected' : '' ?>>
                        Men
                    </option>

                    <option value="Women" 
                        <?= $data['category'] == 'Women' ? 'selected' : '' ?>>
                        Women
                    </option>

                    <option value="Accessories" 
                        <?= $data['category'] == 'Accessories' ? 'selected' : '' ?>>
                        Accessories
                    </option>
                </select>
            </div>

        </div>

        <div class="form-group full">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
        </div>

        <div class="form-actions">
            <a href="add_product.php" class="cancel-btn">Cancel</a>
            <button type="submit" name="update" class="save-btn">Save Changes</button>
        </div>

    </form>

</div>

</body>
</html>
