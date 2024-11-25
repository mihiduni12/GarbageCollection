<?php


// Include necessary files
require_once _DIR_ . '/db_config.php';

/** 
 * Database Class (Singleton Pattern)
 * Manages a single database connection across the application.
 */
class Database 
{
    private static ?Database $instance = null;
    private mysqli $conn;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

        // Check for connection errors and handle gracefully
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    // Returns the singleton instance of the Database class
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Provides access to the database connection object
    public function getConnection(): mysqli {
        return $this->conn;
    }
}

/**
 * ResponseFactory Class (Factory Pattern)
 * Generates consistent JSON responses for the API.
 */
class ResponseFactory 
{
    // Creates a JSON response with a success flag and optional message
    public static function createResponse(bool $success, string $message = ""): string {
        return json_encode([
            "success" => $success,
            "message" => $message
        ]);
    }
}

/**
 * UserRepository Class (Repository Pattern)
 * Handles all database operations related to the User entity.
 */
class UserRepository 
{
    private mysqli $conn;

    // Initializes the UserRepository with the singleton database connection
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Deletes a user by ID, returns true on success and false on failure
    public function deleteUserById(int $userId): bool {
        $stmt = $this->conn->prepare("DELETE FROM login WHERE user_id = ?");
        if (!$stmt) {
            return false; // Handle failed statement preparation
        }
        
        $stmt->bind_param("i", $userId);  // Bind parameter to prevent SQL injection
        $result = $stmt->execute();
        $stmt->close();  // Always close statements to release resources
        return $result;
    }
}

/********
 * Main Execution Logic
 ********/

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"));
$userId = isset($data->id) ? (int)$data->id : null;

// Validate input: Check if user ID is provided and valid
if ($userId === null || $userId <= 0) {
    echo ResponseFactory::createResponse(false, "Invalid or missing User ID.");
    exit();
}

// Create an instance of UserRepository to delete the user
$userRepo = new UserRepository();
$isDeleted = $userRepo->deleteUserById($userId);

// Generate the appropriate JSON response based on the result
if ($isDeleted) {
    echo ResponseFactory::createResponse(true, "User deleted successfully.");
} else {
    echo ResponseFactory::createResponse(false, "Failed to delete user.");
}

?>