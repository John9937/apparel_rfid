<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php';

$res = mysqli_query($conn, "SELECT budget FROM settings WHERE id = 1");
$row = mysqli_fetch_assoc($res);
$budget = $row['budget'] ?? 0;

$error_check = mysqli_query($conn, "SELECT last_error FROM settings WHERE id=1");
$error_row = mysqli_fetch_assoc($error_check);
$rfid_error = $error_row['last_error'] ?? null;



$res = mysqli_query($conn, "SELECT SUM(p.price * c.quantity) AS total FROM cart c
    JOIN products p ON p.id = c.product_id");
    
$row = mysqli_fetch_assoc($res);
$total = $row['total'] ?? 0;


if (isset($_POST['set_budget'])) {
    $newBudget = floatval($_POST['budget']);

    mysqli_query($conn, "UPDATE settings SET budget = $newBudget WHERE id = 1");

    header("Location: shop.php");
    exit;
}

$sql = "SELECT 
    p.id,
    p.name,
    p.description,
    p.price,
    p.image,

    COUNT(CASE WHEN pi.status='in_stock' THEN 1 END) AS stock,

    SUM(CASE WHEN pi.size='Small' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS small_stock,
    SUM(CASE WHEN pi.size='Medium' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS medium_stock,
    SUM(CASE WHEN pi.size='Large' AND pi.status='in_stock' THEN 1 ELSE 0 END) AS large_stock

FROM products p
LEFT JOIN product_items pi 
    ON pi.product_id = p.id

WHERE 1=1";

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


$sql .= " GROUP BY p.id";

$products = mysqli_query($conn, $sql);


$percentUsed = $budget > 0 ? min(100, ($total / $budget) * 100) : 0;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Shop | ApparelEase</title>
    <link rel="stylesheet" href="shop.css">
</head>

<div id="productModal" class="modal">
  <div class="modal-content product-modal">

    <button class="modal-close" onclick="closeProductModal()">×</button>

    <img id="modalImage" class="modal-img">

    <h2 id="modalName"></h2>
    <p id="modalDescription" class="modal-desc"></p>
    
    <div class="size-selector">
        <button onclick="setSize('Small', this)">S</button>
        <button onclick="setSize('Medium', this)">M</button>
        <button onclick="setSize('Large', this)">L</button>
    </div>

    <div class="modal-details">
        <p><strong>Price:</strong> ₱<span id="modalPrice"></span></p>
        <p><strong>Stock:</strong> <span id="modalStock"></span></p>
    </div>



  </div>
</div>

<body>
<div id="globalToast" class="global-toast"></div>

<header class="navbar">
    <div class="navbar-container">

        <div class="nav-left">
            <a href="index.php" class="home-btn"><img src="home.png" alt="Home"></a>
            <h2>ApparelEase</h2>
        </div>

        <div class="nav-actions">
            <h5>SET BUDGET :<h5> 
            <button class="budget-btn" onclick="openBudget()">
                ₱<?= number_format($budget, 2) ?>
            </button>

            <button class="cart-pill" onclick="openCart()">
                Cart
                <span id="cartCount" class="cart-badge">0</span>
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
            <div class="product-card"
            onclick="openProductModal(
                '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>',
                '<?= number_format($row['price'],2) ?>',
                '<?= htmlspecialchars($row['image'], ENT_QUOTES) ?>',
                <?= $row['stock'] ?? 0 ?>,
                <?= $row['small_stock'] ?? 0 ?>,
                <?= $row['medium_stock'] ?? 0 ?>,
                <?= $row['large_stock'] ?? 0 ?>
            )">

                <img src="products/<?= htmlspecialchars($row['image']) ?>">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <strong>₱<?= number_format($row['price'], 2) ?></strong>
            </div>
        <?php endwhile; ?>
        </div>
    </section>

</main>

<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div id="budgetModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeBudget()">×</button>
        <h2>Set / Change Budget</h2>

        <form method="POST">
            <input type="number" name="budget" step="0.01" required value="<?= $budget ?>">
            <button class="checkout-btn" name="set_budget">Save Budget</button>
        </form>

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
                Go To checkout
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


// 🔥 NEW: BADGE FUNCTION (SEPARATED)
function updateCartBadge() {
    fetch('cart_modal_data.php?t=' + new Date().getTime(), { cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
            const countEl = document.getElementById("cartCount");

            let totalQty = 0;
            data.items.forEach(item => {
                totalQty += parseInt(item.qty);
            });

            if (totalQty > 0) {
                countEl.innerText = totalQty > 99 ? "99+" : totalQty;
                countEl.style.display = "flex";
            } else {
                countEl.style.display = "none";
            }
        });
}


// 🔥 UPDATED CART MODAL (NO BADGE LOGIC HERE)
function loadCartModal() {
    fetch('cart_modal_data.php?t=' + new Date().getTime(), {
        cache: 'no-store'
    })
    .then(res => res.json())
    .then(data => {

        console.log("UPDATED CART:", data); // 👈 DEBUG

        const totalEl = document.getElementById('cartTotal');
        const container = document.getElementById('cartItems');

        totalEl.innerText = parseFloat(data.total).toFixed(2);
        container.innerHTML = '';

        if (data.items.length === 0) {
            container.innerHTML = '<p>Your cart is empty.</p>';
            document.querySelector('.checkout-btn').disabled = true;
            return;
        }

        document.querySelector('.checkout-btn').disabled = false;

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

            div.querySelector(".qty-btn").onclick = () => {
                updateQty(item.product_id, "decrease");
            };

            div.querySelector(".remove-text").onclick = () => {
                updateQty(item.product_id, "remove");
            };

            container.appendChild(div);
        });
    });
}

