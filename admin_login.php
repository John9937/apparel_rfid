<?php
session_start();
include "db.php";

$error = "";

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, 
        "SELECT * FROM admins WHERE username='$username' AND password='$password'"
    );

    if(mysqli_num_rows($query) > 0){
        $_SESSION['admin'] = $username;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid admin credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | ApparelEase</title>
    <link rel="stylesheet" href="admin_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-wrapper">

  
    <div class="login-left">
        <div class="brand">
            APPARELEASE
        </div>

        <div class="admin-side-text">
            <h1>
                Behind Every Collection<br>
                Is Smart Management.
            </h1>
            <p>
            Oversee collections, update pricing, manage stock levels,
            and ensure every piece reflects the quality and elegance
            of the ApparelEase brand.
            </p>
        </div>

    </div>

   
    <div class="login-right">
        <div class="login-box">
            <h2>ADMIN PANEL</h2>

            <?php if($error != ""): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST">

                <input type="text" 
                       name="username" 
                       placeholder="Username"
                       required>

                <input type="password" 
                       name="password" 
                       placeholder="Password"
                       required>

                <button type="submit" name="login">
                    Log In
                </button>

            </form>

            <a href="home.html" class="back-home">
                ← Back to Home
            </a>

        </div>
    </div>

</div>

</body>
</html>
