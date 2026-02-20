<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $FIRST_NAME = mysqli_real_escape_string($conn, $_POST['first_name']);
    $LAST_NAME  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $EMAIL      = mysqli_real_escape_string($conn, $_POST['email']);
    $USERNAME   = mysqli_real_escape_string($conn, $_POST['username']);
    $PASSWORD   = $_POST['password'];

    $checkSql = "
        SELECT id
        FROM registration2
        WHERE username = '$USERNAME'
        LIMIT 1
    ";

    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>
            alert('Username already exists!');
            window.location.href='registration.php';
        </script>";
        exit;
    }

    $HASHED_PASSWORD = password_hash($PASSWORD, PASSWORD_DEFAULT);

    $insertSql = "
        INSERT INTO registration2
        (first_name, last_name, email, username, password)
        VALUES
        ('$FIRST_NAME', '$LAST_NAME', '$EMAIL', '$USERNAME', '$HASHED_PASSWORD')
    ";

    if (mysqli_query($conn, $insertSql)) {
        header("Location: home.html");
        exit;
    } else {
        echo "Registration failed.";
    }
}
?>
