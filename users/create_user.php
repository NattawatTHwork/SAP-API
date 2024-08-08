<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/users.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['username', 'user_password', 'firstname', 'lastname', 'role'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $username = trim($data['username']);
            $password = trim($data['user_password']);
            $firstname = trim($data['firstname']);
            $lastname = trim($data['lastname']);
            $role = trim($data['role']);

            $users = new Users();
            $userId = $users->createUser($username, password_hash($password, PASSWORD_DEFAULT), $firstname, $lastname, $role);

            if ($userId) {
                http_response_code(201);
                echo json_encode(array("status" => "success", "message" => "User created successfully", "user_id" => $userId));
            } else {
                throw new Exception("Error creating user.");
            }
        } else {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}
