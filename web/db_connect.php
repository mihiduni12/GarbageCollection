<?php

// Database configuration (you can move these constants to a separate config file if needed)
define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'your_database_name');

class Database {
    // Hold the class instance.
    private static $instance = null;
    private $conn;
    
    // Database configuration variables
    private $host = DB_SERVER;
    private $user = DB_USER;
    private $pass = DB_PASSWORD;
    private $dbname = DB_DATABASE;

    // Private constructor to prevent direct creation of object
    private function __construct() {
        // Create a new mysqli connection
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        // Check for connection errors
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Static method to get the instance of the Database class
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Method to get the database connection
    public function getConnection() {
        return $this->conn;
    }
}

// Usage example
$db = Database::getInstance(); // Get the singleton instance
$conn = $db->getConnection();   // Get the connection

// Optional: You can now use $conn to execute queries, e.g.
// $result = $conn->query("SELECT * FROM users");

?>

