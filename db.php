<?php
$host = "localhost";
$user = "DB_USER";
$pass = "DB_PASSWORD";
$db   = "apparel";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
