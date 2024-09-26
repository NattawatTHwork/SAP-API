<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/roles.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['role_id'])) {
            $role_id = trim($_GET['role_id']);
            if (!empty($role_id)) {
                $roles = new Roles();
                $role = $roles->getRole($role_id);

                if ($role) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $role]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Role not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Role ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Role ID is missing."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
