<?php

use App\Controllers\SessionController;
// authenticate.php
// Deteremines whether active user/pass is an actual user that has been registered or not
require_once __DIR__ . '/../bootstrap.php';
require ROOT_PATH . 'app/Auth/session_config.php';
require ROOT_PATH . '/db_connect.php';

$cont = new SessionController(get_connection());

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$new_user = $_POST['new-user'] ?? false;
$user_id = $cont->authenticate($email, $password);


if ($new_user) {
    // new 'user' selected details 
    if ($user_id) {
        header('Location: ../pages/login.php?error=1');
        exit;
    }

    $user_id = $cont->register($email, $password);
} else {
    if (!$user_id) {
        header('Location: ../pages/login.php?error=1');
        exit;
    }
}

// Prevent session fixation
session_regenerate_id(true);

$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;
$_SESSION['logged_in'] = true;

header('Location: ../pages/user_page.php');
exit;


