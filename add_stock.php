<?php 
include "db.php"; 

$products = mysqli_query($conn, "SELECT * FROM products"); 

$stocks = mysqli_query($conn, "
    SELECT pi.*, p.name 
    FROM product_items pi
    LEFT JOIN products p ON pi.product_id = p.id
    ORDER BY pi.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Stock</title>
    <link rel="stylesheet" href="add_stock.css">
</head>

<body>

<div class="main">

<div class="top-bar">
    <h2 class="page-title">Add Stock (RFID)</h2>
    <a href="admin.php" class="btn-back">← Back</a>
</div>

<!-- SCAN -->
<div class="card scan-card">
    <label>Scan RFID</label>

    <div class="scan-row">
        <input type="text" id="rfid_input" placeholder="Tap or scan RFID..." autofocus>

        <button id="clearRFID" class="btn-clear" onclick="resetScan()" style="display:none;">
            CLEAR
        </button>
    </div>

    <div id="toast" class="toast"></div>
</div>

<!-- ASSIGN -->
<div class="card" id="productSection" style="display:none;">
    <label>Assign Product</label>

    <div class="form-row">
        <select id="product_id">
            <?php while($p = mysqli_fetch_assoc($products)): ?>
                <option value="<?= $p['id'] ?>">
                    <?= $p['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- <input type="text" id="tag_id" placeholder="Tag ID (e.g. TAG-01)"> -->
           <select id="size">
                <option>Small</option>
                <option>Medium</option>
                <option>Large</option>
            </select>

        <button class="btn-primary" onclick="saveStock()">Save Stock</button>
    </div>
</div>

<!-- STOCK LIST -->
<div class="card">
    <div class="stock-header">
    <h3>Stock List</h3>

    <div class="stock-controls">
        <input type="text" id="searchStock" placeholder="Search RFID, product..." />

        <select id="statusFilter">
            <option value="all">All</option>
            <option value="in_stock">In Stock</option>
            <option value="out_of_stock">Out of Stock</option>
        </select>
    </div>
</div>

    <table class="stock-table">
        <thead>
            <tr>
                <th>RFID</th>
                <th>Product</th>
                <!-- <th>Tag ID</th> -->
                <th>Size</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = mysqli_fetch_assoc($stocks)): ?>
            <tr id="row-<?= $row['id'] ?>">
                <td><?= $row['rfid_uid'] ?></td>
                <td><?= $row['name'] ?></td>
                <!-- <td><?= $row['tag_id'] ?? '-' ?></td> -->
                <td><?= $row['size'] ?? '-' ?></td>
                <td>
                    <span class="badge <?= $row['status'] ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>
                    <td>
                        <?php if($row['status'] === 'out_of_stock'): ?>
                            <button 
                                class="btn-restore" 
                                onclick="markInStock(<?= $row['id'] ?>)">
                                Restock
                            </button>
                        <?php endif; ?>

                        <button 
                            class="btn-delete open-delete" 
                            data-id="<?= $row['id'] ?>">
                            Remove
                        </button>
                    </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</div>

<!-- ✅ DELETE MODAL -->
<div id="deleteModal" class="modal">
    <div class="modal-box">
        <h3>Remove Stock</h3>
        <p>Are you sure you want to remove this stock item?</p>

        <div class="modal-actions">
            <button id="cancelDelete" class="btn-cancel">Cancel</button>
            <button id="confirmDelete" class="btn-delete">Remove</button>
        </div>
    </div>
</div>

<script>

let scannedRFID = "";
let locked = false;

let currentFilter = "all";
let currentSearch = "";

let deleteId = null;

const input = document.getElementById("rfid_input");
const section = document.getElementById("productSection");
const searchInput = document.getElementById("searchStock");
const statusFilter = document.getElementById("statusFilter");

const modal = document.getElementById("deleteModal");
const confirmBtn = document.getElementById("confirmDelete");
const cancelBtn = document.getElementById("cancelDelete");

// ================= FILTER =================
function applyFilter() {
    document.querySelectorAll(".stock-table tbody tr").forEach(row => {
        const text = row.innerText.toLowerCase();
        const status = row.querySelector(".badge")?.textContent.trim();

        const matchSearch = text.includes(currentSearch);
        const matchStatus = (currentFilter === "all" || status === currentFilter);

        row.style.display = (matchSearch && matchStatus) ? "" : "none";
    });
}

searchInput.addEventListener("keyup", function() {
    currentSearch = this.value.toLowerCase();
    applyFilter();
});

statusFilter.addEventListener("change", function() {
    currentFilter = this.value;
    applyFilter();
});

// ================= AUTO FOCUS =================
setInterval(() => {
    if (locked) return;

    const active = document.activeElement;

    if (
        active.tagName === "INPUT" ||
        active.tagName === "SELECT" ||
        active.tagName === "TEXTAREA"
    ) return;

    input.focus();
}, 500);

// ================= SCAN =================
input.addEventListener("keypress", function(e){
    if (locked) return;

    if(e.key === "Enter"){
        e.preventDefault();

        const rfid = this.value.trim();
        if(!rfid) return;

        fetch("scan_rfid_admin.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "rfid_uid=" + encodeURIComponent(rfid)
        })
        .then(res => res.text())
        .then(data => {

            if(data === "EXISTS"){
                showToast("RFID already registered!", "error");
                resetScan();
            } else {
                scannedRFID = rfid;
                locked = true;

                input.value = rfid;
                input.setAttribute("readonly", true);
                input.style.background = "#eee";

                section.style.display = "block";
                document.getElementById("clearRFID").style.display = "inline-block";
            }
        });
    }
});

