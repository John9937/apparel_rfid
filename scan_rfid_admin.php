<?php
include "db.php";

$rfid = $_POST['rfid_uid'];

$check = mysqli_query($conn, "SELECT * FROM product_items WHERE rfid_uid='$rfid'");

if(mysqli_num_rows($check) > 0){
    echo "EXISTS";
} else {
    echo "NEW";
}
?>