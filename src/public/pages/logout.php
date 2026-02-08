<?php
require_once __DIR__ . '/../../bootstrap.php';
// logout.php
require ROOT_PATH . 'app/auth/session_config.php';

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
