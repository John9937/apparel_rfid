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
    <style>
        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.paid {
            background-color: #16a34a;
            color: white;
        }

        .status-badge.waiting {
            background-color: #f59e0b;
            color: white;
        }
</style>
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
                </tr>
            </thead>

            <tbody>
                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>₱<?= number_format($row['total_amount'],2) ?></td>

                    <td>
                    <?php if($row['payment_status'] == 'paid'): ?>
                        <span class="status-badge paid">Paid</span>
                    <?php elseif($row['payment_status'] == 'waiting_verification'): ?>
                        <span class="status-badge waiting">Waiting Verification</span>
                    <?php else: ?>
                        <span class="status-badge"><?= $row['payment_status'] ?></span>
                    <?php endif; ?>
                    </td>

                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <?php if($row['qr_token']): ?>
                            <a href="order_view.php?token=<?= $row['qr_token'] ?>" 
                               class="edit-btn">View</a>
                        <?php else: ?>
                            —
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