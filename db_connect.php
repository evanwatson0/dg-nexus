<?php
    include 'config.php';

    // FUNCTION TO RETRIEVE CONNECTION!
    // ENSURE YOU HAVE FILLED IN ALL ENTRIES BELOW
    // DEPENDENT ON HOW YOU'VE STORED THE DB ON YOUR OWN SETUP
    // $host, $user, $pass, $db

    /**
     * Function to retrieve database connection!
     * Should pass in 
     * 
     * @param mixed $host
     * @param mixed $user
     * @param mixed $password
     * @param mixed $db_name
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
