<?php
include "db.php";
$order_id = $_GET['order_id'];

$order = mysqli_query($conn, "SELECT * FROM orders WHERE id='$order_id'");
$order_data = mysqli_fetch_assoc($order);

if(isset($_POST['select_method'])){
    $method = $_POST['select_method']; 

    mysqli_query($conn, "UPDATE orders SET payment_method='$method' WHERE id='$order_id'");

    header("Location: payment.php?order_id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="payment.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<title>Select Payment Method</title>
<style>
.payment-option{
    display:inline-block;
    padding:12px 20px;
    border-radius:25px;
    background:#8b5a2b;
    color:white;
    cursor:pointer;
    margin:10px;
}
</style>
</head>
<body>
    <div class="card">

        <h2>Select Payment Method</h2>

        <form method="POST" class="payment-buttons">
            <button type="submit" name="select_method" value="gcash" class="payment-btn gcash">
                GCash
            </button>

            <button type="submit" name="select_method" value="paymaya" class="payment-btn paymaya">
                PayMaya
            </button>

            <button type="submit" name="select_method" value="gotyme" class="payment-btn gotyme">
                GoTyme
            </button>
        </form>

        <?php if(!empty($order_data['payment_method'])): ?>

            <div class="qr-section">
                <h3>Scan to Pay via <?= strtoupper($order_data['payment_method']) ?></h3>

                <?php
                $qrMap = [
                    "gcash" => "gcash_qr.jpg",
                    "paymaya" => "paymaya_qr.jpg",
                    "gotyme" => "gotyme_qr.jpg"
                ];
                ?>

                <img src="<?= $qrMap[$order_data['payment_method']] ?>">

                <form method="POST" action="mark_pending.php">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <button class="confirm-btn">I Have Paid</button>
                </form>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>