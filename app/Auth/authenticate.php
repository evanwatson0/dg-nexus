<?php
// authenticate.php
// Deteremines whether active user/pass is an actual user that has been registered or not
require_once __DIR__ . '/../../bootstrap.php';
require ROOT_PATH . '/app/Controllers/LoginController.php';

use App\Controllers\LoginController;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php?error=1');
    exit;
}

$cont = new LoginController();

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$new_user = $_POST['new-user'] ?? false;

$user_id = $cont->authenticate($email, $password);


if ($new_user) {
    // new 'user' selected details 
    if ($user_id) {
        header('Location: ../../public/pages/login.php?error=1');
        exit;
    }

    $user_id = $cont->register($email, $password);
} else {
    if ($user_id) {
        header('Location: ../../public/pages/login.php?error=1');
        exit;
    }
}

// Prevent session fixation
session_regenerate_id(true);

$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;
$_SESSION['logged_in'] = true;

header('Location: ../../public/pages/user_page.php');
exit;


