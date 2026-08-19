<?php
include "db.php";

$result = mysqli_query($conn, "
SELECT p.*, COUNT(pi.id) as stock
FROM products p 
LEFT JOIN product_items pi 
ON p.id = pi.product_id AND pi.status='in_stock'
GROUP BY p.id
");

while($row = mysqli_fetch_assoc($result)){
?>
<tr>
    <td><?= $row['name'] ?></td>
    <td>₱<?= number_format($row['price'],2) ?></td>
    <td><?= $row['stock'] ?></td>
    <td>
        <span class="badge <?= $row['status'] ?>">
            <?= $row['status'] ?>
        </span>
    </td>
    <td><?= $row['category'] ?></td>
    <td>
        <a href="edit_product.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
    </td>
</tr>
<?php } ?>