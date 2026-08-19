<?php
// access_denied.php
?>
<!DOCTYPE html>
<html>
<head>
<title>Access Denied</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #4b2e14, #8b5a2b);
    height: 100vh;
}

/* MODAL */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-box {
    background: #fff;
    padding: 50px 60px;   /* ⬅ bigger padding */
    border-radius: 20px;
    text-align: center;
    width: 90%;
    max-width: 500px;     /* ⬅ bigger width */
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
}

.modal-box h3 {
    margin: 0;
    color: #7a3e00;
    font-size: 24px;   /* ⬅ bigger title */
    margin-bottom: 10px;

}

.modal-box p {
    font-size: 16px;   /* ⬅ bigger subtitle */
    margin-bottom: 25px;
    margin: 10px 0 20px;
    color: #444;
}

.btn {
    background: #7a3e00;
    color: white;
    padding: 12px 28px;   /* ⬅ bigger button */
    border: none;
    border-radius: 25px;
    font-size: 15px;      /* ⬅ bigger text */
    cursor: pointer;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="modal">
    <div class="modal-box">
        <h3 style="display:flex; align-items:center; justify-content:center; gap:8px;">
            ⛔ Access Denied
        </h3>
        <p>Admin login required</p>

        <button class="btn" onclick="goLogin()">Go to Login</button>
    </div>
</div>

<script>
// optional auto redirect
setTimeout(() => {
    window.location.href = "admin_login.php";
}, 4000);

function goLogin() {
    window.location.href = "admin_login.php";
}
</script>

</body>
</html>