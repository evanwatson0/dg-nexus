<?php
    require 'config.php';

    /**
     * Retrieve a new mysqli database connection object
     * @return mysqli
     */
    function get_connection(): mysqli {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

?>
