<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit;
}

/* ===================== DASHBOARD COUNTS ===================== */

// TOTAL PRODUCTS
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];

// IN STOCK
$inStock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM product_items WHERE status='in_stock'"))['total'];

// OUT OF STOCK
$outStock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM product_items WHERE status='out_of_stock'"))['total'];

// IN CART
$inCart = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM product_items WHERE status='in_cart'"))['total'];


/* ===================== TABLE DATA ===================== */

// ALL PRODUCTS
$products = mysqli_query($conn, "SELECT 
    p.name,
    p.price,

    -- Available per size
    SUM(CASE WHEN pi.size='Small' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS small,
    SUM(CASE WHEN pi.size='Medium' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS medium,
    SUM(CASE WHEN pi.size='Large' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS large,

    -- ALL items (including unavailable)
    COUNT(pi.id) AS total_registered,

    -- ONLY available items
    SUM(CASE WHEN pi.status='in_stock' THEN 1 ELSE 0 END) AS available_total

FROM products p
LEFT JOIN product_items pi ON pi.product_id = p.id
GROUP BY p.id
");
// IN STOCK LIST
$inStockList = mysqli_query($conn, "SELECT pi.rfid_uid, pi.size, p.name FROM product_items pi
    LEFT JOIN products p ON pi.product_id = p.id 
    WHERE pi.status='in_stock' ORDER BY pi.id DESC
");

// OUT OF STOCK LIST
$outStockList = mysqli_query($conn, "SELECT pi.rfid_uid, pi.size, p.name FROM product_items pi
    LEFT JOIN products p ON pi.product_id = p.id 
    WHERE pi.status='out_of_stock' ORDER BY pi.id DESC
");

// IN CART LIST
$inCartList = mysqli_query($conn, "SELECT pi.rfid_uid, pi.size, p.name FROM product_items pi
    LEFT JOIN products p ON pi.product_id = p.id 
    WHERE pi.status='in_cart' ORDER BY pi.id DESC
");

// FILTER DROPDOWN DATA
$productFilter1 = mysqli_query($conn, "SELECT DISTINCT name FROM products");
$productFilter2 = mysqli_query($conn, "SELECT DISTINCT name FROM products");
$productFilter3 = mysqli_query($conn, "SELECT DISTINCT name FROM products");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel | ApparelEase</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">ApparelEase</div>

        <ul class="menu">
            <li class="active"><a href="admin.php">Dashboard</a></li>
            <li><a href="add_product.php">Products</a></li>
            <li><a href="add_stock.php">Stocks</a></li>
            <li><a href="order_page.php">Orders</a></li>
        </ul>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <!-- TOPBAR -->
        <div class="topbar">
            <div>
                <h2>Dashboard</h2>
                <p>Welcome, <?= $_SESSION['name']; ?> 👋</p>
            </div>

            <div class="actions">
                <a href="admin_logout.php" class="btn btn-brown">Logout</a>
            </div>
        </div>

        <!-- DASHBOARD CARDS -->
        <div class="dashboard-cards">

            <div class="dash-card">
                <h3>All Products</h3>
                <p id="totalProducts"><?= $totalProducts ?></p>
            </div>

            <div class="dash-card red">
                <h3>Out of Stock</h3>
                <p id="outStock"><?= $outStock ?></p>
            </div>

            <div class="dash-card green">
                <h3>In Stock</h3>
                <p id="inStock"><?= $inStock ?></p>
            </div>

            <div class="dash-card orange">
                <h3>In Cart</h3>
                <p id="inCart"><?= $inCart ?></p>
            </div>

        </div>

        <div class="dashboard-grid">

            <!-- ALL PRODUCTS -->
            <div class="card">
                <h3>All Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Small</th>
                            <th>Medium</th>
                            <th>Large</th>
                            <th class="center-bold">Available</th>
                            <th class="center-bold">Total Registered Products</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><?= $row['name'] ?></td>
                            <td>₱<?= number_format($row['price'],2) ?></td>
                            <td><?= $row['small'] ?></td>
                            <td><?= $row['medium'] ?></td>
                            <td><?= $row['large'] ?></td>
                            <td class="center-bold"><?= $row['available_total'] ?></td>
                            <td class="center-bold"><?= $row['total_registered'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- OUT OF STOCK -->
            <div class="card red-card">
                 <div class="filter-group" style="justify-content:space-between; margin-bottom:10px;">

                <h3>Unavailable Products</h3>

                <div class="filter-group">
                    
                    <div class="size-filter">
                        <label><input type="checkbox" value="Small"> S</label>
                        <label><input type="checkbox" value="Medium"> M</label>
                        <label><input type="checkbox" value="Large"> L</label>
                    </div>

                    <select id="productFilterOut">
                        <option value="">All</option>
                        <?php 
                        $pf = mysqli_query($conn, "SELECT DISTINCT name FROM products");
                        while($p = mysqli_fetch_assoc($pf)): ?>
                            <option value="<?= $p['name'] ?>"><?= $p['name'] ?></option>
                        <?php endwhile; ?>
                    </select>

                </div>

            </div>  
                <table>
                    <thead>
                        <tr>
                            <th>RFID UID</th>
                            <th>Product</th>
                            <th>Size</th>
                        </tr>
                    </thead>

                    <tbody id="outStockTable">
                        <?php while($row = mysqli_fetch_assoc($outStockList)): ?>
                        <tr>
                            <td><?= $row['rfid_uid'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['size'] ?? '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- IN STOCK WITH FILTER -->
            <div class="card green-card">

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
    
                <h3>Available Products</h3>

                <div class="filter-group">
                    
                    <div class="size-filter">
                        <label><input type="checkbox" value="Small"> S</label>
                        <label><input type="checkbox" value="Medium"> M</label>
                        <label><input type="checkbox" value="Large"> L</label>
                    </div>

                    <select id="productFilterIn">
                        <option value="">All</option>
                        <?php while($p = mysqli_fetch_assoc($productFilter2)): ?>
                            <option value="<?= $p['name'] ?>"><?= $p['name'] ?></option>
                        <?php endwhile; ?>
                    </select>

                </div>

            </div>

                <table>
                    <thead>
                        <tr>
                            <th>RFID UID</th>
                            <th>Product</th>
                            <th>Size</th>
                        </tr>
                    </thead>

                    <tbody id="inStockTable">
                        <?php while($row = mysqli_fetch_assoc($inStockList)): ?>
                        <tr>
                            <td><?= $row['rfid_uid'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['size'] ?? '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- IN CART -->
        <div class="card orange-card">

        <div class="filter-group" style="justify-content:space-between; margin-bottom:10px;">
            
            <h3>In Cart Products</h3>

            <div class="filter-group">
                
                <div class="size-filter">
                    <label><input type="checkbox" value="Small"> S</label>
                    <label><input type="checkbox" value="Medium"> M</label>
                    <label><input type="checkbox" value="Large"> L</label>
                </div>

                <select id="productFilterCart">
                    <option value="">All</option>
                    <?php 
                    $pf = mysqli_query($conn, "SELECT DISTINCT name FROM products");
                    while($p = mysqli_fetch_assoc($pf)): ?>
                        <option value="<?= $p['name'] ?>"><?= $p['name'] ?></option>
                    <?php endwhile; ?>
                </select>

            </div>

        </div>

            <table>
                <thead>
                    <tr>
                        <th>RFID UID</th>
                        <th>Product</th>
                        <th>Size</th>
                    </tr>
                </thead>

                <tbody id="inCartTable">
                    <?php while($row = mysqli_fetch_assoc($inCartList)): ?>
                    <tr>
                        <td><?= $row['rfid_uid'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['size'] ?? '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>

<!-- ONLY SHOWING SCRIPT FIX (everything else stays SAME) -->
<script>
function loadDashboard() {
    fetch("dashboard_data.php", {
        cache: "no-store"
    })
    .then(res => res.json())
    .then(data => {

        // ================= COUNTS =================
        document.getElementById("totalProducts").innerText = data.totalProducts;
        document.getElementById("inStock").innerText = data.inStock;
        document.getElementById("outStock").innerText = data.outStock;
        document.getElementById("inCart").innerText = data.inCart;

        // ================= ALL PRODUCTS TABLE =================
        const productTable = document.querySelector(".card table tbody");
        productTable.innerHTML = "";

        data.products.forEach(p => {
            productTable.innerHTML += `
                <tr>
                    <td>${p.name}</td>
                    <td>₱${parseFloat(p.price).toFixed(2)}</td>
                    <td>${p.small}</td>
                    <td>${p.medium}</td>
                    <td>${p.large}</td>
                    <td class="center-bold">${p.available_total}</td>
                    <td class="center-bold">${p.total_registered}</td>
                </tr>
            `;
        });

        // ================= OUT OF STOCK =================
        const outTable = document.querySelector(".red-card table tbody");
        outTable.innerHTML = "";

        data.outStockList.forEach(item => {
            outTable.innerHTML += `
                <tr>
                    <td>${item.rfid_uid}</td>
                    <td>${item.name}</td>
                    <td>${item.size}</td>
                </tr>
            `;
        });

        // ================= IN CART =================
        const cartTable = document.querySelector(".orange-card table tbody");
        if (cartTable) {
            cartTable.innerHTML = "";

            data.inCartList.forEach(item => {
                cartTable.innerHTML += `
                    <tr>
                        <td>${item.rfid_uid}</td>
                        <td>${item.name}</td>
                        <td>${item.size}</td>
                    </tr>
                `;
            });
        }

    });
}

/* ================= FILTER EVENTS ================= */

document.querySelectorAll(".green-card input[type='checkbox']")
    .forEach(cb => cb.addEventListener("change", filterAvailable));

document.querySelectorAll(".red-card input[type='checkbox']")
    .forEach(cb => cb.addEventListener("change", filterUnavailable));

document.querySelectorAll(".orange-card input[type='checkbox']")
    .forEach(cb => cb.addEventListener("change", filterCart));

document.getElementById("productFilterIn").addEventListener("change", filterAvailable);
document.getElementById("productFilterOut").addEventListener("change", filterUnavailable);
document.getElementById("productFilterCart").addEventListener("change", filterCart);

/* ================= FILTER FUNCTIONS ================= */

function filterAvailable() {
    let selectedProduct = document.getElementById("productFilterIn").value.toLowerCase();
    let rows = document.querySelectorAll("#inStockTable tr");

    let sizeFilter = getSelectedSizes(document.querySelector(".green-card"));

    rows.forEach(row => {
        let product = row.children[1].innerText.toLowerCase();
        let size = row.children[2].innerText.toLowerCase();

        let matchProduct = (selectedProduct === "" || product === selectedProduct);
        let matchSize = (sizeFilter.length === 0 || sizeFilter.includes(size));

        row.style.display = (matchProduct && matchSize) ? "" : "none";
    });
}

function filterUnavailable() {
    let selectedProduct = document.getElementById("productFilterOut").value.toLowerCase();
    let rows = document.querySelectorAll("#outStockTable tr");

    let sizeFilter = getSelectedSizes(document.querySelector(".red-card"));

    rows.forEach(row => {
        let product = row.children[1].innerText.toLowerCase();
        let size = row.children[2].innerText.toLowerCase();

        let matchProduct = (selectedProduct === "" || product === selectedProduct);
        let matchSize = (sizeFilter.length === 0 || sizeFilter.includes(size));

        row.style.display = (matchProduct && matchSize) ? "" : "none";
    });
}

function filterCart() {
    let selectedProduct = document.getElementById("productFilterCart").value.toLowerCase();
    let rows = document.querySelectorAll("#inCartTable tr");

    let sizeFilter = getSelectedSizes(document.querySelector(".orange-card"));

    rows.forEach(row => {
        let product = row.children[1].innerText.toLowerCase();
        let size = row.children[2].innerText.toLowerCase();

        let matchProduct = (selectedProduct === "" || product === selectedProduct);
        let matchSize = (sizeFilter.length === 0 || sizeFilter.includes(size));

        row.style.display = (matchProduct && matchSize) ? "" : "none";
    });
}

/* ================= SIZE HELPER ================= */

function getSelectedSizes(container) {
    return Array.from(container.querySelectorAll("input[type='checkbox']:checked"))
        .map(cb => cb.value.toLowerCase());
}

/* ================= AUTO REFRESH ================= */

setInterval(() => {
    setTimeout(loadDashboard, 300);
}, 2000);

loadDashboard();
</script>

</html>