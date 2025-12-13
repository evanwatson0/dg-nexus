<?php
// authenticate.php
// Deteremines whether active user/pass is an actual user that has been registered or not

require 'session_config.php';
require '../../db_connect.php';


$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$new_user = $_POST['new-user'] ?? false;


echo "$new_user";
if ($new_user) {
    $user_id = add_new_user($email, $password);
} else {
    $user_id = check_valid_user($email, $password);

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


function add_new_user($email, $password) {
    $conn = get_connection();
    $sql = "INSERT INTO web_user (Email, Password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    
    $ret_id = $stmt->insert_id;
    if (!$ret_id) {
      die("Error: Could not retrieve new InteractionID.");
    }

    return $ret_id;
}

function check_valid_user($email, $password) {
    $conn = get_connection();
    $sql = "SELECT UserIdentifier FROM web_user where Email = ? AND Password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // if no matches
    if ($result->num_rows == 0) {
      return null;
    }

    while ($row = $result->fetch_assoc()) {
        return $row["UserIdentifier"];
    }
}