<?php
include "db.php";

$query = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");

while($row = mysqli_fetch_assoc($query)){
?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['description'] ?></td>
    <td>₱<?= number_format($row['price'],2) ?></td>
    <td><?= $row['image'] ?></td>
    <td><?= $row['rfid_uid'] ?></td>

    <td>
        <span class="status-badge <?= $row['status'] ?>">
            <?= $row['status'] ?>
        </span>
    </td>

    <td><?= $row['tag_id'] ?></td>
    <td><?= $row['category'] ?></td>

    <td>
        <a href="edit_product.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
    </td>
</tr>
<?php } ?>
