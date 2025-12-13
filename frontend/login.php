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

    <style>


        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Noto Sans TC, Arial;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('styling/background.jpg');
            background-repeat: no-repeat; 
            background-size: cover; 
            background-position: center;
            background-attachment: fixed;
        }

        main {
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
            color: #374151;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 9px 10px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            box-sizing: border-box;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.25);
        }

        .field {
            margin-bottom: 16px;
        }

        .error-text {
            display: block;
            margin-top: 4px;
            min-height: 16px;
            font-size: 12px;
            color: #b91c1c; /* 紅色錯誤訊息 */
        }

        button[type="submit"] {
            width: 100%;
            margin-top: 4px;
            padding: 10px 14px;
            font-size: 14px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: #111827;
            color: #ffffff;
            font-weight: 500;
        }

        button[type="submit"]:hover {
            background: #000000;
        }

        .small-note {
            margin-top: 10px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <main>
        <form id="login-form" class="card" method="POST" novalidate>
            <h1>User login</h1>
            <p class="subtitle">Log in with any email and a password of at least 6 characters.</p>

            <div class="field">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="name@example.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
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
