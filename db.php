<?php
$host = "localhost";
$user = "apparel_user";
$pass = "@Johnuser1202";
$db   = "apparel";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
