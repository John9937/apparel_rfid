<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';


if (!isset($_SESSION['budget'])) {
    $res = mysqli_query($conn, "SELECT budget FROM settings WHERE id = 1");
    $row = mysqli_fetch_assoc($res);
    $_SESSION['budget'] = $row['budget'] ?? 0;
}

$res = mysqli_query($conn, "
    SELECT SUM(p.price * c.quantity) AS total
    FROM cart c
    JOIN products p ON p.id = c.product_id
");
$row = mysqli_fetch_assoc($res);
$total = $row['total'] ?? 0;


if (isset($_POST['set_budget'])) {
    $newBudget = floatval($_POST['budget']);
    $_SESSION['budget'] = $newBudget;

    if (!isset($_SESSION['budget_history'])) {
        $_SESSION['budget_history'] = [];
    }

    $_SESSION['budget_history'][] = $newBudget;

    mysqli_query($conn, "UPDATE settings SET budget = $newBudget WHERE id = 1");

    header("Location: shop.php");
    exit;
}


$sql = "
SELECT MIN(id) AS id, name, description, price, image
FROM products
WHERE 1=1
";


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

$budget = $_SESSION['budget'] ?? 0;
$percentUsed = $budget > 0 ? min(100, ($total / $budget) * 100) : 0;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Shop | ApparelEase</title>
    <link rel="stylesheet" href="shop.css">
</head>

<body>


<header class="navbar">
    <div class="navbar-container">

        <div class="nav-left">
            <a href="index.php" class="home-btn"><img src="home.png" alt="Home"></a>
            <h2>ApparelEase</h2>
        </div>

        <div class="nav-actions">
            <button class="budget-btn" onclick="openBudget()">
                ₱<?= number_format($budget, 2) ?>
            </button>

            <button class="cart-pill" onclick="openCart()">
                Cart
            </button>
        </div>

    </div>
</header>




<main class="shop-layout">

<aside class="shop-sidebar glass">
    <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
<form id="filterForm">

        <h3>Filters</h3>

 
        <div class="filter-group search-wrapper">
            <input type="text"
                name="search"
                id="searchInput"
                placeholder="Search products..."
                class="filter-search"
                value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">

            <button type="button" class="clear-search" onclick="clearSearch()">×</button>
        </div>


        <div class="filter-group">
            <h4>Category</h4>

            <label>
                <input type="radio" name="category" value="Men"
                    <?php if(isset($_GET['category']) && $_GET['category']=="Men") echo "checked"; ?>
                    onclick="toggleRadio(this)">
                Men
            </label>

            <label>
                <input type="radio" name="category" value="Women"
                    <?php if(isset($_GET['category']) && $_GET['category']=="Women") echo "checked"; ?>
                    onclick="toggleRadio(this)">
                Women
            </label>

            <label>
                <input type="radio" name="category" value="Accessories"
                    <?php if(isset($_GET['category']) && $_GET['category']=="Accessories") echo "checked"; ?>
                    onclick="toggleRadio(this)">
                Accessories
            </label>

        </div>


        <div class="filter-group">
            <h4>Price Range</h4>
            <div class="price-range">
                <input type="number"
                       name="min"
                       placeholder="Min"
                       value="<?php if(isset($_GET['min'])) echo $_GET['min']; ?>">

                <span>—</span>

                <input type="number"
                       name="max"
                       placeholder="Max"
                       value="<?php if(isset($_GET['max'])) echo $_GET['max']; ?>">
            </div>
        </div>
        <button type="button" class="see-all-btn" onclick="resetFilters()">
         SEE ALL PRODUCTS
        </button>

        <button type="submit" class="cart-btn" style="margin-top:20px;width:100%;">
            Apply Filters
        </button>

    </form>
</aside>


    <section class="shop-content glass">
        <button class="filter-toggle" onclick="toggleSidebar()">
        ☰ Filters
        </button>

        <h1 class="shop-title">New Arrivals</h1>

        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <img src="products/<?= htmlspecialchars($row['image']) ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <strong>₱<?= number_format($row['price'], 2) ?></strong>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

</main>



<div id="budgetModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeBudget()">×</button>
        <h2>Set / Change Budget</h2>

        <form method="POST">
            <input type="number" name="budget" step="0.01" required value="<?= $budget ?>">
            <button class="checkout-btn" name="set_budget">Save Budget</button>
        </form>

        <?php if (!empty($_SESSION['budget_history'])): ?>
            <hr>
            <h3>Previous Budgets</h3>
            <ul>
                <?php foreach (array_reverse($_SESSION['budget_history']) as $b): ?>
                    <li>₱<?= number_format($b, 2) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>



<div id="cartModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeCart()">×</button>
        <h2>Your Cart</h2>

        <p id="removeTagMessage"
           style="color:#dc2626;font-weight:600;margin-top:8px;display:none;">
        </p>

        <p>
            ₱<span id="cartTotal">0.00</span> /
            ₱<?= number_format($budget, 2) ?>
        </p>

        <div id="cartItems"></div>

        <form action="create_checkout.php" method="POST">
            <button class="checkout-btn">
                Checkout (PayMongo)
            </button>
        </form>
    </div>
</div>


<footer class="site-footer minimal-footer">
    <div class="footer-bottom">
        © 2026 ApparelEase. All Rights Reserved.
    </div>
</footer>

<script>

const searchInput = document.getElementById("searchInput");
const clearBtn = document.querySelector(".clear-search");

if (searchInput) {
    searchInput.addEventListener("input", function() {
        clearBtn.style.display = this.value ? "block" : "none";
    });
}

function clearSearch() {
    searchInput.value = "";
    clearBtn.style.display = "none";
}

let lastChecked = null;

function toggleRadio(radio) {
    if (lastChecked === radio) {
        radio.checked = false;
        lastChecked = null;
    } else {
        lastChecked = radio;
    }
}

function resetFilters() {


    document.getElementById("searchInput").value = "";
    clearBtn.style.display = "none";


    document.querySelectorAll('input[name="category"]').forEach(radio => {
        radio.checked = false;
    });

    lastChecked = null;

    document.querySelector('input[name="min"]').value = "";
    document.querySelector('input[name="max"]').value = "";


    fetch("fetch_products.php")
        .then(response => response.text())
        .then(data => {
            document.querySelector(".product-grid").innerHTML = data;
        });
}

let cartRefreshInterval = null;


function loadCartModal() {
    fetch('cart_modal_data.php?t=' + new Date().getTime(), { cache: 'no-store' })
        .then(res => res.json())
        .then(data => {

            const totalEl = document.getElementById('cartTotal');
            const container = document.getElementById('cartItems');

            totalEl.innerText = parseFloat(data.total).toFixed(2);
            container.innerHTML = '';

           if (data.items.length === 0) {
                container.innerHTML = '<p>Your cart is empty.</p>';

                const checkoutBtn = document.querySelector('.checkout-btn');
                if (checkoutBtn) checkoutBtn.disabled = true;

                return;
            } else {
                const checkoutBtn = document.querySelector('.checkout-btn');
                if (checkoutBtn) checkoutBtn.disabled = false;
            }

            data.items.forEach(item => {

                const div = document.createElement("div");
                div.className = "cart-item";

                div.innerHTML = `
                    <img src="products/${item.image}">
                    <div>
                        <h4>${item.name}</h4>
                        <div style="display:flex;gap:8px;margin-top:6px;">
                            <strong>${item.qty}</strong>
                            <button class="qty-btn">−</button>
                            <button class="remove-text">Remove</button>
                        </div>
                    </div>
                `;

                const decreaseBtn = div.querySelector(".qty-btn");
                const removeBtn = div.querySelector(".remove-text");

                decreaseBtn.addEventListener("click", function() {
                    updateQty(item.name, "decrease");
                });

                removeBtn.addEventListener("click", function() {
                    updateQty(item.name, "remove");
                });

                container.appendChild(div);

            });
        });
}


function updateQty(name, action) {
    fetch('cart_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `name=${encodeURIComponent(name)}&action=${action}`
    })
    .then(res => res.text())
    .then(tagIds => {
        if (tagIds) showRemoveMessage(tagIds);
        loadCartModal();
    });
}


