<?php
include 'db.php';

$res = mysqli_query($conn, "SELECT last_error FROM settings WHERE id=1");
$row = mysqli_fetch_assoc($res);

if (!empty($row['last_error'])) {
    echo $row['last_error'];
    mysqli_query($conn, "UPDATE settings SET last_error=NULL WHERE id=1");
}
