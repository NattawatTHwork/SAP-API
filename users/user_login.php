<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../class/users.php';
include_once '../class/token.php';
include_once '../class/role_menus.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['username', 'password', 'sysid'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $username = trim($data['username']);
            $password = trim($data['password']);
            $sysid = trim($data['sysid']);

            $users = new Users();
            
            // Attempt login
            $loginResult = $users->login($username, $password, $sysid);

            if ($loginResult['status'] === 'success') {
                $tokenClass = new Token();
                $token = $tokenClass->generateToken([
                    'user_id' => $loginResult['user_id'],
                    'username' => $loginResult['username'],
                    'sysid' => $loginResult['sysid']
                ]);

                http_response_code(200);
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Login successful",
                    "token" => $token
                ));
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode(array("status" => $loginResult['status'], "message" => $loginResult['message']));
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(array("status" => "error", "message" => "Missing required fields: " . implode(', ', $missing_fields)));
        }
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("status" => "error", "message" => "Method not allowed"));
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}
?>