// ================= SAVE =================
function saveStock(){

    const product_id = document.getElementById("product_id").value;
    const productName = document.querySelector("#product_id option:checked").text;
    const size = document.getElementById("size").value;

    if(!scannedRFID){
        showToast("Scan RFID first", "error");
        return;
    }

    if(!size){
        showToast("Select size", "error");
        return;
    }

    fetch("save_stock.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `rfid_uid=${scannedRFID}&product_id=${product_id}&size=${size}`
    })
    .then(res => res.text())
    .then(data => {

        if (data.includes("SUCCESS")) {

            showToast("Stock added", "success");

            const table = document.querySelector(".stock-table tbody");

            const newRow = document.createElement("tr");

            newRow.innerHTML = `
                <td>${scannedRFID}</td>
                <td>${productName}</td>
                <td>${size}</td>
                <td><span class="badge in_stock">in_stock</span></td>
                <td>
                    <button class="btn-delete open-delete">Remove</button>
                </td>
            `;

            const deleteBtn = newRow.querySelector(".open-delete");
            deleteBtn.addEventListener("click", function(){
                showToast("Refresh to fully sync ID", "warning");
            });

            table.prepend(newRow);

            applyFilter();

            setTimeout(() => {
                resetScan();
            }, 500);

        } else {
            showToast("Error saving", "error");
        }

    });
}

// ================= RESET =================
function resetScan(){
    scannedRFID = "";
    locked = false;

    input.value = "";
    input.removeAttribute("readonly");
    input.style.background = "#fff";

    section.style.display = "none";
}

// ================= TOAST =================
function showToast(msg, type="success"){
    const t = document.getElementById("toast");
    t.textContent = msg;
    t.className = `toast show ${type}`;

    setTimeout(() => t.classList.remove("show"), 2000);
}

// ================= DELETE =================
document.querySelectorAll(".open-delete").forEach(btn => {
    btn.addEventListener("click", function(){
        deleteId = this.dataset.id;
        modal.classList.add("show");
    });
});

cancelBtn.onclick = () => modal.classList.remove("show");

modal.onclick = (e) => {
    if (e.target === modal) modal.classList.remove("show");
};

confirmBtn.onclick = () => {

    fetch("delete_stock.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "id=" + deleteId
    })
    .then(res => res.text())
    .then(data => {

        if(data.includes("SUCCESS")){
            showToast("Stock removed", "success");
            document.getElementById("row-" + deleteId)?.remove();
            applyFilter();
        } else {
            showToast("Delete failed", "error");
        }

        modal.classList.remove("show");
    });
};

// ================= RESTOCK =================
function markInStock(id) {
    fetch("mark_in_stock.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "id=" + id
    })
    .then(res => res.text())
    .then(data => {

        if (data.includes("SUCCESS")) {

            showToast("Item Restocked", "success");

            const row = document.getElementById("row-" + id);

            row.querySelector(".badge").className = "badge in_stock";
            row.querySelector(".badge").textContent = "in_stock";

            row.querySelector(".btn-restore")?.remove();

            applyFilter();
        } else {
            showToast("Update failed", "error");
        }
    });
}

</script>

</body>
</html>