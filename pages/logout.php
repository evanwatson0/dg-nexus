<?php
// logout.php
require '../authentication/session_config.php';

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
