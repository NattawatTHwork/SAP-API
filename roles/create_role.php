<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/roles.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the input data
        $data = json_decode(file_get_contents('php://input'), true);

        // Define required fields
        $required_fields = ['role'];
        $missing_fields = [];

        // Check for missing fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // Extract the role data
            $role = trim($data['role']);
            
            // Instantiate the Roles class and call the createRole method
            $roles = new Roles();
            $roleId = $roles->createRole($role);

            if ($roleId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Role created successfully", "role_id" => $roleId]);
            } else {
                throw new Exception("Error creating role.");
            }
        } else {
            // Handle missing required fields
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // Handle invalid request methods
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // Handle any other errors
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
