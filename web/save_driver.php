<?php


// Include necessary files
require_once _DIR_ . '/db_config.php';

/**
 * Database Class (Singleton Pattern)
 * Manages a single database connection instance.
 */
class Database 
{
    private static ?Database $instance = null; // Holds the singleton instance
    private mysqli $conn; // Database connection

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * Returns the singleton instance of the Database class.
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Provides access to the database connection object.
     */
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
    /**
     * Creates a JSON response with a success flag and optional message.
     */
    public static function createResponse(bool $success, string $message = ""): string {
        return json_encode([
            "success" => $success,
            "message" => $message
        ]);
    }
}

/**
 * Template Method Pattern: LoginUpdaterTemplate Class
 * Defines the skeleton of the login update process.
 */
abstract class LoginUpdaterTemplate 
{
    /**
     * Template method: Executes the login update process.
     */
    public function updateLogin($data): string {
        if ($this->validateData($data)) {
            $conn = Database::getInstance()->getConnection();
            $query = $this->prepareQuery($data);
            $result = $conn->query($query);

            return $this->processResult($result);
        } else {
            return ResponseFactory::createResponse(false, "Required fields are missing.");
        }
    }

    // Abstract methods to be implemented by subclasses
    abstract protected function validateData($data): bool;
    abstract protected function prepareQuery($data): string;
    abstract protected function processResult(bool $result): string;
}

/**
 * LoginUpdater Class: Implements the login update logic.
 */
class LoginUpdater extends LoginUpdaterTemplate 
{
    /**
     * Validates the input data.
     */
    protected function validateData($data): bool {
        return !empty($data->password) && 
               !empty($data->name) && 
               !empty($data->mobile);
    }

    /**
     * Prepares the SQL query for the login update.
     */
    protected function prepareQuery($data): string {
        // Escaping input to prevent SQL injection
        $email = $this->escape($data->email);
        $name = $this->escape($data->name);
        $mobile = $this->escape($data->mobile);
        $password = $this->escape($data->password);
        $field1 = $this->escape($data->field_1);
        $field2 = $this->escape($data->field_2);
        $field3 = $this->escape($data->field_3);
        $cus_id = (int)$data->cus_id; // Cast to integer for safety

        return sprintf(
            "UPDATE login SET email='%s', name='%s', mobile='%s', password='%s', 
            field_1='%s', field_2='%s', field_3='%s' WHERE user_id=%d",
            $email, $name, $mobile, $password, $field1, $field2, $field3, $cus_id
        );
    }

    /**
     * Processes the result of the update query.
     */
    protected function processResult(bool $result): string {
        if ($result) {
            return ResponseFactory::createResponse(true, "Update successful.");
        } else {
            return ResponseFactory::createResponse(false, "Update failed.");
        }
    }

    /**
     * Escapes input to prevent SQL injection attacks.
     */
    private function escape(string $input): string {
        $conn = Database::getInstance()->getConnection();
        return $conn->real_escape_string($input);
    }
}

/*******
 * Main Code Execution
 *******/

// Decode the JSON input from the request body
$data = json_decode(file_get_contents("php://input"));

// Check if input data is valid
if (!$data) {
    echo ResponseFactory::createResponse(false, "Invalid JSON input.");
    exit();
}

// Create an instance of LoginUpdater and execute the login update
$loginUpdater = new LoginUpdater();
$response = $loginUpdater->updateLogin($data);

// Output the JSON response
echo $response;

?>