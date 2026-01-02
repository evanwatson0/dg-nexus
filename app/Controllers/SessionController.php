<?php

namespace App\Controllers;

use mysqli;

class SessionController
{

    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }
    

    /**
     * Creates a new user session
     * @param mixed $user_identifier: username of individual with session
     * @return int Session id of user
     */
    public function createSession(?string $userIdentifier): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO llm_session (UserIdentifier) VALUES (?)'
        );
        $stmt->bind_param('s', $userIdentifier);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function nameSession(int $sessionId, string $name): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE llm_session SET SessionName = ? WHERE SessionID = ?'
        );
        $stmt->bind_param('si', $name, $sessionId);
        $stmt->execute();
    }

    public function retrieveSessionByName(string $sessionName): ?int
    {
        $stmt = $this->conn->prepare(
            'SELECT SessionID FROM llm_session WHERE SessionName = ?'
        );
        $stmt->bind_param('s', $sessionName);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return (int)$row['SessionID'];
        }

        return null;
    }

}