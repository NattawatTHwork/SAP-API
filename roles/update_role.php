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
        $required_fields = ['role_id', 'role'];
        $missing_fields = [];

        // Check for missing required fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // Extract role data from input
            $role_id = trim($data['role_id']);
            $role = trim($data['role']);
            $description = isset($data['description']) ? trim($data['description']) : '';

            // Instantiate the Roles class and call the updateRole method
            $roles = new Roles();
            $result = $roles->updateRole($role_id, $role, $description);

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Role updated successfully"]);
            } else {
                throw new Exception("Error updating role.");
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
