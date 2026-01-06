<?php

namespace App\Controllers;

use mysqli;

require ROOT_PATH . '/config/db_connect.php';

class SessionController
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = get_connection();
    }


    /**
     * Register a new user
     */
    public function register(string $email, string $password): ?int
    {


        // Prevent duplicate accounts
        if ($this->accountExists($email)) {
            return null;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO web_user (Email, Password) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $email, $hashedPassword);
        $stmt->execute();

        return $stmt->insert_id ?: null;
    }

    /**
     * Check if account already exists
     */
    public function accountExists(string $email): bool
    {


        $sql = "SELECT 1 FROM web_user WHERE Email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Validate user login
     */
    public function authenticate(string $email, string $password): ?int
    {


        $sql = "SELECT UserIdentifier, Password FROM web_user WHERE Email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['Password'])) {
                return (int) $row['UserIdentifier'];
            }
        }

        return null;
    }


}
