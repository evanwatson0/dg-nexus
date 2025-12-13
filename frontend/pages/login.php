<?php
// login.php
require '../authentication/session_config.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ./user_page.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../styling/login_page.css">

</head>
<body>




    <form id="login-form" class="card" method="post" onsubmit="checkAll()" action="../authentication/authenticate.php">
        <h1>Login</h1>
        <p class="subtitle">Log in with any email and a password of at least 6 characters.</p>
    
        <!-- User Email Section -->
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="name@example.com" oninput="checkEmail()">
            <span class="error-text" id="email-error"></span>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="At least 6 chars" oninput="checkPass()">
            <span class="error-text" id="password-error"></span>

        </div>

        <div class="field">
            <label for="new-user">Create New User? (Yes/No)</label>
            <input type="checkbox" id="new-user" name="new-user">
        </div>

        <button type="submit">Login</button>
    </form>


    <script>
        
        function checkEmail() {
            const first = document.getElementById('email').value;
            const error = document.getElementById('email-error');

            const pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,4}$/;
            if (pattern.test(first)) {
                error.textContent = "";
                return true;
            } else {
                error.textContent = "fix email format!";
                return false;
            }
        }

        function checkPass() {
            const pass = document.getElementById('password').value;
            const error = document.getElementById('password-error');

            const pattern = /^.{6}/;
            if (pattern.test(pass)) {
                error.textContent = "";
                return true;
            } else {
                error.textContent = "At least 6 chars for password!";
                return false;
            }
        }


        function checkAll() {
            if (checkPass() && checkEmail()) {
                return true;
            } else {
                alert("Please fix errors in the form before submitting.");
                return false;
            }

        }
    </script>
</body>
</html>
