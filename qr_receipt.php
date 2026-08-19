<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    die("Invalid order ID.");
}

$order = mysqli_query($conn, "SELECT * FROM orders WHERE id='$order_id'");

if (!$order) {
    die("Query error: " . mysqli_error($conn));
}

if (mysqli_num_rows($order) == 0) {
    die("Order not found. (ID: $order_id)");
}

$order_data = mysqli_fetch_assoc($order);
?>
<!DOCTYPE html>
<html>
<head>
<title>Payment Successful</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #4b2e14, #8b5a2b);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.card {
    background: rgba(255,255,255,0.95);
    padding: 50px;
    border-radius: 25px;
    width: 90%;
    max-width: 550px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.card h2 {
    color: #7a3e00;
    margin-bottom: 10px;
}

.order-info {
    margin-bottom: 25px;
    color: #444;
}

.qr-box img {
    width: 220px;
    border-radius: 15px;
    padding: 15px;
    background: #f9f6f2;
}

.note {
    margin-top: 20px;
    font-weight: 500;
    color: #7a3e00;
}

/* MODAL */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-box {
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    text-align: center;
}
</style>
</head>

<body>

<div class="card">
    <h2>Payment Verification</h2>

    <div class="order-info">
        <p><strong>Order #<?= $order_data['id']; ?></strong></p>
        <p>Total: ₱<?= $order_data['total_amount']; ?></p>
    </div>

    <div class="qr-box">
        <img src="<?= !empty($order_data['qr_code_path']) ? $order_data['qr_code_path'] : 'no-image.png'; ?>">
    </div>

    <div class="note">
        Please show this QR to the counter for verification.
    </div>
</div>

<!-- MODAL -->
<div id="paymentModal" class="modal">
    <div class="modal-box">
        <h3>✅ Payment Confirmed</h3>
        <p>Redirecting to home...</p>
    </div>
</div>

<!-- ✅ FIXED SCRIPT (ONLY ONE, AT BOTTOM) -->
<script>
let alreadyPaid = false;

function checkPayment() {
    if (alreadyPaid) return;

    fetch("check_payment.php?order_id=<?= $order_id ?>")
    .then(res => res.json())
    .then(data => {
        console.log("STATUS:", data.status);

        if (data.status === "paid") {
            alreadyPaid = true;

            document.getElementById("paymentModal").classList.add("show");

            setTimeout(() => {
                window.location.href = "index.php";
            }, 2000);
        }
    })
    .catch(err => console.error(err));
}

// start after page loads
window.onload = function () {
    setInterval(checkPayment, 2000);
};
</script>

</body>
</html>