function showRemoveMessage(tagIds) {
    const messageEl = document.getElementById("removeTagMessage");

    if (!messageEl) return;

    if (tagIds.includes(",")) {
        messageEl.innerText = `Please remove tags ${tagIds} from the cart`;
    } else {
        messageEl.innerText = `Please remove tag ${tagIds} from the cart`;
    }

    messageEl.style.display = "block";

    setTimeout(() => {
        messageEl.style.display = "none";
    }, 4000);
}


function openCart() {
    document.getElementById('cartModal').style.display = 'block';
    loadCartModal();
    cartRefreshInterval = setInterval(loadCartModal, 1000);
}

function closeCart() {
    document.getElementById('cartModal').style.display = 'none';
    clearInterval(cartRefreshInterval);
}

function openBudget() {
    document.getElementById('budgetModal').style.display = 'block';
}

function closeBudget() {
    document.getElementById('budgetModal').style.display = 'none';
}

function closeQR() {
    document.getElementById('qrModal').style.display = 'none';
}


function toggleSidebar() {
    document.querySelector('.shop-sidebar').classList.toggle('active');
}


const filterForm = document.getElementById("filterForm");

if (filterForm) {
    filterForm.addEventListener("submit", function(e) {
        e.preventDefault(); 

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();

        fetch("fetch_products.php?" + params)
            .then(response => response.text())
            .then(data => {
                document.querySelector(".product-grid").innerHTML = data;
            });
    });
}


<?php if (isset($_SESSION['qr_checkout'])): ?>
document.getElementById('qrModal').style.display = 'block';
<?php unset($_SESSION['qr_checkout']); endif; ?>

<?php if (isset($_SESSION['qr_success'])): ?>
alert("Payment successful!");
<?php unset($_SESSION['qr_success']); endif; ?>

</script>

</body>
</html>
