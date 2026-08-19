<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// ADD PRODUCT
if (isset($_POST['add'])) {

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];

    move_uploaded_file($imageTmp, "products/" . $imageName);

    mysqli_query($conn, "
        INSERT INTO products (name, description, price, image, category)
        VALUES ('$name','$description','$price','$imageName','$category')
    ");

    header("Location: add_product.php?success=1");
    exit;
}

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="add_product.css">
</head>

<body>

<div class="page">

    <!-- LEFT -->
    <div class="card">
        <h2>Add Product</h2>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Men">Men</option>
                    <option value="Women">Women</option>
                    <option value="Accessories">Accessories</option>
                </select>
            </div>

            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" required>
            </div>

            <div class="buttons">
                <a href="admin.php" class="btn-cancel">Cancel</a>
                <button type="submit" name="add" class="btn-save">Add Product</button>
            </div>

        </form>
    </div>

    <!-- RIGHT -->
    <div class="card">

        <!-- ✅ FIXED HEADER WITH SEARCH -->
        <div class="card-header">
            <h2>All Products</h2>
            <input type="text" id="searchInput" placeholder="Search product..." class="search-box">
        </div>

        <!-- TOAST -->
        <div id="toast" class="toast"></div>

        <div class="product-list">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($row = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><img src="products/<?= $row['image'] ?>" width="50"></td>
                        <td><?= $row['name'] ?></td>
                        <td>₱<?= number_format($row['price'],2) ?></td>
                        <td><?= $row['category'] ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>

                            <a href="#" class="btn-delete open-delete" data-id="<?= $row['id'] ?>">
                                Remove
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal">
    <div class="modal-box">
        <h3>Delete Product</h3>
        <p>Are you sure you want to delete this product?</p>

        <div class="modal-actions">
            <button id="cancelDelete" class="btn-cancel">Cancel</button>
            <a id="confirmDelete" href="#" class="btn-delete">Delete</a>
        </div>
    </div>
</div>

<script>

// ✅ LIVE SEARCH
document.getElementById("searchInput").addEventListener("keyup", function () {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll(".product-list tbody tr");

    rows.forEach(row => {
        const name = row.children[1].innerText.toLowerCase();

        if (name.includes(value)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

// TOAST
function showToast(message) {
    const toast = document.getElementById("toast");
    toast.innerText = message;
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 2000);
}

// URL TOAST
<?php if(isset($_GET['success'])): ?>
    showToast("Item Added Successfully");
<?php endif; ?>

<?php if(isset($_GET['deleted'])): ?>
    showToast("Product Deleted Successfully");
<?php endif; ?>

// CLEAN URL
if (window.location.search) {
    window.history.replaceState({}, document.title, "add_product.php");
}

// DELETE MODAL
const modal = document.getElementById("deleteModal");
const confirmBtn = document.getElementById("confirmDelete");
const cancelBtn = document.getElementById("cancelDelete");

document.querySelectorAll(".open-delete").forEach(btn => {
    btn.addEventListener("click", function(e){
        e.preventDefault();
        const id = this.getAttribute("data-id");

        confirmBtn.href = "delete_product.php?id=" + id;
        modal.classList.add("show");
    });
});

cancelBtn.onclick = () => modal.classList.remove("show");

modal.onclick = (e) => {
    if (e.target === modal) modal.classList.remove("show");
};

</script>

</body>
</html>