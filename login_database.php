<?php
include 'db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Form not submitted properly.");
}

$username = trim($_POST['username']);
$password = $_POST['password'];

if ($username === '' || $password === '') {
    die("Missing login fields.");
}


$usernameSafe = mysqli_real_escape_string($conn, $username);


$sql = "
    SELECT id, username, email, password
    FROM registration2
    WHERE email = '$usernameSafe'
       OR username = '$usernameSafe'
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {


    if (password_verify($password, $row['password'])) {

        $logUser = mysqli_real_escape_string($conn, $row['username']);

        mysqli_query(
            $conn,
            "INSERT INTO log_in (username)
             VALUES ('$logUser')"
        );

        echo "Login Successful!";
  

    } else {
        echo "Wrong username/email or password!";
    }

} else {
    echo "Wrong username/email or password!";
}
?>
