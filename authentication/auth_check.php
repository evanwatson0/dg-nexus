<?php
// auth_check.php
require ROOT_PATH . '/authentication/session_config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
