<?php
// auth_check.php
require ROOT_PATH . '/app/Auth/session_config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
