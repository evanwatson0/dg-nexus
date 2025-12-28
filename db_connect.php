<?php
    // 
    // Database Constants
    //
    // ENSURE YOU HAVE FILLED IN ALL ENTRIES BELOW TO ACCESS DB VIA PHP
    // DEPENDENT ON HOW YOU'VE STORED THE DB ON YOUR OWN SETUP
    // $host, $user, $pass, $db

    define("DB_HOST", "localhost");
    define("DB_NAME", "druggene_db");
    define("DB_USER", "root");
    define("DB_PASS", "12345678");

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
