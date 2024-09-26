<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/role_menus.php'; // Include RoleMenus class
include_once '../class/token.php'; // Include the Token class

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);

        $tokenClass = new Token();
        $decodedToken = $tokenClass->decodeToken($token);

        // Check if decoding was successful
        if (!isset($decodedToken['user_id'])) {
            http_response_code(401); // Unauthorized
            echo json_encode(["status" => "error", "message" => "Invalid token."]);
            exit;
        }

        // Create RoleMenus object
        $roleMenus = new RoleMenus();
        $roleMenuList = $roleMenus->getRoleMenu($decodedToken['user_id']);
        $menuList = array_column($roleMenuList, 'menu_id');
        $menuList = array_map('strval', $menuList);

        // Check if role menus were found
        if ($roleMenuList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $menuList]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["status" => "error", "message" => "No role menus found."]);
        }
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
