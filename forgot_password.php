<?php

include 'db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $LOGIN_INPUT      = mysqli_real_escape_string($conn, $_POST['login_input']);
    $NEW_PASSWORD     = $_POST['new_password'];
    $CONFIRM_PASSWORD = $_POST['confirm_password'];

    
    if ($NEW_PASSWORD !== $CONFIRM_PASSWORD) {
        echo "<script>
            alert('New password and confirmation do not match.');
            window.location.href='forgot_password.php';
        </script>";
        exit;
    }

    
    $checkSql = "
        SELECT id
        FROM registration2
        WHERE email = '$LOGIN_INPUT'
           OR username = '$LOGIN_INPUT'
        LIMIT 1
    ";

    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) === 0) {
        echo "<script>
            alert('Account not found. Please check your username or email.');
            window.location.href='forgot_password.php';
        </script>";
        exit;
    }

    
    $HASHED_NEW_PASSWORD = password_hash($NEW_PASSWORD, PASSWORD_DEFAULT);

    
    $updateSql = "
        UPDATE registration2
        SET password = '$HASHED_NEW_PASSWORD'
        WHERE email = '$LOGIN_INPUT'
           OR username = '$LOGIN_INPUT'
    ";

    if (mysqli_query($conn, $updateSql)) {
        echo "<script>
            alert('Password updated successfully! You can now log in with your new password.');
            window.location.href='login.html';
        </script>";
        exit;
    } else {
        echo "Error updating password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApparelEase | Reset Password</title>
    <link rel="stylesheet" href="Log_inStyle.css">
</head>
<body>

<div class="container">

    
    <div class="left-panel">
        <div class="brand">
            <img src="ApparelEase_Logo_LogIn.png" alt="ApparelEase Logo">
        </div>
    </div>

    
    <div class="login-card">
        <h1>Reset Password</h1>

        <form action="forgot_password.php" method="POST">

            <input
                type="text"
                name="login_input"
                placeholder="Username or Email"
                required
            >

            <input
                type="password"
                name="new_password"
                placeholder="New Password"
                minlength="5"
                maxlength="20"
                required
            >

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm New Password"
                minlength="5"
                maxlength="20"
                required
            >

            <button type="submit" class="btn-primary">
                Update Password
            </button>

            <a href="login.html" class="forgot">
                Back to Login
            </a>

        </form>
    </div>

</div>

</body>
</html>
