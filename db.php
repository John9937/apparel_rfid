<?php
$host = "localhost";
$user = "username";
$pass = "password";
$db   = "apparel";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
