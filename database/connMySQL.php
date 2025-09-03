<?php

/**
 * connMySQL Class
 * Handles the basic connection to the MySQL database using MySQLi.
 * This class will be extended by other table-specific classes.
 */
class connMySQL
{
    // --- Database Credentials ---
    private $servername = "localhost"; // SERVER NAME
    private $username = "u627446752_db_staking";       // USERNAME
    private $password = "HsnP52s1&g";           // PASSWORD
    private $dbname = 'u627446752_db_staking';
    
    // Store the connection object to allow reuse within a single request, if desired.
    // However, for the pattern used in the child classes (getting a new connection for each method),
    // this specific property might not be strictly necessary, but it's a good practice
    // if you want to implement a singleton connection later.
    // private $conn; 

    /**
     * METHOD TO ESTABLISH A DATABASE CONNECTION
     * Each call to this method will return a new mysqli connection instance.
     * This ensures that each operation in child classes gets a fresh connection,
     * which they then close after their work is done.
     *
     * @return mysqli A new mysqli connection object.
     * @throws mysqli_sql_exception If connection fails.
     */
    protected function dbConn() {
        // SET CONNECTION
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exceptions for MySQLi errors
        try {
            $connect = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
            // Optional: Set character set for proper encoding
            // $connect->set_charset("utf8mb4"); 
            return $connect;
        } catch (mysqli_sql_exception $e) {
            // Log the error and re-throw, or handle it gracefully
            error_log("Database Connection Error: " . $e->getMessage());
            throw new mysqli_sql_exception("Could not connect to the database. Please check configuration.", $e->getCode(), $e);
        }
    }
    
    /**
     * METHOD TO CHECK IF A TABLE EXISTS IN THE DATABASE
     * This method now gets a connection and closes it internally,
     * as it's a standalone check. Child classes will get their own connection
     * for their operations.
     *
     * @param string|null $tableName The name of the table to check.
     * @return int The number of rows (0 if table doesn't exist, >0 if it exists).
     */
    protected function checkTable(?string $tableName = null) {
        if (is_null($tableName)) {
            error_log("checkTable method called without a table name.");
            return 0;
        }

        $conn = null;
        try {
            $conn = $this->dbConn(); // Get a new connection for this specific check
            // QUERY TO CHECK TABLE
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                error_log("Prepare failed in checkTable: (" . $conn->errno . ") " . $conn->error);
                return 0;
            }
            
            $stmt->bind_param("s", $tableName);
            $stmt->execute();
            $result = $stmt->get_result();
            $num_rows = $result ? $result->num_rows : 0;
            $stmt->close();
            return $num_rows;
        } catch (mysqli_sql_exception $e) {
            error_log("Error checking table '$tableName': " . $e->getMessage());
            return 0;
        } finally {
            if ($conn) {
                $conn->close(); // Close the connection after the check
            }
        }
    }
}
?>
