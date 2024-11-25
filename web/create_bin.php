<?php
/*********************
**** CPanel ******************
*********/

/* Following register will admin login credentials */

// array for JSON response
$response = array();

// include db connect class
require_once __DIR__ . '/db_connect.php';

// Factory class to create the response
class ResponseFactory {
    public static function createResponse($successCode) {
        $response = array();
        $response["success"] = $successCode;
        return $response;
    }
}

// Class to handle database-related tasks
class DatabaseHandler {
    private $conn;

    // Constructor to initialize the DB connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function insertGarbage($data) {
        $get_email = $data->email;
        $get_field_1 = $data->field_1;
        $get_field_2 = $data->field_2;
        $get_field_3 = $data->field_3;
        $get_field_4 = $data->field_4;
        $get_field_5 = $data->field_5;
        $get_field_6 = $data->field_6;
        $get_field_7 = $data->field_7;
        $get_field_8 = $data->field_8;
        $get_field_9 = 'Pending';
        $get_created_date = date('Y-m-d');

        if (empty($get_field_1) || empty($get_field_2) || empty($get_field_3) ||
            empty($get_field_4) || empty($get_field_5) || empty($get_field_6) || empty($get_field_7)) {
            return false;
        }

        $query = "INSERT INTO garbage (email, field_1, field_2, field_3, field_4, field_5, field_6, field_7, field_8, field_9, created_date)
                  VALUES ('$get_email', '$get_field_1', '$get_field_2', '$get_field_3', '$get_field_4', '$get_field_5', '$get_field_6', '$get_field_7', '$get_field_8', '$get_field_9', '$get_created_date')";
        
        return mysqli_query($this->conn, $query);
    }
}

// Get the input data
$data = json_decode(file_get_contents("php://input"));

// Create a DatabaseHandler object
$databaseHandler = new DatabaseHandler($conn);

// Check if the data is inserted successfully
if ($databaseHandler->insertGarbage($data)) {
    // Use the factory to create the success response
    $response = ResponseFactory::createResponse(1);
} else {
    // Use the factory to create the failure response
    $response = ResponseFactory::createResponse(2);
}

// Output the JSON response
echo json_encode($response);
?>
