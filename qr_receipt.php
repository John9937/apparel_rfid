<?php
session_start();
include "db.php";

$order_id = $_GET['order_id'];

$order = mysqli_query($conn, "SELECT * FROM orders WHERE id='$order_id'");

if (mysqli_num_rows($order) == 0) {
    die("No paid order found.");
}

$order_data = mysqli_fetch_assoc($order);

if($order_data['claimed'] == 1){
   
    $_SESSION['budget'] = 0;
    echo "<script>alert('Order verified! Redirecting to shop...');
            window.location.href='index.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>

<meta http-equiv="refresh" content="3">

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
    overflow: hidden;
}



.card {
    position: relative;
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
    font-size: 28px;
}

.order-info {
    margin-bottom: 25px;
    color: #444;
}

.order-info p {
    margin: 5px 0;
    font-weight: 500;
}

.qr-box {
    margin: 20px 0;
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
</style>
</head>

<body>

<div class="card">
    <h2>Payment Successful</h2>

    <div class="order-info">
        <p><strong>Order #<?php echo $order_data['id']; ?></strong></p>
        <p>Total: ₱<?php echo $order_data['total_amount']; ?></p>
    </div>

    <div class="qr-box">
        <img src="<?php echo $order_data['qr_code_path']; ?>">
    </div>

    <div class="note">
        Please show this QR to the counter for verification.
    </div>
</div>

</body>
</html>