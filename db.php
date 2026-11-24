
<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');

if (!$host || !$user || !$pass || !$db) {
    error_log("Database configuration is incomplete.");
    http_response_code(500);
    exit("Application configuration error.");
}

try {

    $conn = new mysqli($host, $user, $pass, $db);

    // Set secure character encoding
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    // Log details privately
    error_log("Database connection error: " . $e->getMessage());

    // Show generic message to users
    http_response_code(500);
    exit("Unable to connect to the database.");

}
?>