function updateQty(productId, action) {

    console.log("SENDING:", productId, action);

    fetch('cart_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `product_id=${productId}&action=${action}`
    })
    .then(res => res.text())
    .then(res => {

        console.log("RESPONSE:", res);

        if (res && res !== "NOT_FOUND") {
            showRemoveMessage(res);
        }

        // 🔥 FORCE SYNC
        loadCartModal();
        updateCartBadge();

        // 🔥 ADD THIS
        setTimeout(() => {
            fetch("dashboard_data.php", { cache: "no-store" });
        }, 300);
    });
}
function showRemoveMessage(response) {

    const messageEl = document.getElementById("removeTagMessage");

    if (!messageEl) return;

    let parts = response.split(":");

    let type = parts[0];
    let rfids = parts[1] || "";

    if (type === "REMOVED_ONE") {
        messageEl.innerText = `Remove item with RFID: ${rfids}`;
    }

    if (type === "REMOVED_ALL") {
        messageEl.innerText = `Remove these RFIDs: ${rfids}`;
    }

    messageEl.style.display = "block";

    setTimeout(() => {
        messageEl.style.display = "none";
    }, 5000);
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
    const sidebar = document.querySelector('.shop-sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');

    document.body.classList.toggle('no-scroll');
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


// 🔥 AUTO LOAD BADGE ON PAGE LOAD
updateCartBadge();

// 🔥 AUTO REFRESH LIKE REAL APPS
setInterval(updateCartBadge, 2000);


<?php if (isset($_SESSION['qr_checkout'])): ?>
document.getElementById('qrModal').style.display = 'block';
<?php unset($_SESSION['qr_checkout']); endif; ?>

<?php if (isset($_SESSION['qr_success'])): ?>
alert("Payment successful!");
<?php unset($_SESSION['qr_success']); endif; ?>

setInterval(function() {
    fetch('rfid_error.php')
        .then(res => res.text())
        .then(data => {

            if (data === "SET_BUDGET_FIRST") {
                showToast("Please set budget first", "warning");
            }

            if (data === "BUDGET_EXCEEDED") {
                showToast("Budget exceeded", "error");
            }

            if (data === "ALREADY_SCANNED") {
                showToast("Item already scanned", "warning");
            }

            else if (data === "RFID_NOT_REGISTERED") {
                showToast("RFID tag is not registered", "error");
            }

            else if (data === "RFID_UNAVAILABLE") {
                showToast("RFID Unavailable", "error");
            }

        });
}, 1500);

let sizeStocks = {
    Small: 0,
    Medium: 0,
    Large: 0
};

function openProductModal(name, description, price, image, total, small, medium, large) {

    document.getElementById("modalName").innerText = name;
    document.getElementById("modalDescription").innerText = description;
    document.getElementById("modalPrice").innerText = price;
    document.getElementById("modalImage").src = "products/" + image;

    sizeStocks.Small = small;
    sizeStocks.Medium = medium;
    sizeStocks.Large = large;

    document.getElementById("modalStock").innerText = total;

    // 🔥 disable buttons based on stock
    const buttons = document.querySelectorAll(".size-selector button");

    buttons.forEach(btn => {
        const sizeMap = {
            S: "Small",
            M: "Medium",
            L: "Large"
        };

        const size = sizeMap[btn.innerText];

        if (sizeStocks[size] <= 0) {
            btn.disabled = true;
            btn.classList.remove("active");
        } else {
            btn.disabled = false;
        }
    });

    document.getElementById("productModal").style.display = "block";
    document.querySelectorAll(".size-selector button").forEach(btn => {
    btn.classList.remove("active");
});
}

function setSize(size, el) {
    // update stock
    document.getElementById("modalStock").innerText = sizeStocks[size] ?? 0;

    // remove active from all
    document.querySelectorAll(".size-selector button").forEach(btn => {
        btn.classList.remove("active");
    });

    // add active to clicked
    el.classList.add("active");
}

function closeProductModal() {
    document.getElementById("productModal").style.display = "none";
}

// close when clicking outside
window.addEventListener("click", function(e) {
    const modal = document.getElementById("productModal");
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

function showToast(message, type="success") {
    const toast = document.getElementById("globalToast");
    toast.innerText = message;
    toast.className = `global-toast show ${type}`;

    setTimeout(() => {
        toast.classList.remove("show");
    }, 2500);
}
</script>
</body>
</html>
