<?php
session_start();
include "db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$orders = mysqli_query($conn, "
    SELECT id, total_amount, payment_status, created_at, qr_token, claimed
    FROM orders
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Orders | Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body style="background:#F5F3EF; padding:60px;">

<div class="admin-wrapper">

    <div class="admin-header">
        <h1>All Orders</h1>
        <a href="admin.php" class="orders-btn">← Back</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>QR</th>
                    <th>Claimed</th>
                </tr>
            </thead>

            <tbody>
                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>₱<?= number_format($row['total_amount'],2) ?></td>
                    <td><?= $row['payment_status'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <?php if($row['qr_token']): ?>
                            <a href="order_view.php?token=<?= $row['qr_token'] ?>" 
                               class="edit-btn">View</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['claimed'] == 1): ?>
                            <span class="status-badge in_stock">Scanned</span>
                        <?php else: ?>
                            <span class="status-badge out_of_stock">Not Scanned</span>
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