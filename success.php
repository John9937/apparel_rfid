<?php
session_start();
include 'db.php';

$order_id = $_GET['order_id'] ?? 0;


echo "<h2>Payment Processing...</h2>";
echo "<p>Please wait while we confirm your payment.</p>";

echo "<script>
setTimeout(function(){
    window.location.href = 'shop.php';
}, 3000);
</script>";