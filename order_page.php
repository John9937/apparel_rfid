<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$orders = mysqli_query($conn, "
    SELECT id, total_amount, payment_status, created_at, qr_token
    FROM orders
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders | Admin</title>
    <link rel="stylesheet" href="order_page.css">

    <!-- AUTO REFRESH (REAL-TIME FEEL) -->
    <meta http-equiv="refresh" content="5">
</head>

<script>
setTimeout(() => {
    const banner = document.getElementById("successBanner");
    if (banner) {
        banner.style.opacity = "0";
        banner.style.transform = "translateY(-10px)";
        
        setTimeout(() => banner.remove(), 300);
    }
}, 2000);
</script>

<body>

<?php if(isset($_GET['success'])): ?>
    <div class="success-banner" id="successBanner">
        ✅ Order marked as paid
    </div>
<?php endif; ?>

<div class="main">

    <!-- TOP BAR -->
    <div class="top-bar">
        <h2 class="page-title">All Orders</h2>
        <a href="admin.php" class="btn btn-back">← Back to Dashboard</a>
    </div>

    <!-- TABLE CARD -->
    <div class="card">
        <div class="table-wrapper">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>QR</th>
                </tr>
            </thead>

            <tbody>
                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                <tr>

                    <!-- ORDER ID -->
                    <td>#<?= $row['id'] ?></td>

                    <!-- TOTAL -->
                    <td class="price">
                        ₱<?= number_format($row['total_amount'],2) ?>
                    </td>

                    <!-- STATUS -->
                    <td>
                        <?php $status = $row['payment_status']; ?>

                        <?php if($status == 'paid'): ?>
                            <span class="badge paid">Paid</span>

                        <?php elseif($status == 'waiting_verification'): ?>
                            <span class="badge waiting">Waiting Verification</span>

                        <?php else: ?>
                            <span class="badge pending">Pending</span>
                        <?php endif; ?>
                    </td>

                    <!-- DATE -->
                    <td class="date">
                        <?= date("M d, Y h:i A", strtotime($row['created_at'])) ?>
                    </td>

                    <!-- QR -->
                    <td>
                        <?php if(!empty($row['qr_token'])): ?>
                        <a href="order_view.php?token=<?= $row['qr_token'] ?>&from=orders" class="btn btn-primary">
                            View QR
                        </a>
                        <?php else: ?>
                        <span class="btn btn-disabled">No QR</span>
                        <?php endif; ?>
                    </td>

                </tr>
                <?php endwhile; ?>
            </tbody>

        </table>

    </div>

</div>

</body>
</html>