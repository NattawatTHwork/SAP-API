<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/roles.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode the incoming JSON request
        $data = json_decode(file_get_contents('php://input'), true);

        // Required field for deleting a role
        $required_fields = ['role_id'];
        $missing_fields = [];

        // Check for missing required fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // Proceed if there are no missing fields
        if (empty($missing_fields)) {
            // Instantiate the Roles class and delete the role by role_id
            $roles = new Roles();
            $result = $roles->deleteRole(trim($data['role_id']));

            // Check if deletion was successful
            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Role deleted successfully"]);
            } else {
                throw new Exception("Error deleting role.");
            }
        } else {
            // Throw an error if required fields are missing
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // Handle invalid request method
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
