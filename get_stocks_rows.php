<?php
include "db.php";

$stocks = mysqli_query($conn, "
    SELECT pi.*, p.name 
    FROM product_items pi
    LEFT JOIN products p ON pi.product_id = p.id
    ORDER BY pi.id DESC
");

while($row = mysqli_fetch_assoc($stocks)):
?>

<tr id="row-<?= $row['id'] ?>">
    <td><?= $row['rfid_uid'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['tag_id'] ?? '-' ?></td>
    <td>
        <span class="badge <?= $row['status'] ?>">
            <?= $row['status'] ?>
        </span>
    </td>
    <td>
        <?php if($row['status'] === 'out_of_stock'): ?>
            <button class="btn-restore" onclick="markInStock(<?= $row['id'] ?>)">
                Restock
            </button>
        <?php endif; ?>

        <button class="btn-delete open-delete" data-id="<?= $row['id'] ?>">
            Remove
        </button>
    </td>
</tr>

<?php endwhile; ?>