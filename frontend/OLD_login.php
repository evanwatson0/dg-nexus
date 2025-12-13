<?php
session_start();

$emailError = '';
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // email 格式檢查
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = 'Please enter a valid email address.';
    }

    // 密碼長度檢查
    if (strlen($password) < 6) {
        $passwordError = 'Password must be at least 6 characters.';
    }

    // 兩個都 OK 才讓他登入（不用對照資料庫）
    if ($emailError === '' && $passwordError === '') {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        header('Location: user_page.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- 
    Login Page
    Author: Claire 

-->
<head>
    <meta charset="UTF-8">
    <title>DGI Explorer Login</title>
        
    <link rel="stylesheet" href="./styling/login_page.css">

</head>
<body>
    <main>
        <form id="login-form" class="card" method="POST" novalidate>
            <h1>User login</h1>
            <p class="subtitle">Log in with any email and a password of at least 6 characters.</p>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="name@example.com">
                <span id="email-error" class="error-text">
                    <?php if ($emailError) echo htmlspecialchars($emailError); ?>
                </span>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="At least 6 characters"
                >
                <span id="password-error" class="error-text">
                    <?php if ($passwordError) echo htmlspecialchars($passwordError); ?>
                </span>
            </div>

            <button type="submit">Log in</button>
            <div class="small-note">This is a demo login page for the DGI Explorer.</div>
        </form>
    </main>

    <script>
        const form = document.getElementById('login-form');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const emailErrorSpan = document.getElementById('email-error');
        const passwordErrorSpan = document.getElementById('password-error');

        form.addEventListener('submit', function (e) {
            let valid = true;

            emailErrorSpan.textContent = '';
            passwordErrorSpan.textContent = '';

            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!email) {
                emailErrorSpan.textContent = 'Email is required.';
                valid = false;
            } else if (!emailPattern.test(email)) {
                emailErrorSpan.textContent = 'Please enter a valid email address.';
                valid = false;
            }

            if (!password) {
                passwordErrorSpan.textContent = 'Password is required.';
                valid = false;
            } else if (password.length < 6) {
                passwordErrorSpan.textContent = 'Password must be at least 6 characters.';
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
